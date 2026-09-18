<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owasp_categories', function (Blueprint $table) {
            $table->id();
            $table->string('version')->default('2021'); // Pour gérer 2017, 2021, 2025...
            $table->string('code'); // A01, A02...
            $table->string('name');
            $table->string('status');
            $table->string('color');
            $table->string('guideline');
            $table->boolean('is_active')->default(true); // Pour activer/désactiver une version
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owasp_categories');
    }
};