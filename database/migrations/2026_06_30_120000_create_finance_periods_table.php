<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_periods', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_month');
            $table->date('salary_received_on');
            $table->decimal('salary_amount', 12, 2);
            $table->decimal('carry_balance_before_salary', 12, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'period_year', 'period_month'], 'finance_periods_user_period_unique');
            $table->index(['user_id', 'period_year', 'period_month'], 'finance_periods_user_period_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_periods');
    }
};
