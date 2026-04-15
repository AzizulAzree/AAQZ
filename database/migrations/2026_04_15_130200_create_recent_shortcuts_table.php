<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recent_shortcuts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workspace_node_id')->constrained('workspace_nodes')->cascadeOnDelete();
            $table->timestamp('opened_at');
            $table->timestamps();

            $table->unique(['user_id', 'workspace_node_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recent_shortcuts');
    }
};
