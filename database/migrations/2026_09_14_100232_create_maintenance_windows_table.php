<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_windows', function (Blueprint $table) {
            $table->id();
            $table->string('resource_type'); // 'server' ou 'application'
            $table->string('resource_name'); // Le nom pour l'afficher facilement
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->boolean('is_active')->default(true); // Si false, la maintenance est annulée
            $table->timestamps();
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_windows');
    }
};