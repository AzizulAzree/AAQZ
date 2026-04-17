<?php

namespace App\Services;

use App\Models\Bpp;

class BppQuotationExtractionParser
{
    public function formatVersion(): string
    {
        return (string) config('bpp.quotation_extraction.format_version', 'QUOTATION_EXTRACTION_V1');
    }

    public function prompt(): string
    {
        return (string) config('bpp.quotation_extraction.prompt', '');
    }

    public function parse(string $text): array
    {
        $lines = $this->normalizedLines($text);

        if ($lines === []) {
            return $this->failure(['Paste content is required.']);
        }

        $errors = [];

        if (array_shift($lines) !== $this->formatVersion()) {
            return $this->failure([
                'Unsupported extraction format. Only '.$this->formatVersion().' is accepted.',
            ]);
        }

        $procurementCategory = $this->parseKeyValue($lines, 'PROCUREMENT_CATEGORY', $errors);
        $selectedSupplier = $this->parseKeyValue($lines, 'SELECTED_SUPPLIER', $errors);
        $selectionReason = $this->parseKeyValue($lines, 'SELECTION_REASON', $errors);
        $selectionReasonLainLain = $this->parseKeyValue($lines, 'SELECTION_REASON_LAIN_LAIN', $errors, false);

        $suppliers = $this->parseSectionTable(
            $lines,
            'SUPPLIERS:',
            'supplier_name|total_price|delivery_period|validity_period|quotation_reference',
            5,
            $errors,
            function (array $parts, int $index): array {
                $supplierName = trim($parts[0]);
                $totalPrice = $this->normalizedNumber($parts[1]);

                if ($supplierName === '') {
                    throw new \RuntimeException('Supplier row '.($index + 1).' is missing supplier_name.');
                }

                if ($totalPrice === null) {
                    throw new \RuntimeException('Supplier row '.($index + 1).' has an invalid total_price.');
                }

                return [
                    'supplier_name' => $supplierName,
                    'total_price' => $this->formattedNumber($totalPrice),
                    'delivery_period' => trim($parts[2]),
                    'validity_period' => trim($parts[3]),
                    'quotation_reference' => trim($parts[4]) !== '' ? trim($parts[4]) : null,
                ];
            }
        );

        $appendixRows = $this->parseSectionTable(
            $lines,
            'SELECTED_SUPPLIER_ITEMS:',
            'item_spesifikasi|kuantiti|unit_ukuran|harga_seunit|jumlah_harga',
            5,
            $errors,
            function (array $parts, int $index): array {
                $itemSpesifikasi = trim($parts[0]);
                $kuantiti = $this->normalizedNumber($parts[1]);
                $hargaSeunit = $this->normalizedNumber($parts[3]);
                $jumlahHarga = $this->normalizedNumber($parts[4]);

                if ($itemSpesifikasi === '') {
                    throw new \RuntimeException('Item row '.($index + 1).' is missing item_spesifikasi.');
                }

                if ($kuantiti === null || $kuantiti <= 0) {
                    throw new \RuntimeException('Item row '.($index + 1).' has an invalid kuantiti.');
                }

                if (trim($parts[2]) === '') {
                    throw new \RuntimeException('Item row '.($index + 1).' is missing unit_ukuran.');
                }

                if ($hargaSeunit === null) {
                    throw new \RuntimeException('Item row '.($index + 1).' has an invalid harga_seunit.');
                }

                if ($jumlahHarga === null) {
                    throw new \RuntimeException('Item row '.($index + 1).' has an invalid jumlah_harga.');
                }

                if (round($kuantiti * $hargaSeunit, 2) !== round($jumlahHarga, 2)) {
                    throw new \RuntimeException('Item row '.($index + 1).' has an inconsistent jumlah_harga.');
                }

                return [
                    'line_number' => $index + 1,
                    'item_spesifikasi' => $itemSpesifikasi,
                    'kuantiti' => $this->formattedNumber($kuantiti),
                    'unit_ukuran' => trim($parts[2]),
                    'harga_seunit' => $this->formattedNumber($hargaSeunit),
                    'jumlah_harga' => $this->formattedNumber($jumlahHarga),
                ];
            }
        );

        $totals = $this->parseTotals($lines, $errors);

        if ($lines !== []) {
            $errors[] = 'Unexpected extra content was found after the TOTALS section.';
        }

        $appendixType = Bpp::appendixTypeForCategory($procurementCategory);

        if ($appendixType === null) {
            $errors[] = 'PROCUREMENT_CATEGORY must be Bekalan, Perkhidmatan, or Kerja.';
        }

        $reasonOptions = (new Bpp())->selectionReasonOptions();

        if (! in_array($selectionReason, $reasonOptions, true)) {
            $errors[] = 'SELECTION_REASON is not supported.';
        }

        if ($selectionReason !== 'Lain-lain' && $selectionReasonLainLain !== '') {
            $errors[] = 'SELECTION_REASON_LAIN_LAIN must be blank unless SELECTION_REASON is Lain-lain.';
        }

        if ($selectionReason === 'Lain-lain' && $selectionReasonLainLain === '') {
            $errors[] = 'SELECTION_REASON_LAIN_LAIN is required when SELECTION_REASON is Lain-lain.';
        }

        if ($suppliers === []) {
            $errors[] = 'At least one supplier row is required.';
        }

        if ($appendixRows === []) {
            $errors[] = 'At least one selected supplier item row is required.';
        }

        $selectedSupplierRow = collect($suppliers)->firstWhere('supplier_name', $selectedSupplier);

        if ($selectedSupplierRow === null) {
            $errors[] = 'SELECTED_SUPPLIER must match one supplier_name in the SUPPLIERS section.';
        }

        $supplierNames = collect($suppliers)->pluck('supplier_name');

        if ($supplierNames->count() !== $supplierNames->unique()->count()) {
            $errors[] = 'Supplier names must be unique in the SUPPLIERS section.';
        }

        $calculatedTotal = array_reduce(
            $appendixRows,
            fn (float $carry, array $row): float => $carry + (float) $row['jumlah_harga'],
            0.0
        );

        if (round($calculatedTotal, 2) !== round((float) ($totals['appendix_total'] ?? 0), 2)) {
            $errors[] = 'appendix_total does not match the sum of SELECTED_SUPPLIER_ITEMS.jumlah_harga.';
        }

        if (round($calculatedTotal, 2) !== round((float) ($totals['selected_supplier_total'] ?? 0), 2)) {
            $errors[] = 'selected_supplier_total does not match the sum of SELECTED_SUPPLIER_ITEMS.jumlah_harga.';
        }

        if ($selectedSupplierRow !== null
            && round((float) $selectedSupplierRow['total_price'], 2) !== round((float) ($totals['selected_supplier_total'] ?? 0), 2)) {
            $errors[] = 'The selected supplier total_price does not match selected_supplier_total.';
        }

        if ($errors !== []) {
            return $this->failure($errors);
        }

        $warnings = [];

        foreach ($suppliers as $supplier) {
            if ($supplier['quotation_reference'] === null) {
                $warnings[] = 'Quotation reference is missing for '.$supplier['supplier_name'].'.';
            }
        }

        return [
            'valid' => true,
            'errors' => [],
            'warnings' => $warnings,
            'data' => [
                'format_version' => $this->formatVersion(),
                'procurement_category' => $procurementCategory,
                'appendix_type' => $appendixType,
                'appendix_label' => Bpp::appendixLabelForType($appendixType),
                'selected_supplier' => $selectedSupplier,
                'selection_reason' => $selectionReason,
                'selection_reason_lain_lain' => $selectionReasonLainLain !== '' ? $selectionReasonLainLain : null,
                'suppliers' => array_map(
                    fn (array $supplier): array => [
                        ...$supplier,
                        'is_selected' => $supplier['supplier_name'] === $selectedSupplier,
                    ],
                    $suppliers
                ),
                'appendix_rows' => $appendixRows,
                'totals' => [
                    'appendix_total' => $this->formattedNumber((float) $totals['appendix_total']),
                    'selected_supplier_total' => $this->formattedNumber((float) $totals['selected_supplier_total']),
                ],
            ],
        ];
    }

