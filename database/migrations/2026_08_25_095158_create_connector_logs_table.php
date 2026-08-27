<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('connector_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('connector_id')->constrained('connectors')->cascadeOnDelete();
            $table->timestamp('executed_at');
            $table->boolean('success');
            $table->unsignedInteger('duration_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('connector_id');
            $table->index('executed_at');
            $table->index(['connector_id', 'executed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('connector_logs');
    }
};