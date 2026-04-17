<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <title>{{ __('BPP Package PDF') }}</title>
        <style>
            {!! $embeddedCss !!}

            body {
                margin: 0;
                font-family: DejaVu Sans, sans-serif;
                background: #ffffff;
            }

            .pdf-package-shell {
                padding: 0;
            }
        </style>
    </head>
    <body>
        <div class="pdf-package-shell">
            @include('bpp.printables.partials.pages.checklist-page', $checklistData)
            @include('bpp.printables.partials.pages.page-one-page', $pageOneData)
            @include('bpp.printables.partials.pages.page-two-page', $pageTwoData)
            @include('bpp.printables.partials.pages.c1-page', $c1Data)

            @if ($activeAppendix !== null)
                @include($activeAppendix['view'], $activeAppendix['data'])
            @else
                <section class="print-page">
                    <div class="print-page-header">
                        <div>
                            <p class="print-form-code">{{ $bpp->ruj_dokumen ?: 'NIBM/F/PRC/02/01' }}</p>
                            <h2 class="print-form-title">{{ __('Lampiran Aktif') }}</h2>
                            <p class="print-form-subtitle">{{ __('Pakej PDF BPP') }}</p>
                        </div>
                        <div class="print-meta-stack">
                            <div class="print-meta-row"><span>{{ __('No. Rujukan Perolehan') }}</span><strong>{{ $bpp->no_rujukan_perolehan ?: '-' }}</strong></div>
                            <div class="print-meta-row"><span>{{ __('Kategori Perolehan') }}</span><strong>{{ $bpp->b2_kategori_perolehan ?: '-' }}</strong></div>
                            <div class="print-meta-row"><span>{{ __('Status Readiness') }}</span><strong>{{ $validationResult['state']['label'] }}</strong></div>
                        </div>
                    </div>

                    <div class="mt-6 print-note-box">
                        <p class="print-section-label">{{ __('Nota Eksport') }}</p>
                        <p class="mt-2 text-sm text-slate-700">
                            {{ __('Lampiran aktif belum dapat ditentukan kerana kategori perolehan masih belum dipilih atau tidak sepadan dengan C2, C3, atau C4. Pakej PDF ini masih menggunakan data draft semasa tanpa mengubah apa-apa rekod.') }}
                        </p>
                    </div>
                </section>
            @endif
        </div>
    </body>
</html>
