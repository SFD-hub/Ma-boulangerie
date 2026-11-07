<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Les quantités de pain sont désormais comptées en sixièmes de pain
     * (entier = 6, moitié = 3, tiers = 2), en nombres entiers : plus aucun
     * arrondi (0,33 + 0,33 + 0,33 = 0,99) dans le stock ni dans les montants.
     * Les anciennes colonnes décimales sont conservées pour compatibilité.
     */
    public function up(): void
    {
        Schema::table('depot_ventes', function (Blueprint $table) {
            $table->unsignedInteger('parts')->default(0)->after('nombre_tiers');
        });

        Schema::table('distributions', function (Blueprint $table) {
            // null = distribution pas encore réglée avec la précision par pièce
            // (on se replie alors sur l'ancien compteur entier nombre_invendus).
            $table->unsignedInteger('invendus_parts')->nullable()->after('invendus_equivalent');
        });

        DB::table('depot_ventes')->update([
            'parts' => DB::raw('ROUND(quantite_equivalente * 6)'),
        ]);

        DB::table('distributions')->whereNotNull('invendus_equivalent')->update([
            'invendus_parts' => DB::raw('ROUND(invendus_equivalent * 6)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('depot_ventes', function (Blueprint $table) {
            $table->dropColumn('parts');
        });

        Schema::table('distributions', function (Blueprint $table) {
            $table->dropColumn('invendus_parts');
        });
    }
};
