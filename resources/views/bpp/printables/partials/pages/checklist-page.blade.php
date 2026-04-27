@php
    $methodChecks = [
        'pembelian_terus' => $bpp->kaedah_perolehan === 'pembelian_terus',
        'pembekal_tunggal_bawah_50k' => $bpp->kaedah_perolehan === 'pembekal_tunggal_bawah_50k',
        'sebut_harga' => $bpp->kaedah_perolehan === 'sebut_harga',
        'tender' => $bpp->kaedah_perolehan === 'tender',
        'pembekal_tunggal_rundingan_terus' => $bpp->kaedah_perolehan === 'pembekal_tunggal_rundingan_terus',
    ];
    $singleSource = in_array($bpp->kaedah_perolehan, ['pembekal_tunggal_bawah_50k', 'pembekal_tunggal_rundingan_terus'], true);
@endphp

<section class="bpp-preview-page checklist-page-1">
    <div class="page">
        @include('bpp.printables.partials.form-header', ['bpp' => $bpp, 'pageNumber' => '1/1'])

        <div class="bpp-line-ref">
            <div class="bpp-line-ref-label">{{ __('NO BPP') }}</div>
            <div class="bpp-line-ref-value">{{ $bpp->no_rujukan_perolehan ?: $bpp->title }}</div>
        </div>

        <div class="bpp-guide">
            <div>{{ __('Panduan:') }}</div>
            <div>i. {{ __('Sila rujuk Polisi Perolehan dan SOP berkaitan sebagai panduan mengisi borang ini') }}</div>
            <div>ii. {{ __('Sila isikan Bahagian A sahaja jika tidak melibatkan Pembekal/Pengedar Tunggal dan Pembuat/Pengilang') }}</div>
            <div>iii. {{ __('Sila isikan Bahagian A dan B sekiranya melibatkan Pembekal Tunggal dan Pembuat/Pengilang') }}</div>
            <div>iv. {{ __('Tandatangan sokongan adalah diperlukan untuk permohonan perolehan bagi Pembekal Tunggal dan Pembuat/Pengilang di Bahagian B') }}</div>
        </div>

        <div class="bpp-checklist-head row">
            <div>{{ __('BIL.') }}</div>
            <div>{{ __('PERKARA') }}</div>
            <div>{{ __('DITANDATANGAN PEMOHON') }}</div>
            <div>{{ __('DISEMAK PRC') }}</div>
        </div>

        <div class="section-title bpp-centered">{{ __('BAHAGIAN A') }}</div>

        <div class="bpp-checklist-block">
            <div class="bpp-checklist-bil">1</div>
            <div class="bpp-checklist-body">
                <div class="bpp-soft-title">{{ __('Borang permohonan yang lengkap ditandatangan') }}</div>
                @foreach ([
                    ['1.1 Pemohon', filled($bpp->a1_nama_pemohon)],
                    ['1.2 Sokongan Ketua Bahagian/Ketua Jabatan/Ketua Projek/Timbalan Ketua Projek', $singleSource],
                    ['1.3 Semakan PMD', false],
                    ['1.4 Semakan Bahagian Kewangan', false],
                    ['1.5 Semakan Bahagian Akaun', false],
                    ['1.6 Kelulusan Pre-Sanction Ketua Pegawai Kewangan', false],
                ] as [$label, $checked])
                    <div class="bpp-checklist-item row">
                        <div class="bpp-checklist-text">{{ __($label) }}</div>
                        <div class="bpp-check-cell"><span class="box">{{ $checked ? '/' : '' }}</span></div>
                        <div class="bpp-check-cell"></div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bpp-checklist-block">
            <div class="bpp-checklist-bil">2</div>
            <div class="bpp-checklist-body">
                <div class="bpp-soft-title">{{ __('Kategori Perolehan') }}</div>
                <div class="bpp-checklist-item row bpp-strong-row">
                    <div class="bpp-checklist-text">{{ __('2.1 Pembelian Terus (tidak melebihi RM 5,000.00)') }}</div>
                    <div class="bpp-check-cell"><span class="box">{{ $methodChecks['pembelian_terus'] ? '/' : '' }}</span></div>
                    <div class="bpp-check-cell"></div>
                </div>
                <div class="bpp-checklist-item row bpp-strong-row">
                    <div class="bpp-checklist-text">{{ __('2.2 Pembelian Terus (melebihi RM 5,000.00 hingga tidak melebihi RM 50,000.00)') }}</div>
                    <div class="bpp-check-cell"><span class="box">{{ $methodChecks['pembekal_tunggal_bawah_50k'] ? '/' : '' }}</span></div>
                    <div class="bpp-check-cell"></div>
                </div>
                <div class="bpp-sub-grid row">
                    <div>{{ __('Sebutharga (quotation)') }}</div>
                    <div class="bpp-centered">{{ __('< 2') }}</div>
                    <div class="bpp-centered">{{ __('≥ 3') }}</div>
                    <div>{{ __('Justifikasi tanpa 3 sebut harga') }}</div>
                </div>
                <div class="bpp-sub-grid row">
                    <div>{{ __('Tanda (√) atau (X)') }}</div>
                    <div></div>
                    <div class="bpp-centered">{{ $methodChecks['sebut_harga'] ? '/' : '' }}</div>
                    <div></div>
                </div>
                <div class="bpp-sub-grid row">
                    <div>{{ __('Tandatangan PBM tanpa 3 sebut harga') }}</div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
                <div class="bpp-checklist-item row">
                    <div class="bpp-checklist-text">{{ __('2.3 Kajian pasaran') }}</div>
                    <div class="bpp-check-cell"><span class="box">{{ $methodChecks['sebut_harga'] ? '/' : '' }}</span></div>
                    <div class="bpp-check-cell"></div>
                </div>
                <div class="bpp-checklist-item row bpp-strong-row">
                    <div class="bpp-checklist-text">{{ __('2.4 Tender/Sebutharga (melebihi RM50,000.00)') }}</div>
                    <div class="bpp-check-cell"><span class="box">{{ ($methodChecks['sebut_harga'] || $methodChecks['tender']) ? '/' : '' }}</span></div>
                    <div class="bpp-check-cell"></div>
                </div>
                @foreach ([
                    'a. Senarai kehadiran JK Spesifikasi',
                    'b. Minit Mesyuarat & Laporan JK Spesifikasi',
                    'c. Jadual 2 - Spesifikasi Teknikal',
                    'd. Jadual 1A - Jadual Kadar Harga (Kuantiti)',
                    'e. Jadual 1B - Jadual Kadar Harga (Pukal)',
                    'f. Jadual 3 - Jadual Kaedah Perolehan',
                    'g. Jadual 4 - Jadual Perlaksanaan Perkhidmatan/Pembekalan/Kerja',
                    'h. Jadual 5 - Jadual Pembayaran',
                    'i. Integrity Pact selesai menjalan tugas Jawatankuasa Perolehan',
                ] as $label)
                    <div class="bpp-checklist-item row bpp-indent-row">
                        <div class="bpp-checklist-text">{{ __($label) }}</div>
                        <div class="bpp-check-cell"></div>
                        <div class="bpp-check-cell"></div>
                    </div>
                @endforeach
                <div class="bpp-checklist-item row bpp-strong-row">
                    <div class="bpp-checklist-text">{{ __('2.3 Pembekal/Pengedar Tunggal/Pembuat/Pengeluar (Isikan bahagian B)') }}</div>
                    <div class="bpp-check-cell"><span class="box">{{ $singleSource ? '/' : '' }}</span></div>
                    <div class="bpp-check-cell"></div>
                </div>
                <div class="bpp-checklist-item row bpp-strong-row">
                    <div class="bpp-checklist-text">{{ __('2.4 Rundingan Terus - kepilkan borang senarai semak rundingan terus (Isikan bahagian B)') }}</div>
                    <div class="bpp-check-cell"><span class="box">{{ $methodChecks['pembekal_tunggal_rundingan_terus'] ? '/' : '' }}</span></div>
                    <div class="bpp-check-cell"></div>
                </div>
            </div>
        </div>

        <div class="bpp-checklist-block">
            <div class="bpp-checklist-bil">3</div>
            <div class="bpp-checklist-body">
                <div class="bpp-soft-title">{{ __('Maklumat Tambahan') }}</div>
                <div class="bpp-checklist-item row bpp-strong-row">
                    <div class="bpp-checklist-text">{{ __('3.1 Maklumat tambahan (bagi perolehan penyelenggaraan melebihi RM 50,000)') }}</div>
                    <div class="bpp-check-cell"></div>
                    <div class="bpp-check-cell"></div>
                </div>
                @foreach ([
                    ['a. Nilai perolehan 2 tahun terakhir (WAJIB)', filled($bpp->b12_nilai_perolehan_2_tahun_lalu)],
                    ['b. Rekod/Senarai Pihak atau individu yang menggunakan alat tersebut / rekod penggunaan 2 tahun terakhir', $bpp->b17_rekod_senarai_pihak_pengguna],
                    ['c. Analisis ROI/ROV penggunaan alat', $bpp->b16_kepilkan_analisis_roi_rov],
                ] as [$label, $checked])
                    <div class="bpp-checklist-item row bpp-indent-row">
                        <div class="bpp-checklist-text">{{ __($label) }}</div>
                        <div class="bpp-check-cell"><span class="box">{{ $checked ? '/' : '' }}</span></div>
                        <div class="bpp-check-cell"></div>
                    </div>
                @endforeach
                <div class="bpp-checklist-item row bpp-strong-row">
                    <div class="bpp-checklist-text">{{ __('3.2 Perolehan Pembaikan (peralatan atau bangunan) - Kepilkan laporan/aduan kerosakan (WAJIB)') }}</div>
                    <div class="bpp-check-cell"><span class="box">{{ $bpp->b18_salinan_laporan_kerosakan ? '/' : '' }}</span></div>
                    <div class="bpp-check-cell"></div>
                </div>
            </div>
        </div>

        <div class="bpp-checklist-block">
            <div class="bpp-checklist-bil">4</div>
            <div class="bpp-checklist-body">
                <div class="bpp-soft-title">{{ __('Dokumen sokongan syarikat & sijil pendaftaran yang masih berkuatkuasa') }}</div>
                <div class="bpp-cert-grid row">
                    <div>{{ __('Sijil') }}</div>
                    <div>{{ __('SSM / ROB / ROC') }}</div>
                    <div>{{ __('MOF') }}</div>
                    <div>{{ __('PKK') }}</div>
                    <div>{{ __('CIDB') }}</div>
                    <div>{{ __('Ordinan Perlesenan Perdagangan') }}</div>
                </div>
                <div class="bpp-cert-grid row">
                    <div>{{ __('Tandakan (√)') }}</div>
                    <div class="bpp-centered">/</div>
                    <div class="bpp-centered">{{ filled($bpp->d_no_pendaftaran_syarikat) ? '/' : '' }}</div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            </div>
        </div>

        @foreach ([
            ['5', 'Penyata kewangan terkini syarikat/maklumat perbankan', filled($bpp->d_nama_pembekal)],
            ['6', 'Keratan/Cabutan/Dokumen kelulusan bajet (Cabutan BOT / JKE / Management Committee Meeting)', false],
            ['7', 'Borang Aduan Kerosakan (Membaikpulih, Kalibrasi & Menyelenggara Peralatan)', $bpp->b18_salinan_laporan_kerosakan],
            ['8', 'Minit Mesyuarat JTICT', false],
            ['9', 'Integrity Pact', false],
            ['10', 'Lain-lain dokumen jika berkaitan', false],
        ] as [$number, $label, $checked])
            <div class="bpp-checklist-tail row">
                <div class="bpp-checklist-tail-bil">{{ $number }}</div>
                <div class="bpp-checklist-tail-text">{{ __($label) }}</div>
                <div class="bpp-check-cell"><span class="box">{{ $checked ? '/' : '' }}</span></div>
                <div class="bpp-check-cell"></div>
            </div>
        @endforeach
    </div>
</section>
