<?php

return [
    'quotation_extraction' => [
        'format_version' => 'QUOTATION_EXTRACTION_V1',
        'prompt' => <<<'PROMPT'
Return ONLY in the exact format below. Do not add markdown, code fences, commentary, bullets, or extra headings.

QUOTATION_EXTRACTION_V1
PROCUREMENT_CATEGORY: Bekalan
SELECTED_SUPPLIER: Example Supplier Sdn. Bhd.
SELECTION_REASON: Tawaran harga terbaik
SELECTION_REASON_LAIN_LAIN:
SUPPLIERS:
supplier_name|total_price|delivery_period|validity_period|quotation_reference
Example Supplier Sdn. Bhd.|12500.50|14 hari|30 hari|QT-001
Another Supplier Sdn. Bhd.|12990.00|21 hari|30 hari|QT-002
SELECTED_SUPPLIER_ITEMS:
item_spesifikasi|kuantiti|unit_ukuran|harga_seunit|jumlah_harga
Komputer riba|10|unit|1250.05|12500.50
TOTALS:
appendix_total|12500.50
selected_supplier_total|12500.50

Rules:
1. PROCUREMENT_CATEGORY must be exactly one of: Bekalan, Perkhidmatan, Kerja
2. SELECTION_REASON must be exactly one of:
   - Tawaran harga terbaik
   - Keupayaan teknikal dan kewangan
   - Pengalaman dan rekod prestasi
   - Keupayaan operasi dan sumber
   - Tempoh pembekalan/perlaksanaan yang munasabah
   - Pembekal Tunggal
   - Lain-lain
3. If SELECTION_REASON is not Lain-lain, leave SELECTION_REASON_LAIN_LAIN blank
4. Keep the SUPPLIERS and SELECTED_SUPPLIER_ITEMS header lines exactly as shown
5. Use one row per line under each table section
6. Keep decimal values numeric only, for example 12500.50
7. The selected supplier must also appear in the SUPPLIERS section
8. appendix_total and selected_supplier_total must match the sum of SELECTED_SUPPLIER_ITEMS.jumlah_harga
PROMPT,
    ],
];
