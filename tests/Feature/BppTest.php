<?php

namespace Tests\Feature;

use App\Models\Bpp;
use App\Models\BppAppendixRow;
use App\Models\BppSupplierQuote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BppTest extends TestCase
{
    use RefreshDatabase;

    public function test_bpp_index_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/bpp');

        $response->assertOk();
        $response->assertSee('BPP (Borang Permohonan Perolehan)');
        $response->assertSee('Start New BPP');
        $response->assertSee('No BPP drafts yet');
    }

    public function test_authenticated_user_can_create_a_bpp_draft(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/bpp', [
                'title' => 'Office chair procurement',
                'b2_kategori_perolehan' => 'Bekalan',
            ]);

        $bpp = Bpp::query()->first();

        $this->assertNotNull($bpp);
        $response->assertRedirect(route('bpp.show', $bpp));
        $this->assertDatabaseHas('bpps', [
            'title' => 'Office chair procurement',
            'status' => 'draft',
            'b2_kategori_perolehan' => 'Bekalan',
        ]);
    }

    public function test_bpp_title_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->from('/bpp')
            ->actingAs($user)
            ->post('/bpp', [
                'title' => '',
                'b2_kategori_perolehan' => '',
            ]);

        $response->assertRedirect('/bpp');
        $response->assertSessionHasErrors(['title', 'b2_kategori_perolehan']);
    }

    public function test_bpp_show_page_displays_draft_details(): void
    {
        $user = User::factory()->create();

        $bpp = Bpp::query()->create([
            'title' => 'Printer toner refill',
            'status' => 'draft',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('bpp.show', $bpp));

        $response->assertOk();
        $response->assertSee('Printer toner refill');
        $response->assertSee('draft');
        $response->assertSee((string) $bpp->id);
        $response->assertSee('Top Section');
        $response->assertSee('No. Rujukan Perolehan');
        $response->assertSee('Kaedah Perolehan');
        $response->assertSee('Pembelian Terus');
        $response->assertSee('Pembekal Tunggal / Rundingan Terus');
    }

    public function test_bpp_index_lists_existing_drafts_with_continue_links_most_recent_first(): void
    {
        $user = User::factory()->create();

        $older = Bpp::query()->create([
            'title' => 'Older draft',
            'status' => 'draft',
        ]);

        $newer = Bpp::query()->create([
            'title' => 'Newer draft',
            'status' => 'draft',
        ]);

        $older->forceFill(['updated_at' => now()->subDay()])->save();
        $newer->forceFill(['updated_at' => now()])->save();

        $response = $this
            ->actingAs($user)
            ->get('/bpp');

        $response->assertOk();
        $response->assertSee('Older draft');
        $response->assertSee('Newer draft');
        $response->assertSee('Continue Draft');
        $response->assertSeeInOrder(['Newer draft', 'Older draft']);
    }

    public function test_bpp_page_one_data_can_be_saved_to_existing_draft(): void
    {
        $user = User::factory()->create();

        $bpp = Bpp::query()->create([
            'title' => 'Medical equipment request',
            'status' => 'draft',
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('bpp.update', $bpp), [
                'title' => 'Medical equipment request',
                'no_rujukan_perolehan' => 'PRC-2026-001',
                'kaedah_perolehan' => 'sebut_harga',
                'b3_nilai_tawaran_perolehan' => 'RM 9,031.00',
                'b4_harga_indikatif' => 'RM 12,000.50',
                'b5_peruntukan_diluluskan' => 'RM 15,000.00',
                'b8_tarikh_diperlukan' => '2026-04',
            ]);

        $response->assertRedirect(route('bpp.show', $bpp));

        $bpp->refresh();

        $this->assertSame(9031.00, (float) $bpp->b3_nilai_tawaran_perolehan);
        $this->assertSame(12000.50, (float) $bpp->b4_harga_indikatif);
        $this->assertSame(15000.00, (float) $bpp->b5_peruntukan_diluluskan);
        $this->assertSame('2026-04-01', $bpp->b8_tarikh_diperlukan?->format('Y-m-d'));

        $this->assertDatabaseHas('bpps', [
            'id' => $bpp->id,
            'title' => 'Medical equipment request',
            'status' => 'draft',
            'no_rujukan_perolehan' => 'PRC-2026-001',
            'kaedah_perolehan' => 'sebut_harga',
        ]);
    }

    public function test_bpp_show_page_displays_selected_procurement_method_option(): void
    {
        $user = User::factory()->create();

        $bpp = Bpp::query()->create([
            'title' => 'Stationery reorder',
            'status' => 'draft',
            'kaedah_perolehan' => 'tender',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('bpp.show', $bpp));

        $response->assertOk();
        $response->assertSee('Tender (melebihi RM500,000.00)');
    }

    public function test_matching_appendix_editor_is_shown_for_bekalan_category(): void
    {
        $user = User::factory()->create();

        $bpp = Bpp::query()->create([
            'title' => 'Lab chairs',
            'status' => 'draft',
            'b2_kategori_perolehan' => 'Bekalan',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('bpp.show', $bpp));

        $response->assertOk();
        $response->assertSee('C2 - Perbekalan');
        $response->assertDontSee('C3 - Perkhidmatan');
        $response->assertDontSee('C4 - Kerja');
    }

    public function test_appendix_row_can_be_added_and_b3_total_is_synced(): void
    {
        $user = User::factory()->create();

        $bpp = Bpp::query()->create([
            'title' => 'Lab chairs',
            'status' => 'draft',
            'b2_kategori_perolehan' => 'Bekalan',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('bpp.appendix-rows.store', $bpp), [
                'appendix_type' => 'c2',
                'item_spesifikasi' => 'Kerusi makmal ergonomik',
                'kuantiti' => '3',
                'unit_ukuran' => 'unit',
                'harga_seunit' => '250.50',
            ]);

        $response->assertRedirect(route('bpp.show', $bpp));

        $this->assertDatabaseHas('bpp_appendix_rows', [
            'bpp_id' => $bpp->id,
            'appendix_type' => 'c2',
            'line_number' => 1,
            'item_spesifikasi' => 'Kerusi makmal ergonomik',
            'unit_ukuran' => 'unit',
            'jumlah_harga' => '751.50',
        ]);

        $this->assertDatabaseHas('bpps', [
            'id' => $bpp->id,
            'b3_nilai_tawaran_perolehan' => '751.50',
        ]);
    }

    public function test_appendix_row_can_be_updated_and_total_is_recomputed(): void
    {
        $user = User::factory()->create();

        $bpp = Bpp::query()->create([
            'title' => 'Cleaning services',
            'status' => 'draft',
            'b2_kategori_perolehan' => 'Perkhidmatan',
        ]);

        $row = BppAppendixRow::query()->create([
            'bpp_id' => $bpp->id,
            'appendix_type' => 'c3',
            'line_number' => 1,
            'item_spesifikasi' => 'Kontrak pembersihan',
            'kuantiti' => 1,
            'unit_ukuran' => 'lot',
            'harga_seunit' => 500,
            'jumlah_harga' => 500,
        ]);

        $bpp->syncAppendixGrandTotal('c3');

        $response = $this
            ->actingAs($user)
            ->put(route('bpp.appendix-rows.update', [$bpp, $row]), [
                'appendix_type' => 'c3',
                'item_spesifikasi' => 'Kontrak pembersihan tahunan',
                'kuantiti' => '2',
                'unit_ukuran' => 'lot',
                'harga_seunit' => '650',
            ]);

        $response->assertRedirect(route('bpp.show', $bpp));

        $this->assertDatabaseHas('bpp_appendix_rows', [
            'id' => $row->id,
            'item_spesifikasi' => 'Kontrak pembersihan tahunan',
            'jumlah_harga' => '1300.00',
        ]);

        $this->assertDatabaseHas('bpps', [
            'id' => $bpp->id,
            'b3_nilai_tawaran_perolehan' => '1300.00',
        ]);
    }

    public function test_appendix_row_can_be_deleted_and_total_is_updated(): void
    {
        $user = User::factory()->create();

        $bpp = Bpp::query()->create([
            'title' => 'Civil works',
            'status' => 'draft',
            'b2_kategori_perolehan' => 'Kerja',
        ]);

        $firstRow = BppAppendixRow::query()->create([
            'bpp_id' => $bpp->id,
            'appendix_type' => 'c4',
            'line_number' => 1,
            'item_spesifikasi' => 'Kerja awalan',
            'kuantiti' => 1,
            'unit_ukuran' => 'lot',
            'harga_seunit' => 1000,
            'jumlah_harga' => 1000,
        ]);

        $secondRow = BppAppendixRow::query()->create([
            'bpp_id' => $bpp->id,
            'appendix_type' => 'c4',
            'line_number' => 2,
            'item_spesifikasi' => 'Kerja akhir',
            'kuantiti' => 1,
            'unit_ukuran' => 'lot',
            'harga_seunit' => 2000,
            'jumlah_harga' => 2000,
        ]);

        $bpp->syncAppendixGrandTotal('c4');

        $response = $this
            ->actingAs($user)
            ->delete(route('bpp.appendix-rows.destroy', [$bpp, $firstRow]));

        $response->assertRedirect(route('bpp.show', $bpp));

        $this->assertDatabaseMissing('bpp_appendix_rows', [
            'id' => $firstRow->id,
        ]);

        $this->assertDatabaseHas('bpp_appendix_rows', [
            'id' => $secondRow->id,
            'line_number' => 1,
        ]);

        $this->assertDatabaseHas('bpps', [
            'id' => $bpp->id,
            'b3_nilai_tawaran_perolehan' => '2000.00',
        ]);
    }

    public function test_bpp_show_page_displays_manual_c1_section(): void
    {
        $user = User::factory()->create();

        $bpp = Bpp::query()->create([
            'title' => 'Supplier comparison draft',
            'status' => 'draft',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('bpp.show', $bpp));

        $response->assertOk();
        $response->assertSee('Top Section');
        $response->assertSee('Kaedah Perolehan');
        $response->assertDontSee('C1 - Kajian Pasaran');
    }

    public function test_supplier_quote_can_be_added_to_bpp_draft(): void
    {
        $user = User::factory()->create();

        $bpp = Bpp::query()->create([
            'title' => 'Manual C1 draft',
            'status' => 'draft',
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('bpp.supplier-quotes.store', $bpp), [
                'supplier_name' => 'Alpha Supplies Sdn. Bhd.',
                'total_price' => '12500.50',
                'delivery_period' => '14 hari',
                'validity_period' => '30 hari',
                'quotation_reference' => 'QT-001',
            ]);

        $response->assertRedirect(route('bpp.show', $bpp));

        $this->assertDatabaseHas('bpp_supplier_quotes', [
            'bpp_id' => $bpp->id,
            'supplier_name' => 'Alpha Supplies Sdn. Bhd.',
            'total_price' => '12500.50',
            'delivery_period' => '14 hari',
            'validity_period' => '30 hari',
            'quotation_reference' => 'QT-001',
            'is_selected' => false,
        ]);
    }

    public function test_selected_supplier_is_synced_to_d_section_fields(): void
    {
        $user = User::factory()->create();

        $bpp = Bpp::query()->create([
            'title' => 'Supplier sync draft',
            'status' => 'draft',
            'c1_selection_reason' => 'Lain-lain',
            'c1_selection_reason_lain_lain' => 'Harga dan lead time paling sesuai.',
        ]);

        $quote = BppSupplierQuote::query()->create([
            'bpp_id' => $bpp->id,
            'supplier_name' => 'Beta Tech Enterprise',
            'total_price' => 8800,
            'delivery_period' => '21 hari',
            'validity_period' => '45 hari',
            'quotation_reference' => 'QT-002',
            'is_selected' => false,
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('bpp.supplier-quotes.select', [$bpp, $quote]));

        $response->assertRedirect(route('bpp.show', $bpp));

        $this->assertDatabaseHas('bpp_supplier_quotes', [
            'id' => $quote->id,
            'is_selected' => true,
        ]);

        $this->assertDatabaseHas('bpps', [
            'id' => $bpp->id,
            'd_nama_pembekal' => 'Beta Tech Enterprise',
            'd_kriteria_pemilihan' => 'Lain-lain',
            'd_lain_lain_kriteria' => 'Harga dan lead time paling sesuai.',
        ]);
    }

    public function test_selected_supplier_can_be_changed_later(): void
    {
        $user = User::factory()->create();

        $bpp = Bpp::query()->create([
            'title' => 'Supplier reselection draft',
            'status' => 'draft',
            'c1_selection_reason' => 'Tawaran harga terbaik',
        ]);

        $firstQuote = BppSupplierQuote::query()->create([
            'bpp_id' => $bpp->id,
            'supplier_name' => 'First Supplier',
            'total_price' => 1000,
            'delivery_period' => '7 hari',
            'validity_period' => '30 hari',
            'is_selected' => true,
        ]);

        $secondQuote = BppSupplierQuote::query()->create([
            'bpp_id' => $bpp->id,
            'supplier_name' => 'Second Supplier',
            'total_price' => 1200,
            'delivery_period' => '10 hari',
            'validity_period' => '30 hari',
            'is_selected' => false,
        ]);

        $bpp->syncSelectedSupplierQuote();

        $response = $this
            ->actingAs($user)
            ->put(route('bpp.supplier-quotes.select', [$bpp, $secondQuote]));

        $response->assertRedirect(route('bpp.show', $bpp));

        $this->assertDatabaseHas('bpp_supplier_quotes', [
            'id' => $firstQuote->id,
            'is_selected' => false,
        ]);

        $this->assertDatabaseHas('bpp_supplier_quotes', [
            'id' => $secondQuote->id,
            'is_selected' => true,
        ]);

        $this->assertDatabaseHas('bpps', [
            'id' => $bpp->id,
            'd_nama_pembekal' => 'Second Supplier',
            'd_kriteria_pemilihan' => 'Tawaran harga terbaik',
        ]);
    }

    public function test_deleting_selected_supplier_clears_synced_d_fields(): void
    {
        $user = User::factory()->create();

        $bpp = Bpp::query()->create([
            'title' => 'Selected supplier delete draft',
            'status' => 'draft',
            'c1_selection_reason' => 'Pembekal Tunggal',
        ]);

        $quote = BppSupplierQuote::query()->create([
            'bpp_id' => $bpp->id,
            'supplier_name' => 'Single Source Supplier',
            'total_price' => 3000,
            'delivery_period' => '5 hari',
            'validity_period' => '14 hari',
            'is_selected' => true,
        ]);

        $bpp->syncSelectedSupplierQuote();

        $response = $this
            ->actingAs($user)
            ->delete(route('bpp.supplier-quotes.destroy', [$bpp, $quote]));

        $response->assertRedirect(route('bpp.show', $bpp));

        $this->assertDatabaseMissing('bpp_supplier_quotes', [
            'id' => $quote->id,
        ]);

        $this->assertDatabaseHas('bpps', [
            'id' => $bpp->id,
            'd_nama_pembekal' => null,
            'd_kriteria_pemilihan' => null,
            'd_lain_lain_kriteria' => null,
        ]);
    }

    public function test_bpp_show_page_displays_quotation_extraction_assistant(): void
    {
        $user = User::factory()->create();

        $bpp = Bpp::query()->create([
            'title' => 'Extraction assistant draft',
            'status' => 'draft',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('bpp.show', $bpp));

        $response->assertOk();
        $response->assertSee('Top Section');
        $response->assertSee('Kaedah Perolehan');
        $response->assertDontSee('Quotation Extraction Assistant');
    }

    public function test_valid_quotation_extraction_can_be_parsed_and_stored_for_review(): void
    {
        $user = User::factory()->create();

        $bpp = Bpp::query()->create([
            'title' => 'Parse review draft',
            'status' => 'draft',
        ]);

        $payload = <<<'TEXT'
QUOTATION_EXTRACTION_V1
PROCUREMENT_CATEGORY: Bekalan
SELECTED_SUPPLIER: Alpha Supplies Sdn. Bhd.
SELECTION_REASON: Tawaran harga terbaik
SELECTION_REASON_LAIN_LAIN:
SUPPLIERS:
supplier_name|total_price|delivery_period|validity_period|quotation_reference
Alpha Supplies Sdn. Bhd.|12500.50|14 hari|30 hari|QT-001
Beta Tech Enterprise|12990.00|21 hari|30 hari|QT-002
SELECTED_SUPPLIER_ITEMS:
item_spesifikasi|kuantiti|unit_ukuran|harga_seunit|jumlah_harga
Komputer riba|10|unit|1250.05|12500.50
TOTALS:
appendix_total|12500.50
selected_supplier_total|12500.50
TEXT;

        $response = $this
            ->actingAs($user)
            ->post(route('bpp.quotation-extraction.parse', $bpp), [
                'quotation_extraction_text' => $payload,
            ]);

        $response->assertRedirect(route('bpp.show', $bpp));

        $bpp->refresh();

        $this->assertSame('QUOTATION_EXTRACTION_V1', $bpp->quotation_extraction_format_version);
        $this->assertSame($payload, $bpp->quotation_extraction_raw_text);
        $this->assertTrue($bpp->quotation_extraction_review['valid']);
        $this->assertSame('Bekalan', $bpp->quotation_extraction_review['data']['procurement_category']);
        $this->assertSame('Alpha Supplies Sdn. Bhd.', $bpp->quotation_extraction_review['data']['selected_supplier']);
        $this->assertCount(2, $bpp->quotation_extraction_review['data']['suppliers']);
        $this->assertCount(1, $bpp->quotation_extraction_review['data']['appendix_rows']);
    }

    public function test_invalid_quotation_extraction_fails_safely_with_review_errors(): void
    {
        $user = User::factory()->create();

        $bpp = Bpp::query()->create([
            'title' => 'Invalid parse draft',
            'status' => 'draft',
        ]);

        $payload = <<<'TEXT'
QUOTATION_EXTRACTION_V1
PROCUREMENT_CATEGORY: Bekalan
SELECTED_SUPPLIER: Alpha Supplies Sdn. Bhd.
SELECTION_REASON: Tawaran harga terbaik
SELECTION_REASON_LAIN_LAIN:
SUPPLIERS:
supplier_name|total_price|delivery_period|validity_period|quotation_reference
Alpha Supplies Sdn. Bhd.|12500.50|14 hari|30 hari|QT-001
SELECTED_SUPPLIER_ITEMS:
item_spesifikasi|kuantiti|unit_ukuran|harga_seunit|jumlah_harga
Komputer riba|10|unit|1250.05|12500.50
TOTALS:
appendix_total|9999.99
selected_supplier_total|12500.50
TEXT;

        $response = $this
            ->actingAs($user)
            ->post(route('bpp.quotation-extraction.parse', $bpp), [
                'quotation_extraction_text' => $payload,
            ]);

        $response->assertRedirect(route('bpp.show', $bpp));

        $bpp->refresh();

        $this->assertFalse($bpp->quotation_extraction_review['valid']);
        $this->assertContains(
            'appendix_total does not match the sum of SELECTED_SUPPLIER_ITEMS.jumlah_harga.',
            $bpp->quotation_extraction_review['errors']
        );

        $this->assertDatabaseCount('bpp_supplier_quotes', 0);
        $this->assertDatabaseCount('bpp_appendix_rows', 0);
    }

    public function test_parsed_quotation_extraction_can_replace_existing_c1_and_appendix_data(): void
    {
        $user = User::factory()->create();

        $bpp = Bpp::query()->create([
            'title' => 'Apply extraction draft',
            'status' => 'draft',
            'b2_kategori_perolehan' => 'Perkhidmatan',
        ]);

        BppSupplierQuote::query()->create([
            'bpp_id' => $bpp->id,
            'supplier_name' => 'Legacy Supplier',
            'total_price' => 5000,
            'delivery_period' => '7 hari',
            'validity_period' => '30 hari',
            'quotation_reference' => 'OLD-01',
            'is_selected' => true,
        ]);

        BppAppendixRow::query()->create([
            'bpp_id' => $bpp->id,
            'appendix_type' => 'c3',
            'line_number' => 1,
            'item_spesifikasi' => 'Legacy row',
            'kuantiti' => 1,
            'unit_ukuran' => 'lot',
            'harga_seunit' => 5000,
            'jumlah_harga' => 5000,
        ]);

        $bpp->update([
            'quotation_extraction_format_version' => 'QUOTATION_EXTRACTION_V1',
            'quotation_extraction_raw_text' => 'saved payload',
            'quotation_extraction_review' => [
                'valid' => true,
                'errors' => [],
                'warnings' => [],
                'data' => [
                    'format_version' => 'QUOTATION_EXTRACTION_V1',
                    'procurement_category' => 'Bekalan',
                    'appendix_type' => 'c2',
                    'appendix_label' => 'C2 - Perbekalan',
                    'selected_supplier' => 'Alpha Supplies Sdn. Bhd.',
                    'selection_reason' => 'Tawaran harga terbaik',
                    'selection_reason_lain_lain' => null,
                    'suppliers' => [
                        [
                            'supplier_name' => 'Alpha Supplies Sdn. Bhd.',
                            'total_price' => '12500.50',
                            'delivery_period' => '14 hari',
                            'validity_period' => '30 hari',
                            'quotation_reference' => 'QT-001',
                            'is_selected' => true,
                        ],
                        [
                            'supplier_name' => 'Beta Tech Enterprise',
                            'total_price' => '12990.00',
                            'delivery_period' => '21 hari',
                            'validity_period' => '30 hari',
                            'quotation_reference' => 'QT-002',
                            'is_selected' => false,
                        ],
                    ],
                    'appendix_rows' => [
                        [
                            'line_number' => 1,
                            'item_spesifikasi' => 'Komputer riba',
                            'kuantiti' => '10.00',
                            'unit_ukuran' => 'unit',
                            'harga_seunit' => '1250.05',
                            'jumlah_harga' => '12500.50',
                        ],
                    ],
                    'totals' => [
                        'appendix_total' => '12500.50',
                        'selected_supplier_total' => '12500.50',
                    ],
                ],
            ],
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('bpp.quotation-extraction.apply', $bpp), [
                'confirm_replace' => '1',
            ]);

        $response->assertRedirect(route('bpp.show', $bpp));

        $bpp->refresh();

        $this->assertSame('Bekalan', $bpp->b2_kategori_perolehan);
        $this->assertSame('Tawaran harga terbaik', $bpp->c1_selection_reason);
        $this->assertSame('Alpha Supplies Sdn. Bhd.', $bpp->d_nama_pembekal);
        $this->assertSame('Tawaran harga terbaik', $bpp->d_kriteria_pemilihan);
        $this->assertSame('12500.50', $bpp->b3_nilai_tawaran_perolehan);
        $this->assertNull($bpp->quotation_extraction_review);
        $this->assertNull($bpp->quotation_extraction_raw_text);

        $this->assertDatabaseMissing('bpp_supplier_quotes', [
            'supplier_name' => 'Legacy Supplier',
        ]);

        $this->assertDatabaseHas('bpp_supplier_quotes', [
            'bpp_id' => $bpp->id,
            'supplier_name' => 'Alpha Supplies Sdn. Bhd.',
            'is_selected' => true,
        ]);

        $this->assertDatabaseHas('bpp_appendix_rows', [
            'bpp_id' => $bpp->id,
            'appendix_type' => 'c2',
            'item_spesifikasi' => 'Komputer riba',
            'jumlah_harga' => '12500.50',
        ]);

        $this->assertDatabaseMissing('bpp_appendix_rows', [
            'bpp_id' => $bpp->id,
            'item_spesifikasi' => 'Legacy row',
        ]);
    }

    public function test_bpp_show_page_displays_readiness_panel_for_incomplete_draft(): void
    {
        $user = User::factory()->create();

        $bpp = Bpp::query()->create([
            'title' => 'Readiness draft',
            'status' => 'draft',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('bpp.show', $bpp));

        $response->assertOk();
        $response->assertSee('Top Section');
        $response->assertSee('No. Rujukan Perolehan');
        $response->assertDontSee('Draft Readiness');
    }

    public function test_bpp_readiness_can_show_ready_for_review_for_consistent_manual_draft(): void
    {
        $user = User::factory()->create();

        $bpp = Bpp::query()->create([
            'title' => 'Ready draft',
            'status' => 'draft',
            'b1_tajuk_perolehan' => 'Perolehan komputer riba',
            'b2_kategori_perolehan' => 'Bekalan',
            'b6_justifikasi_keperluan' => 'Penggantian aset lama.',
        ]);

        $selectedQuote = BppSupplierQuote::query()->create([
            'bpp_id' => $bpp->id,
            'supplier_name' => 'Alpha Supplies Sdn. Bhd.',
            'total_price' => 12500.50,
            'delivery_period' => '14 hari',
            'validity_period' => '30 hari',
            'quotation_reference' => 'QT-001',
            'is_selected' => true,
        ]);

        BppAppendixRow::query()->create([
            'bpp_id' => $bpp->id,
            'appendix_type' => 'c2',
            'line_number' => 1,
            'item_spesifikasi' => 'Komputer riba',
            'kuantiti' => 10,
            'unit_ukuran' => 'unit',
            'harga_seunit' => 1250.05,
            'jumlah_harga' => 12500.50,
        ]);

        $bpp->update([
            'c1_selection_reason' => 'Tawaran harga terbaik',
            'd_nama_pembekal' => $selectedQuote->supplier_name,
            'd_kriteria_pemilihan' => 'Tawaran harga terbaik',
            'b3_nilai_tawaran_perolehan' => '12500.50',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('bpp.show', $bpp));

        $response->assertOk();
        $response->assertSee('Top Section');
        $response->assertSee('Kaedah Perolehan');
        $response->assertDontSee('Ready for Review');
    }

    public function test_bpp_readiness_shows_pending_extraction_warning_without_blocking_manual_save(): void
    {
        $user = User::factory()->create();

        $bpp = Bpp::query()->create([
            'title' => 'Pending extraction draft',
            'status' => 'draft',
            'b1_tajuk_perolehan' => 'Perolehan komputer riba',
            'b2_kategori_perolehan' => 'Bekalan',
            'b6_justifikasi_keperluan' => 'Penggantian aset lama.',
            'b3_nilai_tawaran_perolehan' => '12500.50',
            'quotation_extraction_review' => [
                'valid' => true,
                'errors' => [],
                'warnings' => [],
                'data' => [
                    'selected_supplier' => 'Alpha Supplies Sdn. Bhd.',
                ],
            ],
        ]);

        BppAppendixRow::query()->create([
            'bpp_id' => $bpp->id,
            'appendix_type' => 'c2',
            'line_number' => 1,
            'item_spesifikasi' => 'Komputer riba',
            'kuantiti' => 10,
            'unit_ukuran' => 'unit',
            'harga_seunit' => 1250.05,
            'jumlah_harga' => 12500.50,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('bpp.show', $bpp));

        $response->assertOk();
        $response->assertSee('Top Section');
        $response->assertSee('Kaedah Perolehan');
        $response->assertDontSee('In Progress');
    }

    public function test_printable_preview_routes_render_stable_bpp_pages(): void
    {
        $user = User::factory()->create();

        $bpp = Bpp::query()->create([
            'title' => 'Printable preview draft',
            'status' => 'draft',
            'b1_tajuk_perolehan' => 'Perolehan komputer riba',
            'b2_kategori_perolehan' => 'Bekalan',
            'b6_justifikasi_keperluan' => 'Penggantian aset lama.',
            'd_nama_pembekal' => 'Alpha Supplies Sdn. Bhd.',
        ]);

        $checklistResponse = $this
            ->actingAs($user)
            ->get(route('bpp.printables.checklist', $bpp));

        $checklistResponse->assertOk();
        $checklistResponse->assertSee('Senarai Semak');
        $checklistResponse->assertSee('Print Preview');

        $pageOneResponse = $this
            ->actingAs($user)
            ->get(route('bpp.printables.page-one', $bpp));

        $pageOneResponse->assertOk();
        $pageOneResponse->assertSee('BPP Page 1');
        $pageOneResponse->assertSee('Perolehan komputer riba');
        $pageOneResponse->assertSee('Alpha Supplies Sdn. Bhd.');

        $pageTwoResponse = $this
            ->actingAs($user)
            ->get(route('bpp.printables.page-two', $bpp));

        $pageTwoResponse->assertOk();
        $pageTwoResponse->assertSee('BPP Page 2');
        $pageTwoResponse->assertSee('Keputusan / Kelulusan');
    }

    public function test_printable_preview_routes_render_dynamic_appendix_pages_from_real_draft_data(): void
    {
        $user = User::factory()->create();

        $bpp = Bpp::query()->create([
            'title' => 'Dynamic appendix preview draft',
            'status' => 'draft',
            'no_rujukan_perolehan' => 'BPP-2026-009',
            'b1_tajuk_perolehan' => 'Perolehan komputer dan servis',
            'b2_kategori_perolehan' => 'Bekalan',
            'd_kriteria_pemilihan' => 'Tawaran harga terbaik',
        ]);

        BppSupplierQuote::query()->create([
            'bpp_id' => $bpp->id,
            'supplier_name' => 'Alpha Supplies Sdn. Bhd.',
            'total_price' => 12500.50,
            'delivery_period' => '14 hari',
            'validity_period' => '30 hari',
            'quotation_reference' => 'QT-001',
            'is_selected' => true,
        ]);

        BppSupplierQuote::query()->create([
            'bpp_id' => $bpp->id,
            'supplier_name' => 'Beta Tech Enterprise',
            'total_price' => 12990.00,
            'delivery_period' => '21 hari',
            'validity_period' => '30 hari',
            'quotation_reference' => 'QT-002',
            'is_selected' => false,
        ]);

        BppAppendixRow::query()->create([
            'bpp_id' => $bpp->id,
            'appendix_type' => 'c2',
            'line_number' => 1,
            'item_spesifikasi' => 'Komputer riba spesifikasi tinggi untuk makmal komputer',
            'kuantiti' => 10,
            'unit_ukuran' => 'unit',
            'harga_seunit' => 1250.05,
            'jumlah_harga' => 12500.50,
        ]);

        BppAppendixRow::query()->create([
            'bpp_id' => $bpp->id,
            'appendix_type' => 'c3',
            'line_number' => 1,
            'item_spesifikasi' => 'Khidmat konfigurasi rangkaian dalaman',
            'kuantiti' => 1,
            'unit_ukuran' => 'lot',
            'harga_seunit' => 3000.00,
            'jumlah_harga' => 3000.00,
        ]);

        $c1Response = $this
            ->actingAs($user)
            ->get(route('bpp.printables.c1', $bpp));

        $c1Response->assertOk();
        $c1Response->assertSee('C1 - Kajian Pasaran');
        $c1Response->assertSee('Alpha Supplies Sdn. Bhd.');
        $c1Response->assertSee('Dipilih');

        $c2Response = $this
            ->actingAs($user)
            ->get(route('bpp.printables.c2', $bpp));

        $c2Response->assertOk();
        $c2Response->assertSee('C2 - Perbekalan');
        $c2Response->assertSee('Komputer riba spesifikasi tinggi untuk makmal komputer');
        $c2Response->assertSee('12500.50');

        $c3Response = $this
            ->actingAs($user)
            ->get(route('bpp.printables.c3', $bpp));

        $c3Response->assertOk();
        $c3Response->assertSee('C3 - Perkhidmatan');
        $c3Response->assertSee('Khidmat konfigurasi rangkaian dalaman');
        $c3Response->assertSee('bukan lampiran aktif');

        $c4Response = $this
            ->actingAs($user)
            ->get(route('bpp.printables.c4', $bpp));

        $c4Response->assertOk();
        $c4Response->assertSee('C4 - Kerja');
        $c4Response->assertSee('Tiada baris item direkodkan untuk lampiran ini.');
    }

    public function test_bpp_pdf_package_can_be_exported_without_mutating_the_draft(): void
    {
        $user = User::factory()->create();

        $bpp = Bpp::query()->create([
            'title' => 'PDF export draft',
            'status' => 'draft',
            'no_rujukan_perolehan' => 'BPP-2026-010',
            'b1_tajuk_perolehan' => 'Perkhidmatan penyelenggaraan sistem',
            'b2_kategori_perolehan' => 'Perkhidmatan',
            'b3_nilai_tawaran_perolehan' => '5000.00',
            'b6_justifikasi_keperluan' => 'Penyelenggaraan tahunan sistem sedia ada.',
            'd_nama_pembekal' => 'Gamma Services Sdn. Bhd.',
            'd_kriteria_pemilihan' => 'Keupayaan teknikal dan kewangan',
        ]);

        BppSupplierQuote::query()->create([
            'bpp_id' => $bpp->id,
            'supplier_name' => 'Gamma Services Sdn. Bhd.',
            'total_price' => 5000.00,
            'delivery_period' => '30 hari',
            'validity_period' => '45 hari',
            'quotation_reference' => 'GS-001',
            'is_selected' => true,
        ]);

        BppAppendixRow::query()->create([
            'bpp_id' => $bpp->id,
            'appendix_type' => 'c3',
            'line_number' => 1,
            'item_spesifikasi' => 'Penyelenggaraan sistem aplikasi utama',
            'kuantiti' => 1,
            'unit_ukuran' => 'lot',
            'harga_seunit' => 5000.00,
            'jumlah_harga' => 5000.00,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('bpp.export.pdf', $bpp));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $response->assertHeader('content-disposition');
        $this->assertStringStartsWith('%PDF', $response->getContent());

        $this->assertDatabaseHas('bpps', [
            'id' => $bpp->id,
            'status' => 'draft',
            'b2_kategori_perolehan' => 'Perkhidmatan',
            'b3_nilai_tawaran_perolehan' => '5000.00',
        ]);

        $this->assertDatabaseHas('bpp_appendix_rows', [
            'bpp_id' => $bpp->id,
            'appendix_type' => 'c3',
            'jumlah_harga' => '5000.00',
        ]);
    }
}
