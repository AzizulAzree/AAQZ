@php
    $procurementMethodDetails = [
        'pembelian_terus' => [
            'title' => 'Pembelian Terus',
            'meta' => 'Sehingga tidak melebihi RM50,000.00',
        ],
        'pembekal_tunggal_bawah_50k' => [
            'title' => 'Pembekal Tunggal',
            'meta' => 'Sehingga tidak melebihi RM50,000.00',
        ],
        'sebut_harga' => [
            'title' => 'Sebut Harga',
            'meta' => 'RM50,000.00 sehingga tidak melebihi RM500,000.00',
        ],
        'tender' => [
            'title' => 'Tender',
            'meta' => 'Melebihi RM500,000.00',
        ],
        'pembekal_tunggal_rundingan_terus' => [
            'title' => 'Pembekal Tunggal / Rundingan Terus',
            'meta' => 'Melebihi RM50,000.00',
        ],
    ];

    $categoryOptions = ['Bekalan', 'Perkhidmatan', 'Kerja'];
    $selectionCriteriaOptions = [
        'Tawaran harga terbaik',
        'Keupayaan teknikal dan kewangan',
        'Pengalaman dan rekod prestasi',
        'Keupayaan operasi dan sumber',
        'Tempoh pembekalan/perlaksanaan yang munasabah',
        'Pembekal Tunggal',
        'Lain-lain',
    ];
    $reviewErrors = is_array($quotationExtractionReview['errors'] ?? null) ? $quotationExtractionReview['errors'] : [];
    $reviewWarnings = is_array($quotationExtractionReview['warnings'] ?? null) ? $quotationExtractionReview['warnings'] : [];
    $reviewData = is_array($quotationExtractionReview['data'] ?? null) ? $quotationExtractionReview['data'] : null;
    $oldCriteriaSelection = old('d_kriteria_pemilihan');
    $selectedCriteriaValues = is_array($oldCriteriaSelection)
        ? $oldCriteriaSelection
        : (is_string($oldCriteriaSelection) && trim($oldCriteriaSelection) !== ''
            ? preg_split('/\r\n|\r|\n|\|/', $oldCriteriaSelection)
            : $bpp->selectedCriteriaOptions());
    $comparisonSuppliers = $supplierQuotes->sortBy('id')->values();
    $comparisonMatrixRows = $supplierQuoteItems
        ->groupBy('line_number')
        ->map(function ($rows, $lineNumber) {
            $firstRow = $rows->first();

            return [
                'line_number' => (int) $lineNumber,
                'item_spesifikasi' => $firstRow->item_spesifikasi,
                'kuantiti' => $firstRow->kuantiti,
                'unit_ukuran' => $firstRow->unit_ukuran,
                'supplier_prices' => $rows->mapWithKeys(function ($row) {
                    return [
                        $row->supplierQuote?->supplier_name => [
                            'harga_tawaran' => $row->harga_tawaran,
                            'jumlah_harga' => $row->jumlah_harga,
                        ],
                    ];
                })->all(),
            ];
        })
        ->sortBy('line_number')
        ->values();
@endphp

