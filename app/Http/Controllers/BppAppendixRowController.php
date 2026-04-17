<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBppAppendixRowRequest;
use App\Http\Requests\UpdateBppAppendixRowRequest;
use App\Models\Bpp;
use App\Models\BppAppendixRow;
use Illuminate\Http\RedirectResponse;

class BppAppendixRowController extends Controller
{
    public function store(StoreBppAppendixRowRequest $request, Bpp $bpp): RedirectResponse
    {
        $appendixType = $request->string('appendix_type')->toString();

        abort_unless($bpp->activeAppendixType() === $appendixType, 404);

        BppAppendixRow::query()->create([
            'bpp_id' => $bpp->id,
            'appendix_type' => $appendixType,
            'line_number' => $bpp->nextAppendixLineNumber($appendixType),
            'item_spesifikasi' => $request->string('item_spesifikasi')->toString(),
            'kuantiti' => $request->input('kuantiti'),
            'unit_ukuran' => $request->string('unit_ukuran')->toString(),
            'harga_seunit' => $request->input('harga_seunit'),
            'jumlah_harga' => $this->lineTotal(
                (float) $request->input('kuantiti'),
                (float) $request->input('harga_seunit'),
            ),
        ]);

        $bpp->syncAppendixGrandTotal($appendixType);

        return redirect()
            ->route('bpp.show', $bpp)
            ->with('status', 'bpp-appendix-row-saved');
    }

    public function update(UpdateBppAppendixRowRequest $request, Bpp $bpp, BppAppendixRow $appendixRow): RedirectResponse
    {
        abort_unless($appendixRow->bpp_id === $bpp->id, 404);

        $appendixType = $request->string('appendix_type')->toString();

        abort_unless($appendixRow->appendix_type === $appendixType, 404);
        abort_unless($bpp->activeAppendixType() === $appendixType, 404);

        $appendixRow->update([
            'item_spesifikasi' => $request->string('item_spesifikasi')->toString(),
            'kuantiti' => $request->input('kuantiti'),
            'unit_ukuran' => $request->string('unit_ukuran')->toString(),
            'harga_seunit' => $request->input('harga_seunit'),
            'jumlah_harga' => $this->lineTotal(
                (float) $request->input('kuantiti'),
                (float) $request->input('harga_seunit'),
            ),
        ]);

        $bpp->syncAppendixGrandTotal($appendixType);

        return redirect()
            ->route('bpp.show', $bpp)
            ->with('status', 'bpp-appendix-row-saved');
    }

    public function destroy(Bpp $bpp, BppAppendixRow $appendixRow): RedirectResponse
    {
        abort_unless($appendixRow->bpp_id === $bpp->id, 404);

        $appendixType = $appendixRow->appendix_type;

        abort_unless($bpp->activeAppendixType() === $appendixType, 404);

        $appendixRow->delete();
        $bpp->resequenceAppendixRows($appendixType);
        $bpp->syncAppendixGrandTotal($appendixType);

        return redirect()
            ->route('bpp.show', $bpp)
            ->with('status', 'bpp-appendix-row-deleted');
    }

    private function lineTotal(float $quantity, float $unitPrice): string
    {
        return number_format($quantity * $unitPrice, 2, '.', '');
    }
}
