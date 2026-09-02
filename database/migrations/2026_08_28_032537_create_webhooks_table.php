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
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->enum('direction', ['inbound', 'outbound']);
            $table->enum('scope', ['application', 'platform']);
            $table->string('name');
            $table->foreignId('connector_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('application_id')->nullable()->constrained()->nullOnDelete();
            $table->string('target_url');
            $table->enum('auth_method', ['hmac_signature', 'api_key', 'none'])->default('none');
            $table->string('secret_hash', 64)->nullable();   // sha256 du secret HMAC
            $table->foreignId('api_key_id')->nullable()->constrained()->nullOnDelete();
            $table->tinyInteger('min_severity_level')->default(0); // 0 = tous
            $table->enum('status', ['active', 'paused', 'error'])->default('active');
            $table->string('last_status')->nullable();
            $table->timestamp('last_delivery_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['direction', 'scope']);
            $table->index('status');
            $table->index('application_id');
            $table->index('connector_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhooks');
    }
};
