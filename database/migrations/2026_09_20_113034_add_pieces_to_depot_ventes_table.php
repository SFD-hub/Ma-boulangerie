<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ajout des colonnes de répartition par pièce (entier/moitié/tiers).
        Schema::table('depot_ventes', function (Blueprint $table) {
            $table->unsignedInteger('nombre_entiers')->default(0)->after('produit_id');
            $table->unsignedInteger('nombre_moities')->default(0)->after('nombre_entiers');
            $table->unsignedInteger('nombre_tiers')->default(0)->after('nombre_moities');
            $table->decimal('quantite_equivalente', 8, 2)->default(0)->after('nombre_tiers');
        });

        // 2. Backfill défensif : les lignes existantes sont interprétées comme
        // des pains entiers (le champ "quantite" n'a jamais compté de fractions).
        DB::table('depot_ventes')->update([
            'nombre_entiers'        => DB::raw('quantite'),
            'quantite_equivalente'  => DB::raw('quantite'),
        ]);

        // 3. La colonne "quantite" est remplacée par la répartition ci-dessus.
        Schema::table('depot_ventes', function (Blueprint $table) {
            $table->dropColumn('quantite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('depot_ventes', function (Blueprint $table) {
            $table->unsignedInteger('quantite')->default(0)->after('produit_id');
        });

        DB::table('depot_ventes')->update([
            'quantite' => DB::raw('nombre_entiers'),
        ]);

        Schema::table('depot_ventes', function (Blueprint $table) {
            $table->dropColumn(['nombre_entiers', 'nombre_moities', 'nombre_tiers', 'quantite_equivalente']);
        });
    }
};
