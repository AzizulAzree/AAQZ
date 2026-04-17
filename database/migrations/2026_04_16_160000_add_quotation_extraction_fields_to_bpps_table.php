<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bpps', function (Blueprint $table): void {
            $table->string('quotation_extraction_format_version')->nullable()->after('c1_selection_reason_lain_lain');
            $table->longText('quotation_extraction_raw_text')->nullable()->after('quotation_extraction_format_version');
            $table->json('quotation_extraction_review')->nullable()->after('quotation_extraction_raw_text');
        });
    }

    public function down(): void
    {
        Schema::table('bpps', function (Blueprint $table): void {
            $table->dropColumn([
                'quotation_extraction_format_version',
                'quotation_extraction_raw_text',
                'quotation_extraction_review',
            ]);
        });
    }
};
