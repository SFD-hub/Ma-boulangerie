<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Protège les lignes existantes qui auraient libelle NULL
        DB::statement("UPDATE depenses SET libelle = 'Non précisé' WHERE libelle IS NULL OR libelle = ''");

        Schema::table('depenses', function (Blueprint $table) {
            $table->string('libelle')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('depenses', function (Blueprint $table) {
            $table->string('libelle')->nullable()->change();
        });
    }
};
