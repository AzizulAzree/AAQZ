<section class="print-page">
    <div class="print-page-header">
        <div>
            <p class="print-form-code">{{ $bpp->ruj_dokumen ?: 'NIBM/F/PRC/02/01' }}</p>
            <h2 class="print-form-title">{{ __('Borang Permohonan Perolehan') }}</h2>
            <p class="print-form-subtitle">{{ __('BPP Page 2') }}</p>
        </div>
        <div class="print-meta-stack">
            <div class="print-meta-row"><span>{{ __('No. Rujukan Perolehan') }}</span><strong>{{ $bpp->no_rujukan_perolehan ?: '-' }}</strong></div>
            <div class="print-meta-row"><span>{{ __('Tajuk Perolehan') }}</span><strong>{{ $bpp->b1_tajuk_perolehan ?: '-' }}</strong></div>
            <div class="print-meta-row"><span>{{ __('Muka Surat') }}</span><strong>{{ $bpp->muka_surat ?: '2 / 2' }}</strong></div>
        </div>
    </div>

    <div class="print-block">
        <table class="print-table">
            <tbody>
                <tr>
                    <th style="width: 13rem;">{{ __('Kategori Perolehan') }}</th>
                    <td>{{ $bpp->b2_kategori_perolehan ?: '-' }}</td>
                    <th style="width: 13rem;">{{ __('Nilai Tawaran Perolehan') }}</th>
                    <td>{{ $bpp->displayCurrency($bpp->b3_nilai_tawaran_perolehan) ?: '-' }}</td>
                </tr>
                <tr>
                    <th>{{ __('Pembekal Disyorkan') }}</th>
                    <td colspan="3">{{ $bpp->d_nama_pembekal ?: '-' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="print-section">
        <p class="print-section-title">{{ __('G. Ulasan Tambahan / Ringkasan Penilaian') }}</p>
        <div class="print-large-writing-box">
            @if (filled($bpp->b6_justifikasi_keperluan))
                {{ $bpp->b6_justifikasi_keperluan }}
            @endif
        </div>
    </div>

    <div class="print-grid-two mt-6">
        <div class="print-placeholder-panel">
            <p class="print-section-title">{{ __('H. Perakuan Pemohon') }}</p>
            <div class="print-signature-box"></div>
            <div class="mt-4 grid gap-2 text-sm text-slate-700">
                <div class="print-sign-row"><span>{{ __('Nama') }}</span><span></span></div>
                <div class="print-sign-row"><span>{{ __('Jawatan') }}</span><span></span></div>
                <div class="print-sign-row"><span>{{ __('Tarikh') }}</span><span></span></div>
            </div>
        </div>
        <div class="print-placeholder-panel">
            <p class="print-section-title">{{ __('I. Sokongan Ketua Jabatan / Institusi') }}</p>
            <div class="print-signature-box"></div>
            <div class="mt-4 grid gap-2 text-sm text-slate-700">
                <div class="print-sign-row"><span>{{ __('Nama') }}</span><span></span></div>
                <div class="print-sign-row"><span>{{ __('Jawatan') }}</span><span></span></div>
                <div class="print-sign-row"><span>{{ __('Tarikh') }}</span><span></span></div>
            </div>
        </div>
    </div>

    <div class="print-section mt-6">
        <p class="print-section-title">{{ __('J. Keputusan / Kelulusan') }}</p>
        <table class="print-table">
            <tbody>
                <tr>
                    <th style="width: 14rem;">{{ __('Disokong / Tidak Disokong') }}</th>
                    <td></td>
                </tr>
                <tr>
                    <th>{{ __('Diluluskan / Tidak Diluluskan') }}</th>
                    <td></td>
                </tr>
                <tr>
                    <th>{{ __('Catatan') }}</th>
                    <td style="height: 7rem;"></td>
                </tr>
            </tbody>
        </table>
    </div>
</section>
