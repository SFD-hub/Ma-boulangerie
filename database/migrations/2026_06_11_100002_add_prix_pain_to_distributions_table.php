<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distributions', function (Blueprint $table) {
            $table->unsignedInteger('prix_pain')->nullable()->after('nombre_pains');
        });

        // Backfill pour les distributions existantes : prix déduit de montant_attendu / nombre_pains
        DB::statement("
            UPDATE distributions
            SET prix_pain = ROUND(montant_attendu / nombre_pains)
            WHERE nombre_pains > 0
              AND montant_attendu > 0
              AND prix_pain IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('distributions', function (Blueprint $table) {
            $table->dropColumn('prix_pain');
        });
    }
};
