<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bpp_supplier_quote_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('bpp_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bpp_supplier_quote_id')->constrained('bpp_supplier_quotes')->cascadeOnDelete();
            $table->unsignedInteger('line_number');
            $table->text('item_spesifikasi');
            $table->decimal('kuantiti', 12, 2);
            $table->string('unit_ukuran');
            $table->decimal('harga_tawaran', 15, 2);
            $table->decimal('jumlah_harga', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bpp_supplier_quote_items');
    }
};
