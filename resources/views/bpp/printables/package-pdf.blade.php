<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $bpp->title }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        @page page-five-landscape {
            size: A4 landscape;
            margin: 0;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .pdf-package-page {
            page-break-after: always;
            break-after: page;
        }

        .pdf-package-page-landscape {
            page: page-five-landscape;
            page-break-after: always;
            break-after: page;
        }

        .pdf-package-page:last-child {
            page-break-after: auto;
            break-after: auto;
        }

        .pdf-package-page-landscape:last-child {
            page-break-after: auto;
            break-after: auto;
        }

        .pdf-blank-page {
            width: 210mm;
            height: 297mm;
            margin: 0;
            background: #fff;
        }

    </style>
</head>
<body>
    @php
        $pageOneLogo = !empty($pdfLogoPath)
            ? 'file:///' . str_replace('\\', '/', $pdfLogoPath)
            : asset('images/bpp-preview/nibm-logo.png');
    @endphp
    <div class="pdf-package-page">
        @include('bpp.printables.partials.page-one-document', ['pageOneLogo' => $pageOneLogo])
    </div>
    <div class="pdf-package-page">
        @include('bpp.printables.partials.page-two-document')
    </div>
    <div class="pdf-package-page">
        @include('bpp.printables.partials.page-three-document', ['pageThreeLogo' => $pageOneLogo])
    </div>
    <div class="pdf-package-page">
        @include('bpp.printables.partials.page-four-document', ['pageFourLogo' => $pageOneLogo])
    </div>
    <div class="pdf-package-page-landscape">
        @include('bpp.printables.partials.page-five-document')
    </div>
    <div class="pdf-package-page-landscape">
        @include('bpp.printables.partials.page-six-document')
    </div>
</body>
</html>
