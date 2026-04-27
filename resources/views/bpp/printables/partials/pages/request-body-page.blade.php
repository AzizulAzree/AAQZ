@php
    $categoryAppendixMap = [
        'Bekalan' => 'C2 - Perbekalan',
        'Perkhidmatan' => 'C3 - Perkhidmatan',
        'Kerja' => 'C4 - Kerja',
    ];
    $procurementMethods = [
        'pembelian_terus' => 'Pembelian Terus (sehingga tidak melebihi RM50,000.00)',
        'pembekal_tunggal_bawah_50k' => 'Pembekal Tunggal (sehingga tidak melebihi RM50,000.00)',
        'sebut_harga' => 'Sebut Harga (RM50,000.00 sehingga tidak melebihi RM500,000.00)',
        'tender' => 'Tender (melebihi RM500,000.00)',
        'pembekal_tunggal_rundingan_terus' => 'Pembekal Tunggal/Rundingan Terus (Melebihi RM50,000.00)',
    ];
    $selectedCriteria = $bpp->selectedCriteriaOptions();
    $displayAddress = trim(($bpp->d_nama_pembekal ?: '')."\n\n".($bpp->d_alamat_pembekal ?: ''));
@endphp

<section class="bpp-preview-page bpp-page-1">
    <div class="page">
        @include('bpp.printables.partials.form-header', ['bpp' => $bpp, 'pageNumber' => '1 dari 2'])

        <div class="bpp-ref-row row">
            <div class="bpp-ref-row-label">{{ __('No. Rujukan Perolehan') }}</div>
            <div class="bpp-ref-row-colon">:</div>
            <div class="field bpp-ref-row-value">{{ $bpp->no_rujukan_perolehan }}</div>
        </div>
        <div class="bpp-subnote">{{ __('Maklumat kod peruntukan:') }} <span>01-OE</span> <span>02-Hasil</span> <span>03-DE</span> <span>04-Geran/Projek</span></div>

        <div class="section-title">{{ __('ARAHAN') }}</div>
        <div class="bpp-note-lines">
            <div>i. {{ __('Sila rujuk Polisi Perolehan dan Prosedur berkaitan sebagai panduan mengisi borang ini') }}</div>
            <div>ii. {{ __('Sila pastikan borang adalah lengkap dan borang senarai semak permohonan perolehan PERLU disertakan bersama dengan BPP ini') }}</div>
        </div>

        <div class="section-title">{{ __('KAEDAH PEROLEHAN') }}</div>
        <div class="bpp-method-grid">
            <div class="bpp-method-column">
                @foreach (['pembelian_terus', 'pembekal_tunggal_bawah_50k'] as $methodCode)
                    <div class="bpp-method-item">
                        <span class="box">{{ $bpp->kaedah_perolehan === $methodCode ? '/' : '' }}</span>
                        <span>{{ __($procurementMethods[$methodCode]) }}</span>
                    </div>
                @endforeach
            </div>
            <div class="bpp-method-column">
                @foreach (['sebut_harga', 'tender', 'pembekal_tunggal_rundingan_terus'] as $methodCode)
                    <div class="bpp-method-item">
                        <span class="box">{{ $bpp->kaedah_perolehan === $methodCode ? '/' : '' }}</span>
                        <span>{{ __($procurementMethods[$methodCode]) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="section-title">{{ __('A: PERIHAL PEMOHON') }}</div>
        <div class="bpp-two-col-grid">
            <div class="row bpp-field-row">
                <div>{{ __('A1. Nama Pemohon') }}</div>
                <div>:</div>
                <div class="field">{{ $bpp->a1_nama_pemohon }}</div>
            </div>
            <div class="row bpp-field-row">
                <div>{{ __('A2. Jawatan/Gred') }}</div>
                <div>:</div>
                <div class="field">{{ $bpp->a2_jawatan_gred }}</div>
            </div>
            <div class="row bpp-field-row">
                <div>{{ __('A3. Jabatan/Inst.') }}</div>
                <div>:</div>
                <div class="field">{{ $bpp->a3_jabatan_institusi }}</div>
            </div>
            <div class="row bpp-field-row">
                <div>{{ __('A4. No. Tel/Email') }}</div>
                <div>:</div>
                <div class="field">{{ $bpp->a4_no_tel_email }}</div>
            </div>
        </div>

        <div class="section-title">{{ __('B(I): PERIHAL PEROLEHAN (Wajib diisi oleh Pemohon dan sila tandakan √ pada yang berkenaan)') }}</div>
        <div class="row bpp-field-row bpp-stack-row">
            <div>{{ __('B1. Tajuk Perolehan') }}</div>
            <div>:</div>
            <div class="field field-tall">{{ $bpp->b1_tajuk_perolehan }}</div>
        </div>

        <div class="row bpp-field-row">
            <div>{{ __('B2. Kategori Perolehan') }}</div>
            <div>:</div>
            <div class="bpp-inline-options">
                @foreach (['Bekalan', 'Perkhidmatan', 'Kerja'] as $category)
                    <div class="bpp-method-item">
                        <span class="box">{{ $bpp->b2_kategori_perolehan === $category ? '/' : '' }}</span>
                        <span>{{ __($category) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bpp-money-head">
            <div>
                <div>{{ __('B3. Nilai Tawaran Perolehan') }}</div>
                <div class="bpp-head-sub">{{ __('(Pembelian terus)') }}</div>
            </div>
            <div>
                <div>{{ __('B4. Harga Indikatif') }}</div>
                <div class="bpp-head-sub">{{ __('(Sebut Harga/Tender)') }}</div>
            </div>
            <div>
                <div>{{ __('B5. Peruntukan yang diluluskan') }}</div>
                <div class="bpp-head-sub">&nbsp;</div>
            </div>
        </div>
        <div class="bpp-money-values">
            <div class="field">{{ $bpp->displayCurrency($bpp->b3_nilai_tawaran_perolehan) }}</div>
            <div class="field">{{ $bpp->displayCurrency($bpp->b4_harga_indikatif) }}</div>
            <div class="field">{{ $bpp->displayCurrency($bpp->b5_peruntukan_diluluskan) }}</div>
        </div>

        <div class="row bpp-field-row bpp-stack-row">
            <div>{{ __('B6. Justifikasi keperluan perolehan') }}</div>
            <div>:</div>
            <div class="field field-large">{{ $bpp->b6_justifikasi_keperluan }}</div>
        </div>

        <div class="row bpp-field-row bpp-stack-row">
            <div>{{ __('B7. Tajuk asal perolehan (seperti di dalam perancangan perolehan)') }}</div>
            <div>:</div>
            <div class="field field-medium">{{ $bpp->b7_tajuk_asal_perolehan }}</div>
        </div>

        <div class="bpp-b9-grid">
            <div class="row bpp-field-row">
                <div>{{ __('B8. Tarikh Perolehan Diperlukan') }}</div>
                <div>:</div>
                <div class="field bpp-centered">{{ $bpp->procurementRequiredMonthLabel() }}</div>
            </div>
            <div class="row bpp-field-row">
                <div>{{ __('B9. Lokasi Perolehan Diperlukan') }}</div>
                <div>:</div>
                <div class="field">{{ $bpp->b9_lokasi_diperlukan }}</div>
            </div>
        </div>
        <div class="bpp-subnote bpp-spread-note">{{ __('sekurang-kurangnya:') }} <span>PT-21 hari</span> <span>SH-90 hari</span> <span>T-120 hari</span> <span>{{ __('selepas borang lengkap diterima') }}</span></div>

        <div class="section-title">{{ __('B(II): MAKLUMAT TAMBAHAN (PEMBAIKAN ATAU PENYELENGGARAAN PERALATAN MAKMAL BERNILAI MELEBIHI RM50,000.00 SAHAJA)') }}</div>
        <div class="bpp-bii-grid">
            <div class="row bpp-field-row">
                <div>{{ __('B10. Nilai perolehan terdahulu') }}</div>
                <div>:</div>
                <div class="field">{{ $bpp->displayCurrency($bpp->b10_nilai_perolehan_terdahulu) }}</div>
            </div>
            <div class="row bpp-field-row">
                <div>{{ __('B11. No rujukan perolehan PO/SST') }}</div>
                <div>:</div>
                <div class="field">{{ $bpp->b11_no_rujukan_perolehan_po_sst_terdahulu }}</div>
            </div>
            <div class="row bpp-field-row">
                <div>{{ __('B12. Nilai perolehan 2 tahun yang lalu') }}</div>
                <div>:</div>
                <div class="field">{{ $bpp->displayCurrency($bpp->b12_nilai_perolehan_2_tahun_lalu) }}</div>
            </div>
            <div class="row bpp-field-row">
                <div>{{ __('B13. No rujukan perolehan PO/SST') }}</div>
                <div>:</div>
                <div class="field">{{ $bpp->b13_no_rujukan_perolehan_po_sst_2_tahun_lalu }}</div>
            </div>
            <div class="row bpp-field-row">
                <div>{{ __('B14. Nilai perolehan alat') }}</div>
                <div>:</div>
                <div class="field">{{ $bpp->displayCurrency($bpp->b14_nilai_perolehan_alat) }}</div>
            </div>
            <div class="row bpp-field-row">
                <div>{{ __('B15. No rujukan perolehan PO/SST') }}</div>
                <div>:</div>
                <div class="field">{{ $bpp->b15_no_rujukan_perolehan_po_sst_alat }}</div>
            </div>
        </div>

        @foreach ([
            ['B16. Kepilkan analisis ROI/ROV bagi peralatan yang perlu diselenggara', $bpp->b16_kepilkan_analisis_roi_rov],
            ['B17. Rekod senarai pihak atau individu yang menggunakan alat yang perlu disenggara', $bpp->b17_rekod_senarai_pihak_pengguna],
            ['B18. Salinan laporan kerosakan (bagi perolehan pembaikan) - wajib dikepilkan', $bpp->b18_salinan_laporan_kerosakan],
        ] as [$label, $checked])
            <div class="bpp-box-line row">
                <div>{{ __($label) }}</div>
                <div class="bpp-check-cell"><span class="box">{{ $checked ? '/' : '' }}</span></div>
            </div>
        @endforeach

        <div class="section-title">{{ __('C: KAJIAN PASARAN (sila tandakan)') }}</div>
        <div class="bpp-note-lines">{{ __('Sila kemukakan maklumat lengkap menggunakan lampiran C1 untuk kaedah kajian pasaran') }}</div>
        <div class="bpp-inline-options bpp-market-row">
            <span>{{ __('Senarai item/perkhidmatan di dalam jadual') }}</span>
            @foreach (['C2 - Perbekalan', 'C3 - Perkhidmatan', 'C4 - Kerja'] as $appendixLabel)
                <div class="bpp-method-item">
                    <span class="box">{{ ($categoryAppendixMap[$bpp->b2_kategori_perolehan] ?? null) === $appendixLabel ? '/' : '' }}</span>
                    <span>{{ __($appendixLabel) }}</span>
                </div>
            @endforeach
        </div>

        <div class="section-title">{{ __('D: PEMBEKAL YANG DISYORKAN') }}</div>
        <div class="bpp-d-grid">
            <div>
                <div class="bpp-mini-label">{{ __('Maklumat pembekal') }}</div>
                <div class="bpp-mini-label bpp-mt">{{ __('Nama dan alamat pembekal:') }}</div>
                <div class="field field-address bpp-centered serif">{{ $displayAddress }}</div>
            </div>
            <div>
                <div class="row bpp-field-row">
                    <div>{{ __('No. Pendaftaran Syarikat') }}</div>
                    <div>:</div>
                    <div class="field">{{ $bpp->d_no_pendaftaran_syarikat }}</div>
                </div>
                <div class="bpp-mini-label bpp-mt">{{ __('Kriteria pemilihan:') }}</div>
                <div class="bpp-criteria-grid">
                    @foreach ($bpp->selectionReasonOptions() as $criterion)
                        <div class="bpp-method-item bpp-criteria-item">
                            <span class="box">{{ in_array($criterion, $selectedCriteria, true) ? '/' : '' }}</span>
                            <span>
                                {{ __($criterion) }}
                                @if ($criterion === 'Lain-lain' && filled($bpp->d_lain_lain_kriteria))
                                    {{ '('.$bpp->d_lain_lain_kriteria.')' }}
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="bpp-sign-grid">
            <div class="bpp-sign-panel">
                <div class="section-title">{{ __('E: PERAKUAN PEMOHON') }}</div>
                <div class="bpp-sign-copy">{{ __('Saya mengaku bahawa semua maklumat yang diisi oleh saya adalah benar dan perolehan ini adalah mematuhi Polisi dan Prosedur Perolehan NIBM. Saya juga mengaku bahawa saya tiada kepentingan peribadi di dalam perkara ini dan tidak akan mendapat apa-apa manfaat secara langsung atau tidak langsung daripadanya.') }}</div>
                <div class="field bpp-signature-field">
                    <div>{{ __('Tandatangan') }}</div>
                    <div class="bpp-signature-lines">
                        <div>{{ __('Nama') }}</div>
                        <div>{{ __('Cop Rasmi') }}</div>
                        <div>{{ __('Tarikh') }}</div>
                    </div>
                </div>
            </div>
            <div class="bpp-sign-panel">
                <div class="section-title">{{ __('F: PERAKUAN KETUA JABATAN/PROJEK/TIMB KETUA PROJEK') }}</div>
                <div class="bpp-sign-copy">{{ __('Permohonan pembelian ini disokong menurut keperluan dan perancangan perolehan yang telah dikemukakan dan diperakui semua maklumat di dalam borang ini adalah benar. Saya juga mengesahkan bahawa saya telah meneliti dan mengesahkan spesifikasi teknikal yang dikemukakan bersama permohonan ini') }}</div>
                <div class="field bpp-signature-field">
                    <div class="bpp-signature-lines">
                        <div>{{ __('Nama') }}</div>
                        <div>{{ __('Cop Rasmi') }}</div>
                        <div>{{ __('Tarikh') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