<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <section class="bpp-workspace-shell">
                <div class="bpp-workspace-header">
                    <div class="bpp-workspace-header-copy">
                        <p class="project-tree-label">{{ __('BPP Draft') }}</p>
                        <div class="mt-3 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                                    <h1 class="bpp-workspace-title">{{ $bpp->title }}</h1>
                                    <a href="{{ route('bpp.printables.preview', $bpp) }}" class="bpp-secondary-button w-fit" target="_blank" rel="noopener noreferrer">
                                        {{ __('Open Blank A4 Page') }}
                                    </a>
                                    <a href="{{ route('bpp.pdf', $bpp) }}" class="bpp-primary-button w-fit" target="_blank" rel="noopener noreferrer">
                                        {{ __('Generate PDF') }}
                                    </a>
                                </div>
                                <p class="bpp-workspace-subtitle">{{ __('Page 1 drafting workspace.') }}</p>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <div class="project-ghost-action">{{ __('Status') }}: {{ $bpp->status }}</div>
                                <div class="project-ghost-action">{{ __('ID') }}: {{ $bpp->id }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-5 px-5 py-5 sm:px-8 sm:py-7">
                    <div class="bpp-category-banner">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="project-tree-label text-amber-800">{{ __('Kategori Perolehan') }}</p>
                                <p class="mt-2 text-lg font-semibold text-slate-900">{{ $bpp->b2_kategori_perolehan ?: __('Not selected yet') }}</p>
                            </div>
                            @if ($activeAppendixLabel)
                                <div class="project-ghost-action">{{ __('Active Appendix') }}: {{ __($activeAppendixLabel) }}</div>
                            @endif
                        </div>
                    </div>

                    <form method="POST" action="{{ route('bpp.update', $bpp) }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="title" value="{{ $bpp->title }}">

                        <details open class="bpp-group">
                            <summary class="bpp-group-summary">
                                <div class="bpp-group-summary-copy">
                                    <h2 class="bpp-group-title">{{ __('Top Section & A. Perihal Pemohon') }}</h2>
                                </div>
                                <span class="bpp-group-toggle" aria-hidden="true">
                                    <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4">
                                        <path d="M6 8.5 10 12.5 14 8.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
                                    </svg>
                                </span>
                            </summary>

                            <div class="bpp-group-body">
                                <section class="bpp-subsection">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <p class="project-tree-label">{{ __('Top Section') }}</p>
                                            <h3 class="mt-1 text-base font-semibold text-slate-900">{{ __('No. Rujukan Perolehan & Kaedah Perolehan') }}</h3>
                                        </div>
                                    </div>

                                    <div class="grid gap-4 xl:grid-cols-[minmax(0,14rem)_minmax(0,1fr)] xl:items-start">
                                        <div>
                                            <x-input-label for="no_rujukan_perolehan" :value="__('No. Rujukan Perolehan')" />
                                            <x-text-input id="no_rujukan_perolehan" name="no_rujukan_perolehan" type="text" class="mt-1 block w-full" :value="old('no_rujukan_perolehan', $bpp->no_rujukan_perolehan)" />
                                            <x-input-error class="mt-2" :messages="$errors->get('no_rujukan_perolehan')" />
                                        </div>

                                        <div class="space-y-3">
                                            <x-input-label :value="__('Kaedah Perolehan')" />

                                            <div class="grid gap-2.5 lg:grid-cols-2">
                                                @foreach ($bpp->procurementMethodOptions() as $methodValue => $methodLabel)
                                                    @php
                                                        $methodDetail = $procurementMethodDetails[$methodValue] ?? [
                                                            'title' => $methodLabel,
                                                            'meta' => null,
                                                        ];
                                                    @endphp

                                                    <label class="bpp-option-tile">
                                                        <input
                                                            type="radio"
                                                            name="kaedah_perolehan"
                                                            value="{{ $methodValue }}"
                                                            class="mt-0.5 h-4 w-4 border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500"
                                                            @checked(old('kaedah_perolehan', $bpp->kaedah_perolehan) === $methodValue)
                                                        >
                                                        <span class="min-w-0">
                                                            <span class="block text-sm font-semibold leading-5 text-slate-900">{{ __($methodDetail['title']) }}</span>
                                                            @if ($methodDetail['meta'])
                                                                <span class="mt-0.5 block text-[11px] leading-4 text-slate-500">{{ __($methodDetail['meta']) }}</span>
                                                            @endif
                                                            <span class="sr-only">{{ __($methodLabel) }}</span>
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            <x-input-error class="mt-2" :messages="$errors->get('kaedah_perolehan')" />
                                        </div>
                                    </div>
                                </section>

                                <section class="bpp-subsection">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <p class="project-tree-label">{{ __('A') }}</p>
                                            <h3 class="mt-1 text-base font-semibold text-slate-900">{{ __('Perihal Pemohon') }}</h3>
                                        </div>
                                        <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('4 fields') }}</p>
                                    </div>

                                    <div class="grid gap-4 md:grid-cols-2">
                                        <div>
                                            <x-input-label for="a1_nama_pemohon" :value="__('A1. Nama Pemohon')" />
                                            <x-text-input id="a1_nama_pemohon" name="a1_nama_pemohon" type="text" class="mt-1 block w-full" :value="old('a1_nama_pemohon', $bpp->a1_nama_pemohon)" />
                                            <x-input-error class="mt-2" :messages="$errors->get('a1_nama_pemohon')" />
                                        </div>

                                        <div>
                                            <x-input-label for="a2_jawatan_gred" :value="__('A2. Jawatan / Gred')" />
                                            <x-text-input id="a2_jawatan_gred" name="a2_jawatan_gred" type="text" class="mt-1 block w-full" :value="old('a2_jawatan_gred', $bpp->a2_jawatan_gred)" />
                                            <x-input-error class="mt-2" :messages="$errors->get('a2_jawatan_gred')" />
                                        </div>

                                        <div>
                                            <x-input-label for="a3_jabatan_institusi" :value="__('A3. Jabatan / Institusi')" />
                                            <x-text-input id="a3_jabatan_institusi" name="a3_jabatan_institusi" type="text" class="mt-1 block w-full" :value="old('a3_jabatan_institusi', $bpp->a3_jabatan_institusi)" />
                                            <x-input-error class="mt-2" :messages="$errors->get('a3_jabatan_institusi')" />
                                        </div>

                                        <div>
                                            <x-input-label for="a4_no_tel_email" :value="__('A4. No. Tel / E-mel')" />
                                            <x-text-input id="a4_no_tel_email" name="a4_no_tel_email" type="text" class="mt-1 block w-full" :value="old('a4_no_tel_email', $bpp->a4_no_tel_email)" />
                                            <x-input-error class="mt-2" :messages="$errors->get('a4_no_tel_email')" />
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </details>

                        <details open class="bpp-group">
                            <summary class="bpp-group-summary">
                                <div class="bpp-group-summary-copy">
                                    <h2 class="bpp-group-title">{{ __('B(I) Perihal Perolehan') }}</h2>
                                </div>
                                <span class="bpp-group-toggle" aria-hidden="true">
                                    <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4">
                                        <path d="M6 8.5 10 12.5 14 8.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
                                    </svg>
                                </span>
                            </summary>

                            <div class="bpp-group-body">
                                <div class="grid gap-4">
                                    <div>
                                        <x-input-label for="b1_tajuk_perolehan" :value="__('B1. Tajuk Perolehan')" />
                                        <x-text-input id="b1_tajuk_perolehan" name="b1_tajuk_perolehan" type="text" class="mt-1 block w-full" :value="old('b1_tajuk_perolehan', $bpp->b1_tajuk_perolehan)" />
                                        <x-input-error class="mt-2" :messages="$errors->get('b1_tajuk_perolehan')" />
                                    </div>

                                    <div class="space-y-2">
                                        <x-input-label :value="__('B2. Kategori Perolehan')" />
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($categoryOptions as $categoryOption)
                                                @php
                                                    $isActiveCategory = strcasecmp((string) $bpp->b2_kategori_perolehan, $categoryOption) === 0;
                                                @endphp
                                                <div class="bpp-category-chip {{ $isActiveCategory ? 'bpp-category-chip-active' : '' }}">
                                                    <span class="inline-flex h-4 w-4 items-center justify-center rounded-sm border text-[10px] font-bold {{ $isActiveCategory ? 'border-amber-500 bg-amber-400 text-white' : 'border-slate-300 bg-white text-transparent' }}">/</span>
                                                    <span class="font-medium">{{ __($categoryOption) }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="grid gap-4 lg:grid-cols-3">
                                        <div>
                                            <x-input-label for="b3_nilai_tawaran_perolehan" :value="__('B3. Nilai Tawaran Perolehan')" />
                                            <x-text-input id="b3_nilai_tawaran_perolehan" name="b3_nilai_tawaran_perolehan" type="text" inputmode="decimal" class="mt-1 block w-full" :value="old('b3_nilai_tawaran_perolehan', $bpp->displayCurrency($bpp->b3_nilai_tawaran_perolehan))" placeholder="RM 0,000.00" />
                                            <p class="mt-1 text-xs text-slate-500">{{ __('Pembelian terus') }}</p>
                                            <x-input-error class="mt-2" :messages="$errors->get('b3_nilai_tawaran_perolehan')" />
                                        </div>

                                        <div>
                                            <x-input-label for="b4_harga_indikatif" :value="__('B4. Harga Indikatif')" />
                                            <x-text-input id="b4_harga_indikatif" name="b4_harga_indikatif" type="text" inputmode="decimal" class="mt-1 block w-full" :value="old('b4_harga_indikatif', $bpp->displayCurrency($bpp->b4_harga_indikatif))" placeholder="RM 0,000.00" />
                                            <p class="mt-1 text-xs text-slate-500">{{ __('Sebut Harga / Tender') }}</p>
                                            <x-input-error class="mt-2" :messages="$errors->get('b4_harga_indikatif')" />
                                        </div>

                                        <div>
                                            <x-input-label for="b5_peruntukan_diluluskan" :value="__('B5. Peruntukan yang diluluskan')" />
                                            <x-text-input id="b5_peruntukan_diluluskan" name="b5_peruntukan_diluluskan" type="text" inputmode="decimal" class="mt-1 block w-full" :value="old('b5_peruntukan_diluluskan', $bpp->displayCurrency($bpp->b5_peruntukan_diluluskan))" placeholder="RM 0,000.00" />
                                            <x-input-error class="mt-2" :messages="$errors->get('b5_peruntukan_diluluskan')" />
                                        </div>
                                    </div>

                                    <div>
                                        <x-input-label for="b6_justifikasi_keperluan" :value="__('B6. Justifikasi keperluan perolehan')" />
                                        <textarea id="b6_justifikasi_keperluan" name="b6_justifikasi_keperluan" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('b6_justifikasi_keperluan', $bpp->b6_justifikasi_keperluan) }}</textarea>
                                        <x-input-error class="mt-2" :messages="$errors->get('b6_justifikasi_keperluan')" />
                                    </div>

                                    <div>
                                        <x-input-label for="b7_tajuk_asal_perolehan" :value="__('B7. Tajuk asal perolehan')" />
                                        <x-text-input id="b7_tajuk_asal_perolehan" name="b7_tajuk_asal_perolehan" type="text" class="mt-1 block w-full" :value="old('b7_tajuk_asal_perolehan', $bpp->b7_tajuk_asal_perolehan)" />
                                        <x-input-error class="mt-2" :messages="$errors->get('b7_tajuk_asal_perolehan')" />
                                    </div>

                                    <div class="grid gap-4 lg:grid-cols-[minmax(0,14rem)_minmax(0,1fr)]">
                                        <div>
                                            <x-input-label for="b8_tarikh_diperlukan" :value="__('B8. Tarikh Perolehan Diperlukan')" />
                                            <x-text-input id="b8_tarikh_diperlukan" name="b8_tarikh_diperlukan" type="month" class="mt-1 block w-full" :value="old('b8_tarikh_diperlukan', $bpp->procurementRequiredMonthValue())" />
                                            <p class="mt-1 text-xs text-slate-500">{{ __('Sekurang-kurangnya: PT-21 hari / SH-90 hari / T-120 hari') }}</p>
                                            <x-input-error class="mt-2" :messages="$errors->get('b8_tarikh_diperlukan')" />
                                        </div>

                                        <div>
                                            <x-input-label for="b9_lokasi_diperlukan" :value="__('B9. Lokasi Perolehan Diperlukan')" />
                                            <x-text-input id="b9_lokasi_diperlukan" name="b9_lokasi_diperlukan" type="text" class="mt-1 block w-full" :value="old('b9_lokasi_diperlukan', $bpp->b9_lokasi_diperlukan)" />
                                            <p class="mt-1 text-xs text-slate-500">{{ __('Selepas borang lengkap diterima') }}</p>
                                            <x-input-error class="mt-2" :messages="$errors->get('b9_lokasi_diperlukan')" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </details>

                        @if ($bpp->requiresBiiSection() || in_array(old('kaedah_perolehan', $bpp->kaedah_perolehan), ['tender', 'pembekal_tunggal_rundingan_terus'], true))
                            <details open class="bpp-group">
                                <summary class="bpp-group-summary">
                                    <div class="bpp-group-summary-copy">
                                        <h2 class="bpp-group-title">{{ __('B(II) Maklumat Tambahan') }}</h2>
                                        <p class="bpp-group-hint">{{ __('Available for Tender and Pembekal Tunggal / Rundingan Terus only.') }}</p>
                                    </div>
                                    <span class="bpp-group-toggle" aria-hidden="true">
                                        <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4">
                                            <path d="M6 8.5 10 12.5 14 8.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
                                        </svg>
                                    </span>
                                </summary>

                                <div class="bpp-group-body">
                                    <div class="grid gap-4 lg:grid-cols-2">
                                        <div>
                                            <x-input-label for="b10_nilai_perolehan_terdahulu" :value="__('B10. Nilai perolehan terdahulu')" />
                                            <x-text-input id="b10_nilai_perolehan_terdahulu" name="b10_nilai_perolehan_terdahulu" type="text" inputmode="decimal" class="mt-1 block w-full" :value="old('b10_nilai_perolehan_terdahulu', $bpp->displayCurrency($bpp->b10_nilai_perolehan_terdahulu))" placeholder="RM 0,000.00" />
                                            <x-input-error class="mt-2" :messages="$errors->get('b10_nilai_perolehan_terdahulu')" />
                                        </div>

                                        <div>
                                            <x-input-label for="b11_no_rujukan_perolehan_po_sst_terdahulu" :value="__('B11. No rujukan perolehan PO / SST')" />
                                            <x-text-input id="b11_no_rujukan_perolehan_po_sst_terdahulu" name="b11_no_rujukan_perolehan_po_sst_terdahulu" type="text" class="mt-1 block w-full" :value="old('b11_no_rujukan_perolehan_po_sst_terdahulu', $bpp->b11_no_rujukan_perolehan_po_sst_terdahulu)" />
                                            <x-input-error class="mt-2" :messages="$errors->get('b11_no_rujukan_perolehan_po_sst_terdahulu')" />
                                        </div>

                                        <div>
                                            <x-input-label for="b12_nilai_perolehan_2_tahun_lalu" :value="__('B12. Nilai perolehan 2 tahun yang lalu')" />
                                            <x-text-input id="b12_nilai_perolehan_2_tahun_lalu" name="b12_nilai_perolehan_2_tahun_lalu" type="text" inputmode="decimal" class="mt-1 block w-full" :value="old('b12_nilai_perolehan_2_tahun_lalu', $bpp->displayCurrency($bpp->b12_nilai_perolehan_2_tahun_lalu))" placeholder="RM 0,000.00" />
                                            <x-input-error class="mt-2" :messages="$errors->get('b12_nilai_perolehan_2_tahun_lalu')" />
                                        </div>

                                        <div>
                                            <x-input-label for="b13_no_rujukan_perolehan_po_sst_2_tahun_lalu" :value="__('B13. No rujukan perolehan PO / SST')" />
                                            <x-text-input id="b13_no_rujukan_perolehan_po_sst_2_tahun_lalu" name="b13_no_rujukan_perolehan_po_sst_2_tahun_lalu" type="text" class="mt-1 block w-full" :value="old('b13_no_rujukan_perolehan_po_sst_2_tahun_lalu', $bpp->b13_no_rujukan_perolehan_po_sst_2_tahun_lalu)" />
                                            <x-input-error class="mt-2" :messages="$errors->get('b13_no_rujukan_perolehan_po_sst_2_tahun_lalu')" />
                                        </div>

                                        <div>
                                            <x-input-label for="b14_nilai_perolehan_alat" :value="__('B14. Nilai perolehan alat')" />
                                            <x-text-input id="b14_nilai_perolehan_alat" name="b14_nilai_perolehan_alat" type="text" inputmode="decimal" class="mt-1 block w-full" :value="old('b14_nilai_perolehan_alat', $bpp->displayCurrency($bpp->b14_nilai_perolehan_alat))" placeholder="RM 0,000.00" />
                                            <x-input-error class="mt-2" :messages="$errors->get('b14_nilai_perolehan_alat')" />
                                        </div>

                                        <div>
                                            <x-input-label for="b15_no_rujukan_perolehan_po_sst_alat" :value="__('B15. No rujukan perolehan PO / SST')" />
                                            <x-text-input id="b15_no_rujukan_perolehan_po_sst_alat" name="b15_no_rujukan_perolehan_po_sst_alat" type="text" class="mt-1 block w-full" :value="old('b15_no_rujukan_perolehan_po_sst_alat', $bpp->b15_no_rujukan_perolehan_po_sst_alat)" />
                                            <x-input-error class="mt-2" :messages="$errors->get('b15_no_rujukan_perolehan_po_sst_alat')" />
                                        </div>
                                    </div>

                                    <div class="mt-5 grid gap-3">
                                        <label class="bpp-check-row">
                                            <input type="checkbox" name="b16_kepilkan_analisis_roi_rov" value="1" class="rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500" @checked(old('b16_kepilkan_analisis_roi_rov', $bpp->b16_kepilkan_analisis_roi_rov))>
                                            <span>{{ __('B16. Kepilkan analisis ROI / ROV bagi peralatan yang perlu diselenggara') }}</span>
                                        </label>

                                        <label class="bpp-check-row">
                                            <input type="checkbox" name="b17_rekod_senarai_pihak_pengguna" value="1" class="rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500" @checked(old('b17_rekod_senarai_pihak_pengguna', $bpp->b17_rekod_senarai_pihak_pengguna))>
                                            <span>{{ __('B17. Rekod senarai pihak atau individu yang menggunakan alat yang perlu diselenggara') }}</span>
                                        </label>

                                        <label class="bpp-check-row">
                                            <input type="checkbox" name="b18_salinan_laporan_kerosakan" value="1" class="rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500" @checked(old('b18_salinan_laporan_kerosakan', $bpp->b18_salinan_laporan_kerosakan))>
                                            <span>{{ __('B18. Salinan laporan kerosakan (bagi perolehan pembaikan) - wajib dikepilkan') }}</span>
                                        </label>
                                    </div>
                                </div>
                            </details>
                        @endif

                        <div class="bpp-action-bar">
                            <x-primary-button>{{ __('Save Draft') }}</x-primary-button>
                        </div>
                    </form>

                    <details open class="bpp-group" x-data="{ copiedPrompt: false }">
                            <summary class="bpp-group-summary">
                                <div class="bpp-group-summary-copy">
                                    <h2 class="bpp-group-title">{{ __('C. Quotation Comparison') }}</h2>
                                    <p class="bpp-group-hint">{{ __('Copy the prepared prompt, paste the ChatGPT result, then apply it to insert supplier comparison rows automatically.') }}</p>
                                </div>
                                <span class="bpp-group-toggle" aria-hidden="true">
                                    <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4">
                                        <path d="M6 8.5 10 12.5 14 8.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
                                    </svg>
                                </span>
                            </summary>

                            <div class="bpp-group-body space-y-4">
                                <section class="bpp-subsection">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <p class="project-tree-label">{{ __('Quotation Extraction Assistant') }}</p>
                                            <h3 class="mt-1 text-base font-semibold text-slate-900">{{ __('Copy Prompt And Paste Result') }}</h3>
                                        </div>
                                        <div class="project-ghost-action">{{ $quotationExtractionFormatVersion }}</div>
                                    </div>

                                    <div class="mt-4 flex flex-wrap items-center gap-3">
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 transition hover:bg-slate-50"
                                            x-on:click="navigator.clipboard.writeText($refs.extractionPrompt.value).then(() => { copiedPrompt = true; setTimeout(() => copiedPrompt = false, 1800); });"
                                        >
                                            {{ __('Copy ChatGPT Prompt') }}
                                        </button>
                                        <span class="text-xs text-slate-500" x-show="copiedPrompt" x-transition.opacity>{{ __('Prompt copied.') }}</span>
                                    </div>
                                    <textarea x-ref="extractionPrompt" class="sr-only">{{ $quotationExtractionPrompt }}</textarea>

                                    <form method="POST" action="{{ route('bpp.quotation-extraction.parse', $bpp) }}" class="mt-5 space-y-4">
                                        @csrf
                                        <div>
                                            <x-input-label for="quotation_extraction_text" :value="__('Paste ChatGPT Result')" />
                                            <textarea id="quotation_extraction_text" name="quotation_extraction_text" rows="14" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('quotation_extraction_text', $bpp->quotation_extraction_raw_text) }}</textarea>
                                            <x-input-error class="mt-2" :messages="$errors->get('quotation_extraction_text')" />
                                        </div>
                                        @if ($hasExistingImportedDraftData)
                                            <label class="bpp-check-row">
                                                <input type="checkbox" name="confirm_replace" value="1" class="rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500">
                                                <span>{{ __('Replace the current C section and item rows when importing from this prompt result.') }}</span>
                                            </label>
                                            <x-input-error class="mt-2" :messages="$errors->get('confirm_replace')" />
                                        @endif
                                        <div class="flex flex-wrap justify-end gap-3">
                                            <button type="submit" class="bpp-secondary-button">{{ __('Review Result') }}</button>
                                            <button type="submit" formaction="{{ route('bpp.quotation-extraction.import', $bpp) }}" class="bpp-primary-button">{{ __('Import To C1 & D') }}</button>
                                        </div>
                                    </form>
                                </section>

                                @if ($reviewErrors !== [])
                                    <section class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4 text-sm text-rose-700">
                                        <p class="project-tree-label text-rose-700">{{ __('Parse Errors') }}</p>
                                        <div class="mt-3 space-y-2">
                                            @foreach ($reviewErrors as $error)
                                                <p>{{ $error }}</p>
                                            @endforeach
                                        </div>
                                    </section>
                                @endif

                                @if ($reviewData !== null)
                                    <section class="bpp-subsection space-y-4">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <p class="project-tree-label">{{ __('Review') }}</p>
                                                <h3 class="mt-1 text-base font-semibold text-slate-900">{{ __('Parsed Comparison Result') }}</h3>
                                            </div>
                                            <div class="text-sm text-slate-600">
                                                <p>{{ __('Kategori') }}: {{ $reviewData['procurement_category'] ?? '-' }}</p>
                                                <p>{{ __('Supplier Dipilih') }}: {{ $reviewData['selected_supplier'] ?? '-' }}</p>
                                            </div>
                                        </div>

                                        @if ($reviewWarnings !== [])
                                            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-800">
                                                @foreach ($reviewWarnings as $warning)
                                                    <p>{{ $warning }}</p>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                                <thead class="bg-slate-50">
                                                    <tr>
                                                        <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Nama Pembekal') }}</th>
                                                        <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('No. Pendaftaran') }}</th>
                                                        <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Alamat') }}</th>
                                                        <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Harga') }}</th>
                                                        <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Tempoh') }}</th>
                                                        <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Sah Laku') }}</th>
                                                        <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Rujukan') }}</th>
                                                        <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Dipilih') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100 bg-white">
                                                    @foreach (($reviewData['suppliers'] ?? []) as $supplier)
                                                        <tr>
                                                            <td class="px-3 py-4">{{ $supplier['supplier_name'] }}</td>
                                                            <td class="px-3 py-4">{{ $supplier['registration_number'] ?? '-' }}</td>
                                                            <td class="px-3 py-4 whitespace-pre-line">{{ $supplier['supplier_address'] ?? '-' }}</td>
                                                            <td class="px-3 py-4">{{ $bpp->displayCurrency($supplier['total_price']) }}</td>
                                                            <td class="px-3 py-4">{{ $supplier['delivery_period'] }}</td>
                                                            <td class="px-3 py-4">{{ $supplier['validity_period'] }}</td>
                                                            <td class="px-3 py-4">{{ $supplier['quotation_reference'] ?: '-' }}</td>
                                                            <td class="px-3 py-4">
                                                                @if ($supplier['is_selected'])
                                                                    <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">{{ __('Ya') }}</span>
                                                                @else
                                                                    <span class="text-slate-400">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <form method="POST" action="{{ route('bpp.quotation-extraction.apply', $bpp) }}" class="space-y-4">
                                            @csrf
                                            @if ($hasExistingImportedDraftData && $supplierQuotes->isNotEmpty())
                                                <label class="bpp-check-row">
                                                    <input type="checkbox" name="confirm_replace" value="1" class="rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500">
                                                    <span>{{ __('Replace the current C section and item rows with this parsed result.') }}</span>
                                                </label>
                                                <x-input-error class="mt-2" :messages="$errors->get('confirm_replace')" />
                                            @endif
                                            <div class="flex justify-end">
                                                <button type="submit" class="bpp-primary-button">{{ __('Apply To Draft') }}</button>
                                            </div>
                                        </form>
                                    </section>
                                @endif

                                <section class="bpp-subsection">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <p class="project-tree-label">{{ __('Saved Comparison') }}</p>
                                            <h3 class="mt-1 text-base font-semibold text-slate-900">{{ __('C1 Supplier Table') }}</h3>
                                        </div>
                                        <div class="project-ghost-action">{{ $supplierQuotes->count() }} {{ __('supplier rows') }}</div>
                                    </div>

                                    <div class="mt-4 overflow-x-auto">
                                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                                            <thead class="bg-slate-50">
                                                <tr>
                                                    <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Nama Pembekal') }}</th>
                                                    <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('No. Pendaftaran') }}</th>
                                                    <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Alamat') }}</th>
                                                    <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Harga') }}</th>
                                                    <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Tempoh') }}</th>
                                                    <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Sah Laku') }}</th>
                                                    <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Rujukan') }}</th>
                                                    <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Dipilih') }}</th>
                                                    <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Tindakan') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 bg-white">
                                                @forelse ($supplierQuotes as $supplierQuote)
                                                    <tr>
                                                        <td class="px-3 py-4">{{ $supplierQuote->supplier_name }}</td>
                                                        <td class="px-3 py-4">{{ $supplierQuote->registration_number ?: '-' }}</td>
                                                        <td class="px-3 py-4 whitespace-pre-line">{{ $supplierQuote->supplier_address ?: '-' }}</td>
                                                        <td class="px-3 py-4">{{ $bpp->displayCurrency($supplierQuote->total_price) }}</td>
                                                        <td class="px-3 py-4">{{ $supplierQuote->delivery_period }}</td>
                                                        <td class="px-3 py-4">{{ $supplierQuote->validity_period }}</td>
                                                        <td class="px-3 py-4">{{ $supplierQuote->quotation_reference ?: '-' }}</td>
                                                        <td class="px-3 py-4">
                                                            @if ($supplierQuote->is_selected)
                                                                <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">{{ __('Dipilih') }}</span>
                                                            @else
                                                                <span class="text-slate-400">-</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-4">
                                                            <div class="flex flex-wrap gap-2">
                                                                <form method="POST" action="{{ route('bpp.supplier-quotes.select', [$bpp, $supplierQuote]) }}">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <button type="submit" class="bpp-secondary-button">{{ __('Pilih') }}</button>
                                                                </form>
                                                                <form method="POST" action="{{ route('bpp.supplier-quotes.destroy', [$bpp, $supplierQuote]) }}">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="bpp-danger-button">{{ __('Delete') }}</button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="9" class="px-3 py-6 text-center text-slate-500">{{ __('No quotation comparison rows yet.') }}</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    <form method="POST" action="{{ route('bpp.supplier-quotes.store', $bpp) }}" class="mt-5 grid gap-4 md:grid-cols-2">
                                        @csrf
                                        <div>
                                            <x-input-label for="supplier_name" :value="__('Nama Pembekal')" />
                                            <x-text-input id="supplier_name" name="supplier_name" type="text" class="mt-1 block w-full" :value="old('supplier_name')" />
                                        </div>
                                        <div>
                                            <x-input-label for="registration_number" :value="__('No. Pendaftaran Syarikat')" />
                                            <x-text-input id="registration_number" name="registration_number" type="text" class="mt-1 block w-full" :value="old('registration_number')" />
                                        </div>
                                        <div class="md:col-span-2">
                                            <x-input-label for="supplier_address" :value="__('Alamat Pembekal')" />
                                            <textarea id="supplier_address" name="supplier_address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('supplier_address') }}</textarea>
                                        </div>
                                        <div>
                                            <x-input-label for="total_price" :value="__('Harga Tawaran')" />
                                            <x-text-input id="total_price" name="total_price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('total_price')" />
                                        </div>
                                        <div>
                                            <x-input-label for="delivery_period" :value="__('Tempoh Penghantaran')" />
                                            <x-text-input id="delivery_period" name="delivery_period" type="text" class="mt-1 block w-full" :value="old('delivery_period')" />
                                        </div>
                                        <div>
                                            <x-input-label for="validity_period" :value="__('Terma / Sah Laku')" />
                                            <x-text-input id="validity_period" name="validity_period" type="text" class="mt-1 block w-full" :value="old('validity_period')" />
                                        </div>
                                        <div class="md:col-span-2">
                                            <x-input-label for="quotation_reference" :value="__('No. Rujukan Dokumen Tawaran')" />
                                            <x-text-input id="quotation_reference" name="quotation_reference" type="text" class="mt-1 block w-full" :value="old('quotation_reference')" />
                                        </div>
                                        <div class="md:col-span-2">
                                            <x-input-error class="mt-2" :messages="$errors->get('supplier_name')" />
                                            <x-input-error class="mt-2" :messages="$errors->get('registration_number')" />
                                            <x-input-error class="mt-2" :messages="$errors->get('supplier_address')" />
                                            <x-input-error class="mt-2" :messages="$errors->get('total_price')" />
                                            <x-input-error class="mt-2" :messages="$errors->get('delivery_period')" />
                                            <x-input-error class="mt-2" :messages="$errors->get('validity_period')" />
                                            <x-input-error class="mt-2" :messages="$errors->get('quotation_reference')" />
                                        </div>
                                        <div class="md:col-span-2 flex justify-end">
                                            <button type="submit" class="bpp-secondary-button">{{ __('Add Supplier Row') }}</button>
                                        </div>
                                    </form>
                                </section>
                            </div>
                        </details>

                        <details open class="bpp-group">
                            <summary class="bpp-group-summary">
                                <div class="bpp-group-summary-copy">
                                    <h2 class="bpp-group-title">{{ __('D. Pembekal Yang Disyorkan') }}</h2>
                                </div>
                                <span class="bpp-group-toggle" aria-hidden="true">
                                    <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4">
                                        <path d="M6 8.5 10 12.5 14 8.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
                                    </svg>
                                </span>
                            </summary>

                            <div class="bpp-group-body">
                                <form method="POST" action="{{ route('bpp.update', $bpp) }}" class="bpp-subsection">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="title" value="{{ $bpp->title }}">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <p class="project-tree-label">{{ __('D') }}</p>
                                            <h3 class="mt-1 text-base font-semibold text-slate-900">{{ __('Pembekal Yang Disyorkan') }}</h3>
                                        </div>
                                        <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Supplier auto from C') }}</p>
                                    </div>

                                    <div class="mt-4 grid gap-4 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]">
                                        <div class="space-y-4">
                                            <div class="rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-4">
                                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ __('Nama dan alamat pembekal') }}</p>
                                                <div class="mt-3 space-y-3 text-sm text-slate-700">
                                                    <p class="text-base font-semibold text-slate-900">{{ $bpp->d_nama_pembekal ?: __('Belum dipilih dari C') }}</p>
                                                    <p class="whitespace-pre-line leading-6 text-slate-600">{{ $bpp->d_alamat_pembekal ?: '-' }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="space-y-4">
                                            <div class="rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-4">
                                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ __('No. Pendaftaran Syarikat') }}</p>
                                                <p class="mt-3 text-base font-semibold text-slate-900">{{ $bpp->d_no_pendaftaran_syarikat ?: '-' }}</p>
                                            </div>

                                            <div class="rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-4">
                                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ __('Kriteria pemilihan') }}</p>
                                                <div class="mt-3 grid gap-2 md:grid-cols-2">
                                                    @foreach ($selectionCriteriaOptions as $criterion)
                                                        @php
                                                            $isSelectedCriterion = in_array($criterion, $selectedCriteriaValues, true);
                                                        @endphp
                                                        <label class="bpp-check-row">
                                                            <input type="checkbox" name="d_kriteria_pemilihan[]" value="{{ $criterion }}" class="rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500" @checked($isSelectedCriterion)>
                                                            <span>{{ __($criterion) }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>

                                                <div class="mt-4">
                                                    <x-input-label for="d_lain_lain_kriteria" :value="__('Lain-lain (nyatakan)')" />
                                                    <textarea id="d_lain_lain_kriteria" name="d_lain_lain_kriteria" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('d_lain_lain_kriteria', $bpp->d_lain_lain_kriteria) }}</textarea>
                                                    <x-input-error class="mt-2" :messages="$errors->get('d_kriteria_pemilihan')" />
                                                    <x-input-error class="mt-2" :messages="$errors->get('d_lain_lain_kriteria')" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-5 flex justify-end">
                                        <button type="submit" class="bpp-primary-button">{{ __('Save D Section') }}</button>
                                    </div>
                                </form>
                            </div>
                        </details>

                        <details open class="bpp-group">
                            <summary class="bpp-group-summary">
                                <div class="bpp-group-summary-copy">
                                    <h2 class="bpp-group-title">{{ __('C1. Kaedah Kajian Pasaran') }}</h2>
                                    <p class="bpp-group-hint">{{ __('Auto-generated from imported quotation comparison data.') }}</p>
                                </div>
                                <span class="bpp-group-toggle" aria-hidden="true">
                                    <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4">
                                        <path d="M6 8.5 10 12.5 14 8.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
                                    </svg>
                                </span>
                            </summary>

                            <div class="bpp-group-body space-y-4">
                                <section class="bpp-subsection">
                                    <div>
                                        <p class="project-tree-label">{{ __('Part 1') }}</p>
                                        <h3 class="mt-1 text-base font-semibold text-slate-900">{{ __('Kaedah Kajian Pasaran') }}</h3>
                                    </div>

                                    <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200">
                                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                                            <thead class="bg-slate-100">
                                                <tr>
                                                    <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Kaedah') }}</th>
                                                    <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Perincian (Nama Pembekal / URL)') }}</th>
                                                    <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Harga Yang Diperolehi (RM)') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 bg-white">
                                                @foreach ([
                                                    'Harga Perolehan terdahulu',
                                                    'Laman Sesawang 1',
                                                    'Laman Sesawang 2 (jika berkaitan)',
                                                    'Lain-lain Sumber (Sila Nyatakan)',
                                                ] as $sourceLabel)
                                                    <tr>
                                                        <td class="px-3 py-4 text-slate-700">{{ __($sourceLabel) }}</td>
                                                        <td class="px-3 py-4 text-slate-300">-</td>
                                                        <td class="px-3 py-4 text-slate-300">-</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </section>

                                <section class="bpp-subsection">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <p class="project-tree-label">{{ __('Part 2') }}</p>
                                            <h3 class="mt-1 text-base font-semibold text-slate-900">{{ __('Laporan Analisa Harga / Kajian Pasaran') }}</h3>
                                        </div>
                                        <div class="project-ghost-action">{{ $comparisonSuppliers->count() }} {{ __('supplier columns') }}</div>
                                    </div>

                                    @if ($comparisonSuppliers->isNotEmpty() && $comparisonMatrixRows->isNotEmpty())
                                        <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200">
                                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                                <thead class="bg-slate-100">
                                                    <tr>
                                                        <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('No.') }}</th>
                                                        <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Perincian setiap item') }}</th>
                                                        <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Kuantiti') }}</th>
                                                        <th class="px-3 py-3 text-left font-semibold text-slate-700">{{ __('Unit') }}</th>
                                                        @foreach ($comparisonSuppliers as $supplierQuote)
                                                            <th class="min-w-[10rem] px-3 py-3 text-left font-semibold text-slate-700">{{ $supplierQuote->supplier_name }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100 bg-white">
                                                    @foreach ($comparisonMatrixRows as $matrixRow)
                                                        <tr>
                                                            <td class="px-3 py-4 font-medium text-slate-700">{{ $matrixRow['line_number'] }}</td>
                                                            <td class="px-3 py-4 text-slate-800">{{ $matrixRow['item_spesifikasi'] }}</td>
                                                            <td class="px-3 py-4 text-slate-700">{{ rtrim(rtrim((string) $matrixRow['kuantiti'], '0'), '.') }}</td>
                                                            <td class="px-3 py-4 text-slate-700">{{ $matrixRow['unit_ukuran'] }}</td>
                                                            @foreach ($comparisonSuppliers as $supplierQuote)
                                                                @php
                                                                    $priceCell = $matrixRow['supplier_prices'][$supplierQuote->supplier_name] ?? null;
                                                                @endphp
                                                                <td class="px-3 py-4 text-slate-700">
                                                                    {{ $priceCell ? $bpp->displayCurrency($priceCell['harga_tawaran']) : '-' }}
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach

                                                    <tr class="bg-slate-50/70">
                                                        <td colspan="4" class="px-3 py-4 text-right font-semibold text-slate-800">{{ __('Jumlah Harga Diperoleh') }}</td>
                                                        @foreach ($comparisonSuppliers as $supplierQuote)
                                                            <td class="px-3 py-4 font-semibold text-slate-900">{{ $bpp->displayCurrency($supplierQuote->total_price) }}</td>
                                                        @endforeach
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="mt-4 rounded-2xl border border-dashed border-slate-200 bg-slate-50/70 px-4 py-6 text-sm text-slate-500">
                                            {{ __('No imported supplier comparison matrix yet. Use the quotation extraction import to generate this section automatically.') }}
                                        </div>
                                    @endif
                                </section>
                            </div>
                        </details>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
