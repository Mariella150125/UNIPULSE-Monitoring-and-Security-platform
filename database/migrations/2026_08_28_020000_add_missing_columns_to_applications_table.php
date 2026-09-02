<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $cols = [
            ['language',          'string', '100', true],
            ['framework',         'string', '100', true],
            ['version',           'string', '50',  true],
            ['application_type_id', 'foreignId', null, true, 'application_types'],
            ['environment',       'string', '50',  false],
            ['database_used',     'string', '100', true],
            ['client_name',       'string', '255', true],
            ['status',            'string', '50',  false],
            ['responsible_user_id', 'foreignId', null, true, 'users'],
            ['is_hosted',         'boolean', null,  false],
            ['server_id',         'foreignId', null, true, 'servers'],
            ['port',              'integer', null,  true],
            ['deployment_path',   'string', '500', true],
            ['monitoring_enabled', 'boolean', null, false],
            ['prometheus_job',    'string', '255', true],
            ['metrics_endpoint',  'string', '255', true],
            ['scrape_interval',   'string', '50',  true],
            ['url_health_check',  'string', '500', true],
            ['last_sync_at',      'timestamp', null, true],
            ['wazuh_enabled',     'boolean', null, false],
            ['criticality',       'string', '50',  true],
        ];

        Schema::table('applications', function (Blueprint $table) use ($cols) {
            foreach ($cols as $def) {
                $col = $def[0];
                if (Schema::hasColumn('applications', $col)) {
                    continue;
                }

                $type     = $def[1];
                $length   = $def[2];
                $nullable = $def[3];
                $refTable = $def[4] ?? null;

                if ($type === 'foreignId') {
                    $colObj = $table->foreignId($col)->nullable();
                    if ($refTable) {
                        $colObj->constrained($refTable)->nullOnDelete();
                    }
                } elseif ($type === 'timestamp') {
                    $colObj = $table->timestamp($col)->nullable();
                } elseif ($length) {
                    $colObj = $table->$type($col, $length);
                    if ($nullable) $colObj->nullable();
                } else {
                    $colObj = $table->$type($col);
                    if ($nullable) $colObj->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'language', 'framework', 'version',
                'application_type_id', 'environment', 'database_used',
                'client_name', 'status', 'responsible_user_id',
                'is_hosted', 'server_id', 'port', 'deployment_path',
                'monitoring_enabled', 'prometheus_job', 'metrics_endpoint',
                'scrape_interval', 'url_health_check', 'last_sync_at',
                'wazuh_enabled', 'criticality',
            ]);
        });
    }
};