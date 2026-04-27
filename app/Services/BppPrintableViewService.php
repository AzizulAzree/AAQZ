<?php

namespace App\Services;

use App\Models\Bpp;

class BppPrintableViewService
{
    public function packagePreview(Bpp $bpp, array $validationResult): array
    {
        $c1 = $this->c1($bpp);
        $c2 = $this->appendix($bpp, 'c2', 'C2 - Perbekalan');

        return [
            'bpp' => $bpp,
            'previewTitle' => 'Preview BPP',
            'checklistItems' => $this->checklistItems($bpp),
            'validationResult' => $validationResult,
            'supplierQuotes' => $c1['supplierQuotes'],
            'selectedSupplier' => $c1['selectedSupplier'],
            'c2Data' => $c2,
        ];
    }

    public function checklist(Bpp $bpp, array $validationResult): array
    {
        return [
            'bpp' => $bpp,
            'previewTitle' => 'Senarai Semak',
            'checklistItems' => $this->checklistItems($bpp),
            'validationResult' => $validationResult,
        ];
    }

    public function pageOne(Bpp $bpp): array
    {
        return [
            'bpp' => $bpp,
            'previewTitle' => 'BPP Page 1',
        ];
    }

    public function pageTwo(Bpp $bpp): array
    {
        return [
            'bpp' => $bpp,
            'previewTitle' => 'BPP Page 2',
        ];
    }

    public function c1(Bpp $bpp): array
    {
        $supplierQuotes = $bpp->supplierQuotes()->orderByDesc('is_selected')->latest()->get();

        return [
            'bpp' => $bpp,
            'previewTitle' => 'C1 - Kajian Pasaran',
            'supplierQuotes' => $supplierQuotes,
            'selectedSupplier' => $supplierQuotes->firstWhere('is_selected', true),
        ];
    }

    public function appendix(Bpp $bpp, string $appendixType, string $previewTitle): array
    {
        $rows = $bpp->appendixRows()->where('appendix_type', $appendixType)->get();
        $grandTotal = (float) $rows->sum('jumlah_harga');

        return [
            'bpp' => $bpp,
            'previewTitle' => $previewTitle,
            'appendixType' => $appendixType,
            'appendixLabel' => Bpp::appendixLabelForType($appendixType) ?? strtoupper($appendixType),
            'appendixRows' => $rows,
            'appendixGrandTotal' => number_format($grandTotal, 2, '.', ''),
            'isActiveAppendix' => $bpp->activeAppendixType() === $appendixType,
        ];
    }

    public function activeAppendix(Bpp $bpp): ?array
    {
        $appendixType = $bpp->activeAppendixType();

        if ($appendixType === null) {
            return null;
        }

        $previewTitle = Bpp::appendixLabelForType($appendixType) ?? strtoupper($appendixType);
        $view = match ($appendixType) {
            'c2' => 'bpp.printables.partials.pages.c2-page',
            'c3' => 'bpp.printables.partials.pages.c3-page',
            'c4' => 'bpp.printables.partials.pages.c4-page',
            default => null,
        };

        if ($view === null) {
            return null;
        }

        return [
            'view' => $view,
            'data' => $this->appendix($bpp, $appendixType, $previewTitle),
        ];
    }

    private function checklistItems(Bpp $bpp): array
    {
        $activeAppendixType = $bpp->activeAppendixType();
        $activeAppendixLabel = $bpp->activeAppendixLabel() ?? 'Lampiran';
        $selectedSupplier = $bpp->selectedSupplierQuote();
        $appendixRowCount = $activeAppendixType === null
            ? 0
            : $bpp->appendixRows()->where('appendix_type', $activeAppendixType)->count();
        $appendixGrandTotal = $activeAppendixType === null
            ? '0.00'
            : number_format((float) $bpp->appendixRows()->where('appendix_type', $activeAppendixType)->sum('jumlah_harga'), 2, '.', '');
        $b3Total = number_format((float) $bpp->b3_nilai_tawaran_perolehan, 2, '.', '');

        return [
            [
                'label' => 'Borang Permohonan Perolehan (BPP) Draft',
                'checked' => filled($bpp->title),
                'note' => $bpp->title ?: 'Draft title belum diisi.',
            ],
            [
                'label' => 'B1. Tajuk Perolehan',
                'checked' => filled($bpp->b1_tajuk_perolehan),
                'note' => $bpp->b1_tajuk_perolehan ?: 'Maklumat belum diisi.',
            ],
            [
                'label' => 'B2. Kategori Perolehan',
                'checked' => filled($bpp->b2_kategori_perolehan),
                'note' => $bpp->b2_kategori_perolehan ?: 'Maklumat belum diisi.',
            ],
            [
                'label' => 'B6. Justifikasi Keperluan',
                'checked' => filled($bpp->b6_justifikasi_keperluan),
                'note' => filled($bpp->b6_justifikasi_keperluan)
                    ? 'Justifikasi telah direkodkan.'
                    : 'Maklumat belum diisi.',
            ],
            [
                'label' => 'C1. Kajian Pasaran / Perbandingan Pembekal',
                'checked' => $bpp->supplierQuotes()->exists(),
                'note' => $bpp->supplierQuotes()->exists()
                    ? $bpp->supplierQuotes()->count().' pembekal direkodkan.'
                    : 'Tiada pembekal direkodkan.',
            ],
            [
                'label' => 'Pembekal Disyorkan',
                'checked' => $selectedSupplier !== null,
                'note' => $selectedSupplier?->supplier_name ?: 'Belum dipilih.',
            ],
            [
                'label' => $activeAppendixLabel,
                'checked' => $appendixRowCount > 0,
                'note' => $appendixRowCount > 0
                    ? $appendixRowCount.' baris item direkodkan.'
                    : 'Tiada baris item direkodkan.',
            ],
            [
                'label' => 'B3. Nilai Tawaran Perolehan',
                'checked' => $appendixGrandTotal === $b3Total,
                'note' => 'Jumlah lampiran: '.$bpp->displayCurrency((float) $appendixGrandTotal).' | B3: '.$bpp->displayCurrency((float) $b3Total),
            ],
        ];
    }
}
