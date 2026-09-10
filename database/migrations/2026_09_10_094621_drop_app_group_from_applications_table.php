<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            // On supprime le vieux champ texte qu'on avait ajouté par erreur
            if (Schema::hasColumn('applications', 'app_group')) {
                $table->dropColumn('app_group');
            }
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('app_group')->nullable()->after('environment');
        });
    }
};