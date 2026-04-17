<section class="print-page">
    <div class="print-page-header">
        <div>
            <p class="print-form-code">{{ $bpp->ruj_dokumen ?: 'NIBM/F/PRC/02/01' }}</p>
            <h2 class="print-form-title">{{ __('C3 - Perkhidmatan') }}</h2>
            <p class="print-form-subtitle">{{ __('Lampiran Item Perkhidmatan') }}</p>
        </div>
        <div class="print-meta-stack">
            <div class="print-meta-row"><span>{{ __('No. Rujukan Perolehan') }}</span><strong>{{ $bpp->no_rujukan_perolehan ?: '-' }}</strong></div>
            <div class="print-meta-row"><span>{{ __('Tajuk Perolehan') }}</span><strong>{{ $bpp->b1_tajuk_perolehan ?: '-' }}</strong></div>
            <div class="print-meta-row"><span>{{ __('Muka Surat') }}</span><strong>{{ 'C3 / 1' }}</strong></div>
        </div>
    </div>

    <div class="print-block">
        <div class="print-grid-two">
            <div class="print-kv"><span>{{ __('Kategori Perolehan Semasa') }}</span><strong>{{ $bpp->b2_kategori_perolehan ?: '-' }}</strong></div>
            <div class="print-kv"><span>{{ __('Lampiran Aktif Draft') }}</span><strong>{{ $bpp->activeAppendixLabel() ?: '-' }}</strong></div>
        </div>
    </div>

    @if (! $isActiveAppendix)
        <div class="mt-6 print-status-banner">
            <em>{{ __('Nota:') }}</em>
            {{ __('Lampiran ini bukan lampiran aktif untuk kategori perolehan semasa, namun pratonton ini masih memaparkan data C3 yang disimpan pada draft.') }}
        </div>
    @endif

    <div class="print-section">
        <p class="print-section-title">{{ __('Butiran Item C3 - Perkhidmatan') }}</p>
        @include('bpp.printables.partials.appendix-table', ['appendixRows' => $appendixRows])
    </div>

    <div class="mt-6 print-totals-grid">
        <div class="print-total-box">
            <span>{{ __('Jumlah Baris C3') }}</span>
            <strong>{{ $appendixRows->count() }}</strong>
        </div>
        <div class="print-total-box">
            <span>{{ __('Jumlah Besar C3 (RM)') }}</span>
            <strong>{{ $appendixGrandTotal }}</strong>
        </div>
    </div>
</section>
