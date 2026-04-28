<style>
*{box-sizing:border-box}.pdf-page{width:210mm;height:297mm;margin:0 auto;background:#fff;overflow:hidden;padding:8mm 15mm 5mm}.bpp-shell{width:100%;height:100%;border:2px solid #000;padding:1.25mm 1.1mm .95mm;font-family:Arial,Helvetica,sans-serif;color:#000}.top{width:100%;border-collapse:collapse;table-layout:fixed;font-size:7.9px;line-height:1.04}.top td{border:1px solid #000;padding:.42mm .62mm;vertical-align:top}.logo-cell{width:41mm;text-align:center;vertical-align:middle!important;padding:0!important}.logo{width:38mm;display:block;margin:0 auto}.title{text-align:center;font-size:11.3px;font-weight:700;vertical-align:middle!important;height:6.5mm}.doc{text-align:center;font-size:7px;line-height:1.04;height:6.2mm}.doc b{display:block;font-size:9.3px}.meta{height:6.05mm;font-size:7.85px}.sec{margin-top:.58mm;background:#7d7d7d;color:#fff;border:1px solid #000;font-size:8.75px;font-weight:700;line-height:1;padding:.48mm .7mm}.g,.h,.hf,.ij,.jk{width:100%;border-collapse:collapse;table-layout:fixed;font-size:7.75px;line-height:1.05}.g td,.h td,.hf td,.ij td,.jk td{border:0;padding:.22mm .3mm;vertical-align:top}.bpp2-field{border:1px solid #000!important;height:4.2mm!important;min-height:4.2mm!important;max-height:4.2mm!important;padding:.28mm .48mm!important;background:#fff!important;overflow:hidden}.bpp2-box{display:inline-block;width:4.15mm!important;height:4.15mm!important;border:1px solid #000!important;background:#fff!important;text-align:center;line-height:3.55mm;margin:0 1.45mm;vertical-align:middle}.times{font-family:"Times New Roman",serif;font-size:7.05px}.gap td{height:.78mm!important;padding:0!important;font-size:0;line-height:0}.gap2 td{height:1.25mm!important;padding:0!important;font-size:0;line-height:0}.inner{width:100%;height:100%;border-collapse:collapse;table-layout:fixed}.inner td{border:0;padding:0;vertical-align:top}.border-box,.bpp2-note,.perakuan-box,.i-box,.j-box,.k-box{border:1px solid #000!important;background:#fff!important;padding:.38mm!important}.ulasan{height:20.5mm}.sign{height:20.5mm}.note{height:18.5mm}.h-note-left{height:17.5mm!important}.nest{width:100%;border-collapse:collapse;table-layout:fixed}.nest td{border:0;padding:0;vertical-align:top}.perakuan-box{height:17.5mm;position:relative}.perakuan-bottom{position:absolute;left:.4mm;bottom:.38mm}.i-box{height:39mm}.i-sign{height:39mm}.j-text{height:23mm}.j-ulasan,.k-ulasan{height:17.5mm}.j-sign,.k-sign{height:17.5mm}.tight{line-height:1.01}
.h-row{width:100%;table-layout:fixed}
.h-row td{padding:.25mm .35mm;vertical-align:top}
.h-full-field{width:100%;height:4.8mm!important;min-height:4.8mm!important;max-height:4.8mm!important;border:1px solid #000!important;background:#fff!important;padding:.45mm .65mm!important;overflow:hidden;white-space:nowrap;margin-left:-2mm;}
.h-colon{
width:2mm;
text-align:center;
padding-right:0 !important;   /* remove default gap */
padding-left:.2mm;            /* keep slight balance */
}
.ij{width:100%;height:50mm;border-collapse:collapse;table-layout:fixed;font-size:8.55px;line-height:1.12}
.ij td{border:0;padding:.35mm .45mm;vertical-align:top}
.i-left,.i-right{width:100%;border-collapse:collapse;table-layout:fixed}
.i-left{border:1px solid #000!important}
.i-left .i-head{border:0!important;border-bottom:1px solid #000!important;background:#d9d9d9!important}
.i-right .i-head{border:1px solid #000!important;background:#d9d9d9!important}
.gray-head{background:#d9d9d9!important}
.i-check td{padding:.18mm .35mm}
.i-check-cell{width:6mm;text-align:center}
.j-box{width:100%;border-collapse:collapse;table-layout:fixed;font-size:8.3px;line-height:1.08}
.j-box td{border:1px solid #000;padding:.8mm 1mm 1mm 5mm;vertical-align:top}
.j-intro{margin-left:0}
.j-list{margin-left:5mm}
.j-list div{display:flex}
.j-list span{width:6mm;flex:0 0 6mm}
.j-list p{margin:0}
.j-note{margin-top:.3mm}
</style>

<div class="pdf-page"><div class="bpp-shell">

<table class="top">
<tr><td rowspan="3" class="logo-cell"><img src="{{ $pageFourLogo ?? asset('images/bpp-preview/nibm-logo.png') }}" class="logo"></td><td colspan="4" class="title">BORANG</td></tr>
<tr><td colspan="4" class="doc">Tajuk Dokumen:<b>{{ $bpp->tajuk_dokumen ?? 'Borang Permohonan Perolehan' }}</b></td></tr>
<tr><td class="meta">Ruj. Dokumen:<br>{{ $bpp->ruj_dokumen ?? 'NIBM/F/PRC/02/01' }}</td><td class="meta">No. Semakan:<br>{{ $bpp->no_semakan ?? '01' }}</td><td class="meta">Tarikh Kuat Kuasa:<br>{{ $bpp->tarikh_kuat_kuasa ?? '01 DISEMBER 2025' }}</td><td class="meta">{{ $bpp->muka_surat ?? 'Muka surat 2 dari 2' }}</td></tr>
</table>

<div class="sec">G: SEMAKAN JABATAN PENGURUSAN PROJEK (JIKA PERLU)</div>

<table class="g">
<colgroup><col style="width:31mm"><col style="width:3mm"><col style="width:26mm"><col style="width:26mm"><col style="width:26mm"><col style="width:26mm"><col style="width:26mm"><col></colgroup>
<tr class="gap"><td colspan="8"></td></tr>
<tr><td colspan="8" style="padding:0"><table class="nest"><tr><td style="width:.35mm"></td><td style="width:28.5mm;padding:.35mm .2mm">Tarikh BPP diterima</td><td style="width:2.4mm;padding:.35mm .1mm;text-align:center">:</td><td class="bpp2-field">{{ $bpp->g_tarikh_bpp_diterima ?? '' }}</td><td style="width:.45mm"></td></tr></table></td></tr>
<tr class="gap"><td colspan="8"></td></tr>
<tr><td colspan="8" style="padding:0"><table class="nest"><tr><td style="width:.4mm"></td><td><table class="inner"><tr><td style="width:132mm">G1. Permohonan ini adalah diperakukan telah disemak dan disahkan mengikut skop/item yang diluluskan</td><td style="width:9mm;text-align:center"><span class="bpp2-box">{{ ($bpp->g1_diperakukan ?? null)==='ya' ? '/' : '' }}</span></td><td style="width:7mm">Ya</td><td style="width:9mm;text-align:center"><span class="bpp2-box">{{ ($bpp->g1_diperakukan ?? null)==='tidak' ? '/' : '' }}</span></td><td style="width:12mm">Tidak</td></tr></table></td><td style="width:.45mm"></td></tr></table></td></tr>
<tr class="gap"><td colspan="8"></td></tr>
<tr><td colspan="8" style="padding:0"><table class="nest"><tr><td style="width:.4mm"></td><td style="width:30.5mm;padding:.35mm .2mm">G2. Nama geran/projek</td><td style="width:2.4mm;padding:.35mm .1mm;text-align:center">:</td><td class="bpp2-field times">{{ $bpp->g2_nama_geran_projek ?? '' }}</td><td style="width:.45mm"></td></tr></table></td></tr>
<tr class="gap"><td colspan="8"></td></tr>
<tr><td colspan="8" style="padding:0"><table class="nest"><tr><td style="width:.4mm"></td><td><table class="inner"><tr><td class="border-box ulasan" style="width:50%;padding-left:.2mm!important">G3. Ulasan :</td><td style="width:1.4mm"></td><td class="border-box sign"><table class="inner"><tr><td>Tandatangan</td></tr><tr><td style="height:7.2mm"></td></tr><tr><td style="padding-left:.35mm!important">Nama&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td></tr><tr><td style="padding-left:.35mm!important">Cop Rasmi&nbsp;&nbsp;:</td></tr><tr><td style="padding-left:.35mm!important">Tarikh&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</td></tr></table></td></tr></table></td><td style="width:.45mm"></td></tr></table></td></tr>
</table>
<div class="sec">H: SEMAKAN JABATAN KEWANGAN DAN AKAUN (Kelulusan peruntukan dan baki tunai)</div>

<!-- H BLOCK 1: Tarikh + H1-H6 -->
<div class="h-block h-block-1">
<table class="h">
<colgroup>
<col style="width:50%">
<col style="width:50%">
</colgroup>

<tr>
<td style="padding-right:1.5mm;vertical-align:top">

<!-- LEFT TABLE: 7 ROWS -->
<table class="inner h-tight">

<tr>
<td>
<table class="inner h-row"><tr>
<td style="width:37mm">Tarikh BPP diterima dari pemohon/PMD</td>
<td class="h-colon">:</td>
<td class="h-full-field">{{ $bpp->h_tarikh_bpp_diterima_pemohon ?? '' }}</td>
</tr></table>
</td>
</tr>

<tr class="gap"><td></td></tr>

<tr>
<td>
<table class="inner h-row"><tr>
<td style="width:25mm">H1. Mengurus/Hasil</td>
<td style="width:25mm">Kod perbelanjaan</td>
<td class="h-colon">:</td>
<td class="h-full-field">{{ $bpp->h1_kod_perbelanjaan ?? '' }}</td>
</tr></table>
</td>
</tr>

<tr class="gap"><td></td></tr>

<tr>
<td>
<table class="inner h-row"><tr>
<td style="width:29mm">H2. Pembangunan/Projek</td>
<td class="h-colon">:</td>
<td class="h-full-field">{{ $bpp->h2_pembangunan_projek ?? '' }}</td>
</tr></table>
</td>
</tr>

<tr class="gap"><td></td></tr>

<tr>
<td>
<table class="inner h-row"><tr>
<td style="width:41mm">H3. Jumlah Kod Belanja Diluluskan</td>
<td class="h-colon">:</td>
<td class="h-full-field">RM {{ $bpp->h3_jumlah_kod_belanja_diluluskan ?? '' }}</td>
</tr></table>
</td>
</tr>

<tr class="gap"><td></td></tr>

<tr>
<td>
<table class="inner h-row"><tr>
<td style="width:34mm">H4. Baki Kod Belanja Sebelum</td>
<td class="h-colon">:</td>
<td class="h-full-field">RM {{ $bpp->h4_baki_kod_belanja_sebelum ?? '' }}</td>
</tr></table>
</td>
</tr>

<tr class="gap"><td></td></tr>

<tr>
<td>
<table class="inner h-row"><tr>
<td style="width:42mm">H5. Baki Kod Belanja Selepas Perolehan Ini</td>
<td class="h-colon">:</td>
<td class="h-full-field">RM {{ $bpp->h5_baki_kod_belanja_selepas ?? '' }}</td>
</tr></table>
</td>
</tr>

<tr class="gap"><td></td></tr>

<tr>
<td>
<table class="inner h-row"><tr>
<td style="width:43mm">H6. Peruntukan adalah seperti yang diluluskan</td>
<td>
<span class="bpp2-box">{{ ($bpp->h6_peruntukan_diluluskan ?? null)==='ya' ? '/' : '' }}</span> Ya
<span class="bpp2-box">{{ ($bpp->h6_peruntukan_diluluskan ?? null)==='tidak' ? '/' : '' }}</span> Tidak
</td>
</tr></table>
</td>
</tr>

</table>

</td>

<td style="padding-left:1.5mm;vertical-align:top">

<!-- RIGHT TABLE: 4 ROWS -->
 <table class="inner h-tight">

<tr>
<td>
<table class="inner h-row">
<tr>
<td style="width:57mm">Tarikh BPP diterima dari Seksyen Kewangan</td>
<td class="h-colon">:</td>
<td class="h-full-field">{{ $bpp->h_tarikh_bpp_diterima_kewangan ?? '' }}</td>
</tr>
</table>
</td>
</tr>

<tr class="gap"><td></td></tr>

<tr>
<td>
<table class="inner h-row">
<tr>
<td style="width:8mm">OS</td>
<td class="h-colon">:</td>
<td class="h-full-field">{{ $bpp->h1_os ?? '' }}</td>
</tr>
</table>
</td>
</tr>

<tr class="gap"><td></td></tr>

<tr>
<td>
<table class="inner h-row">
<tr>
<td style="width:18mm">Kod Projek</td>
<td class="h-colon">:</td>
<td class="h-full-field">{{ $bpp->h2_kod_projek ?? '' }}</td>
</tr>
</table>
</td>
</tr>

<tr class="gap"><td></td></tr>

<tr>
<td class="bpp2-note" style="height:22mm">Catatan Seksyen Akaun:</td>
</tr>

</table>
</td>
</tr>
</table>
</div>

<!-- H BLOCK 2: H7-H11 -->
<div class="h-block h-block-2">
<table class="h">
<colgroup><col style="width:50%"><col style="width:50%"></colgroup>

<tr>
<td style="padding-right:1.5mm;vertical-align:top">
<table class="inner h-tight">
<tr>
<td class="h-label">H7. Butiran Bank Pembayar</td>
<td class="h-colon">:</td>
<td class="bpp2-field">{{ $bpp->h7_butiran_bank_pembayar ?? '' }}</td>
</tr>

<tr class="gap"><td colspan="3"></td></tr>

<tr>
<td colspan="3" class="bpp2-note" style="height:14mm">Catatan Seksyen Kewangan:</td>
</tr>

<tr class="gap"><td colspan="3"></td></tr>

<tr>
<td colspan="3">H8. Perakuan Seksyen Kewangan</td>
</tr>

<tr>
<td colspan="3" class="bpp2-note" style="height:16mm">
Tandatangan<br><br><br>
Nama<br>
Cop Rasmi<br>
Tarikh
</td>
</tr>
</table>
</td>

<td style="padding-left:1.5mm;vertical-align:top">

<!-- ✅ UPDATED RIGHT SIDE -->
<table class="inner h-tight">

<tr>
<td>
<table class="inner h-row">
<tr>
<td style="width:28mm">H9. Baki Tunai</td>
<td class="h-colon">:</td>
<td class="h-full-field">RM {{ $bpp->h9_baki_tunai ?? '' }}</td>
</tr>
</table>
</td>
</tr>

<tr class="gap"><td></td></tr>

<tr>
<td>
<table class="inner h-row">
<tr>
<td style="width:30mm">H10. Tanggungan</td>
<td class="h-colon">:</td>
<td class="h-full-field">RM {{ $bpp->h10_tanggungan ?? '' }}</td>
</tr>
</table>
</td>
</tr>

<tr>
<td style="height:9mm"></td>
</tr>

<tr>
<td>H11. Perakuan Seksyen Akaun</td>
</tr>

<tr>
<td class="bpp2-note" style="height:16mm">
Tandatangan<br><br><br>
Nama<br>
Cop Rasmi<br>
Tarikh
</td>
</tr>

</table>

</td>
</tr>
</table>
</div>
<!-- I SECTION -->
<div class="sec">I: SEMAKAN JABATAN PEROLEHAN</div>

<table class="ij">
<tr class="gap"><td colspan="2"></td></tr>

<tr>
<td colspan="2" style="padding:0">
<table class="inner h-row">
<tr>
<td style="width:26mm">Tarikh BPP lengkap</td>
<td class="h-colon">:</td>
<td class="h-full-field">{{ $bpp->i_tarikh_bpp_lengkap ?? '' }}</td>
</tr>
</table>
</td>
</tr>

<tr class="gap"><td colspan="2"></td></tr>

<tr>
<td style="width:46%;padding:0 .8mm 0 0;vertical-align:top">
<table class="inner i-left">
<tr><td class="i-head gray-head">I1. Keperluan Sijil-sijil dan dokumen sokongan</td></tr>
<tr>
<td>
<table class="inner i-check">
<tr><td>Keperluan/Lampiran/Sijil Pendaftaran/Dokumen lain yang diperlukan</td><td class="i-check-cell"><span class="bpp2-box"></span></td></tr>
<tr><td>Borang permohonan yang lengkap bertandatangan</td><td class="i-check-cell"><span class="bpp2-box"></span></td></tr>
<tr><td>Sebutharga yang lengkap</td><td class="i-check-cell"><span class="bpp2-box"></span></td></tr>
<tr><td>Dokumen pembekal tunggal</td><td class="i-check-cell"><span class="bpp2-box"></span></td></tr>
<tr><td>Sijil pendaftaran entiti</td><td class="i-check-cell"><span class="bpp2-box"></span></td></tr>
<tr><td>Lampiran-lampiran berkaitan</td><td class="i-check-cell"><span class="bpp2-box"></span></td></tr>
</table>
</td>
</tr>
<tr>
<td>
<table class="inner h-row">
<tr>
<td style="width:16mm">Kod bidang</td>
<td style="width:6mm"><span class="bpp2-box" style="margin:0"></span></td>
<td style="width:25mm">Butiran kod bidang</td>
<td class="h-full-field">{{ $bpp->i1_butiran_kod_bidang ?? '' }}</td>
</tr>
</table>
</td>
</tr>
<tr>
<td>
<table class="inner i-check">
<tr><td>Lain-lain dokumen</td><td class="i-check-cell"><span class="bpp2-box"></span></td></tr>
</table>
</td>
</tr>
</table>
</td>

<td style="width:54%;padding:0 0 0 .8mm;vertical-align:top">
<table class="inner i-right">
<tr><td class="i-head gray-head">I2. Pengesahan Bahagian Perolehan</td></tr>
<tr>
<td class="bpp2-note" style="height:44mm">
<div>Disahkan bahawa permohonan ini mematuhi Polisi dan Prosedur Perolehan NIBM.</div>
<div>Maklumat pada item H adalah lengkap mengikut keperluan perolehan yang dimohon.</div>
<div style="margin-top:2.6mm">Tandatangan</div>
<div style="height:10.5mm"></div>
<div>Nama&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</div>
<div>Cop rasmi&nbsp;&nbsp;:</div>
<div>Tarikh&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</div>
</td>
</tr>
</table>
</td>
</tr>
</table>

<div class="sec">J: SOKONGAN DAN PERAKUAN PIHAK BERKUASA MELULUS</div>

<table class="j-box">
<tr>
<td>
<div class="j-intro">Bahagian ini perlu ditandatangan oleh pihak yang diberi kuasa/PBM untuk menyokong dan meluluskan untuk perolehan diteruskan bagi:</div>

<div class="j-list">
<div><span>1)</span><p>Perolehan bagi Pembekal/Pengedaran Tunggal/Pembuat/Pengilang tanpa dokumen sokongan yang jelas menyatakannya</p></div>
<div><span>2)</span><p>Kajian pasaran tidak sempurna / jumlah sebutharga tidak mencukupi</p></div>
<div><span>3)</span><p>Perolehan bagi Sebut Harga dan Tender</p></div>
<div><span>4)</span><p>Perkara-perkara lain yang memerlukan sokongan PBM</p></div>
</div>

<div class="j-note">Pihak yang diberi kuasa/PBM perlu berpuas hati dan memahami permohonan yang dikemukakan sebelum perolehan ini disokong dan diluluskan untuk diteruskan.</div>
</td>
</tr>
</table>
<table class="jk">
<tr class="gap"><td></td></tr>
<tr><td style="padding:.1mm .2mm"><span class="bpp2-box" style="margin-left:0"></span>Diluluskan&nbsp;&nbsp;&nbsp;&nbsp;<span class="bpp2-box"></span>Tidak diluluskan</td></tr>
<tr class="gap"><td></td></tr>
<tr>
<td style="padding:0">
<table class="inner"><tr>
<td style="width:46%;padding-right:.8mm"><div class="j-box j-ulasan">Ulasan :</div></td>
<td style="width:54%;padding-left:.8mm"><div class="j-box j-sign"><div>Tandatangan</div><div style="height:6.8mm"></div><div>Nama&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</div><div>Cop Rasmi&nbsp;&nbsp;:</div><div>Tarikh&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</div></div></td>
</tr></table>
</td>
</tr>
</table>

<div class="sec">K: PERAKUAN PRE-SANCTION CFO</div>
<table class="jk">
<tr>
<td style="padding:0">
<table class="inner"><tr>
<td style="width:46%;padding-right:.8mm"><div class="k-box k-ulasan">Ulasan :</div></td>
<td style="width:54%;padding-left:.8mm"><div class="k-box k-sign"><div>Tandatangan</div><div style="height:6.8mm"></div><div>Nama&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</div><div>Cop Rasmi&nbsp;&nbsp;:</div><div>Tarikh&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</div></div></td>
</tr></table>
</td>
</tr>
</table>
</div></div>
