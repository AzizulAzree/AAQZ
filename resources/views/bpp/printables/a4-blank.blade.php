@php
    $selectedCriteria = $bpp->selectedCriteriaOptions();
    $appendixType = $bpp->activeAppendixType();
    $hasC1 = filled($bpp->c1_selection_reason) || filled($bpp->c1_selection_reason_lain_lain) || $appendixType !== null;
    $criteriaMark = static fn (string $label): string => in_array($label, $selectedCriteria, true) ? '/' : '';
    $appendixMark = static fn (string $type): string => $appendixType === $type ? '/' : '';
    $currency = static fn (mixed $amount): string => $amount === null || $amount === '' ? 'RM' : ('RM'.number_format((float) $amount, 2, '.', ','));
    $requiredDate = $bpp->procurementRequiredMonthLabel() ?? '';
    $addressLines = preg_split('/\r\n|\r|\n/', trim((string) $bpp->d_alamat_pembekal)) ?: [];
@endphp

<style>
:root{--b:#000;--gray:#7f7f7f}*{box-sizing:border-box}.pdf-page{width:210mm;height:297mm;margin:0 auto;background:#fff;overflow:hidden;padding:6mm 6mm 5mm}.bpp-shell{width:100%;border:2px solid var(--b);padding:1.7mm 1.45mm 1.2mm}.bpp-table{width:100%;border-collapse:collapse;table-layout:fixed;font-family:Arial,Helvetica,sans-serif;color:#000;font-size:7.8px;line-height:1.1}.bpp-table td{border:1px solid var(--b);padding:.62mm .8mm;vertical-align:top}.bpp-logo-cell{width:43mm;text-align:center;vertical-align:middle!important;padding:0!important}.bpp-logo{width:40mm;max-width:95%;height:auto;display:block;margin:0 auto}.bpp-title{text-align:center;vertical-align:middle!important;font-size:12px;font-weight:700;padding:1.8mm 1mm!important}.bpp-doc-title{text-align:center;vertical-align:middle!important;font-size:7.5px;line-height:1.12;padding:.8mm!important}.bpp-doc-title strong{display:block;font-size:10px}.bpp-meta{font-size:8.5px;line-height:1.08;padding:.65mm .85mm!important}.bpp-ref{margin-top:.95mm;font-family:Arial,Helvetica,sans-serif}.bpp-ref-table{width:100%;border-collapse:collapse;table-layout:fixed}.bpp-ref-table td{border:1px solid var(--b);padding:.62mm .72mm;vertical-align:middle;line-height:1}.bpp-ref-label{width:41mm;background:#e6e6e6;font-weight:700;font-size:10px}.bpp-ref-colon{width:3.5mm;text-align:center;font-weight:700;font-size:10px}.bpp-ref-value{text-align:center;font-weight:700;font-size:10.8px}.bpp-ref-notes{display:grid;grid-template-columns:41mm 22mm 20mm 20mm 21mm 34mm;font-size:5.9px;font-style:italic;line-height:1;text-align:center;height:3.1mm;padding-top:.42mm}.bpp-section{margin-top:.82mm;background:var(--gray);color:#fff;border:1px solid var(--b);font-family:Arial,Helvetica,sans-serif;font-size:9.6px;font-weight:700;line-height:1;padding:.68mm .85mm}.bpp-arahan{font-family:Arial,Helvetica,sans-serif;font-size:8.7px;line-height:1.2;border-left:1px solid var(--b);border-right:1px solid var(--b);border-bottom:1px solid var(--b);padding:.82mm 1mm}.bpp-method{font-family:Arial,Helvetica,sans-serif;font-size:8.65px;line-height:1.18;border-left:1px solid var(--b);border-right:1px solid var(--b);border-bottom:1px solid var(--b);padding:1mm 1mm;display:grid;grid-template-columns:1fr 1.35fr;gap:.85mm 3.4mm}.bpp-box{display:inline-block;width:4.6mm;height:4.6mm;border:1px solid var(--b);background:#fff;text-align:center;line-height:4.18mm;font-size:9.4px;margin-right:2mm;vertical-align:middle}.bpp-form-table{width:100%;border-collapse:collapse;table-layout:fixed;font-family:Arial,Helvetica,sans-serif;font-size:8.7px;line-height:1.12;margin-top:.95mm}.bpp-form-table td{border:0;padding:.4mm .58mm;vertical-align:top}.bpp-form-table .label{width:28mm}.bpp-form-table .colon{width:2.2mm;text-align:center;padding-top:.56mm}.bpp-form-table .value{border:1px solid var(--b);height:5.3mm;padding:.56mm .7mm!important}.bpp-form-gap td{height:.9mm;padding:0!important;border:0!important}.bpp-bi{width:100%;border-collapse:collapse;table-layout:fixed;font-family:Arial,Helvetica,sans-serif;font-size:8.55px;line-height:1.08;margin-top:.85mm}.bpp-bi td{border:0;padding:.22mm .32mm;vertical-align:top}.bi-l{width:31mm}.bi-c{width:2.2mm;text-align:center;padding-top:.42mm!important}.bi-v{border:1px solid var(--b)!important;min-height:4.9mm;padding:.45mm .58mm!important}.bi-top{vertical-align:top!important}.bi-b1{height:9.5mm}.bi-b6{height:8.2mm}.bi-b7{height:8.2mm}.bi-small{font-size:6.15px}.bi-note{text-align:center;font-size:5.7px;line-height:1;padding:.1mm 0 0!important}.bi-center{text-align:center}.bi-nested{width:100%;border-collapse:collapse;table-layout:fixed;font-family:Arial,Helvetica,sans-serif;font-size:8.55px;line-height:1.08}.bi-nested td{border:0;padding:0 .32mm;vertical-align:top}.bi-nested .v{border:1px solid var(--b)!important;height:4.9mm;padding:.45mm .58mm!important}.bi-nested .c{width:2.2mm;text-align:center}.bi-gap td{height:.92mm;padding:0!important;border:0!important}.bpp-bii,.bpp-c{width:100%;border-collapse:collapse;table-layout:fixed;font-family:Arial,Helvetica,sans-serif;font-size:8.45px;line-height:1.1}.bpp-bii td,.bpp-c td{border:0;padding:.34mm .32mm;vertical-align:middle}.bii-v{border:1px solid var(--b)!important;height:4.9mm;padding:.45mm .58mm!important}.bii-box{display:inline-block;width:4.5mm;height:4.5mm;border:1px solid var(--b);background:#fff;text-align:center;line-height:4.05mm;vertical-align:middle}.bii-gap td{height:.92mm;padding:0!important;border:0!important}.dummy-gap{height:1mm}.c-check{width:49mm;text-align:left}.c-small{display:inline-block;width:5.2mm;height:4.5mm;border:1px solid var(--b);text-align:center;line-height:4.05mm;margin-left:18mm;margin-right:3mm;vertical-align:middle}.bpp-d,.bpp-ef{width:100%;border-collapse:collapse;table-layout:fixed;font-family:Arial,Helvetica,sans-serif;font-size:8.35px;line-height:1.05}.bpp-d td,.bpp-ef td{border:0;padding:.26mm .32mm;vertical-align:top}.d-left{width:40%}.d-reg-label{width:18%;text-align:right}.d-colon{width:2%;text-align:center}.d-reg-val{border:1px solid var(--b)!important;height:4.35mm;width:30%;padding:.45mm .58mm!important}.d-inner{width:100%;border-collapse:collapse;table-layout:fixed}.d-supplier{width:46%;height:31mm}.d-criteria{width:54%;height:31mm}.d-address{text-align:center;font-family:"Times New Roman",serif;font-size:8.1px;line-height:1.15;margin-top:1.1mm}.d-crit-row{display:grid;grid-template-columns:4mm 49mm 4mm auto;align-items:center;min-height:4.35mm}.d-box{display:inline-block;width:3.8mm;height:3.8mm;border:1px solid var(--b);text-align:center;line-height:3.42mm;vertical-align:middle}.d-note{font-size:7px;margin-left:4.4mm;line-height:1.04}.d-line{width:45%;border-top:1px solid var(--b);height:1.1mm;margin-left:.32mm}.bpp-ef{margin-top:.18mm}.ef-col{width:49%}.ef-gap{width:1%}.ef-box{border:1px solid var(--b);height:29mm;padding:.82mm .75mm;box-sizing:border-box;position:relative}.ef-sign{margin-top:2mm}.ef-bottom{position:absolute;left:.75mm;bottom:.8mm}
</style>

<div class="pdf-page"><div class="bpp-shell">
<table class="bpp-table">
<tr><td rowspan="3" class="bpp-logo-cell"><img src="{{ asset('images/bpp-preview/nibm-logo.png') }}" class="bpp-logo"></td><td colspan="4" class="bpp-title">BORANG</td></tr>
<tr><td colspan="4" class="bpp-doc-title"><div>Tajuk Dokumen:</div><strong>{{ $bpp->tajuk_dokumen ?? 'Borang Permohonan Perolehan' }}</strong></td></tr>
<tr><td class="bpp-meta">Ruj. Dokumen:<br>{{ $bpp->ruj_dokumen ?? 'NIBM/F/PRC/02/01' }}</td><td class="bpp-meta">No. Semakan:<br>{{ $bpp->no_semakan ?? '01' }}</td><td class="bpp-meta">Tarikh Kuatkuasa:<br>{{ $bpp->tarikh_kuat_kuasa ?? '01/01/2026' }}</td><td class="bpp-meta">{{ $bpp->muka_surat ?? 'Muka surat 1 dari 2' }}</td></tr>
</table>

<div class="bpp-ref">
    <table class="bpp-ref-table">
        <tr>
            <td class="bpp-ref-label">No. Rujukan Perolehan</td>
            <td class="bpp-ref-colon">:</td>
            <td class="bpp-ref-value">{{ $bpp->no_rujukan_perolehan }}</td>
        </tr>
    </table>
    <div class="bpp-ref-notes"><span></span><span>Maklumat kod peruntukan:</span><span>01-OE</span><span>02-Hasil</span><span>03-DE</span><span>04-Geran/Projek</span></div>
</div>

<div class="bpp-section">ARAHAN</div>
<div class="bpp-arahan">i. Sila rujuk Polisi Perolehan dan Prosedur berkaitan sebagai panduan mengisi borang ini<br>ii. Sila pastikan borang adalah lengkap dan borang senarai semak permohonan perolehan PERLU disertakan bersama dengan BPP ini</div>

<div class="bpp-section">KAEDAH PEROLEHAN</div>
<div class="bpp-method">
    <div><span class="bpp-box">{{ $bpp->kaedah_perolehan === 'pembelian_terus' ? '/' : '' }}</span>Pembelian Terus (sehingga tidak melebihi RM50,000.00)</div>
    <div><span class="bpp-box">{{ $bpp->kaedah_perolehan === 'sebut_harga' ? '/' : '' }}</span>Sebut Harga (RM50,000.00 sehingga tidak melebihi RM500,000.00)</div>
    <div><span class="bpp-box">{{ $bpp->kaedah_perolehan === 'pembekal_tunggal_bawah_50k' ? '/' : '' }}</span>Pembekal Tunggal (sehingga tidak melebihi RM500,000.00)</div>
    <div><span class="bpp-box">{{ $bpp->kaedah_perolehan === 'tender' ? '/' : '' }}</span>Tender (melebihi RM500,000.00)</div>
    <div></div>
    <div><span class="bpp-box">{{ $bpp->kaedah_perolehan === 'pembekal_tunggal_rundingan_terus' ? '/' : '' }}</span>Pembekal Tunggal/Rundingan Terus (Melebihi RM50,000.00)</div>
</div>

<div class="bpp-section">A: PERIHAL PEMOHON</div>
<table class="bpp-form-table">
    <tr><td class="label">A1. Nama Pemohon</td><td class="colon">:</td><td class="value">{{ $bpp->a1_nama_pemohon }}</td><td class="label">A2. Jawatan/Gred</td><td class="colon">:</td><td class="value">{{ $bpp->a2_jawatan_gred }}</td></tr>
    <tr class="bpp-form-gap"><td colspan="6"></td></tr>
    <tr><td class="label">A3. Jabatan/Inst.</td><td class="colon">:</td><td class="value">{{ $bpp->a3_jabatan_institusi }}</td><td class="label">A4. No. Tel/Email</td><td class="colon">:</td><td class="value">{{ $bpp->a4_no_tel_email }}</td></tr>
</table>

<div class="bpp-section">B(i): PERIHAL PEROLEHAN (Wajib diisi oleh Pemohon dan sila tandakan √ pada yang berkenaan)</div>
<table class="bpp-bi">
    <colgroup><col class="bi-l"><col style="width:2mm"><col></colgroup>
    <tr><td>B1. Tajuk Perolehan</td><td class="bi-c">:</td><td class="bi-v bi-b1 bi-top">{{ $bpp->b1_tajuk_perolehan }}</td></tr>
    <tr class="bi-gap"><td colspan="3"></td></tr>
    <tr><td>B2. Kategori Perolehan</td><td class="bi-c">:</td><td><span class="bpp-box">{{ $bpp->b2_kategori_perolehan === 'Bekalan' ? '/' : '' }}</span>Bekalan&nbsp;&nbsp;&nbsp;<span class="bpp-box">{{ $bpp->b2_kategori_perolehan === 'Perkhidmatan' ? '/' : '' }}</span>Perkhidmatan&nbsp;&nbsp;&nbsp;<span class="bpp-box">{{ $bpp->b2_kategori_perolehan === 'Kerja' ? '/' : '' }}</span>Kerja</td></tr>
    <tr class="bi-gap"><td colspan="3"></td></tr>
    <tr>
        <td colspan="3">
            <table class="bi-nested">
                <colgroup><col style="width:20mm"><col style="width:2mm"><col style="width:26mm"><col style="width:25mm"><col style="width:2mm"><col style="width:30mm"><col style="width:31mm"><col style="width:2mm"><col></colgroup>
                <tr>
                    <td>B3. Nilai Tawaran Perolehan<br><span class="bi-small">(Pembelian terus)</span></td><td class="c">:</td><td class="v">{{ $currency($bpp->b3_nilai_tawaran_perolehan) }}</td>
                    <td>B4. Harga Indikatif<br><span class="bi-small">(Sebut Harga/Tender)</span></td><td class="c">:</td><td class="v">{{ $currency($bpp->b4_harga_indikatif) }}</td>
                    <td>B5. Peruntukan yang diluluskan</td><td class="c">:</td><td class="v">{{ $currency($bpp->b5_peruntukan_diluluskan) }}</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr class="bi-gap"><td colspan="3"></td></tr>
    <tr><td>B6. Justifikasi keperluan perolehan</td><td class="bi-c">:</td><td class="bi-v bi-b6 bi-top">{{ $bpp->b6_justifikasi_keperluan }}</td></tr>
    <tr class="bi-gap"><td colspan="3"></td></tr>
    <tr><td>B7. Tajuk asal perolehan (seperti di dalam perancangan perolehan)</td><td class="bi-c">:</td><td class="bi-v bi-b7 bi-top">{{ $bpp->b7_tajuk_asal_perolehan }}</td></tr>
    <tr class="bi-gap"><td colspan="3"></td></tr>
    <tr>
        <td colspan="3">
            <table class="bi-nested">
                <colgroup><col style="width:30mm"><col style="width:2mm"><col style="width:34mm"><col style="width:36mm"><col style="width:2mm"><col></colgroup>
                <tr><td>B8. Tarikh Perolehan Diperlukan</td><td class="c">:</td><td class="v bi-center">{{ $requiredDate }}</td><td>B9. Lokasi Perolehan Diperlukan</td><td class="c">:</td><td class="v">{{ $bpp->b9_lokasi_diperlukan }}</td></tr>
                <tr><td></td><td></td><td colspan="4" class="bi-note">sekurang-kurangnya:&nbsp;&nbsp;&nbsp;&nbsp; PT-21 hari &nbsp;&nbsp;&nbsp;&nbsp; SH-90 hari &nbsp;&nbsp;&nbsp;&nbsp; T-120 hari &nbsp;&nbsp;&nbsp;&nbsp; selepas borang lengkap diterima</td></tr>
            </table>
        </td>
    </tr>
</table>

<div class="bpp-section">B(II): MAKLUMAT TAMBAHAN (PEMBAIKAN ATAU PENYELENGGARAAN PERALATAN MAKMAL BERNILAI MELEBIHI RM50,000.00 SAHAJA)</div>
<table class="bpp-bii">
    <colgroup><col style="width:32mm"><col style="width:2mm"><col style="width:37mm"><col style="width:30mm"><col style="width:2mm"><col></colgroup>
    <tr class="bii-gap"><td colspan="6"></td></tr>
    <tr><td>B10. Nilai perolehan terdahulu</td><td>:</td><td class="bii-v">{{ $currency($bpp->b10_nilai_perolehan_terdahulu) }}</td><td>B11. No rujukan perolehan PO/SST</td><td>:</td><td class="bii-v">{{ $bpp->b11_no_rujukan_perolehan_po_sst_terdahulu }}</td></tr>
    <tr class="bii-gap"><td colspan="6"></td></tr>
    <tr><td>B12. Nilai perolehan 2 tahun yang lalu</td><td>:</td><td class="bii-v">{{ $currency($bpp->b12_nilai_perolehan_2_tahun_lalu) }}</td><td>B13. No rujukan perolehan PO/SST</td><td>:</td><td class="bii-v">{{ $bpp->b13_no_rujukan_perolehan_po_sst_2_tahun_lalu }}</td></tr>
    <tr class="bii-gap"><td colspan="6"></td></tr>
    <tr><td>B14. Nilai perolehan alat</td><td>:</td><td class="bii-v">{{ $currency($bpp->b14_nilai_perolehan_alat) }}</td><td>B15. No rujukan perolehan PO/SST</td><td>:</td><td class="bii-v">{{ $bpp->b15_no_rujukan_perolehan_po_sst_alat }}</td></tr>
    <tr class="bii-gap"><td colspan="6"></td></tr>
    <tr><td colspan="5">B16. Kepilkan analisis ROI/ROV bagi peralatan yang perlu diselenggara</td><td><span class="bii-box">{{ $bpp->b16_kepilkan_analisis_roi_rov ? '/' : '' }}</span></td></tr>
    <tr class="bii-gap"><td colspan="6"></td></tr>
    <tr><td colspan="5">B17. Rekod senarai pihak atau individu yang menggunakan alat yang perlu diselenggara</td><td><span class="bii-box">{{ $bpp->b17_rekod_senarai_pihak_pengguna ? '/' : '' }}</span></td></tr>
    <tr class="bii-gap"><td colspan="6"></td></tr>
    <tr><td colspan="5">B18. Salinan laporan kerosakan (bagi perolehan pembaikan) - wajib dikepilkan</td><td><span class="bii-box">{{ $bpp->b18_salinan_laporan_kerosakan ? '/' : '' }}</span></td></tr>
</table>

<div class="dummy-gap"></div>
<div class="bpp-section">C: KAJIAN PASARAN (sila tandakan)</div>
<table class="bpp-c">
    <tr><td>Sila kemukakan maklumat lengkap menggunakan lampiran C1 untuk kaedah kajian pasaran</td><td class="c-check"><span class="bii-box">{{ $hasC1 ? '/' : '' }}</span></td></tr>
    <tr class="bii-gap"><td colspan="2"></td></tr>
    <tr><td>Senarai item/perkhidmatan di dalam jadual  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; C2 - Perbekalan <span class="bii-box">{{ $appendixMark('c2') }}</span> &nbsp; C3 - Perkhidmatan <span class="bii-box">{{ $appendixMark('c3') }}</span> &nbsp; C4 - Kerja <span class="bii-box">{{ $appendixMark('c4') }}</span></td><td></td></tr>
</table>

<div class="dummy-gap"></div>
<div class="bpp-section">D: PEMBEKAL YANG DISYORKAN</div>
<table class="bpp-d">
    <tr><td class="d-left">Maklumat pembekal</td><td class="d-reg-label">No. Pendaftaran Syarikat</td><td class="d-colon">:</td><td class="d-reg-val">{{ $bpp->d_no_pendaftaran_syarikat }}</td></tr>
    <tr>
        <td colspan="4">
            <table class="d-inner">
                <tr>
                    <td class="d-supplier">
                        <div>Nama dan alamat pembekal:</div>
                        <div class="d-address">
                            {{ $bpp->d_nama_pembekal }}<br><br>
                            @foreach ($addressLines as $line)
                                {{ $line }}@if (! $loop->last)<br>@endif
                            @endforeach
                        </div>
                    </td>
                    <td class="d-criteria">
                        <div>Kriteria pemilihan:</div>
                        <div class="d-crit-row"><span class="d-box">{{ $criteriaMark('Tawaran harga terbaik') }}</span><span>Tawaran harga terbaik</span><span class="d-box">{{ $criteriaMark('Keupayaan teknikal dan kewangan') }}</span><span>Keupayaan teknikal dan kewangan</span></div>
                        <div class="d-crit-row"><span class="d-box">{{ $criteriaMark('Pengalaman dan rekod prestasi') }}</span><span>Pengalaman dan Rekod Prestasi</span><span class="d-box">{{ $criteriaMark('Keupayaan operasi dan sumber') }}</span><span>Keupayaan operasi dan sumber</span></div>
                        <div class="d-crit-row"><span class="d-box">{{ $criteriaMark('Tempoh pembekalan/perlaksanaan yang munasabah') }}</span><span>Tempoh pembekalan/perlaksanaan yang munasabah</span></div>
                        <div class="d-crit-row"><span class="d-box">{{ $criteriaMark('Lain-lain') ?: $criteriaMark('Pembekal Tunggal') }}</span><span>Lain-lain (nyatakan) {{ $bpp->d_lain_lain_kriteria }}</span></div>
                        <div class="d-note">(contoh: Pembekal tunggal, integriti dan pematuhan undang-undang)</div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<div class="d-line"></div>
<table class="bpp-ef">
    <tr>
        <td class="ef-col">
            <div class="bpp-section">E: PERAKUAN PEMOHON</div>
            <div class="ef-box">
                <div>Saya mengaku bahawa semua maklumat yang diisi oleh saya adalah benar dan perolehan ini adalah mematuhi Polisi dan Prosedur Perolehan NIBM. Saya juga mengaku bahawa saya tiada kepentingan peribadi dalam perkara ini dan tidak akan mendapat apa-apa manfaat secara langsung atau tidak langsung daripadanya.</div>
                <div class="ef-sign">Tandatangan</div>
                <div class="ef-bottom">Nama<br>Cop Rasmi<br>Tarikh</div>
            </div>
        </td>
        <td class="ef-gap"></td>
        <td class="ef-col">
            <div class="bpp-section">F: PERAKUAN KETUA JABATAN/PROJEK/TIMB KETUA PROJEK</div>
            <div class="ef-box">
                <div>Permohonan pembelian ini disokong menurut keperluan dan perancangan perolehan yang telah dikemukakan dan diperakui semua maklumat di dalam borang ini adalah benar. Saya juga mengesahkan bahawa saya telah meneliti dan mengesahkan spesifikasi teknikal yang dikemukakan bersama permohonan ini</div>
                <div class="ef-bottom">Nama<br>Cop Rasmi<br>Tarikh</div>
            </div>
        </td>
    </tr>
</table>
</div></div>
