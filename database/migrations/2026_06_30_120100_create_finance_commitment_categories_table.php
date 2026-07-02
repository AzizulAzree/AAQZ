<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_commitment_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('default_amount', 12, 2)->nullable();
            $table->string('color', 16)->nullable();
            $table->string('icon', 8)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'name'], 'finance_categories_user_name_unique');
            $table->index(['user_id', 'is_active', 'sort_order'], 'finance_categories_user_active_order_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_commitment_categories');
    }
};
