<style>
:root{--b:#000;--peach:#fbe4d5;--gray:#d9d9d9}
.pdf-page{width:210mm;height:297mm;margin:0 auto;background:#fff;box-sizing:border-box;overflow:hidden;padding:12mm 23mm 8mm}
.bpp-shell{width:100%;border:2px solid var(--b);box-sizing:border-box;padding:4mm 2mm 1mm}
.bpp-header-table,.bpp-main-table{width:100%;border-collapse:collapse;table-layout:fixed;font-family:Arial,Helvetica,sans-serif;color:#000}
.bpp-header-table td,.bpp-main-table th,.bpp-main-table td{border:1px solid var(--b)}
.bpp-logo-cell{text-align:center;vertical-align:middle;padding:0}
.bpp-logo{width:44mm;max-width:90%;height:auto;display:block;margin:0 auto}
.bpp-title-cell{text-align:center;vertical-align:middle;font-size:11px;font-weight:700;line-height:1;padding:1.5mm 1mm}
.bpp-document-title-cell{text-align:center;vertical-align:middle;line-height:1.15}
.bpp-document-label{font-size:7px;font-weight:400;margin-bottom:.4mm}
.bpp-document-name{font-size:7.6px;font-weight:700}
.bpp-meta-cell{vertical-align:top;padding:1.2mm 1mm;font-size:7px;line-height:1.15}
.bpp-meta-value{font-weight:700}
.bpp-no-row{font-family:Arial,Helvetica,sans-serif;font-size:7px;line-height:1;padding:3.2mm 0 1.1mm;display:flex;align-items:flex-end}
.bpp-no-label{font-weight:700;width:23mm}
.bpp-no-value{display:inline-block;min-width:86mm;padding-bottom:.3mm;border-bottom:1px solid var(--b)}
.bpp-guide{font-family:Arial,Helvetica,sans-serif;font-size:7.4px;line-height:1.22;margin:2mm 0 2.2mm}
.bpp-guide-title{margin-bottom:.8mm}
.bpp-guide-list{margin:0;padding-left:5mm;list-style-type:lower-roman}
.bpp-guide-list li{margin:0;padding-left:1.2mm}
.bpp-main-table{font-size:7px;line-height:1.1}
.bpp-main-table th,.bpp-main-table td{padding:.45mm .8mm;vertical-align:top}
.bpp-main-table th{background:var(--peach);text-align:center;font-weight:700}
.bpp-gap-row td{height:2.3mm;padding:0!important;border:0!important;background:#fff}
.bpp-small-gap-row td{height:1.3mm;padding:0!important;border:0!important;background:#fff}
.bpp-section-row td{background:var(--gray);text-align:center;font-weight:700;padding:.35mm .8mm;border:1px solid var(--b)}
.bpp-subsection-title,.bpp-subsection-fill{background:var(--peach);text-align:center;font-weight:700;padding:.35mm .8mm}
.bpp-bil-cell{background:#fff!important;text-align:center;vertical-align:middle!important;font-weight:400}
.bpp-check-box{text-align:center;vertical-align:middle!important;font-size:12px;font-weight:400;line-height:1}
.bpp-check-cell{border:0!important;text-align:center!important;vertical-align:middle!important;font-size:12px;font-weight:400;line-height:1}
.bpp-nested-check{text-align:center!important;vertical-align:middle!important;font-size:12px;font-weight:400;line-height:1;padding-top:0!important;padding-bottom:0!important}
.bpp-center{text-align:center}
.bpp-indent{padding-left:8mm!important}
.bpp-col-bil{width:7mm}.bpp-col-mark{width:24mm}.bpp-col-prc{width:20mm}
.bpp-gray-row td{background:#eeeeee}
.bpp-nested-cell{padding:0!important;border:1px solid #000!important;vertical-align:stretch!important}
.bpp-inner-table,.bpp-prc-inner-table{width:100%;height:100%;border-collapse:collapse;table-layout:fixed;font-family:Arial,Helvetica,sans-serif;font-size:7px;line-height:1.1}
.bpp-inner-table td,.bpp-prc-inner-table td{border:1px solid #000;height:4.2mm;padding:.35mm .8mm;vertical-align:middle}
.bpp-inner-table tr:first-child td,.bpp-prc-inner-table tr:first-child td{border-top:0}
.bpp-inner-table tr:last-child td,.bpp-prc-inner-table tr:last-child td{border-bottom:0}
.bpp-inner-table td:first-child{border-left:0;width:28%}
.bpp-inner-table td:nth-child(2){width:31%}
.bpp-inner-table td:nth-child(3){width:24%}
.bpp-inner-table td:last-child{border-right:0;width:17%}
.bpp-prc-inner-table td{border-left:0;border-right:0}
.bpp-cert-table{width:100%;height:100%;border-collapse:collapse;table-layout:fixed;font-family:Arial,Helvetica,sans-serif;font-size:7px;line-height:1.1}
.bpp-cert-table td{border:1px solid #000;padding:.35mm .8mm;vertical-align:middle}
.bpp-cert-table tr:first-child td{border-top:0}
.bpp-cert-table tr:last-child td{border-bottom:0}
.bpp-cert-table td:first-child{border-left:0;width:18%}
.bpp-cert-table td:nth-child(2){width:24%}
.bpp-cert-table td:nth-child(3){width:15%}
.bpp-cert-table td:nth-child(4){width:10%}
.bpp-cert-table td:nth-child(5){width:8%}
.bpp-cert-table td:nth-child(6){border-right:0;width:25%;white-space:nowrap}
</style>

<div class="pdf-page">
<div class="bpp-shell">
<table class="bpp-header-table">
<tr>
<td rowspan="3" class="bpp-logo-cell"><img src="{{ $pageOneLogo }}" class="bpp-logo"></td>
<td colspan="4" class="bpp-title-cell">BORANG</td>
</tr>
<tr>
<td colspan="4" class="bpp-document-title-cell">
<div class="bpp-document-label">Tajuk Dokumen:</div>
<div class="bpp-document-name">Senarai Semak Borang Permohonan Perolehan</div>
</td>
</tr>
<tr>
<td class="bpp-meta-cell"><div>Ruj. Dokumen:</div><div class="bpp-meta-value">NIBM/F/PRC/02/01</div></td>
<td class="bpp-meta-cell"><div>No. Semakan:</div><div class="bpp-meta-value">01</div></td>
<td class="bpp-meta-cell"><div>Tarikh Kuatkuasa:</div><div class="bpp-meta-value">01 DISEMBER 2025</div></td>
<td class="bpp-meta-cell"><div>Muka surat:</div><div class="bpp-meta-value">1/1</div></td>
</tr>
</table>
<div class="bpp-no-row"><strong class="bpp-no-label">NO BPP</strong><span class="bpp-no-value">BPP/NIBM-ABI/04/2026-326</span></div>
<div class="bpp-guide"><div class="bpp-guide-title">Panduan:</div><ol class="bpp-guide-list"><li>Sila rujuk Polisi Perolehan dan SOP berkaitan sebagai panduan mengisi borang ini</li><li>Sila isikan Bahagian A sahaja jika tidak melibatkan Pembekal/Pengedar Tunggal dan Pembuat/Pengilang</li><li>Sila isikan Bahagian A dan B sekiranya melibatkan Pembekal Tunggal dan Pembuat/Pengilang</li><li>Tandatangan sokongan adalah diperlukan untuk permohonan perolehan bagi Pembekal Tunggal dan Pembuat/Pengilang di Bahagian B</li></ol></div>
<table class="bpp-main-table">
<thead><tr><th class="bpp-col-bil">BIL.</th><th>PERKARA</th><th class="bpp-col-mark">DITANDAKAN<br>PEMOHON</th><th class="bpp-col-prc">DISEMAK PRC</th></tr></thead>
<tbody>
<tr class="bpp-gap-row"><td colspan="4"></td></tr>
<tr class="bpp-section-row"><td colspan="4">BAHAGIAN A</td></tr>
<tr class="bpp-gap-row"><td colspan="4"></td></tr>
<tr><td rowspan="7" class="bpp-bil-cell">1</td><td class="bpp-subsection-title">Borang permohonan yang lengkap ditandatangan</td><td class="bpp-subsection-fill"></td><td class="bpp-subsection-fill"></td></tr>
<tr><td>1.1 Pemohon</td><td class="bpp-check-cell">/</td><td></td></tr>
<tr><td>1.2 Sokongan Ketua Bahagian/Ketua Jabatan/Ketua Projek/Timbalan Ketua Projek</td><td></td><td></td></tr>
<tr><td>1.3 Semakan PMD</td><td></td><td></td></tr>
<tr><td>1.4 Semakan Bahagian Kewangan</td><td></td><td></td></tr>
<tr><td>1.5 Semakan Bahagian Akaun</td><td></td><td></td></tr>
<tr><td>1.6 Kelulusan Pre-Sanction Ketua Pegawai Kewangan</td><td></td><td></td></tr>
<tr class="bpp-gap-row"><td colspan="4"></td></tr>
<tr><td rowspan="24" class="bpp-bil-cell">2</td><td class="bpp-subsection-title">Kategori Perolehan</td><td class="bpp-subsection-fill"></td><td class="bpp-subsection-fill"></td></tr>
<tr><td><strong>2.1&nbsp; Pembelian Terus (tidak melebihi RM 5,000.00)</strong></td><td></td><td></td></tr>
<tr><td><strong>2.2&nbsp; Pembelian Terus (melebihi RM 5,000.00 hingga tidak melebihi RM 50,000.00)</strong></td><td class="bpp-check-box">/</td><td></td></tr>
<tr class="bpp-small-gap-row"><td colspan="3"></td></tr>
<tr><td colspan="2" class="bpp-nested-cell"><table class="bpp-inner-table"><tr class="bpp-gray-row"><td>Sebutharga (quotation)</td><td class="bpp-center">&lt; 2</td><td class="bpp-center">≥ 3</td><td class="bpp-center">Justifikasi tanpa 3 sebut</td></tr><tr><td>Tanda (√) atau (X)</td><td></td><td class="bpp-nested-check">/</td><td></td></tr><tr><td colspan="3">Tandatangan PBM tanpa 3 sebut harga</td><td></td></tr></table></td><td class="bpp-nested-cell"><table class="bpp-prc-inner-table"><tr><td></td></tr><tr><td></td></tr><tr><td></td></tr></table></td></tr>
<tr class="bpp-small-gap-row"><td colspan="3"></td></tr>
<tr><td><strong>2.3&nbsp; Kajian pasaran</strong></td><td></td><td></td></tr>
<tr class="bpp-small-gap-row"><td colspan="3"></td></tr>
<tr><td><strong>2.4&nbsp; Tender/Sebutharga (melebihi RM50,000.00)</strong></td><td></td><td></td></tr>
<tr><td class="bpp-indent">a.&nbsp; Senarai kehadiran JK Spesifikasi</td><td></td><td></td></tr>
<tr><td class="bpp-indent">b.&nbsp; Minit Mesyuarat &amp; Laporan JK Spesifikasi</td><td></td><td></td></tr>
<tr><td class="bpp-indent">c.&nbsp; Jadual 2 – Spesifikasi Teknikal</td><td></td><td></td></tr>
<tr><td class="bpp-indent">d.&nbsp; Jadual 1A – Jadual Kadar Harga (Kuantiti)</td><td></td><td></td></tr>
<tr><td class="bpp-indent">e.&nbsp; Jadual 1B – Jadual Kadar Harga (Pukal)</td><td></td><td></td></tr>
<tr><td class="bpp-indent">f.&nbsp; Jadual 3 – Jadual Kaedah Perolehan</td><td></td><td></td></tr>
<tr><td class="bpp-indent">g.&nbsp; Jadual 4 – Jadual Perlaksanaan Perkhidmatan/Pembekalan/Kerja</td><td></td><td></td></tr>
<tr><td class="bpp-indent">h.&nbsp; Jadual 5 – Jadual Pembayaran</td><td></td><td></td></tr>
<tr><td class="bpp-indent">i.&nbsp; Integrity Pact selesai menjalan tugas Jawatankuasa Perolehan</td><td></td><td></td></tr>
<tr class="bpp-small-gap-row"><td colspan="3"></td></tr>
<tr><td><strong>2.3&nbsp; Pembekal/Pengedar Tunggal/Pembuat/Pengeluar (Isikan bahagian B)</strong></td><td></td><td></td></tr>
<tr class="bpp-small-gap-row"><td colspan="3"></td></tr>
<tr><td><strong>2.4&nbsp; Rundingan Terus</strong> – kepilkan borang senarai semak rundingan terus <strong>(Isikan bahagian B)</strong></td><td></td><td></td></tr>
</tbody>
<tr class="bpp-gap-row"><td colspan="4"></td></tr>
<tr><td rowspan="6" class="bpp-bil-cell">3</td><td class="bpp-subsection-title" style="background:#fce4e4;">Maklumat Tambahan</td><td class="bpp-subsection-fill" style="background:#fce4e4;"></td><td class="bpp-subsection-fill" style="background:#fce4e4;"></td></tr>
<tr><td><strong>3.1&nbsp; Maklumat tambahan</strong> (bagi perolehan penyelenggaraan melebihi RM 50,000)</td><td></td><td></td></tr>
<tr><td style="padding-left:10mm;">a.&nbsp;&nbsp;&nbsp;&nbsp; Nilai perolehan 2 tahun terakhir <strong>(WAJIB)</strong></td><td></td><td></td></tr>
<tr><td style="padding-left:10mm;">b.&nbsp;&nbsp;&nbsp;&nbsp; Rekod/Senarai Pihak atau individu yang menggunakan alat tersebut / rekod penggunaan 2 tahun<br><span style="padding-left:6mm;">terakhir (buku log/invois/buku rekod)</span></td><td></td><td></td></tr>
<tr><td style="padding-left:10mm;">c.&nbsp;&nbsp;&nbsp;&nbsp; Analisis ROI/ROV penggunaan alat</td><td></td><td></td></tr>
<tr class="bpp-small-gap-row"><td colspan="3"></td></tr>
<tr><td></td><td><strong>3.2&nbsp; Perolehan Pembaikan (peralatan atau bangunan)</strong> - Kepilkan laporan/aduan kerosakan <strong>(WAJIB)</strong></td><td></td><td></td></tr>
<tr class="bpp-gap-row"><td colspan="4"></td></tr>
<tr><td rowspan="8" class="bpp-bil-cell">4</td><td class="bpp-subsection-title">Dokumen sokongan syarikat &amp; sijil pendaftaran yang masih berkuatkuasa</td><td class="bpp-subsection-fill"></td><td class="bpp-subsection-fill"></td></tr>
<tr><td colspan="2" class="bpp-nested-cell"><table class="bpp-cert-table"><tr><td class="bpp-center">Sijil</td><td class="bpp-center">SSM / ROB / ROC</td><td class="bpp-center">MOF</td><td class="bpp-center">PKK</td><td class="bpp-center">CIDB</td><td class="bpp-center">Ordinan Perlesenan Perdagangan</td></tr><tr><td>Tandakan<br>(√)</td><td class="bpp-nested-check">/</td><td class="bpp-nested-check">/</td><td></td><td></td><td></td></tr></table></td><td class="bpp-nested-cell"><table class="bpp-prc-inner-table"><tr><td></td></tr><tr><td></td></tr></table></td></tr>
<tr><td><strong>5</strong>&nbsp;&nbsp; Penyata kewangan terkini syarikat/maklumat perbankan</td><td class="bpp-check-box">/</td><td></td></tr>
<tr><td><strong>6</strong>&nbsp;&nbsp; Keratan/Cabutan/Dokumen kelulusan bajet<br>(Cabutan BOT / JKE / Management Committee Meeting)</td><td></td><td></td></tr>
<tr><td><strong>7</strong>&nbsp;&nbsp; Borang Aduan Kerosakan (Membaikpulih, Kalibrasi &amp; Menyelenggara Peralatan)</td><td></td><td></td></tr>
<tr><td><strong>8</strong>&nbsp;&nbsp; Minit Mesyuarat JTICT</td><td></td><td></td></tr>
<tr><td><strong>9</strong>&nbsp;&nbsp; Integrity Pact</td><td></td><td></td></tr>
<tr><td><strong>10</strong>&nbsp; Lain-lain dokumen jika berkaitan</td><td></td><td></td></tr>
</table>
</div>
</div>
