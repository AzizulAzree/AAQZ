<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bpp_supplier_quotes', function (Blueprint $table): void {
            $table->string('registration_number')->nullable()->after('supplier_name');
            $table->text('supplier_address')->nullable()->after('registration_number');
        });
    }

    public function down(): void
    {
        Schema::table('bpp_supplier_quotes', function (Blueprint $table): void {
            $table->dropColumn([
                'registration_number',
                'supplier_address',
            ]);
        });
    }
};
