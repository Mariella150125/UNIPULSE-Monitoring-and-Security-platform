<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('owasp_categories', function (Blueprint $table) {
            $table->string('url')->default('https://owasp.org/www-project-top-ten/')->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('owasp_categories', function (Blueprint $table) {
            $table->dropColumn('url');
        });
    }
};