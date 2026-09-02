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
        Schema::create('webhook_event_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 60)->unique();           // alert.created, wazuh.vulnerability…
            $table->string('label');
            $table->enum('applicable_direction', ['inbound', 'outbound', 'both'])->default('outbound');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_event_types');
    }
};
