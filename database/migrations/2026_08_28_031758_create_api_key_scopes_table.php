<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_key_scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_key_id')->constrained()->cascadeOnDelete();
            $table->enum('resource', ['servers', 'applications', 'alerts', 'reports']);
            $table->enum('action', ['read', 'write']);
            $table->unique(['api_key_id', 'resource', 'action']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_key_scopes');
    }
};