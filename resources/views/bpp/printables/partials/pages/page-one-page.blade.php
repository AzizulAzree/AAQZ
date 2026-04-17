<section class="print-page">
    <div class="print-page-header">
        <div>
            <p class="print-form-code">{{ $bpp->ruj_dokumen ?: 'NIBM/F/PRC/02/01' }}</p>
            <h2 class="print-form-title">{{ __('Borang Permohonan Perolehan') }}</h2>
            <p class="print-form-subtitle">{{ __('BPP Page 1') }}</p>
        </div>
        <div class="print-meta-stack">
            <div class="print-meta-row"><span>{{ __('Ruj. Dokumen') }}</span><strong>{{ $bpp->ruj_dokumen ?: 'NIBM/F/PRC/02/01' }}</strong></div>
            <div class="print-meta-row"><span>{{ __('No. Semakan') }}</span><strong>{{ $bpp->no_semakan ?: '01' }}</strong></div>
            <div class="print-meta-row"><span>{{ __('Tarikh Kuat Kuasa') }}</span><strong>{{ $bpp->tarikh_kuat_kuasa ?: '-' }}</strong></div>
            <div class="print-meta-row"><span>{{ __('Muka Surat') }}</span><strong>{{ $bpp->muka_surat ?: '1 / 2' }}</strong></div>
        </div>
    </div>

    <div class="print-block">
        <table class="print-table">
            <tbody>
                <tr>
                    <th style="width: 15rem;">{{ __('No. Rujukan Perolehan') }}</th>
                    <td>{{ $bpp->no_rujukan_perolehan ?: '-' }}</td>
                    <th style="width: 10rem;">{{ __('Tajuk Dokumen') }}</th>
                    <td>{{ $bpp->tajuk_dokumen ?: 'Borang Permohonan Perolehan' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="print-section">
        <p class="print-section-title">{{ __('A. Perihal Pemohon') }}</p>
        <table class="print-table">
            <tbody>
                <tr><th style="width: 13rem;">{{ __('A1. Nama Pemohon') }}</th><td>{{ $bpp->a1_nama_pemohon ?: '-' }}</td></tr>
                <tr><th>{{ __('A2. Jawatan / Gred') }}</th><td>{{ $bpp->a2_jawatan_gred ?: '-' }}</td></tr>
                <tr><th>{{ __('A3. Jabatan / Institusi') }}</th><td>{{ $bpp->a3_jabatan_institusi ?: '-' }}</td></tr>
                <tr><th>{{ __('A4. No. Tel / E-mel') }}</th><td>{{ $bpp->a4_no_tel_email ?: '-' }}</td></tr>
            </tbody>
        </table>
    </div>

    <div class="print-section">
        <p class="print-section-title">{{ __('Kaedah Perolehan') }}</p>
        <div class="print-lined-field">{{ $bpp->kaedah_perolehan ?: '' }}</div>
    </div>

    <div class="print-section">
        <p class="print-section-title">{{ __('B(I). Perihal Perolehan') }}</p>
        <table class="print-table">
            <tbody>
                <tr><th style="width: 13rem;">{{ __('B1. Tajuk Perolehan') }}</th><td colspan="3">{{ $bpp->b1_tajuk_perolehan ?: '-' }}</td></tr>
                <tr>
                    <th>{{ __('B2. Kategori Perolehan') }}</th>
                    <td>{{ $bpp->b2_kategori_perolehan ?: '-' }}</td>
                    <th style="width: 13rem;">{{ __('B3. Nilai Tawaran Perolehan') }}</th>
                    <td>{{ $bpp->displayCurrency($bpp->b3_nilai_tawaran_perolehan) ?: '-' }}</td>
                </tr>
                <tr>
                    <th>{{ __('B4. Harga Indikatif') }}</th>
                    <td>{{ $bpp->displayCurrency($bpp->b4_harga_indikatif) ?: '-' }}</td>
                    <th>{{ __('B5. Peruntukan Diluluskan') }}</th>
                    <td>{{ $bpp->displayCurrency($bpp->b5_peruntukan_diluluskan) ?: '-' }}</td>
                </tr>
                <tr><th>{{ __('B6. Justifikasi Keperluan') }}</th><td colspan="3" class="print-multiline">{{ $bpp->b6_justifikasi_keperluan ?: '-' }}</td></tr>
                <tr>
                    <th>{{ __('B7. Tajuk Asal Perolehan') }}</th>
                    <td>{{ $bpp->b7_tajuk_asal_perolehan ?: '-' }}</td>
                    <th>{{ __('B8. Tarikh Diperlukan') }}</th>
                    <td>{{ $bpp->procurementRequiredMonthLabel() ?: '-' }}</td>
                </tr>
                <tr><th>{{ __('B9. Lokasi Diperlukan') }}</th><td colspan="3">{{ $bpp->b9_lokasi_diperlukan ?: '-' }}</td></tr>
            </tbody>
        </table>
    </div>

    <div class="print-section">
        <p class="print-section-title">{{ __('D. Pembekal yang Disyorkan') }}</p>
        <table class="print-table">
            <tbody>
                <tr><th style="width: 13rem;">{{ __('Nama Pembekal') }}</th><td>{{ $bpp->d_nama_pembekal ?: '-' }}</td></tr>
                <tr><th>{{ __('Alamat Pembekal') }}</th><td>{{ $bpp->d_alamat_pembekal ?: '-' }}</td></tr>
                <tr><th>{{ __('No. Pendaftaran Syarikat') }}</th><td>{{ $bpp->d_no_pendaftaran_syarikat ?: '-' }}</td></tr>
                <tr><th>{{ __('Kriteria Pemilihan') }}</th><td class="print-multiline">{{ $bpp->d_kriteria_pemilihan ?: '-' }}</td></tr>
                <tr><th>{{ __('Lain-lain Kriteria') }}</th><td class="print-multiline">{{ $bpp->d_lain_lain_kriteria ?: '-' }}</td></tr>
            </tbody>
        </table>
    </div>

    <div class="print-grid-two mt-6">
        <div class="print-placeholder-panel">
            <p class="print-section-title">{{ __('E. Ulasan / Catatan') }}</p>
            <div class="print-signature-box"></div>
        </div>
        <div class="print-placeholder-panel">
            <p class="print-section-title">{{ __('F. Sokongan / Pengesahan') }}</p>
            <div class="print-signature-box"></div>
        </div>
    </div>
</section>
