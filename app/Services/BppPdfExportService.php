<?php

namespace App\Services;

use App\Models\Bpp;
use App\Models\BppSupplierQuote;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use RuntimeException;
use setasign\Fpdi\Fpdi;

class BppPdfExportService
{
    private const TEMPLATE_PATH = 'app/templates/bpp-template.pdf';

    /**
     * @var array<int, array<string, mixed>>
     */
    private array $coordinateMap = [
        3 => [
            'masks' => [
                ['x' => 48.2, 'y' => 36.4, 'w' => 99, 'h' => 6],
                ['x' => 42.2, 'y' => 85.3, 'w' => 62.5, 'h' => 5.4],
                ['x' => 133, 'y' => 85.3, 'w' => 61.5, 'h' => 5.4],
                ['x' => 42.2, 'y' => 91.9, 'w' => 62.5, 'h' => 5.4],
                ['x' => 133, 'y' => 91.9, 'w' => 61.5, 'h' => 5.4],
                ['x' => 44.2, 'y' => 103.1, 'w' => 150.2, 'h' => 12.4],
                ['x' => 40.8, 'y' => 114.2, 'w' => 12.2, 'h' => 5.5],
                ['x' => 61.9, 'y' => 114.2, 'w' => 15.8, 'h' => 5.5],
                ['x' => 87.1, 'y' => 114.2, 'w' => 9.8, 'h' => 5.5],
                ['x' => 48.1, 'y' => 120.2, 'w' => 27.8, 'h' => 5.8],
                ['x' => 104.8, 'y' => 120.2, 'w' => 27.8, 'h' => 5.8],
                ['x' => 166.7, 'y' => 120.2, 'w' => 27.8, 'h' => 5.8],
                ['x' => 61.1, 'y' => 127.8, 'w' => 133.3, 'h' => 13.4],
                ['x' => 61.1, 'y' => 140.4, 'w' => 133.3, 'h' => 10.8],
                ['x' => 54.1, 'y' => 152.1, 'w' => 40, 'h' => 6.5],
                ['x' => 129.8, 'y' => 152.1, 'w' => 64.6, 'h' => 6.5],
                ['x' => 114.7, 'y' => 219.7, 'w' => 65.4, 'h' => 5.6],
                ['x' => 17.5, 'y' => 227.9, 'w' => 92, 'h' => 6.2],
                ['x' => 17.5, 'y' => 234.3, 'w' => 92, 'h' => 13.8],
                ['x' => 103.3, 'y' => 230.8, 'w' => 39.8, 'h' => 4.8],
                ['x' => 147.7, 'y' => 230.8, 'w' => 45.6, 'h' => 4.8],
                ['x' => 103.3, 'y' => 236.0, 'w' => 39.8, 'h' => 4.8],
                ['x' => 147.7, 'y' => 236.0, 'w' => 45.6, 'h' => 4.8],
                ['x' => 103.3, 'y' => 241.1, 'w' => 70.2, 'h' => 4.8],
                ['x' => 103.3, 'y' => 246.3, 'w' => 40.2, 'h' => 4.8],
                ['x' => 103.3, 'y' => 251.4, 'w' => 90.2, 'h' => 4.8],
                ['x' => 28.1, 'y' => 273.2, 'w' => 34.2, 'h' => 4.1],
                ['x' => 28.1, 'y' => 276.5, 'w' => 34.2, 'h' => 4.1],
                ['x' => 28.8, 'y' => 279.8, 'w' => 21.4, 'h' => 4.1],
            ],
            'text' => [
                ['value' => 'no_rujukan_perolehan', 'x' => 49, 'y' => 37.5, 'w' => 97, 'h' => 4, 'font_size' => 10],
                ['value' => 'a1_nama_pemohon', 'x' => 43, 'y' => 86.5, 'w' => 61, 'h' => 4, 'font_size' => 8.8],
                ['value' => 'a2_jawatan_gred', 'x' => 134, 'y' => 86.5, 'w' => 60, 'h' => 4, 'font_size' => 8.8],
                ['value' => 'a3_jabatan_institusi', 'x' => 43, 'y' => 93.2, 'w' => 61, 'h' => 4, 'font_size' => 8.8],
                ['value' => 'a4_no_tel_email', 'x' => 134, 'y' => 93.2, 'w' => 60, 'h' => 4, 'font_size' => 8.8],
                ['value' => 'b1_tajuk_perolehan', 'x' => 45, 'y' => 104.3, 'w' => 149, 'h' => 3.9, 'font_size' => 8.5],
                ['value' => 'b3_nilai_tawaran_perolehan', 'x' => 49, 'y' => 121.4, 'w' => 26, 'h' => 4, 'font_size' => 9],
                ['value' => 'b4_harga_indikatif', 'x' => 106, 'y' => 121.4, 'w' => 24, 'h' => 4, 'font_size' => 9],
                ['value' => 'b5_peruntukan_diluluskan', 'x' => 169, 'y' => 121.4, 'w' => 25, 'h' => 4, 'font_size' => 9],
                ['value' => 'b6_justifikasi_keperluan', 'x' => 62, 'y' => 129.2, 'w' => 132, 'h' => 3.8, 'font_size' => 8.1],
                ['value' => 'b7_tajuk_asal_perolehan', 'x' => 62, 'y' => 141.6, 'w' => 132, 'h' => 3.8, 'font_size' => 8.1],
                ['value' => 'b8_tarikh_diperlukan', 'x' => 55, 'y' => 153.4, 'w' => 38, 'h' => 5, 'font_size' => 8.5, 'align' => 'C'],
                ['value' => 'b9_lokasi_diperlukan', 'x' => 131, 'y' => 153.4, 'w' => 63, 'h' => 5, 'font_size' => 8.5],
                ['value' => 'd_no_pendaftaran_syarikat', 'x' => 115.8, 'y' => 220.8, 'w' => 63, 'h' => 4, 'font_size' => 8.4],
                ['value' => 'd_nama_pembekal', 'x' => 18.5, 'y' => 229.3, 'w' => 90, 'h' => 3.8, 'font_size' => 7.7, 'align' => 'C'],
                ['value' => 'd_alamat_pembekal', 'x' => 18.5, 'y' => 236.0, 'w' => 90, 'h' => 3.6, 'font_size' => 7.0, 'align' => 'C'],
                ['value' => 'd_lain_lain_kriteria_inline', 'x' => 120.2, 'y' => 252.3, 'w' => 72, 'h' => 3.8, 'font_size' => 7.1],
                ['value' => 'applicant_name_signature', 'x' => 28.5, 'y' => 274.3, 'w' => 33, 'h' => 3.6, 'font_size' => 7.5],
                ['value' => 'applicant_role_signature', 'x' => 28.5, 'y' => 277.5, 'w' => 33, 'h' => 3.6, 'font_size' => 7.2],
                ['value' => 'applicant_declaration_date', 'x' => 29.2, 'y' => 280.7, 'w' => 20, 'h' => 3.6, 'font_size' => 8.0],
            ],
            'checkboxes' => [
                'kategori_perolehan' => [
                    'Bekalan' => ['x' => 42.2, 'y' => 114.8],
                    'Perkhidmatan' => ['x' => 63.2, 'y' => 114.8],
                    'Kerja' => ['x' => 88.4, 'y' => 114.8],
                ],
                'selection_criteria' => [
                    'Tawaran harga terbaik' => ['x' => 104.2, 'y' => 231.8],
                    'Keupayaan teknikal dan kewangan' => ['x' => 148.7, 'y' => 231.8],
                    'Pengalaman dan rekod prestasi' => ['x' => 104.2, 'y' => 236.9],
                    'Keupayaan operasi dan sumber' => ['x' => 148.7, 'y' => 236.9],
                    'Tempoh pembekalan/perlaksanaan yang munasabah' => ['x' => 104.2, 'y' => 242.1],
                    'Pembekal Tunggal' => ['x' => 104.2, 'y' => 247.2],
                    'Lain-lain' => ['x' => 104.2, 'y' => 252.3],
                ],
            ],
        ],
        4 => [
            'masks' => [
                ['x' => 145.7, 'y' => 41.8, 'w' => 6.2, 'h' => 4.8],
                ['x' => 47.4, 'y' => 48.5, 'w' => 148.2, 'h' => 5.6],
            ],
            'text' => [
                ['value' => 'g2_project_name', 'x' => 48.5, 'y' => 49.6, 'w' => 146, 'h' => 4, 'font_size' => 8.2],
            ],
            'checkboxes' => [
                'g1_review_yes' => ['x' => 146.5, 'y' => 42.6],
            ],
        ],
    ];

