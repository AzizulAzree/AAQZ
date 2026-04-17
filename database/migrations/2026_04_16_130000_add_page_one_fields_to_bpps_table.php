<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bpps', function (Blueprint $table): void {
            $table->string('no_rujukan_perolehan')->nullable()->after('status');
            $table->string('tajuk_dokumen')->nullable()->after('no_rujukan_perolehan');
            $table->string('ruj_dokumen')->nullable()->after('tajuk_dokumen');
            $table->string('no_semakan')->nullable()->after('ruj_dokumen');
            $table->string('tarikh_kuat_kuasa')->nullable()->after('no_semakan');
            $table->string('muka_surat')->nullable()->after('tarikh_kuat_kuasa');

            $table->string('a1_nama_pemohon')->nullable()->after('muka_surat');
            $table->string('a2_jawatan_gred')->nullable()->after('a1_nama_pemohon');
            $table->string('a3_jabatan_institusi')->nullable()->after('a2_jawatan_gred');
            $table->string('a4_no_tel_email')->nullable()->after('a3_jabatan_institusi');

            $table->string('kaedah_perolehan')->nullable()->after('a4_no_tel_email');

            $table->string('b1_tajuk_perolehan')->nullable()->after('kaedah_perolehan');
            $table->string('b2_kategori_perolehan')->nullable()->after('b1_tajuk_perolehan');
            $table->string('b3_nilai_tawaran_perolehan')->nullable()->after('b2_kategori_perolehan');
            $table->string('b4_harga_indikatif')->nullable()->after('b3_nilai_tawaran_perolehan');
            $table->string('b5_peruntukan_diluluskan')->nullable()->after('b4_harga_indikatif');
            $table->text('b6_justifikasi_keperluan')->nullable()->after('b5_peruntukan_diluluskan');
            $table->string('b7_tajuk_asal_perolehan')->nullable()->after('b6_justifikasi_keperluan');
            $table->string('b8_tarikh_diperlukan')->nullable()->after('b7_tajuk_asal_perolehan');
            $table->string('b9_lokasi_diperlukan')->nullable()->after('b8_tarikh_diperlukan');

            $table->string('d_nama_pembekal')->nullable()->after('b9_lokasi_diperlukan');
            $table->string('d_alamat_pembekal')->nullable()->after('d_nama_pembekal');
            $table->string('d_no_pendaftaran_syarikat')->nullable()->after('d_alamat_pembekal');
            $table->text('d_kriteria_pemilihan')->nullable()->after('d_no_pendaftaran_syarikat');
            $table->text('d_lain_lain_kriteria')->nullable()->after('d_kriteria_pemilihan');
        });
    }

    public function down(): void
    {
        Schema::table('bpps', function (Blueprint $table): void {
            $table->dropColumn([
                'no_rujukan_perolehan',
                'tajuk_dokumen',
                'ruj_dokumen',
                'no_semakan',
                'tarikh_kuat_kuasa',
                'muka_surat',
                'a1_nama_pemohon',
                'a2_jawatan_gred',
                'a3_jabatan_institusi',
                'a4_no_tel_email',
                'kaedah_perolehan',
                'b1_tajuk_perolehan',
                'b2_kategori_perolehan',
                'b3_nilai_tawaran_perolehan',
                'b4_harga_indikatif',
                'b5_peruntukan_diluluskan',
                'b6_justifikasi_keperluan',
                'b7_tajuk_asal_perolehan',
                'b8_tarikh_diperlukan',
                'b9_lokasi_diperlukan',
                'd_nama_pembekal',
                'd_alamat_pembekal',
                'd_no_pendaftaran_syarikat',
                'd_kriteria_pemilihan',
                'd_lain_lain_kriteria',
            ]);
        });
    }
};
