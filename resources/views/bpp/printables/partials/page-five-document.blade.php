@php
    $quotes = $bpp->supplierQuotes()->with('items')->get()->take(6)->values();
    $selectedQuote = $quotes->firstWhere('is_selected', true) ?? $quotes->first();
    $selectedItems = $selectedQuote?->items?->values() ?? collect();
    $appendixRows = $bpp->appendixRows()->where('appendix_type', 'c2')->orderBy('line_number')->get();

    $lineNumbers = $quotes
        ->flatMap(fn ($quote) => $quote->items->pluck('line_number'))
        ->unique()
        ->sort()
        ->values();

    $maxLine = max(14, (int) ($lineNumbers->last() ?? 0));
    $displayLines = collect(range(1, $maxLine));

    $quoteItemsBySupplier = $quotes->mapWithKeys(function ($quote) {
        return [$quote->id => $quote->items->keyBy('line_number')];
    });

    $currency = static fn ($value) => ($value === null || $value === '') ? '' : number_format((float) $value, 2, '.', ',');
    $qty = static fn ($value) => ($value === null || $value === '') ? '' : rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');

    $surveyRows = collect([
        ['mark' => '', 'method' => 'Harga Perolehan terdahulu', 'detail' => '', 'price' => ''],
        ['mark' => '', 'method' => 'Laman Sesawang 1', 'detail' => '', 'price' => ''],
        ['mark' => '', 'method' => 'Laman Sesawang 2 (jika berkaitan)', 'detail' => '', 'price' => ''],
        ['mark' => '', 'method' => 'Lain-lain Sumber (Sila Nyatakan)', 'detail' => '', 'price' => ''],
    ]);

    $topRightDate = $bpp->tarikh_kuat_kuasa ?? '01 DISEMBER 2025';
@endphp

