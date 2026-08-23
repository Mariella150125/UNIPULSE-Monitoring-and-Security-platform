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
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            

            // Informations générales
            $table->string('name');
            $table->string('hostname')->unique();
            $table->string('ip_address');
            $table->unsignedInteger('port')->nullable();

            $table->string('os');
            $table->string('os_version')->nullable();

            $table->string('environment');
            $table->string('department')->nullable();
            $table->string('criticality')->default('medium');

            $table->text('description')->nullable();
            $table->json('tags')->nullable();

            // Groupe de serveurs
            $table->foreignId('group_id')
                ->nullable()
                ->constrained('server_groups')
                ->nullOnDelete();

            // État global — calculé par le backend
            $table->string('global_status')->default('unknown');
            $table->timestamp('global_status_updated_at')->nullable();

            // Prometheus
            $table->string('prometheus_instance')->nullable();
            $table->string('prometheus_job')->nullable();
            $table->boolean('prometheus_reachable')->default(false);

            // Wazuh
            $table->string('wazuh_agent_id')->nullable();
            $table->string('wazuh_group')->nullable();
            $table->string('wazuh_agent_status')->default('never_seen');

            $table->timestamps();
        });
           
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
