<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBppSupplierQuoteRequest;
use App\Http\Requests\UpdateBppSupplierQuoteRequest;
use App\Models\Bpp;
use App\Models\BppSupplierQuote;
use Illuminate\Http\RedirectResponse;

class BppSupplierQuoteController extends Controller
{
    public function store(StoreBppSupplierQuoteRequest $request, Bpp $bpp): RedirectResponse
    {
        BppSupplierQuote::query()->create([
            'bpp_id' => $bpp->id,
            'supplier_name' => $request->string('supplier_name')->toString(),
            'total_price' => $request->input('total_price'),
            'delivery_period' => $request->string('delivery_period')->toString(),
            'validity_period' => $request->string('validity_period')->toString(),
            'quotation_reference' => $request->filled('quotation_reference')
                ? $request->string('quotation_reference')->toString()
                : null,
            'is_selected' => false,
        ]);

        return redirect()
            ->route('bpp.show', $bpp)
            ->with('status', 'bpp-supplier-quote-saved');
    }

    public function update(UpdateBppSupplierQuoteRequest $request, Bpp $bpp, BppSupplierQuote $supplierQuote): RedirectResponse
    {
        abort_unless($supplierQuote->bpp_id === $bpp->id, 404);

        $supplierQuote->update([
            'supplier_name' => $request->string('supplier_name')->toString(),
            'total_price' => $request->input('total_price'),
            'delivery_period' => $request->string('delivery_period')->toString(),
            'validity_period' => $request->string('validity_period')->toString(),
            'quotation_reference' => $request->filled('quotation_reference')
                ? $request->string('quotation_reference')->toString()
                : null,
        ]);

        if ($supplierQuote->is_selected) {
            $bpp->syncSelectedSupplierQuote();
        }

        return redirect()
            ->route('bpp.show', $bpp)
            ->with('status', 'bpp-supplier-quote-saved');
    }

    public function destroy(Bpp $bpp, BppSupplierQuote $supplierQuote): RedirectResponse
    {
        abort_unless($supplierQuote->bpp_id === $bpp->id, 404);

        $wasSelected = $supplierQuote->is_selected;
        $supplierQuote->delete();

        if ($wasSelected) {
            $bpp->clearSelectedSupplierSync();
        }

        return redirect()
            ->route('bpp.show', $bpp)
            ->with('status', 'bpp-supplier-quote-deleted');
    }

    public function select(Bpp $bpp, BppSupplierQuote $supplierQuote): RedirectResponse
    {
        abort_unless($supplierQuote->bpp_id === $bpp->id, 404);

        $bpp->supplierQuotes()->update(['is_selected' => false]);

        $supplierQuote->update([
            'is_selected' => true,
        ]);

        $bpp->syncSelectedSupplierQuote();

        return redirect()
            ->route('bpp.show', $bpp)
            ->with('status', 'bpp-supplier-selected');
    }
}
