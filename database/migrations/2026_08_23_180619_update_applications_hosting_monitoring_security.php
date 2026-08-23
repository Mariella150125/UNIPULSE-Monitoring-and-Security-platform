<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {

            // ==============================
            // HÉBERGEMENT
            // ==============================

            $table->boolean('is_hosted')
                ->default(false)
                ->after('status');

            $table->unsignedInteger('port')
                ->nullable()
                ->after('server_id');

            $table->string('deployment_path')
                ->nullable()
                ->after('port');

            // ==============================
            // MONITORING PROMETHEUS
            // ==============================

            $table->boolean('monitoring_enabled')
                ->default(false)
                ->after('deployment_path');

            $table->string('metrics_endpoint')
                ->nullable()
                ->after('prometheus_job');

            $table->string('scrape_interval')
                ->nullable()
                ->after('metrics_endpoint');

            // ==============================
            // SÉCURITÉ WAZUH
            // ==============================

            $table->boolean('wazuh_enabled')
                ->default(false)
                ->after('last_sync_at');

            // ==============================
            // CRITICITÉ
            // ==============================

            $table->string('criticality')
                ->nullable()
                ->after('wazuh_enabled');

            // ==============================
            // ANCIEN CHAMP HÉBERGEMENT
            // ==============================

            $table->dropColumn('hosting_type');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {

            $table->string('hosting_type')
                ->nullable();

            $table->dropColumn([
                'is_hosted',
                'port',
                'deployment_path',
                'monitoring_enabled',
                'metrics_endpoint',
                'scrape_interval',
                'wazuh_enabled',
                'criticality',
            ]);
        });
    }
};