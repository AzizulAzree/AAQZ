<section class="{{ !empty($packagePdfMode) ? 'c2-page pdf-package-page' : 'print-page c2-page' }}">
    <div class="print-page-header">
        <div>
            <p class="print-form-code">{{ $bpp->ruj_dokumen ?: 'NIBM/F/PRC/02/01' }}</p>
            <h2 class="print-form-title">{{ __('C2 - Perbekalan') }}</h2>
            <p class="print-form-subtitle">{{ __('Lampiran Item Perbekalan') }}</p>
        </div>
        <div class="print-meta-stack">
            <div class="print-meta-row"><span>{{ __('No. Rujukan Perolehan') }}</span><strong>{{ $bpp->no_rujukan_perolehan ?: '-' }}</strong></div>
            <div class="print-meta-row"><span>{{ __('Tajuk Perolehan') }}</span><strong>{{ $bpp->b1_tajuk_perolehan ?: '-' }}</strong></div>
            <div class="print-meta-row"><span>{{ __('Muka Surat') }}</span><strong>{{ 'C2 / 1' }}</strong></div>
        </div>
    </div>

    <div class="print-block">
        <div class="print-grid-two">
            <div class="print-kv"><span>{{ __('Kategori Perolehan Semasa') }}</span><strong>{{ $bpp->b2_kategori_perolehan ?: '-' }}</strong></div>
            <div class="print-kv"><span>{{ __('Lampiran Aktif Draft') }}</span><strong>{{ $bpp->activeAppendixLabel() ?: '-' }}</strong></div>
        </div>
    </div>

    @if (! $isActiveAppendix && empty($forcePackageMode))
        <div class="mt-6 print-status-banner">
            <em>{{ __('Nota:') }}</em>
            {{ __('Lampiran ini bukan lampiran aktif untuk kategori perolehan semasa, namun pratonton ini masih memaparkan data C2 yang disimpan pada draft.') }}
        </div>
    @endif

    <div class="print-section">
        <p class="print-section-title">{{ __('Butiran Item C2 - Perbekalan') }}</p>
        @include('bpp.printables.partials.appendix-table', ['appendixRows' => $appendixRows])
    </div>

    <div class="mt-6 print-totals-grid">
        <div class="print-total-box">
            <span>{{ __('Jumlah Baris C2') }}</span>
            <strong>{{ $appendixRows->count() }}</strong>
        </div>
        <div class="print-total-box">
            <span>{{ __('Jumlah Besar C2 (RM)') }}</span>
            <strong>{{ $appendixGrandTotal }}</strong>
        </div>
    </div>
</section>
