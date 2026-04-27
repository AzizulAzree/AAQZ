@php
    $isRundinganTerus = $bpp->kaedah_perolehan === 'pembekal_tunggal_rundingan_terus';
    $isSingleSourceMethod = in_array($bpp->kaedah_perolehan, ['pembekal_tunggal_bawah_50k', 'pembekal_tunggal_rundingan_terus'], true);
@endphp

<section class="bpp-preview-page checklist-page-2">
    <div class="page">
        <div class="section-title bpp-centered">{{ __('BAHAGIAN B') }}</div>
        <div class="section-title bpp-centered bpp-light-bar">{{ __('(SENARAI SEMAK PEROLEHAN BAGI PEMBEKAL/PENGEDAR TUNGGAL DAN PEMBUAT / RUNDINGAN TERUS SAHAJA)') }}</div>

        <div class="bpp-b-header row">
            <div>{{ __('BIL') }}</div>
            <div>{{ __('PERKRA') }}</div>
            <div>{{ __('SILA TANDAKAN (√) ATAU (NA) BAGI SETIAP') }}</div>
            <div>{{ __('DISEMAK PRC') }}</div>
        </div>

        <div class="bpp-b-section">
            <div class="bpp-soft-title">{{ __('UNTUK PEROLEHAN RUNDINGAN TERUS SAHAJA') }}</div>
            <div class="bpp-b-line row">
                <div class="bpp-centered">1</div>
                <div>{{ __('Memo / Surat Permohonan Perolehan daripada pemohon kepada Ketua Pegawai Eksekutif (disalinkan kepada Jabatan)') }}</div>
                <div class="bpp-check-cell"><span class="box">{{ $isRundinganTerus ? '/' : '' }}</span></div>
                <div></div>
            </div>
        </div>

        <div class="bpp-b-section">
            <div class="bpp-soft-title">{{ __('DOKUMEN UNTUK PEROLEHAN PEMBEKAL TUNGGAL DAN RUNDINGAN TERUS (SEKIRANYA ADA)') }}</div>
            @foreach ([
                'Minit Mesyuarat & Laporan JK Penilaian',
                'Dokumen sokongan pengesahan pembekal/pengedar tunggal/pembuat',
                'Sebut harga syarikat (Dengan pecahan harga terperinci berserta amaun cukai terlibat)',
                'Sijil Pendaftaran Cukai Jualan dan Perkhidmatan (CJCP) daripada Jabatan Kastam Diraja Malaysia',
                'Profil Syarikat',
                'Penyata Bank Syarikat yang lengkap (3 bulan terkini)',
                'Surat Kelulusan MOF atau mana-mana kelulusan yang berkaitan',
            ] as $index => $label)
                <div class="bpp-b-line row">
                    <div class="bpp-centered">{{ $index + 1 }}</div>
                    <div>{{ __($label) }}</div>
                    <div class="bpp-check-cell"><span class="box">{{ $isSingleSourceMethod ? '/' : '' }}</span></div>
                    <div></div>
                </div>
            @endforeach
        </div>

        <div class="bpp-b-note">{{ __('Tandatangan dibawah diperlukan untuk perakuan perolehan secara Pembekal/Pengedar Tunggal dan Pembuat / Rundingan Terus') }}</div>

        <div class="bpp-sign-grid">
            <div class="bpp-sign-panel">
                <div class="section-title bpp-centered">{{ __('PERAKUAN PEMOHON') }}</div>
                <div class="bpp-sign-copy">{{ __('Dokumen telah disemak dan disahkan benar') }}</div>
                <div class="bpp-sign-copy">{{ __('Tandatangan') }}</div>
                <div class="bpp-sign-space"></div>
                <div class="bpp-sign-labels">
                    <div>{{ __('Nama') }} :</div>
                    <div>{{ __('Cop') }} :</div>
                    <div>{{ __('Tarikh') }}:</div>
                </div>
            </div>
            <div class="bpp-sign-panel">
                <div class="section-title bpp-centered">{{ __('PERAKUAN KETUA JABATAN/KETUA PROJEK/TIMB KETUA PROJEK') }}</div>
                <div class="bpp-sign-copy">{{ __('Perolehan disokong untuk menggunakan Pembekal/Pengedar Tunggal dan Rundingan Terus') }}</div>
                <div class="bpp-sign-copy">{{ __('Tandatangan') }}</div>
                <div class="bpp-sign-space"></div>
                <div class="bpp-sign-labels">
                    <div>{{ __('Nama') }} :</div>
                    <div>{{ __('Cop') }} :</div>
                    <div>{{ __('Tarikh') }}:</div>
                </div>
            </div>
        </div>

        <div class="bpp-footer-note">
            {{ __('Nota: Kesemua dokumen yang lengkap berserta BPP perlu diserahkan secara salinan keras atau emel kepada Jabatan Perolehan (prc@nibm.my).') }}
        </div>
    </div>
</section>
