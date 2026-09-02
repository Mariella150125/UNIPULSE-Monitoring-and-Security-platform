<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── application_endpoints ──
        Schema::table('application_endpoints', function (Blueprint $table) {
            $table->boolean('is_enabled')->default(true)->after('frequency_seconds');
            $table->softDeletes();
        });

        // ── webhooks ──
        Schema::table('webhooks', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('application_endpoints', function (Blueprint $table) {
            $table->dropColumn(['is_enabled', 'deleted_at']);
        });

        Schema::table('webhooks', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};