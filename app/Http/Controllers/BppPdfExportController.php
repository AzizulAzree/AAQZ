<?php

namespace App\Http\Controllers;

use App\Models\Bpp;
use App\Services\BppPrintableViewService;
use App\Services\BppValidationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BppPdfExportController extends Controller
{
    public function export(
        Bpp $bpp,
        BppPrintableViewService $printableViewService,
        BppValidationService $validationService
    ): Response {
        $validationResult = $validationService->validate($bpp);
        $activeAppendix = $printableViewService->activeAppendix($bpp);

        $pdf = Pdf::loadView('bpp.printables.package', [
            'embeddedCss' => $this->compiledAppCss(),
            'bpp' => $bpp,
            'validationResult' => $validationResult,
            'checklistData' => $printableViewService->checklist($bpp, $validationResult),
            'pageOneData' => $printableViewService->pageOne($bpp),
            'pageTwoData' => $printableViewService->pageTwo($bpp),
            'c1Data' => $printableViewService->c1($bpp),
            'activeAppendix' => $activeAppendix,
        ])->setPaper('a4');

        return $pdf->download($this->downloadName($bpp));
    }

    private function downloadName(Bpp $bpp): string
    {
        $slug = Str::slug($bpp->title ?: 'draft');

        return 'bpp-package-'.$bpp->id.'-'.$slug.'.pdf';
    }

    private function compiledAppCss(): string
    {
        $manifestPath = public_path('build/manifest.json');

        if (! File::exists($manifestPath)) {
            return '';
        }

        $manifest = json_decode((string) File::get($manifestPath), true);
        $cssPath = $manifest['resources/css/app.css']['file'] ?? null;

        if (! is_string($cssPath)) {
            return '';
        }

        $compiledCssPath = public_path('build/'.$cssPath);

        if (! File::exists($compiledCssPath)) {
            return '';
        }

        return (string) File::get($compiledCssPath);
    }
}