    private function failure(array $errors): array
    {
        return [
            'valid' => false,
            'errors' => $errors,
            'warnings' => [],
            'data' => null,
        ];
    }

    private function normalizedLines(string $text): array
    {
        return array_values(array_filter(
            array_map(
                static fn (string $line): string => trim($line),
                preg_split('/\r\n|\r|\n/', trim($text)) ?: []
            ),
            static fn (string $line): bool => $line !== ''
        ));
    }

    private function parseKeyValue(array &$lines, string $key, array &$errors, bool $required = true): string
    {
        $line = array_shift($lines);

        if ($line === null) {
            if ($required) {
                $errors[] = $key.' is missing.';
            }

            return '';
        }

        $prefix = $key.':';

        if (! str_starts_with($line, $prefix)) {
            $errors[] = 'Expected '.$prefix;

            return '';
        }

        return trim(substr($line, strlen($prefix)));
    }

    private function parseSectionTable(
        array &$lines,
        string $sectionHeader,
        string $expectedHeaderRow,
        int $expectedColumns,
        array &$errors,
        callable $mapper
    ): array {
        $rows = [];
        $line = array_shift($lines);

        if ($line !== $sectionHeader) {
            $errors[] = 'Expected '.$sectionHeader;

            return [];
        }

        $headerRow = array_shift($lines);

        if ($headerRow !== $expectedHeaderRow) {
            $errors[] = 'Expected header row "'.$expectedHeaderRow.'" after '.$sectionHeader;

            return [];
        }

        while ($lines !== [] && ! str_ends_with($lines[0], ':')) {
            $rowLine = array_shift($lines);
            $parts = array_map('trim', explode('|', (string) $rowLine));

            if (count($parts) !== $expectedColumns) {
                $errors[] = 'Malformed row found in '.$sectionHeader;

                continue;
            }

            try {
                $rows[] = $mapper($parts, count($rows));
            } catch (\RuntimeException $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        return $rows;
    }

    private function parseTotals(array &$lines, array &$errors): array
    {
        $line = array_shift($lines);

        if ($line !== 'TOTALS:') {
            $errors[] = 'Expected TOTALS:';

            return [];
        }

        $totals = [];

        foreach (['appendix_total', 'selected_supplier_total'] as $requiredKey) {
            $row = array_shift($lines);

            if ($row === null) {
                $errors[] = 'Missing total row for '.$requiredKey.'.';

                continue;
            }

            $parts = array_map('trim', explode('|', $row));

            if (count($parts) !== 2) {
                $errors[] = 'Malformed totals row for '.$requiredKey.'.';

                continue;
            }

            [$key, $value] = $parts;

            if ($key !== $requiredKey) {
                $errors[] = 'Expected totals row for '.$requiredKey.'.';

                continue;
            }

            $number = $this->normalizedNumber($value);

            if ($number === null) {
                $errors[] = 'Invalid numeric value for '.$requiredKey.'.';

                continue;
            }

            $totals[$key] = $number;
        }

        return $totals;
    }

    private function normalizedNumber(string $value): ?float
    {
        $normalized = str_replace(',', '', trim($value));

        if ($normalized === '' || ! is_numeric($normalized)) {
            return null;
        }

        return round((float) $normalized, 2);
    }

    private function formattedNumber(float $value): string
    {
        return number_format($value, 2, '.', '');
    }
}
