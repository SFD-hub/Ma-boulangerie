<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distributions', function (Blueprint $table) {
            $table->unsignedInteger('nombre_pains')->nullable()->after('date_distribution');
            $table->unsignedInteger('nombre_invendus')->nullable()->after('nombre_pains');
        });
    }

    public function down(): void
    {
        Schema::table('distributions', function (Blueprint $table) {
            $table->dropColumn(['nombre_pains', 'nombre_invendus']);
        });
    }
};
