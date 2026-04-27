@php
    $pageNumber = $pageNumber ?? '1/1';
    $logoSrc = $pdfLogoPath ?? asset('images/bpp-preview/nibm-logo.png');
@endphp

<div class="bpp-form-header">
    <div class="bpp-form-header-logo">
        <img src="{{ $logoSrc }}" alt="{{ __('NIBM') }}" class="bpp-form-header-logo-image">
    </div>
    <div class="bpp-form-header-main">
        <div class="bpp-form-header-borang">{{ __('BORANG') }}</div>
        <div class="bpp-form-header-doc">
            <div>{{ __('Tajuk Dokumen:') }}</div>
            <strong>{{ $bpp->tajuk_dokumen ?: 'Borang Permohonan Perolehan' }}</strong>
        </div>
        <div class="bpp-form-header-meta">
            <div class="bpp-form-header-meta-cell">
                <div>{{ __('Ruj. Dokumen:') }}</div>
                <strong>{{ $bpp->ruj_dokumen ?: 'NIBM/F/PRC/02/01' }}</strong>
            </div>
            <div class="bpp-form-header-meta-cell">
                <div>{{ __('No. Semakan:') }}</div>
                <strong>{{ $bpp->no_semakan ?: '01' }}</strong>
            </div>
            <div class="bpp-form-header-meta-cell">
                <div>{{ __('Tarikh Kuat Kuasa:') }}</div>
                <strong>{{ $bpp->tarikh_kuat_kuasa ?: '01 DISEMBER 2025' }}</strong>
            </div>
            <div class="bpp-form-header-meta-cell">
                <div>{{ __('Muka surat') }}</div>
                <strong>{{ $pageNumber }}</strong>
            </div>
        </div>
    </div>
</div>
