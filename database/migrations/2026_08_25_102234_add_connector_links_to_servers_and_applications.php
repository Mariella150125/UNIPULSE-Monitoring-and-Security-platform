<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── MF-13 : chaque serveur → son connecteur Prometheus + son connecteur Wazuh ──
        Schema::table('servers', function (Blueprint $table) {
            $table->foreignId('prometheus_connector_id')
                  ->nullable()
                  ->constrained('connectors')
                  ->nullOnDelete()
                  ->after('wazuh_agent_status');

            $table->foreignId('wazuh_connector_id')
                  ->nullable()
                  ->constrained('connectors')
                  ->nullOnDelete()
                  ->after('prometheus_connector_id');
        });

        // ── MF-14 : chaque application → son connecteur Prometheus + son connecteur Wazuh ──
        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('prometheus_connector_id')
                  ->nullable()
                  ->constrained('connectors')
                  ->nullOnDelete()
                  ->after('criticality');

            $table->foreignId('wazuh_connector_id')
                  ->nullable()
                  ->constrained('connectors')
                  ->nullOnDelete()
                  ->after('prometheus_connector_id');
        });
    }

    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('prometheus_connector_id');
            $table->dropConstrainedForeignId('wazuh_connector_id');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('prometheus_connector_id');
            $table->dropConstrainedForeignId('wazuh_connector_id');
        });
    }
};