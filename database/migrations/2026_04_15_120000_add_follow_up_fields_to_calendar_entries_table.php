<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_entries', function (Blueprint $table): void {
            $table->boolean('follow_up_enabled')->default(false)->after('details');
            $table->unsignedSmallInteger('follow_up_days')->nullable()->after('follow_up_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('calendar_entries', function (Blueprint $table): void {
            $table->dropColumn(['follow_up_enabled', 'follow_up_days']);
        });
    }
};
