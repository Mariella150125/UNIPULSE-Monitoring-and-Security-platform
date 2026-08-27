<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_availability', function (Blueprint $table) {

            $table->id();

            $table->foreignId('application_id')
                ->constrained('applications')
                ->cascadeOnDelete();

            $table->timestamp('checked_at');

            $table->boolean('is_available');

            $table->unsignedInteger('response_time')
                ->nullable();

            $table->unsignedSmallInteger('status_code')
                ->nullable();

            $table->timestamps();

            $table->index([
                'application_id',
                'checked_at'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_availability');
    }
};