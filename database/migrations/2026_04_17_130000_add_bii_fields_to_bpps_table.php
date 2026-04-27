<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bpps', function (Blueprint $table): void {
            $table->decimal('b10_nilai_perolehan_terdahulu', 15, 2)->nullable()->after('b9_lokasi_diperlukan');
            $table->string('b11_no_rujukan_perolehan_po_sst_terdahulu')->nullable()->after('b10_nilai_perolehan_terdahulu');
            $table->decimal('b12_nilai_perolehan_2_tahun_lalu', 15, 2)->nullable()->after('b11_no_rujukan_perolehan_po_sst_terdahulu');
            $table->string('b13_no_rujukan_perolehan_po_sst_2_tahun_lalu')->nullable()->after('b12_nilai_perolehan_2_tahun_lalu');
            $table->decimal('b14_nilai_perolehan_alat', 15, 2)->nullable()->after('b13_no_rujukan_perolehan_po_sst_2_tahun_lalu');
            $table->string('b15_no_rujukan_perolehan_po_sst_alat')->nullable()->after('b14_nilai_perolehan_alat');
            $table->boolean('b16_kepilkan_analisis_roi_rov')->default(false)->after('b15_no_rujukan_perolehan_po_sst_alat');
            $table->boolean('b17_rekod_senarai_pihak_pengguna')->default(false)->after('b16_kepilkan_analisis_roi_rov');
            $table->boolean('b18_salinan_laporan_kerosakan')->default(false)->after('b17_rekod_senarai_pihak_pengguna');
        });
    }

    public function down(): void
    {
        Schema::table('bpps', function (Blueprint $table): void {
            $table->dropColumn([
                'b10_nilai_perolehan_terdahulu',
                'b11_no_rujukan_perolehan_po_sst_terdahulu',
                'b12_nilai_perolehan_2_tahun_lalu',
                'b13_no_rujukan_perolehan_po_sst_2_tahun_lalu',
                'b14_nilai_perolehan_alat',
                'b15_no_rujukan_perolehan_po_sst_alat',
                'b16_kepilkan_analisis_roi_rov',
                'b17_rekod_senarai_pihak_pengguna',
                'b18_salinan_laporan_kerosakan',
            ]);
        });
    }
};
