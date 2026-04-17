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
                                <h1 class="bpp-workspace-title">{{ $bpp->title }}</h1>
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

                        <div class="bpp-action-bar">
                            <x-primary-button>{{ __('Save Draft') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
