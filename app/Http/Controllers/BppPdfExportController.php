<?php

namespace App\Http\Controllers;

use App\Models\Bpp;
use App\Services\BppPrintableViewService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class BppPdfExportController extends Controller
{
    public function export(Bpp $bpp, BppPrintableViewService $printableViewService): Response
    {
        @set_time_limit(180);

        $exportDir = storage_path('app/browser-pdf-export');
        File::ensureDirectoryExists($exportDir);

        $token = (string) Str::uuid();
        $htmlPath = $exportDir.DIRECTORY_SEPARATOR.$token.'.html';
        $pdfPath = $exportDir.DIRECTORY_SEPARATOR.$token.'.pdf';
        $profilePath = $exportDir.DIRECTORY_SEPARATOR.'edge-profile-'.$token;

        $html = View::make('bpp.printables.package-pdf', [
            'bpp' => $bpp,
            'pdfLogoPath' => public_path('images/bpp-preview/nibm-logo.png'),
        ])->render();

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

            $pdfBytes = File::get($pdfPath);
        } finally {
            File::delete($htmlPath);
            File::delete($pdfPath);
            if (File::isDirectory($profilePath)) {
                File::deleteDirectory($profilePath);
            }
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
