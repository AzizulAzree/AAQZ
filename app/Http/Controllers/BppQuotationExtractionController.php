<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplyBppQuotationExtractionRequest;
use App\Http\Requests\ParseBppQuotationExtractionRequest;
use App\Models\Bpp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use App\Services\BppQuotationExtractionParser;

class BppQuotationExtractionController extends Controller
{
    public function parse(
        ParseBppQuotationExtractionRequest $request,
        Bpp $bpp,
        BppQuotationExtractionParser $parser
    ): RedirectResponse {
        $rawText = $request->string('quotation_extraction_text')->toString();
        $review = $parser->parse($rawText);

        $bpp->update([
            'quotation_extraction_format_version' => $parser->formatVersion(),
            'quotation_extraction_raw_text' => $rawText,
            'quotation_extraction_review' => $review,
        ]);

        return redirect()
            ->route('bpp.show', $bpp)
            ->with('status', $review['valid'] ? 'bpp-extraction-reviewed' : 'bpp-extraction-invalid');
    }

    public function import(
        ParseBppQuotationExtractionRequest $request,
        Bpp $bpp,
        BppQuotationExtractionParser $parser
    ): RedirectResponse {
        $rawText = $request->string('quotation_extraction_text')->toString();
        $review = $parser->parse($rawText);

        $this->storeReviewState($bpp, $parser, $rawText, $review);

        if (! ($review['valid'] ?? false) || ! is_array($review['data'] ?? null)) {
            return redirect()
                ->route('bpp.show', $bpp)
                ->with('status', 'bpp-extraction-invalid');
        }

        $hasExistingImportData = $bpp->supplierQuotes()->exists()
            || $bpp->supplierQuoteItems()->exists()
            || $bpp->appendixRows()->exists();

        if ($hasExistingImportData && ! $request->boolean('confirm_replace')) {
            return redirect()
                ->route('bpp.show', $bpp)
                ->withErrors([
                    'confirm_replace' => 'Confirm that you want to replace the current C1 and appendix draft data before importing this extraction.',
                ]);
        }

        $this->applyReviewData($bpp, $review['data']);

        return redirect()
            ->route('bpp.show', $bpp)
            ->with('status', 'bpp-extraction-applied');
    }

    public function apply(
        ApplyBppQuotationExtractionRequest $request,
        Bpp $bpp
    ): RedirectResponse {
        $review = $bpp->quotation_extraction_review;

        if (! is_array($review) || ! ($review['valid'] ?? false) || ! is_array($review['data'] ?? null)) {
            return redirect()
                ->route('bpp.show', $bpp)
                ->withErrors([
                    'quotation_extraction_text' => 'Parse a valid extraction result before applying it.',
                ]);
        }

        $hasExistingImportData = $bpp->supplierQuotes()->exists()
            || $bpp->supplierQuoteItems()->exists()
            || $bpp->appendixRows()->exists();

        if ($hasExistingImportData && ! $request->boolean('confirm_replace')) {
            return redirect()
                ->route('bpp.show', $bpp)
                ->withErrors([
                    'confirm_replace' => 'Confirm that you want to replace the current C1 and appendix draft data before applying this extraction.',
                ]);
        }

        $data = $review['data'];

        $this->applyReviewData($bpp, $data);

        return redirect()
            ->route('bpp.show', $bpp)
            ->with('status', 'bpp-extraction-applied');
    }

    private function storeReviewState(
        Bpp $bpp,
        BppQuotationExtractionParser $parser,
        string $rawText,
        array $review
    ): void {
        $bpp->update([
            'quotation_extraction_format_version' => $parser->formatVersion(),
            'quotation_extraction_raw_text' => $rawText,
            'quotation_extraction_review' => $review,
        ]);
    }

    private function applyReviewData(Bpp $bpp, array $data): void
    {
        DB::transaction(function () use ($bpp, $data): void {
            $bpp->supplierQuotes()->delete();
            $bpp->supplierQuoteItems()->delete();
            $bpp->appendixRows()->delete();

            $bpp->update([
                'b2_kategori_perolehan' => $data['procurement_category'],
                'c1_selection_reason' => $data['selection_reason'],
                'c1_selection_reason_lain_lain' => $data['selection_reason_lain_lain'],
            ]);

            $createdQuotes = [];

            foreach ($data['suppliers'] as $supplier) {
                $quote = $bpp->supplierQuotes()->create([
                    'supplier_name' => $supplier['supplier_name'],
                    'registration_number' => $supplier['registration_number'],
                    'supplier_address' => $supplier['supplier_address'],
                    'total_price' => $supplier['total_price'],
                    'delivery_period' => $supplier['delivery_period'],
                    'validity_period' => $supplier['validity_period'],
                    'quotation_reference' => $supplier['quotation_reference'],
                    'is_selected' => $supplier['is_selected'],
                ]);

                $createdQuotes[$supplier['supplier_name']] = $quote;
            }

            foreach ($data['comparison_rows'] as $row) {
                $quote = $createdQuotes[$row['supplier_name']] ?? null;

                if ($quote === null) {
                    continue;
                }

                $bpp->supplierQuoteItems()->create([
                    'bpp_supplier_quote_id' => $quote->id,
                    'line_number' => $row['line_number'],
                    'item_spesifikasi' => $row['item_spesifikasi'],
                    'kuantiti' => $row['kuantiti'],
                    'unit_ukuran' => $row['unit_ukuran'],
                    'harga_tawaran' => $row['harga_tawaran'],
                    'jumlah_harga' => $row['jumlah_harga'],
                ]);
            }

            foreach ($data['appendix_rows'] as $row) {
                $bpp->appendixRows()->create([
                    'appendix_type' => $data['appendix_type'],
                    'line_number' => $row['line_number'],
                    'item_spesifikasi' => $row['item_spesifikasi'],
                    'kuantiti' => $row['kuantiti'],
                    'unit_ukuran' => $row['unit_ukuran'],
                    'harga_seunit' => $row['harga_seunit'],
                    'jumlah_harga' => $row['jumlah_harga'],
                ]);
            }

            $bpp->syncAppendixGrandTotal($data['appendix_type']);
            $bpp->syncSelectedSupplierQuote();

            $bpp->update([
                'quotation_extraction_format_version' => null,
                'quotation_extraction_raw_text' => null,
                'quotation_extraction_review' => null,
            ]);
        });
    }
}
