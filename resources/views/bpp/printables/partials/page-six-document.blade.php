@php
    $rows = $bpp->appendixRows()
        ->where('appendix_type', 'c2')
        ->orderBy('line_number')
        ->get();

    $maxLine = max(10, (int) ($rows->max('line_number') ?? 0));
    $displayLines = collect(range(1, $maxLine));
    $rowsByLine = $rows->keyBy('line_number');
    $total = (float) $rows->sum('jumlah_harga');

    $qty = static fn ($value) => ($value === null || $value === '') ? '' : rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
    $money = static fn ($value) => ($value === null || $value === '') ? '' : number_format((float) $value, 2, '.', ',');
@endphp

<style>
*{box-sizing:border-box}
.pdf-page{width:297mm;height:210mm;margin:0 auto;background:#fff;overflow:hidden;padding:7mm 8mm 6mm}
.c2-shell{width:100%;height:100%;font-family:Arial,Helvetica,sans-serif;color:#000}
.c2-topline{display:flex;justify-content:flex-end;gap:8mm;font-size:3.1mm;line-height:1;margin-bottom:3.5mm}
.c2-table{width:100%;border-collapse:collapse;table-layout:fixed}
.c2-table td,.c2-table th{border:.3mm solid #000;padding:.4mm .6mm;vertical-align:middle}
.c2-table th{font-weight:400}
.c2-gray{background:#d9d9d9}
.c2-center{text-align:center}
.c2-right{text-align:right}
.c2-title{font-size:5.2mm;font-weight:400}
.c2-subhead{font-size:3.8mm;font-weight:400}
.c2-body{font-size:2.65mm;line-height:1.08}
.c2-item{font-size:2.55mm;line-height:1.05}
.c2-total-label{font-size:5mm;font-weight:700}
.c2-total-value{font-size:3.1mm}
.c2-col-bil{width:16mm}
.c2-col-item{width:62mm}
.c2-col-qty{width:27mm}
.c2-col-unit{width:30mm}
.c2-col-price{width:35mm}
.c2-col-total{width:35mm}
</style>

<div class="pdf-page">
    <div class="c2-shell">
        <div class="c2-topline">
            <span>{{ $bpp->ruj_dokumen ?? 'NIBM/F/PRC/02/01' }}</span>
            <span>{{ $bpp->tarikh_kuat_kuasa ?? '01 DISEMBER 2025' }}</span>
            <span>LAMPIRAN 2</span>
        </div>

        <table class="c2-table">
            <colgroup>
                <col class="c2-col-bil">
                <col class="c2-col-item">
                <col class="c2-col-qty">
                <col class="c2-col-unit">
                <col class="c2-col-price">
                <col class="c2-col-total">
            </colgroup>
            <tr>
                <td class="c2-gray c2-center c2-subhead">C2</td>
                <td colspan="5" class="c2-gray c2-title">BUTIRAN PESANAN BELIAN PEMBEKALAN</td>
            </tr>
            <tr class="c2-gray c2-center c2-subhead">
                <td rowspan="2">BIL</td>
                <td rowspan="2">ITEM/SPESIFIKASI</td>
                <td colspan="2">KUANTITI</td>
                <td rowspan="2">HARGA/UNIT<br>(RM)</td>
                <td rowspan="2">JUMLAH<br>(RM)</td>
            </tr>
            <tr class="c2-gray c2-center c2-subhead">
                <td>BILANGAN</td>
                <td>UKURAN</td>
            </tr>

            @foreach ($displayLines as $line)
                @php $row = $rowsByLine->get($line); @endphp
                <tr class="c2-body">
                    <td class="c2-center">{{ $line }}</td>
                    <td class="c2-item c2-center">{{ $row?->item_spesifikasi ?? '' }}</td>
                    <td class="c2-center">{{ $qty($row?->kuantiti) }}</td>
                    <td class="c2-center">{{ $row?->unit_ukuran ?? '' }}</td>
                    <td class="c2-center">{{ $money($row?->harga_seunit) }}</td>
                    <td class="c2-center">{{ $money($row?->jumlah_harga) }}</td>
                </tr>
            @endforeach

            <tr>
                <td colspan="5" class="c2-right c2-total-label">JUMLAH KESELURUHAN (RM)</td>
                <td class="c2-right c2-total-value">{{ $money($total) }}</td>
            </tr>
        </table>
    </div>
</div>
