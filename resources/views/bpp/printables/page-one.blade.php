@php
    $pageOneLogo = !empty($pdfMode) && !empty($pdfLogoPath)
        ? 'file:///' . str_replace('\\', '/', $pdfLogoPath)
        : asset('images/bpp-preview/nibm-logo.png');
@endphp

@if (empty($pdfMode))
<x-app-layout>
    <div class="py-12 print:py-0">
        <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8 print:max-w-none print:space-y-0 print:px-0">
            @include('bpp.printables.partials.toolbar', ['previewTitle' => $previewTitle, 'bpp' => $bpp])
@endif

@include('bpp.printables.partials.page-one-document', ['pageOneLogo' => $pageOneLogo])

@if (empty($pdfMode))
        </div>
    </div>
</x-app-layout>
@endif
