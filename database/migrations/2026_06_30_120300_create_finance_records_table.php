<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('finance_period_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('finance_commitment_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('record_type', 32);
            $table->date('recorded_on');
            $table->decimal('amount', 12, 2);
            $table->string('title')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'record_type', 'recorded_on'], 'finance_records_user_type_date_index');
            $table->index(['finance_period_id', 'record_type'], 'finance_records_period_type_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_records');
    }
};
