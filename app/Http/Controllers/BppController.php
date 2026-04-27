<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBppRequest;
use App\Http\Requests\UpdateBppRequest;
use App\Models\Bpp;
use App\Services\BppQuotationExtractionParser;
use App\Services\BppValidationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BppController extends Controller
{
    public function index(): View
    {
        return view('bpp.index', [
            'bpps' => Bpp::query()->latest('updated_at')->get(),
        ]);
    }

    public function store(StoreBppRequest $request): RedirectResponse
    {
        $bpp = Bpp::query()->create([
            'title' => $request->string('title')->toString(),
            'status' => 'draft',
            'b2_kategori_perolehan' => $request->string('b2_kategori_perolehan')->toString(),
        ]);

        return redirect()->route('bpp.show', $bpp);
    }

    public function show(
        Bpp $bpp,
        BppQuotationExtractionParser $parser,
        BppValidationService $validationService
    ): View
    {
        return view('bpp.show', [
            'bpp' => $bpp,
            'activeAppendixType' => $bpp->activeAppendixType(),
            'activeAppendixLabel' => $bpp->activeAppendixLabel(),
            'activeAppendixRows' => $bpp->appendixRows
                ->where('appendix_type', $bpp->activeAppendixType())
                ->values(),
            'supplierQuotes' => $bpp->supplierQuotes()->get(),
            'supplierQuoteItems' => $bpp->supplierQuoteItems()->with('supplierQuote')->get(),
            'selectionReasonOptions' => $bpp->selectionReasonOptions(),
            'quotationExtractionPrompt' => $parser->prompt(),
            'quotationExtractionFormatVersion' => $parser->formatVersion(),
            'quotationExtractionReview' => $bpp->quotation_extraction_review,
            'hasExistingImportedDraftData' => $bpp->supplierQuotes()->exists() || $bpp->supplierQuoteItems()->exists() || $bpp->appendixRows()->exists(),
            'validationResult' => $validationService->validate($bpp),
        ]);
    }

    public function update(UpdateBppRequest $request, Bpp $bpp): RedirectResponse
    {
        $validated = $request->validated();

        if (filled($bpp->b2_kategori_perolehan)) {
            unset($validated['b2_kategori_perolehan']);
        }

        if (filled($validated['b8_tarikh_diperlukan'] ?? null)) {
            $validated['b8_tarikh_diperlukan'] .= '-01';
        }

        $bpp->update($validated);

        if ($bpp->selectedSupplierQuote() !== null) {
            $bpp->syncSelectedSupplierQuote();
        }

        return redirect()
            ->route('bpp.show', $bpp)
            ->with('status', 'bpp-saved');
    }
}
