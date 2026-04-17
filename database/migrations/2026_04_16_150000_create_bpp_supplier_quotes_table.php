<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bpp_supplier_quotes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('bpp_id')->constrained()->cascadeOnDelete();
            $table->string('supplier_name');
            $table->decimal('total_price', 15, 2);
            $table->string('delivery_period');
            $table->string('validity_period');
            $table->string('quotation_reference')->nullable();
            $table->boolean('is_selected')->default(false);
            $table->timestamps();

            $table->index(['bpp_id', 'is_selected']);
        });

        Schema::table('bpps', function (Blueprint $table): void {
            $table->string('c1_selection_reason')->nullable()->after('d_lain_lain_kriteria');
            $table->text('c1_selection_reason_lain_lain')->nullable()->after('c1_selection_reason');
        });
    }

    public function down(): void
    {
        Schema::table('bpps', function (Blueprint $table): void {
            $table->dropColumn([
                'c1_selection_reason',
                'c1_selection_reason_lain_lain',
            ]);
        });

        Schema::dropIfExists('bpp_supplier_quotes');
    }
};