    public function generate(Bpp $bpp): string
    {
        $templatePath = $this->ensureTemplatePdfExists();

        $this->ensureFpdfLoaded();

        $bpp->loadMissing([
            'supplierQuotes' => fn ($query) => $query->with('items')->orderByDesc('is_selected')->latest(),
            'supplierQuoteItems.supplierQuote',
            'appendixRows',
        ]);

        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);
        $pageCount = $pdf->setSourceFile($templatePath);

        for ($pageNumber = 1; $pageNumber <= min($pageCount, 4); $pageNumber++) {
            $template = $pdf->importPage($pageNumber);
            $size = $pdf->getTemplateSize($template);

            $orientation = $size['width'] > $size['height'] ? 'L' : 'P';

            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            $pdf->useTemplate($template, 0, 0, $size['width'], $size['height']);

            $this->overlayPage($pdf, $bpp, $pageNumber);
        }

        $this->appendC1PartOnePage($pdf, $bpp);
        $this->appendC1PartTwoPage($pdf, $bpp);

        return $pdf->Output('S');
    }

    public function downloadName(Bpp $bpp): string
    {
        $reference = trim((string) ($bpp->no_rujukan_perolehan ?: $bpp->id));
        $reference = preg_replace('/[^A-Za-z0-9\-]+/', '-', $reference) ?: (string) $bpp->id;
        $reference = trim($reference, '-');

        return 'BPP-'.$reference.'.pdf';
    }

    private function overlayPage(Fpdi $pdf, Bpp $bpp, int $pageNumber): void
    {
        $pageMap = $this->coordinateMap[$pageNumber] ?? null;

        if ($pageMap === null) {
            return;
        }

        foreach ($pageMap['masks'] ?? [] as $mask) {
            $this->drawMask(
                $pdf,
                (float) $mask['x'],
                (float) $mask['y'],
                (float) $mask['w'],
                (float) $mask['h']
            );
        }

        foreach ($pageMap['text'] ?? [] as $field) {
            $value = $this->valueFor($bpp, $field['value']);

            if (! filled($value)) {
                continue;
            }

            $this->writeText(
                $pdf,
                (float) $field['x'],
                (float) $field['y'],
                (float) $field['w'],
                (float) $field['h'],
                (string) $value,
                (float) ($field['font_size'] ?? 8.5),
                (string) ($field['align'] ?? 'L')
            );
        }

        foreach (($pageMap['checkboxes']['kategori_perolehan'] ?? []) as $label => $point) {
            if ($bpp->b2_kategori_perolehan === $label) {
                $this->writeCheckbox($pdf, (float) $point['x'], (float) $point['y']);
            }
        }

        foreach (($pageMap['checkboxes']['selection_criteria'] ?? []) as $label => $point) {
            if (in_array($label, $bpp->selectedCriteriaOptions(), true)) {
                $this->writeCheckbox($pdf, (float) $point['x'], (float) $point['y']);
            }
        }

        if (($pageMap['checkboxes']['g1_review_yes'] ?? null) !== null && filled($this->valueFor($bpp, 'g2_project_name'))) {
            $point = $pageMap['checkboxes']['g1_review_yes'];
            $this->writeCheckbox($pdf, (float) $point['x'], (float) $point['y']);
        }
    }

    private function appendC1PartOnePage(Fpdi $pdf, Bpp $bpp): void
    {
        $supplierQuotes = $bpp->supplierQuotes instanceof Collection
            ? $bpp->supplierQuotes->values()
            : $bpp->supplierQuotes()->orderByDesc('is_selected')->latest()->get();

        $pdf->AddPage('P', 'A4');
        $pdf->SetAutoPageBreak(false);
        $this->drawSimplePageHeader($pdf, 'Lampiran 1 / C1 Kajian Pasaran', 'Part 1 - Kaedah Kajian Pasaran', $bpp, '5/6');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY(10, 32);
        $pdf->Cell(190, 6, $this->normalizeText($bpp->b1_tajuk_perolehan ?: '-'), 1, 1, 'L');

        $this->drawTableHeader($pdf, 10, 44, [56, 89, 45], [
            'Kaedah',
            'Perincian (Nama Pembekal / Rujukan / URL)',
            'Harga Yang Diperolehi (RM)',
        ], 8);

        $marketSourceRows = $this->marketSourceRows($bpp, $supplierQuotes);
        $y = 52;

        foreach ($marketSourceRows as $row) {
            $this->drawFixedHeightRow(
                $pdf,
                10,
                $y,
                [56, 89, 45],
                [$row['method'], $row['detail'], $row['amount']],
                13,
                ['L', 'L', 'R']
            );
            $y += 13;
        }

        $selectedSupplier = $supplierQuotes->firstWhere('is_selected', true);

        $this->drawLabeledField(
            $pdf,
            10,
            118,
            58,
            12,
            'Pembekal Disyorkan',
            $selectedSupplier?->supplier_name ?: ($bpp->d_nama_pembekal ?: '-')
        );
        $this->drawLabeledField(
            $pdf,
            10,
            130,
            58,
            18,
            'Alamat Pembekal',
            $selectedSupplier?->supplier_address ?: ($bpp->d_alamat_pembekal ?: '-')
        );
        $this->drawLabeledField(
            $pdf,
            10,
            148,
            58,
            10,
            'No. Pendaftaran',
            $selectedSupplier?->registration_number ?: ($bpp->d_no_pendaftaran_syarikat ?: '-')
        );

        $criteriaText = $this->criteriaSummary($bpp);
        $this->drawLabeledField($pdf, 10, 163, 58, 16, 'Kriteria Pemilihan', $criteriaText);

        $this->drawLabeledField(
            $pdf,
            10,
            184,
            58,
            34,
            'Justifikasi Keperluan',
            $bpp->b6_justifikasi_keperluan ?: '-'
        );

        $this->drawSummaryBox($pdf, 10, 262, 90, 16, 'Jumlah Sumber Direkodkan', (string) count($marketSourceRows));
        $this->drawSummaryBox(
            $pdf,
            110,
            262,
            90,
            16,
            'Jumlah Harga Pembekal Dipilih',
            $selectedSupplier ? number_format((float) $selectedSupplier->total_price, 2, '.', '') : '-'
        );
    }

    private function appendC1PartTwoPage(Fpdi $pdf, Bpp $bpp): void
    {
        $comparisonSuppliers = ($bpp->supplierQuotes instanceof Collection
            ? $bpp->supplierQuotes
            : $bpp->supplierQuotes()->orderBy('id')->get())
            ->sortBy('id')
            ->values();

        $comparisonMatrixRows = $this->comparisonMatrixRows($bpp);

        $pdf->AddPage('L', 'A4');
        $pdf->SetAutoPageBreak(false);
        $this->drawSimplePageHeader($pdf, 'Lampiran 1 / C1 Kajian Pasaran', 'Part 2 - Laporan Analisa Harga / Kajian Pasaran', $bpp, '6/6');

        $usableWidth = 277.0;
        $x = 10.0;
        $y = 30.0;

        $supplierCount = max($comparisonSuppliers->count(), 1);
        $baseWidths = [12.0, 79.0, 18.0, 16.0];
        $supplierWidth = max(32.0, ($usableWidth - array_sum($baseWidths)) / $supplierCount);
        $widths = array_merge($baseWidths, array_fill(0, $supplierCount, $supplierWidth));

        $headers = ['No.', 'Perincian setiap item', 'Kuantiti', 'Unit'];
        foreach ($comparisonSuppliers as $supplierQuote) {
            $headers[] = $this->truncateText($supplierQuote->supplier_name, 28);
        }

        if ($comparisonSuppliers->isEmpty()) {
            $headers[] = 'Pembekal';
            $widths[] = $usableWidth - array_sum($baseWidths);
        }

        $this->drawTableHeader($pdf, $x, $y, $widths, $headers, 8);
        $y += 8;

        if ($comparisonMatrixRows->isEmpty()) {
            $this->drawFixedHeightRow($pdf, $x, $y, [$usableWidth], ['Tiada perbandingan item C1 direkodkan untuk draf ini.'], 12);
            $y += 12;
        } else {
            foreach ($comparisonMatrixRows as $row) {
                $values = [
                    (string) $row['line_number'],
                    $row['item_spesifikasi'],
                    $this->formatQuantity($row['kuantiti']),
                    $row['unit_ukuran'] ?: '-',
                ];

                foreach ($comparisonSuppliers as $supplierQuote) {
                    $priceCell = $row['supplier_prices'][$supplierQuote->supplier_name] ?? null;
                    $values[] = $priceCell === null
                        ? '-'
                        : $this->formatCurrencyNumber($priceCell['harga_tawaran']);
                }

                if ($comparisonSuppliers->isEmpty()) {
                    $values[] = '-';
                }

                $this->drawFixedHeightRow(
                    $pdf,
                    $x,
                    $y,
                    $widths,
                    $values,
                    12,
                    array_merge(['C', 'L', 'R', 'C'], array_fill(0, max($comparisonSuppliers->count(), 1), 'R'))
                );
                $y += 12;

                if ($y > 175) {
                    break;
                }
            }
        }

        $totalValues = [' ', 'Jumlah Harga Diperoleh', ' ', ' '];
        foreach ($comparisonSuppliers as $supplierQuote) {
            $totalValues[] = $this->formatCurrencyNumber($supplierQuote->total_price);
        }
        if ($comparisonSuppliers->isEmpty()) {
            $totalValues[] = '-';
        }

        $this->drawFixedHeightRow(
            $pdf,
            $x,
            max($y, 188),
            $widths,
            $totalValues,
            12,
            array_merge(['C', 'R', 'C', 'C'], array_fill(0, max($comparisonSuppliers->count(), 1), 'R')),
            true
        );

        $pdf->SetFont('Arial', '', 8);
        $pdf->SetXY(10, 212);
        $pdf->Cell(133, 5, $this->normalizeText('Pembekal dipilih: '.($bpp->d_nama_pembekal ?: '-')), 0, 0, 'L');
        $pdf->SetXY(150, 212);
        $pdf->Cell(137, 5, $this->normalizeText('Kriteria: '.$this->truncateText($this->criteriaSummary($bpp), 90)), 0, 0, 'L');

        $this->drawSummaryBox($pdf, 10, 226, 86, 16, 'Bilangan Pembekal', (string) $comparisonSuppliers->count());
        $this->drawSummaryBox($pdf, 105, 226, 86, 16, 'Bilangan Item', (string) $comparisonMatrixRows->count());
        $this->drawSummaryBox(
            $pdf,
            201,
            226,
            86,
            16,
            'Jumlah Appendix Aktif (RM)',
            $this->formatCurrencyNumber(
                $bpp->appendixRows
                    ->where('appendix_type', $bpp->activeAppendixType())
                    ->sum('jumlah_harga')
            )
        );
    }

    private function marketSourceRows(Bpp $bpp, Collection $supplierQuotes): array
    {
        $rows = [];

        $rows[] = [
            'method' => 'Harga Perolehan terdahulu',
            'detail' => $this->combineDetails([
                $bpp->b11_no_rujukan_perolehan_po_sst_terdahulu ? 'Rujukan: '.$bpp->b11_no_rujukan_perolehan_po_sst_terdahulu : null,
                $bpp->b13_no_rujukan_perolehan_po_sst_2_tahun_lalu ? '2 tahun lalu: '.$bpp->b13_no_rujukan_perolehan_po_sst_2_tahun_lalu : null,
                $bpp->b15_no_rujukan_perolehan_po_sst_alat ? 'Alat: '.$bpp->b15_no_rujukan_perolehan_po_sst_alat : null,
            ]) ?: '-',
            'amount' => $this->firstFilledCurrency([
                $bpp->b10_nilai_perolehan_terdahulu,
                $bpp->b12_nilai_perolehan_2_tahun_lalu,
                $bpp->b14_nilai_perolehan_alat,
            ]),
        ];

        $quoteRows = $supplierQuotes->take(3)->values();
        $labels = ['Laman Sesawang 1', 'Laman Sesawang 2 (jika berkaitan)', 'Lain-lain Sumber (Sila Nyatakan)'];

        foreach ($labels as $index => $label) {
            /** @var BppSupplierQuote|null $quote */
            $quote = $quoteRows->get($index);

            $rows[] = [
                'method' => $label,
                'detail' => $quote
                    ? $this->combineDetails(array_filter([
                        $quote->supplier_name,
                        $quote->quotation_reference ? 'Rujukan: '.$quote->quotation_reference : null,
                    ]))
                    : '-',
                'amount' => $quote ? $this->formatCurrencyNumber($quote->total_price) : '-',
            ];
        }

        return $rows;
    }

    private function comparisonMatrixRows(Bpp $bpp): Collection
    {
        $items = $bpp->supplierQuoteItems instanceof Collection
            ? $bpp->supplierQuoteItems
            : $bpp->supplierQuoteItems()->with('supplierQuote')->get();

        return $items
            ->groupBy('line_number')
            ->map(function (Collection $rows, mixed $lineNumber): array {
                $firstRow = $rows->first();

                return [
                    'line_number' => (int) $lineNumber,
                    'item_spesifikasi' => $firstRow->item_spesifikasi,
                    'kuantiti' => $firstRow->kuantiti,
                    'unit_ukuran' => $firstRow->unit_ukuran,
                    'supplier_prices' => $rows->mapWithKeys(function ($row): array {
                        return [
                            (string) $row->supplierQuote?->supplier_name => [
                                'harga_tawaran' => $row->harga_tawaran,
                                'jumlah_harga' => $row->jumlah_harga,
                            ],
                        ];
                    })->all(),
                ];
            })
            ->sortBy('line_number')
            ->values();
    }

    private function drawMask(Fpdi $pdf, float $x, float $y, float $width, float $height): void
    {
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect($x, $y, $width, $height, 'F');
    }

    private function writeText(
        Fpdi $pdf,
        float $x,
        float $y,
        float $width,
        float $lineHeight,
        string $value,
        float $fontSize,
        string $align = 'L'
    ): void {
        $pdf->SetXY($x, $y);
        $pdf->SetFont('Arial', '', $fontSize);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell($width, $lineHeight, $this->normalizeText($value), 0, $align, false);
    }

    private function writeCheckbox(Fpdi $pdf, float $x, float $y): void
    {
        $pdf->SetXY($x, $y);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(4, 4, '/', 0, 0, 'C');
    }

    private function valueFor(Bpp $bpp, string $key): ?string
    {
        return match ($key) {
            'b3_nilai_tawaran_perolehan' => $bpp->displayCurrency($bpp->b3_nilai_tawaran_perolehan),
            'b4_harga_indikatif' => $bpp->displayCurrency($bpp->b4_harga_indikatif),
            'b5_peruntukan_diluluskan' => $bpp->displayCurrency($bpp->b5_peruntukan_diluluskan),
            'b8_tarikh_diperlukan' => $bpp->procurementRequiredMonthLabel(),
            'applicant_name_signature' => $bpp->a1_nama_pemohon,
            'applicant_role_signature' => $bpp->a2_jawatan_gred,
            'applicant_declaration_date' => $bpp->updated_at?->format('j.n.y') ?? $bpp->created_at?->format('j.n.y'),
            'g2_project_name' => $bpp->b7_tajuk_asal_perolehan ?: $bpp->b1_tajuk_perolehan,
            'd_lain_lain_kriteria_inline' => filled($bpp->d_lain_lain_kriteria) ? '('.$bpp->d_lain_lain_kriteria.')' : null,
            default => ($bpp->{$key} ?? null),
        };
    }

    private function normalizeText(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        $value = str_replace(["\r\n", "\r"], "\n", $value);

        return mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8');
    }

    private function ensureTemplatePdfExists(): string
    {
        $templatePath = storage_path(self::TEMPLATE_PATH);

        if (File::exists($templatePath)) {
            return $templatePath;
        }

        $this->buildTemplatePdfFromPreviewImages($templatePath);

        if (! File::exists($templatePath)) {
            throw new RuntimeException('BPP template PDF is missing at '.$templatePath);
        }

        return $templatePath;
    }

    private function buildTemplatePdfFromPreviewImages(string $templatePath): void
    {
        $this->ensureFpdfLoaded();

        $templateDirectory = dirname($templatePath);

        if (! File::isDirectory($templateDirectory)) {
            File::makeDirectory($templateDirectory, 0755, true);
        }

        $images = [
            public_path('images/bpp-preview/fixed-page-1.png'),
            public_path('images/bpp-preview/fixed-page-2.png'),
            public_path('images/bpp-preview/fixed-page-3.png'),
            public_path('images/bpp-preview/fixed-page-4.png'),
        ];

        foreach ($images as $image) {
            if (! File::exists($image)) {
                throw new RuntimeException('Missing BPP template image: '.$image);
            }
        }

        $pdf = new \FPDF('P', 'mm', 'A4');

        foreach ($images as $image) {
            $pdf->AddPage();
            $pdf->Image($image, 0, 0, 210, 297);
        }

        $pdf->Output('F', $templatePath);
    }

    private function drawSimplePageHeader(Fpdi $pdf, string $title, string $subtitle, Bpp $bpp, string $pageLabel): void
    {
        $pageWidth = $pdf->GetPageWidth();
        $metaWidth = 60.0;
        $metaX = $pageWidth - 10 - $metaWidth;

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetXY(10, 10);
        $pdf->Cell(max(80, $metaX - 20), 7, $this->normalizeText($title), 0, 0, 'L');

        $pdf->SetFont('Arial', '', 8);
        $pdf->SetXY($metaX, 10);
        $pdf->Cell($metaWidth, 4, $this->normalizeText('No. Rujukan: '.($bpp->no_rujukan_perolehan ?: '-')), 0, 2, 'R');
        $pdf->SetX($metaX);
        $pdf->Cell($metaWidth, 4, $this->normalizeText('Muka surat: '.$pageLabel), 0, 2, 'R');
        $pdf->SetX($metaX);
        $pdf->Cell($metaWidth, 4, $this->normalizeText('Kategori: '.($bpp->b2_kategori_perolehan ?: '-')), 0, 0, 'R');

        $pdf->SetFont('Arial', '', 9);
        $pdf->SetXY(10, 18);
        $pdf->Cell($pageWidth - 20, 5, $this->normalizeText($subtitle), 0, 0, 'L');
        $pdf->Line(10, 25, $pageWidth - 10, 25);
    }

    /**
     * @param array<int, float> $widths
     * @param array<int, string> $labels
     */
    private function drawTableHeader(Fpdi $pdf, float $x, float $y, array $widths, array $labels, float $height): void
    {
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetXY($x, $y);

        foreach ($labels as $index => $label) {
            $pdf->Cell($widths[$index], $height, $this->normalizeText($label), 1, 0, 'C');
        }

        $pdf->Ln();
    }

    /**
     * @param array<int, float> $widths
     * @param array<int, string> $values
     * @param array<int, string|null> $alignments
     */
    private function drawFixedHeightRow(
        Fpdi $pdf,
        float $x,
        float $y,
        array $widths,
        array $values,
        float $rowHeight,
        array $alignments = [],
        bool $bold = false
    ): void {
        $pdf->SetFont('Arial', $bold ? 'B' : '', 8);

        foreach ($values as $index => $value) {
            $width = $widths[$index];
            $align = $alignments[$index] ?? 'L';
            $cellX = $index === 0 ? $x : $x + array_sum(array_slice($widths, 0, $index));

            $pdf->Rect($cellX, $y, $width, $rowHeight);
            $pdf->SetXY($cellX + 1, $y + 1.2);
            $pdf->MultiCell($width - 2, 3.6, $this->normalizeText((string) $value), 0, $align, false);
        }
    }

    private function drawLabeledField(Fpdi $pdf, float $x, float $y, float $labelWidth, float $height, string $label, string $value): void
    {
        $pageWidth = $pdf->GetPageWidth();
        $valueWidth = $pageWidth - 20 - $labelWidth;

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetXY($x, $y);
        $pdf->Cell($labelWidth, $height, $this->normalizeText($label), 1, 0, 'L');
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetXY($x + $labelWidth, $y + 1);
        $pdf->Rect($x + $labelWidth, $y, $valueWidth, $height);
        $pdf->MultiCell($valueWidth - 2, 4, $this->normalizeText($value), 0, 'L', false);
    }

    private function drawSummaryBox(Fpdi $pdf, float $x, float $y, float $width, float $height, string $label, string $value): void
    {
        $pdf->Rect($x, $y, $width, $height);
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetXY($x + 2, $y + 3);
        $pdf->Cell($width - 4, 4, $this->normalizeText($label), 0, 2, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetX($x + 2);
        $pdf->Cell($width - 4, 6, $this->normalizeText($value), 0, 0, 'L');
    }

    private function criteriaSummary(Bpp $bpp): string
    {
        $criteria = $bpp->selectedCriteriaOptions();

        if ($criteria === []) {
            return $bpp->selectionReasonLabel() ?: '-';
        }

        $summary = implode(', ', $criteria);

        if (in_array('Lain-lain', $criteria, true) && filled($bpp->d_lain_lain_kriteria)) {
            $summary .= ' ('.$bpp->d_lain_lain_kriteria.')';
        }

        return $summary;
    }

    /**
     * @param array<int, mixed> $values
     */
    private function firstFilledCurrency(array $values): string
    {
        foreach ($values as $value) {
            if ($value !== null && $value !== '') {
                return $this->formatCurrencyNumber($value);
            }
        }

        return '-';
    }

    /**
     * @param array<int, string|null> $parts
     */
    private function combineDetails(array $parts): string
    {
        $filtered = array_values(array_filter($parts, static fn (?string $part): bool => filled($part)));

        return implode(' | ', $filtered);
    }

    private function formatCurrencyNumber(float|string|null $amount): string
    {
        if ($amount === null || $amount === '') {
            return '-';
        }

        return number_format((float) $amount, 2, '.', '');
    }

    private function formatQuantity(float|string|null $quantity): string
    {
        if ($quantity === null || $quantity === '') {
            return '-';
        }

        return rtrim(rtrim(number_format((float) $quantity, 2, '.', ''), '0'), '.');
    }

    private function truncateText(string $value, int $limit): string
    {
        $value = trim($value);

        if (mb_strlen($value) <= $limit) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $limit - 1)).'.';
    }

    private function ensureFpdfLoaded(): void
    {
        if (! class_exists('FPDF')) {
            require_once base_path('vendor/setasign/fpdf/fpdf.php');
        }
    }
}
