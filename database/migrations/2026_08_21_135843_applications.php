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
        Schema::create('applications', function (Blueprint $table) {

            $table->id();

            // Identification
            $table->string('identifiant_genere')->unique();
            $table->string('name');
            $table->text('description')->nullable();

            // Informations techniques
            $table->string('url')->nullable();
            $table->string('language')->nullable();
            $table->string('framework')->nullable();
            $table->string('version')->nullable();

            // Classification
            $table->string('environment');
            $table->string('database_used')->nullable();

            // Informations métier
            $table->string('client_name')->nullable();
            $table->json('tags')->nullable();
            $table->string('status')->default('active');
            // Hébergement
            $table->string('hosting_type');
            $table->foreignId('server_id')
                ->nullable()
                ->constrained('servers')
                ->nullOnDelete();
            $table->foreignId('responsible_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Monitoring
            $table->string('prometheus_job')->nullable();
            $table->string('url_health_check')->nullable();
            $table->timestamp('last_sync_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
