<section class="bpp-preview-page bpp-page-2">
    <div class="page">
        @include('bpp.printables.partials.form-header', ['bpp' => $bpp, 'pageNumber' => '2 dari 2'])

        <div class="section-title">{{ __('G: SEMAKAN JABATAN PENGURUSAN PROJEK (JIKA PERLU)') }}</div>
        <div class="row bpp-field-row bpp-short-line">
            <div>{{ __('Tarikh BPP diterima') }}</div>
            <div>:</div>
            <div class="field"></div>
        </div>
        <div class="bpp-inline-row">
            <span>{{ __('G1. Permohonan ini adalah diperakukan telah disemak dan disahkan mengikut skop/item yang diluluskan') }}</span>
            <span class="box">/</span><span>{{ __('Ya') }}</span>
            <span class="box"></span><span>{{ __('Tidak') }}</span>
        </div>
        <div class="row bpp-field-row">
            <div>{{ __('G2. Nama geran/projek') }}</div>
            <div>:</div>
            <div class="field serif">{{ $bpp->b7_tajuk_asal_perolehan ?: $bpp->b1_tajuk_perolehan }}</div>
        </div>
        <div class="bpp-g-grid">
            <div class="field field-panel"><strong>{{ __('G3. Ulasan') }} :</strong></div>
            <div class="field field-panel">
                <div>{{ __('Tandatangan') }}</div>
                <div class="bpp-signature-lines bpp-signature-lines-compact">
                    <div>{{ __('Nama') }} :</div>
                    <div>{{ __('Cop Rasmi') }} :</div>
                    <div>{{ __('Tarikh') }} :</div>
                </div>
            </div>
        </div>

        <div class="section-title">{{ __('H: SEMAKAN JABATAN KEWANGAN DAN AKAUN (Kelulusan peruntukan dan baki tunai)') }}</div>
        <div class="bpp-h-top">
            <div class="row bpp-field-row bpp-short-line">
                <div>{{ __('Tarikh BPP diterima dari pemohon/PMD') }}</div>
                <div>:</div>
                <div class="field"></div>
            </div>
            <div class="row bpp-field-row bpp-short-line">
                <div>{{ __('Tarikh BPP diterima dari Seksyen Kewangan') }}</div>
                <div>:</div>
                <div class="field"></div>
            </div>
        </div>
        <div class="bpp-h-grid">
            <div class="bpp-h-left">
                @foreach ([
                    'H1. Mengurus/Hasil    Kod perbelanjaan',
                    'H2. Pembangunan/Projek',
                    'H3. Jumlah Kod Belanja Diluluskan',
                    'H4. Baki Kod Belanja Sebelum',
                    'H5. Baki Kod Belanja Selepas Perolehan ini',
                ] as $label)
                    <div class="row bpp-field-row">
                        <div>{{ __($label) }}</div>
                        <div>:</div>
                        <div class="field"></div>
                    </div>
                @endforeach
                <div class="bpp-inline-row">
                    <span>{{ __('H6. Peruntukan adalah seperti yang diluluskan') }}</span>
                    <span class="box"></span><span>{{ __('Ya') }}</span>
                    <span class="box"></span><span>{{ __('Tidak') }}</span>
                </div>
                <div class="row bpp-field-row">
                    <div>{{ __('H7. Butiran Bank Pembayar') }}</div>
                    <div>:</div>
                    <div class="field"></div>
                </div>
                <div class="field field-panel bpp-panel-short"><strong>{{ __('Catatan Seksyen Kewangan:') }}</strong></div>
            </div>
            <div class="bpp-h-right">
                @foreach ([
                    'OS',
                    'Kod Projek',
                ] as $label)
                    <div class="row bpp-field-row">
                        <div>{{ __($label) }}</div>
                        <div>:</div>
                        <div class="field"></div>
                    </div>
                @endforeach
                <div class="field field-panel bpp-panel-tall"><strong>{{ __('Catatan Seksyen Akaun:') }}</strong></div>
                <div class="row bpp-field-row">
                    <div>{{ __('H9. Baki Tunai') }}</div>
                    <div>:</div>
                    <div class="field"></div>
                </div>
                <div class="row bpp-field-row">
                    <div>{{ __('H10. Tanggungan') }}</div>
                    <div>:</div>
                    <div class="field"></div>
                </div>
            </div>
        </div>
        <div class="bpp-sign-grid">
            <div class="field field-panel bpp-sign-approval">
                <div>{{ __('H8. Perakuan Seksyen Kewangan') }}</div>
                <div>{{ __('Tandatangan') }}</div>
                <div class="bpp-signature-lines bpp-signature-lines-compact">
                    <div>{{ __('Nama') }}</div>
                    <div>{{ __('Cop Rasmi') }}</div>
                    <div>{{ __('Tarikh') }}</div>
                </div>
            </div>
            <div class="field field-panel bpp-sign-approval">
                <div>{{ __('H11. Perakuan Seksyen Akaun') }}</div>
                <div>{{ __('Tandatangan') }}</div>
                <div class="bpp-signature-lines bpp-signature-lines-compact">
                    <div>{{ __('Nama') }}</div>
                    <div>{{ __('Cop Rasmi') }}</div>
                    <div>{{ __('Tarikh') }}</div>
                </div>
            </div>
        </div>

        <div class="section-title">{{ __('I: SEMAKAN JABATAN PEROLEHAN') }}</div>
        <div class="row bpp-field-row bpp-short-line">
            <div>{{ __('Tarikh BPP lengkap') }}</div>
            <div>:</div>
            <div class="field"></div>
        </div>
        <div class="bpp-sign-grid">
            <div class="bpp-i-left">
                <div class="section-title bpp-light-bar">{{ __('I1. Keperluan Sijil-sijil dan dokumen sokongan') }}</div>
                @foreach ([
                    'Keperluan/Lampiran/Sijil Pendaftaran/Dokumen lain yang diperlukan',
                    'Borang permohonan yang lengkap bertandatangan',
                    'Sebutharga yang lengkap',
                    'Dokumen pembekal tunggal',
                    'Sijil pendaftaran entiti',
                    'Lampiran-lampiran berkaitan',
                ] as $label)
                    <div class="bpp-box-line row">
                        <div>{{ __($label) }}</div>
                        <div class="bpp-check-cell"><span class="box"></span></div>
                    </div>
                @endforeach
                <div class="bpp-inline-row">
                    <span>{{ __('Kod bidang') }}</span><span class="box"></span>
                    <span>{{ __('Butiran kod bidang') }}</span><span class="field bpp-inline-field"></span>
                </div>
                <div class="bpp-box-line row">
                    <div>{{ __('Lain-lain dokumen') }}</div>
                    <div class="bpp-check-cell"><span class="box"></span></div>
                </div>
            </div>
            <div class="field field-panel bpp-i-right">
                <div class="section-title bpp-light-bar">{{ __('I2. Pengesahan Bahagian Perolehan') }}</div>
                <div class="bpp-sign-copy">{{ __('Disahkan bahawa permohonan ini mematuhi Polisi dan Prosedur Perolehan NIBM. Maklumat pada item H adalah lengkap mengikut keperluan perolehan yang dimohon.') }}</div>
                <div>{{ __('Tandatangan') }}</div>
                <div class="bpp-signature-lines bpp-signature-lines-compact">
                    <div>{{ __('Nama') }} :</div>
                    <div>{{ __('Cop rasmi') }} :</div>
                    <div>{{ __('Tarikh') }} :</div>
                </div>
            </div>
        </div>

        <div class="section-title">{{ __('J: SOKONGAN DAN PERAKUAN PIHAK BERKUASA MELULUS') }}</div>
        <div class="field field-panel">
            <div>{{ __('Bahagian ini perlu ditandatangan oleh pihak yang diberi kuasa/PBM untuk menyokong dan meluluskan untuk perolehan diteruskan bagi:') }}</div>
            <ol class="bpp-ordered-list">
                <li>{{ __('Perolehan bagi Pembekal/Pengedar Tunggal/Pembuat/Pengilang tanpa dokumen sokongan yang jelas menyatakannya') }}</li>
                <li>{{ __('Kajian pasaran tidak sempurna / jumlah sebutharga tidak mencukupi') }}</li>
                <li>{{ __('Perolehan bagi Sebut Harga dan Tender') }}</li>
                <li>{{ __('Perkara-perkara lain yang memerlukan sokongan PBM') }}</li>
            </ol>
            <div>{{ __('Pihak yang diberi kuasa/PBM perlu berpuas hati dan memahami permohonan yang dikemukakan sebelum perolehan ini disokong dan diluluskan untuk diteruskan.') }}</div>
        </div>
        <div class="bpp-inline-row bpp-approval-row">
            <span class="box"></span><span>{{ __('Diluluskan') }}</span>
            <span class="box"></span><span>{{ __('Tidak diluluskan') }}</span>
        </div>
        <div class="bpp-sign-grid">
            <div class="field field-panel bpp-panel-medium"><strong>{{ __('Ulasan') }} :</strong></div>
            <div class="field field-panel bpp-panel-medium">
                <div>{{ __('Tandatangan') }}</div>
                <div class="bpp-signature-lines bpp-signature-lines-compact">
                    <div>{{ __('Nama') }}</div>
                    <div>{{ __('Cop Rasmi') }}</div>
                    <div>{{ __('Tarikh') }}</div>
                </div>
            </div>
        </div>

        <div class="section-title">{{ __('K: PERAKUAN PRE-SANCTION CFO') }}</div>
        <div class="bpp-sign-grid">
            <div class="field field-panel bpp-panel-medium"><strong>{{ __('Ulasan') }} :</strong></div>
            <div class="field field-panel bpp-panel-medium">
                <div>{{ __('Tandatangan') }}</div>
                <div class="bpp-signature-lines bpp-signature-lines-compact">
                    <div>{{ __('Nama') }}</div>
                    <div>{{ __('Cop Rasmi') }}</div>
                    <div>{{ __('Tarikh') }}</div>
                </div>
            </div>
        </div>
    </div>
</section>
