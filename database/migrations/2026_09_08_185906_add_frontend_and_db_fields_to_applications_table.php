<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            // Frontend
            $table->string('frontend_language')->nullable()->after('framework');
            $table->string('frontend_framework')->nullable()->after('frontend_language');
            $table->string('frontend_url')->nullable()->after('frontend_framework');
            $table->string('frontend_version')->nullable()->after('frontend_url');

            // Base de données
            $table->string('database_type')->nullable()->after('frontend_version');
            $table->string('database_name')->nullable()->after('database_type');
            $table->string('database_host')->nullable()->after('database_name');
            $table->unsignedInteger('database_port')->nullable()->after('database_host');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['frontend_language', 'frontend_framework', 'frontend_url', 'frontend_version', 'database_type', 'database_name', 'database_host', 'database_port']);
        });
    }
};