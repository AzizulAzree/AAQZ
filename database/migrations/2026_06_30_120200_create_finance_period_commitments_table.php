<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_period_commitments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('finance_period_id')
                ->constrained(indexName: 'fp_commitments_period_fk')
                ->cascadeOnDelete();
            $table->foreignId('finance_commitment_category_id')
                ->nullable()
                ->constrained(indexName: 'fp_commitments_category_fk')
                ->nullOnDelete();
            $table->string('name_snapshot');
            $table->decimal('amount', 12, 2);
            $table->string('status', 16)->default('unpaid');
            $table->date('paid_on')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['finance_period_id', 'status'], 'finance_period_commitments_period_status_index');
            $table->index(['finance_period_id', 'name_snapshot'], 'finance_period_commitments_period_name_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_period_commitments');
    }
};
