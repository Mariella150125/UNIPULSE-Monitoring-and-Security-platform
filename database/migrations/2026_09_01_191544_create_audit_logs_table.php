<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('audit_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
        $table->string('action'); 
        $table->string('resource_type')->nullable(); 
        $table->unsignedBigInteger('resource_id')->nullable();
        $table->string('ip_address', 45);
        $table->boolean('is_success')->default(true); 
        $table->text('details')->nullable(); 
        $table->timestamps();

        // Index pour accélérer la recherche dans le tableau
        $table->index('created_at');
        $table->index('user_id');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
