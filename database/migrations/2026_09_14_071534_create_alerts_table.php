<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            
            // Identification et source (MF-156, MF-162)
            $table->string('code')->unique()->nullable(); // Ex: ALR-00152
            $table->string('title');
            $table->text('description')->nullable();
            
            // Ressource concernée (peut être un serveur ou une application)
            $table->string('resource_type')->nullable(); // 'App\Models\Server' ou 'App\Models\Application'
            $table->unsignedBigInteger('resource_id')->nullable();
            
            // Source et Corrélation (MF-158, MF-169)
            $table->string('source')->default('system'); // 'prometheus', 'wazuh', 'system'
            $table->json('correlated_events')->nullable(); // Pour stocker les événements Wazuh + Prometheus liés
            
            // Gestion de l'alerte (MF-162, MF-167)
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['open', 'acknowledged', 'resolved', 'closed'])->default('open');
            
            // Assignation et Traitement (MF-164, MF-165)
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_comment')->nullable(); // Commentaire de résolution
            
            // Escalade et Snooze (MF-163, MF-170)
            $table->timestamp('last_escalated_at')->nullable(); // Pour savoir quand réémettre le son
            $table->timestamp('snoozed_until')->nullable(); // Pour ignorer l'alerte pendant X minutes
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};