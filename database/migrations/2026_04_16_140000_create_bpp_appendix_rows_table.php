<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bpp_appendix_rows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('bpp_id')->constrained()->cascadeOnDelete();
            $table->string('appendix_type');
            $table->unsignedInteger('line_number');
            $table->text('item_spesifikasi');
            $table->decimal('kuantiti', 12, 2);
            $table->string('unit_ukuran');
            $table->decimal('harga_seunit', 15, 2);
            $table->decimal('jumlah_harga', 15, 2);
            $table->timestamps();

            $table->index(['bpp_id', 'appendix_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bpp_appendix_rows');
    }
};
