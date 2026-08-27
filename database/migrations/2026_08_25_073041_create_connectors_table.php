<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('connectors', function (Blueprint $table) {
            // ── Identité ──
            $table->id();

            $table->enum('type', ['prometheus', 'wazuh']);
            $table->string('name');
            $table->string('base_url');

            // ── Authentification ──
            $table->string('auth_username')->nullable();
            $table->text('auth_password_encrypted')->nullable();
            $table->unsignedInteger('api_port')->nullable();

            // ── Config étendue ──
            $table->json('extra_config')->nullable();

            // ── Statut de connexion ──
            $table->enum('status', ['connected', 'error', 'never_tested'])
                  ->default('never_tested');

            $table->timestamp('last_check_at')->nullable();
            $table->timestamp('last_success_at')->nullable();
            $table->text('last_error_message')->nullable();

            // ── Traçabilité ──
            // foreignId = bigint (même type que users.id)
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('updated_by')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->timestamps();

            // ── Index ──
            $table->index('type');
            $table->index('status');
            $table->index('created_by');
            $table->unique(['type', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('connectors');
    }
};