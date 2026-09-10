<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // On ajoute la clé étrangère dans la table applications
        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('application_group_id')->nullable()->after('environment')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['application_group_id']);
            $table->dropColumn('application_group_id');
        });
        Schema::dropIfExists('application_groups');
    }
};