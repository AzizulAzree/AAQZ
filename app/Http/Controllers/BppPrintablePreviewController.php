<?php

namespace App\Http\Controllers;

use App\Models\Bpp;
use App\Services\BppPrintableViewService;
use App\Services\BppValidationService;
use Illuminate\View\View;

class BppPrintablePreviewController extends Controller
{
    public function packagePreview(
        Bpp $bpp,
        BppValidationService $validationService,
        BppPrintableViewService $printableViewService
    ): View
    {
        return view('bpp.printables.package-preview', $printableViewService->packagePreview(
            $bpp,
            $validationService->validate($bpp)
        ));
    }

    public function checklist(
        Bpp $bpp,
        BppValidationService $validationService,
        BppPrintableViewService $printableViewService
    ): View
    {
        return view('bpp.printables.checklist', $printableViewService->checklist(
            $bpp,
            $validationService->validate($bpp)
        ));
    }

    public function pageOne(Bpp $bpp, BppPrintableViewService $printableViewService): View
    {
        return view('bpp.printables.page-one', $printableViewService->pageOne($bpp));
    }

    public function pageTwo(Bpp $bpp, BppPrintableViewService $printableViewService): View
    {
        return view('bpp.printables.page-two', $printableViewService->pageTwo($bpp));
    }

    public function c1(Bpp $bpp, BppPrintableViewService $printableViewService): View
    {
        return view('bpp.printables.c1', $printableViewService->c1($bpp));
    }

    public function c2(Bpp $bpp, BppPrintableViewService $printableViewService): View
    {
        return view('bpp.printables.c2', $printableViewService->appendix($bpp, 'c2', 'C2 - Perbekalan'));
    }

    public function c3(Bpp $bpp, BppPrintableViewService $printableViewService): View
    {
        return view('bpp.printables.c3', $printableViewService->appendix($bpp, 'c3', 'C3 - Perkhidmatan'));
    }

    public function c4(Bpp $bpp, BppPrintableViewService $printableViewService): View
    {
        return view('bpp.printables.c4', $printableViewService->appendix($bpp, 'c4', 'C4 - Kerja'));
    }
}
