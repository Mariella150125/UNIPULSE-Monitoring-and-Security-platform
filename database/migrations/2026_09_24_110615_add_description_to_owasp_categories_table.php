<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('owasp_categories', function (Blueprint $table) {
            // On ajoute la colonne 'description' juste après 'name'
            $table->text('description')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('owasp_categories', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};