<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ex: Rapport_general_14-09-2026
            $table->enum('type', ['general', 'application', 'server', 'security', 'alert', 'executive']); // MF-174, MF-181
            $table->enum('format', ['pdf', 'excel', 'word', 'image'])->nullable(); // MF-176
            $table->string('file_path')->nullable(); // Où le fichier est stocké
            $table->foreignId('generated_by')->constrained('users')->onDelete('cascade'); // Le responsable
            $table->string('department')->nullable(); // MF-177
            $table->integer('printed_copies')->default(0); // MF-177
            $table->string('printer_used')->nullable(); // MF-177
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};