<style>
*{box-sizing:border-box}
.pdf-page{width:297mm;height:210mm;margin:0 auto;background:#fff;overflow:hidden;padding:6mm 7mm 5mm}
.c1-shell{width:100%;height:100%;font-family:Arial,Helvetica,sans-serif;color:#000;font-size:2.7mm;line-height:1.03;overflow:hidden}
.c1-topline{display:flex;justify-content:flex-end;gap:6mm;font-size:2.8mm;margin-bottom:.7mm}
.c1-topline span{display:inline-block}
.c1-table{width:100%;border-collapse:collapse;table-layout:fixed}
.c1-table td,.c1-table th{border:.25mm solid #000;padding:.22mm .42mm;vertical-align:top}
.c1-table th{font-weight:700}
.c1-gray{background:#d9d9d9;font-weight:700}
.c1-center{text-align:center}
.c1-right{text-align:right}
.c1-small{font-size:2.2mm}
.c1-tight td,.c1-tight th{padding:.18mm .34mm}
.c1-gap{height:.8mm}
.c1-method-no{width:12mm}
.c1-method-name{width:94mm}
.c1-method-detail{width:60mm}
.c1-method-price{width:auto}
.c1-items-no{width:7mm}
.c1-items-desc{width:84mm}
.c1-items-qty{width:18mm}
.c1-items-unit{width:20mm}
.c1-supplier-col{width:25mm}
.c1-blank{color:#aaa}
.c1-note{font-size:2.2mm;margin:.28mm 0 0 .7mm}
.c1-note-row{display:grid;grid-template-columns:1fr 1fr;column-gap:7mm;margin-top:.35mm}
.c1-note-col div{padding:.08mm 0}
.c1-section-title{margin-top:.65mm}
.c1-sign-row{display:grid;grid-template-columns:1fr 1fr;column-gap:5mm;margin-top:.55mm}
.c1-sign-box{min-height:10.6mm;padding:.8mm .7mm .55mm;border:.25mm solid #000}
.c1-sign-box.plain{border:0;padding:.35mm 0 0}
.c1-sign-inline{display:grid;grid-template-columns:44mm 33mm auto;column-gap:3mm;align-items:start}
.c1-sign-labels div,.c1-sign-values div{padding:.15mm 0}
.c1-signature{font-size:9mm;line-height:1;margin-top:-2mm}
.c1-stamp{font-size:3mm;line-height:1.02;color:#1d3f9c}
.c1-bottom-space{height:.4mm}
</style>

<div class="pdf-page">
    <div class="c1-shell">
        <div class="c1-topline">
            <span>{{ $bpp->ruj_dokumen ?? 'NIBM/F/PRC/02/01' }}</span>
            <span>{{ $topRightDate }}</span>
            <span>LAMPIRAN 1</span>
        </div>

        <table class="c1-table c1-tight">
            <colgroup>
                <col>
            </colgroup>
            <tr>
                <td class="c1-gray">C1&nbsp;&nbsp;KAEDAH KAJIAN PASARAN</td>
            </tr>
        </table>

        <table class="c1-table c1-tight">
            <colgroup>
                <col class="c1-method-no">
                <col class="c1-method-name">
                <col class="c1-method-detail">
                <col class="c1-method-price">
            </colgroup>
            <tr>
                <td colspan="4">1.&nbsp; Sila laksanakan sekurang-kurangnya dua (2) kaedah Kajian Pasaran termasuk Harga Perolehan terdahulu.</td>
            </tr>
            <tr class="c1-gray c1-center">
                <td>(√)</td>
                <td>Kaedah</td>
                <td>Perincian (Nama Pembekal / URL)</td>
                <td>Harga Yang Diperolehi (RM)</td>
            </tr>
            @foreach ($surveyRows as $row)
                <tr>
                    <td class="c1-center">{{ $row['mark'] }}</td>
                    <td>{{ $row['method'] }}</td>
                    <td class="c1-small {{ $row['detail'] === '' ? 'c1-blank' : '' }}">{{ $row['detail'] === '' ? ' ' : $row['detail'] }}</td>
                    <td class="c1-center">{{ $row['price'] === '' ? '' : $currency($row['price']) }}</td>
                </tr>
            @endforeach
        </table>

        <div class="c1-note">* Sila sertakan bukti/dokumen berkaitan bagi kaedah yang telah dijalankan</div>
        <div style="font-weight:700;margin:0 0 1.15mm 0">Sebut Harga Pembekal:</div>

        <table class="c1-table c1-tight">
            <colgroup>
                <col class="c1-items-no">
                <col class="c1-items-desc">
                <col class="c1-items-qty">
                <col class="c1-items-unit">
                @for ($i = 0; $i < 6; $i++)
                    <col class="c1-supplier-col">
                @endfor
            </colgroup>
            <tr>
                <td colspan="10" class="c1-gray">2&nbsp;&nbsp;LAPORAN ANALISA HARGA / KAJIAN PASARAN</td>
            </tr>
            <tr class="c1-gray c1-center">
                <td rowspan="2">No.</td>
                <td rowspan="2"></td>
                <td rowspan="2">Kuantiti</td>
                <td rowspan="2">Unit Ukuran</td>
                <td colspan="6">Butiran Tawaran Harga Dari Pembekal</td>
            </tr>
            <tr class="c1-gray c1-center c1-small">
                @for ($i = 0; $i < 6; $i++)
                    @php $quote = $quotes->get($i); @endphp
                    <td>
                        <div>Nama Pembekal #{{ $i + 1 }}</div>
                        <div style="margin-top:.35mm;font-weight:700">{{ $quote?->supplier_name ?? '' }}</div>
                    </td>
                @endfor
            </tr>
            <tr class="c1-gray c1-center">
                <td></td>
                <td>Perincian setiap item</td>
                <td>-</td>
                <td>-</td>
                <td colspan="6">Harga Tawaran</td>
            </tr>
            @foreach ($displayLines as $line)
                @php
                    $rowSeed = $selectedItems->firstWhere('line_number', $line)
                        ?? $appendixRows->firstWhere('line_number', $line)
                        ?? $quotes->map(fn ($quote) => $quoteItemsBySupplier[$quote->id]->get($line))->first(fn ($item) => $item !== null);
                @endphp
                <tr>
                    <td class="c1-center">{{ $line }}</td>
                    <td>{{ $rowSeed?->item_spesifikasi ?? '' }}</td>
                    <td class="c1-center">{{ $qty($rowSeed?->kuantiti) }}</td>
                    <td class="c1-center">{{ $rowSeed?->unit_ukuran ?? '' }}</td>
                    @for ($i = 0; $i < 6; $i++)
                        @php
                            $quote = $quotes->get($i);
                            $item = $quote ? $quoteItemsBySupplier[$quote->id]->get($line) : null;
                        @endphp
                        <td class="c1-right">{{ $currency($item?->harga_tawaran) }}</td>
                    @endfor
                </tr>
            @endforeach
            <tr>
                <td colspan="4" class="c1-right"><strong>Jumlah Harga Tanpa Cukai</strong></td>
                @for ($i = 0; $i < 6; $i++)
                    @php $quote = $quotes->get($i); @endphp
                    <td class="c1-right">{{ $currency($quote?->total_price) }}</td>
                @endfor
            </tr>
            <tr>
                <td colspan="4" class="c1-right"><strong>Cukai</strong></td>
                @for ($i = 0; $i < 6; $i++)
                    <td class="c1-right">%</td>
                @endfor
            </tr>
            <tr>
                <td colspan="4" class="c1-right"><strong>JUMLAH KESELURUHAN TERMASUK CUKAI</strong></td>
                @for ($i = 0; $i < 6; $i++)
                    @php $quote = $quotes->get($i); @endphp
                    <td class="c1-right"><strong>{{ $currency($quote?->total_price) }}</strong></td>
                @endfor
            </tr>
        </table>

        <div class="c1-note"><strong>* Sila sertakan dokumen sijil pendaftaran penubuhan entiti dan tawaran harga lengkap dengan kandungan seperti berikut :</strong></div>
        <div class="c1-note-row c1-small">
            <div class="c1-note-col">
                <div>i.&nbsp;&nbsp;&nbsp;Nama dan alamat lengkap pembekal</div>
                <div>ii.&nbsp;&nbsp;No. tel/no. faks/emel pembekal &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Faks :</div>
                <div>iii.&nbsp;&nbsp;Nama PIC pembekal</div>
                <div>iv.&nbsp;&nbsp;Tempoh sahlaku tawaran harga (hari / minggu / bulan / tahun) &nbsp;&nbsp; {{ $selectedQuote?->validity_period ?? '10-12 weeks' }}</div>
            </div>
            <div class="c1-note-col">
                <div>v.&nbsp;&nbsp;&nbsp;Tarikh penghantaran</div>
                <div>vi.&nbsp;&nbsp;Terma pembayaran</div>
                <div>vii.&nbsp;&nbsp;No rujukan dokumen tawaran pembekal</div>
                <div>viii.&nbsp;Lain-lain</div>
            </div>
        </div>

        <div class="c1-section-title">
            <table class="c1-table c1-tight">
                <tr><td class="c1-gray">3&nbsp;&nbsp;PERAKUAN PEMOHON</td></tr>
            </table>
        </div>

        <div class="c1-sign-row">
            <div class="c1-sign-box plain">
                <div>Saya mengaku dan mengesahkan bahawa semua maklumat di atas adalah benar dan saya telah melaksanakan</div>
                <div>Kajian Pasaran bagi mendapatkan anggaran harga yang munasabah serta menguntungkan NIBM</div>
            </div>
            <div class="c1-sign-box plain">
                <div class="c1-sign-inline">
                    <div>Tandatangan:</div>
                    <div></div>
                    <div class="c1-sign-labels">
                        <div>Nama : {{ $bpp->a1_nama_pemohon ?? '' }}</div>
                        <div>Tarikh : {{ now()->format('j.n.Y') }}</div>
                        <div>Cop Rasmi</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="c1-section-title">
            <table class="c1-table c1-tight">
                <tr><td class="c1-gray">4&nbsp;&nbsp;PENGESAHAN BAHAGIAN PEROLEHAN</td></tr>
            </table>
        </div>

        <div class="c1-sign-row">
            <div class="c1-sign-box plain">
                <div>Kajian Pasaran telah dilaksanakan oleh Pemohon dan anggaran harga dikemukakan adalah mengikut harga yang</div>
                <div>munasabah serta menguntungkan NIBM</div>
            </div>
            <div class="c1-sign-box plain">
                <div class="c1-sign-inline">
                    <div>Tandatangan:</div>
                    <div></div>
                    <div class="c1-sign-labels">
                        <div>Nama :</div>
                        <div>Tarikh :</div>
                        <div>Cop Rasmi</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
