<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('application_endpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('url');
            $table->enum('http_method', ['GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS']);
            $table->text('auth_headers')->nullable();       // JSON chiffré en app
            $table->unsignedInteger('frequency_seconds')->default(60);
            $table->enum('last_status', ['success', 'timeout', 'http_4xx', 'http_5xx', 'never_checked'])->default('never_checked');
            $table->unsignedInteger('last_response_time_ms')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('application_id');
            $table->index('last_status');
            $table->index('last_checked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_endpoints');
    }
};
