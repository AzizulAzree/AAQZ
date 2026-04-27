<?php

return [
    'quotation_extraction' => [
        'format_version' => 'QUOTATION_EXTRACTION_V1',
        'prompt' => <<<'PROMPT'
Return ONLY plain text in the exact format below.
Do not add markdown.
Do not add code fences.
Do not add commentary.
Do not add extra headings.
Do not add any text before QUOTATION_EXTRACTION_V1.
Do not add any text after selected_supplier_total|...

This output maps directly into the database:
- SUPPLIERS -> bpp_supplier_quotes
- SUPPLIER_COMPARISON_ITEMS -> bpp_supplier_quote_items
- SELECTED_SUPPLIER_ITEMS -> bpp_appendix_rows

Use this exact structure and replace example values only:

QUOTATION_EXTRACTION_V1
PROCUREMENT_CATEGORY: Bekalan
SELECTED_SUPPLIER: Example Supplier Sdn. Bhd.
SELECTION_REASON: Tawaran harga terbaik
SELECTION_REASON_LAIN_LAIN:
SUPPLIERS:
supplier_name|registration_number|supplier_address|total_price|delivery_period|validity_period|quotation_reference
Example Supplier Sdn. Bhd.|003495679-P|No. 13, Jalan Nova U5/52, Subang Bestari, Shah Alam|12500.50|14 hari|30 hari|QT-001
Another Supplier Sdn. Bhd.|00998877-X|Lot 8, Jalan Teknologi 2, Cyberjaya|12990.00|21 hari|30 hari|QT-002
SUPPLIER_COMPARISON_ITEMS:
line_number|item_spesifikasi|kuantiti|unit_ukuran|supplier_name|harga_tawaran|jumlah_harga
1|Komputer riba|10|unit|Example Supplier Sdn. Bhd.|1250.05|12500.50
1|Komputer riba|10|unit|Another Supplier Sdn. Bhd.|1299.00|12990.00
SELECTED_SUPPLIER_ITEMS:
item_spesifikasi|kuantiti|unit_ukuran|harga_seunit|jumlah_harga
Komputer riba|10|unit|1250.05|12500.50
TOTALS:
appendix_total|12500.50
selected_supplier_total|12500.50

Hard rules:
1. Keep these section names EXACTLY:
   SUPPLIERS:
   SUPPLIER_COMPARISON_ITEMS:
   SELECTED_SUPPLIER_ITEMS:
   TOTALS:
2. Keep these header rows EXACTLY:
   supplier_name|registration_number|supplier_address|total_price|delivery_period|validity_period|quotation_reference
   line_number|item_spesifikasi|kuantiti|unit_ukuran|supplier_name|harga_tawaran|jumlah_harga
   item_spesifikasi|kuantiti|unit_ukuran|harga_seunit|jumlah_harga
3. PROCUREMENT_CATEGORY must be exactly one of: Bekalan, Perkhidmatan, Kerja
4. SELECTION_REASON must be exactly one of:
   Tawaran harga terbaik
   Keupayaan teknikal dan kewangan
   Pengalaman dan rekod prestasi
   Keupayaan operasi dan sumber
   Tempoh pembekalan/perlaksanaan yang munasabah
   Pembekal Tunggal
   Lain-lain
5. If SELECTION_REASON is not Lain-lain, leave SELECTION_REASON_LAIN_LAIN blank.
6. Use one data row per line under each table section.
7. Never omit SUPPLIER_COMPARISON_ITEMS.
8. Never omit SELECTED_SUPPLIER_ITEMS.
9. Never omit TOTALS.
10. At least one row is required in SUPPLIERS.
11. At least one row is required in SUPPLIER_COMPARISON_ITEMS.
12. At least one row is required in SELECTED_SUPPLIER_ITEMS.
13. SELECTED_SUPPLIER must exactly match one supplier_name in SUPPLIERS.
14. Every supplier_name in SUPPLIER_COMPARISON_ITEMS must exactly match one supplier_name in SUPPLIERS.
15. SELECTED_SUPPLIER_ITEMS must contain only the selected supplier's items.
16. If multiple suppliers quoted the same item, repeat the same line_number for that item across suppliers.
17. Use numeric decimals only like 12500.50.
18. Do not include RM.
19. Do not include thousand separators.
20. registration_number, supplier_address, quotation_reference, delivery_period, and validity_period may be blank, but preserve all separators.
21. jumlah_harga in SUPPLIER_COMPARISON_ITEMS must equal kuantiti x harga_tawaran.
22. jumlah_harga in SELECTED_SUPPLIER_ITEMS must equal kuantiti x harga_seunit.
23. appendix_total must equal the sum of all SELECTED_SUPPLIER_ITEMS.jumlah_harga.
24. selected_supplier_total must equal the sum of all SELECTED_SUPPLIER_ITEMS.jumlah_harga.
25. The selected supplier total_price in SUPPLIERS must equal selected_supplier_total.
26. Recalculate every jumlah_harga and both totals before returning the final answer.
27. If OCR or source arithmetic is inconsistent, correct it to a valid numeric result instead of copying the wrong total.
PROMPT,
    ],
];
