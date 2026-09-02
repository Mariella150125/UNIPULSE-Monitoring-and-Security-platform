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
        Schema::create('webhook_deliveries', function (Blueprint $table) {
           $table->id();
            $table->foreignId('webhook_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_type_id')->nullable()->constrained('webhook_event_types')->nullOnDelete();
            $table->enum('direction', ['inbound', 'outbound']);
            $table->unsignedSmallInteger('attempt_number')->default(1);
            $table->json('payload');
            $table->boolean('signature_valid')->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->boolean('success')->default(false);
            $table->text('error_message')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamp('delivered_at')->useCurrent();

            $table->index('webhook_id');
            $table->index(['success', 'delivered_at']);
            $table->index('direction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
    }
};
