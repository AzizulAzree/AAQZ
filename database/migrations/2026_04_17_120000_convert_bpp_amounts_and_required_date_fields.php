<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bpps', function (Blueprint $table): void {
            $table->decimal('b3_nilai_tawaran_perolehan_tmp', 15, 2)->nullable();
            $table->decimal('b4_harga_indikatif_tmp', 15, 2)->nullable();
            $table->decimal('b5_peruntukan_diluluskan_tmp', 15, 2)->nullable();
            $table->date('b8_tarikh_diperlukan_tmp')->nullable();
        });

        DB::table('bpps')
            ->select([
                'id',
                'b3_nilai_tawaran_perolehan',
                'b4_harga_indikatif',
                'b5_peruntukan_diluluskan',
                'b8_tarikh_diperlukan',
            ])
            ->orderBy('id')
            ->get()
            ->each(function (object $bpp): void {
                DB::table('bpps')
                    ->where('id', $bpp->id)
                    ->update([
                        'b3_nilai_tawaran_perolehan_tmp' => $this->parseAmount($bpp->b3_nilai_tawaran_perolehan),
                        'b4_harga_indikatif_tmp' => $this->parseAmount($bpp->b4_harga_indikatif),
                        'b5_peruntukan_diluluskan_tmp' => $this->parseAmount($bpp->b5_peruntukan_diluluskan),
                        'b8_tarikh_diperlukan_tmp' => $this->parseMonthYear($bpp->b8_tarikh_diperlukan),
                    ]);
            });

        Schema::table('bpps', function (Blueprint $table): void {
            $table->dropColumn([
                'b3_nilai_tawaran_perolehan',
                'b4_harga_indikatif',
                'b5_peruntukan_diluluskan',
                'b8_tarikh_diperlukan',
            ]);
        });

        Schema::table('bpps', function (Blueprint $table): void {
            $table->renameColumn('b3_nilai_tawaran_perolehan_tmp', 'b3_nilai_tawaran_perolehan');
            $table->renameColumn('b4_harga_indikatif_tmp', 'b4_harga_indikatif');
            $table->renameColumn('b5_peruntukan_diluluskan_tmp', 'b5_peruntukan_diluluskan');
            $table->renameColumn('b8_tarikh_diperlukan_tmp', 'b8_tarikh_diperlukan');
        });
    }

    public function down(): void
    {
        Schema::table('bpps', function (Blueprint $table): void {
            $table->string('b3_nilai_tawaran_perolehan_tmp')->nullable();
            $table->string('b4_harga_indikatif_tmp')->nullable();
            $table->string('b5_peruntukan_diluluskan_tmp')->nullable();
            $table->string('b8_tarikh_diperlukan_tmp')->nullable();
        });

        DB::table('bpps')
            ->select([
                'id',
                'b3_nilai_tawaran_perolehan',
                'b4_harga_indikatif',
                'b5_peruntukan_diluluskan',
                'b8_tarikh_diperlukan',
            ])
            ->orderBy('id')
            ->get()
            ->each(function (object $bpp): void {
                DB::table('bpps')
                    ->where('id', $bpp->id)
                    ->update([
                        'b3_nilai_tawaran_perolehan_tmp' => $this->formatAmount($bpp->b3_nilai_tawaran_perolehan),
                        'b4_harga_indikatif_tmp' => $this->formatAmount($bpp->b4_harga_indikatif),
                        'b5_peruntukan_diluluskan_tmp' => $this->formatAmount($bpp->b5_peruntukan_diluluskan),
                        'b8_tarikh_diperlukan_tmp' => $this->formatMonthYear($bpp->b8_tarikh_diperlukan),
                    ]);
            });

        Schema::table('bpps', function (Blueprint $table): void {
            $table->dropColumn([
                'b3_nilai_tawaran_perolehan',
                'b4_harga_indikatif',
                'b5_peruntukan_diluluskan',
                'b8_tarikh_diperlukan',
            ]);
        });

        Schema::table('bpps', function (Blueprint $table): void {
            $table->renameColumn('b3_nilai_tawaran_perolehan_tmp', 'b3_nilai_tawaran_perolehan');
            $table->renameColumn('b4_harga_indikatif_tmp', 'b4_harga_indikatif');
            $table->renameColumn('b5_peruntukan_diluluskan_tmp', 'b5_peruntukan_diluluskan');
            $table->renameColumn('b8_tarikh_diperlukan_tmp', 'b8_tarikh_diperlukan');
        });
    }

    private function parseAmount(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = preg_replace('/[^0-9.\-]/', '', (string) $value);

        if ($normalized === null || $normalized === '' || ! is_numeric($normalized)) {
            return null;
        }

        return number_format((float) $normalized, 2, '.', '');
    }

    private function parseMonthYear(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim((string) $value);

        foreach (['Y-m-d', 'Y-m', 'M-y', 'M-Y'] as $format) {
            $date = DateTime::createFromFormat($format, $value);

            if ($date !== false) {
                return $date->format('Y-m-01');
            }
        }

        return null;
    }

    private function formatAmount(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return number_format((float) $value, 2, '.', '');
    }

    private function formatMonthYear(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return (new DateTime((string) $value))->format('M-y');
        } catch (Throwable) {
            return null;
        }
    }
};
