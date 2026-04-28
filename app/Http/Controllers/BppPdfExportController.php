<?php

namespace App\Http\Controllers;

use App\Models\Bpp;
use App\Services\BppPrintableViewService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use RuntimeException;
use setasign\Fpdi\Fpdi;
use Symfony\Component\Process\Process;

class BppPdfExportController extends Controller
{
    public function export(Bpp $bpp, BppPrintableViewService $printableViewService): Response
    {
        @set_time_limit(300);

        $exportDir = storage_path('app/browser-pdf-export');
        File::ensureDirectoryExists($exportDir);

        $token = (string) Str::uuid();
        $mergedPdfPath = $exportDir.DIRECTORY_SEPARATOR.$token.'-merged.pdf';
        $logoPath = public_path('images/bpp-preview/nibm-logo.png');
        $logoUri = 'file:///'.str_replace('\\', '/', $logoPath);

        $pages = [
            [
                'view' => 'bpp.printables.partials.page-one-document',
                'orientation' => 'portrait',
                'data' => ['pageOneLogo' => $logoUri],
            ],
            [
                'view' => 'bpp.printables.partials.page-two-document',
                'orientation' => 'portrait',
                'data' => [],
            ],
            [
                'view' => 'bpp.printables.partials.page-three-document',
                'orientation' => 'portrait',
                'data' => ['pageThreeLogo' => $logoUri],
            ],
            [
                'view' => 'bpp.printables.partials.page-four-document',
                'orientation' => 'portrait',
                'data' => ['pageFourLogo' => $logoUri],
            ],
            [
                'view' => 'bpp.printables.partials.page-five-document',
                'orientation' => 'landscape',
                'data' => [],
            ],
            [
                'view' => 'bpp.printables.partials.page-six-document',
                'orientation' => 'portrait',
                'data' => [],
            ],
        ];

        $pagePdfPaths = [];

        try {
            foreach ($pages as $index => $page) {
                $pageHtml = $page['html'] ?? $this->pageDocumentHtml(
                    $page['view'],
                    $bpp,
                    $page['orientation'],
                    $page['data'] ?? []
                );

                $pagePdfPaths[] = $this->renderHtmlToPdf(
                    $pageHtml,
                    $page['orientation'],
                    $exportDir,
                    $token.'-page-'.($index + 1)
                );
            }

            $this->mergePdfFiles($pagePdfPaths, $mergedPdfPath);

            if (! File::exists($mergedPdfPath)) {
                throw new RuntimeException('Merged PDF export failed.');
            }

            $pdfBytes = File::get($mergedPdfPath);
        } finally {
            foreach ($pagePdfPaths as $pagePdfPath) {
                File::delete($pagePdfPath);
            }

            File::delete($mergedPdfPath);
        }

        return response(
            $pdfBytes,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$this->downloadName($bpp).'"',
            ]
        );
    }

    private function pageDocumentHtml(string $view, Bpp $bpp, string $orientation, array $data = []): string
    {
        $content = View::make($view, array_merge(['bpp' => $bpp], $data))->render();

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @page {
            size: A4 {$orientation};
            margin: 0;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: #fff;
        }
    </style>
</head>
<body>
{$content}
</body>
</html>
HTML;
    }

    private function blankPageHtml(string $orientation): string
    {
        $width = $orientation === 'landscape' ? '297mm' : '210mm';
        $height = $orientation === 'landscape' ? '210mm' : '297mm';

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        @page {
            size: A4 {$orientation};
            margin: 0;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: #fff;
        }
    </style>
</head>
<body>
    <div style="width: {$width}; height: {$height}; background: #fff;"></div>
</body>
</html>
HTML;
    }

    private function renderHtmlToPdf(string $html, string $orientation, string $exportDir, string $token): string
    {
        $htmlPath = $exportDir.DIRECTORY_SEPARATOR.$token.'.html';
        $pdfPath = $exportDir.DIRECTORY_SEPARATOR.$token.'.pdf';
        $profilePath = $exportDir.DIRECTORY_SEPARATOR.'edge-profile-'.$token;

        File::put($htmlPath, $html);

        try {
            $process = new Process([
                $this->browserBinary(),
                '--headless',
                '--disable-gpu',
                '--user-data-dir='.$profilePath,
                '--no-pdf-header-footer',
                '--print-to-pdf='.$pdfPath,
                'file:///'.str_replace(DIRECTORY_SEPARATOR, '/', $htmlPath),
            ]);
            $process->setTimeout(120);
            $process->run();

            if (! $process->isSuccessful() || ! File::exists($pdfPath)) {
                throw new RuntimeException('Browser PDF export failed: '.$process->getErrorOutput());
            }

            return $pdfPath;
        } finally {
            File::delete($htmlPath);
            if (File::isDirectory($profilePath)) {
                File::deleteDirectory($profilePath);
            }
        }
    }

    private function mergePdfFiles(array $pdfPaths, string $outputPath): void
    {
        $pdf = new Fpdi();

        foreach ($pdfPaths as $pdfPath) {
            $pageCount = $pdf->setSourceFile($pdfPath);

            for ($page = 1; $page <= $pageCount; $page++) {
                $templateId = $pdf->importPage($page);
                $size = $pdf->getTemplateSize($templateId);
                $orientation = $size['width'] > $size['height'] ? 'L' : 'P';

                $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);
            }
        }

        $pdf->Output('F', $outputPath);
    }

    private function browserBinary(): string
    {
        $candidates = [
            'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
            'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
            'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
        ];

        foreach ($candidates as $candidate) {
            if (File::exists($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException('No supported browser binary was found for headless PDF export.');
    }

    private function downloadName(Bpp $bpp): string
    {
        $reference = trim((string) ($bpp->no_rujukan_perolehan ?: $bpp->id));
        $reference = preg_replace('/[^A-Za-z0-9\-]+/', '-', $reference) ?: (string) $bpp->id;
        $reference = trim($reference, '-');

        return 'BPP-'.$reference.'.pdf';
    }
}
