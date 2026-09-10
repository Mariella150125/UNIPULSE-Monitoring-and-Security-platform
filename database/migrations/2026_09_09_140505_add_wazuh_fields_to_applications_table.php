<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('wazuh_agent_id')->nullable()->after('wazuh_enabled');
            $table->string('wazuh_group')->nullable()->after('wazuh_agent_id');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['wazuh_agent_id', 'wazuh_group']);
        });
    }
};