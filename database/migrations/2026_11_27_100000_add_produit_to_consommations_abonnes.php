<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Le pain donné aux abonnés vient de la production du jour : chaque
     * consommation est rattachée au pain de la boulangerie (le premier type
     * de pain), pour pouvoir le retirer du stock du jour.
     */
    public function up(): void
    {
        Schema::table('consommations_abonnes', function (Blueprint $table) {
            $table->unsignedBigInteger('produit_id')->nullable()->after('client_abonne_id');
        });

        // Les consommations déjà saisies sont rattachées au premier pain de leur boulangerie.
        $premier = DB::table('produits')->selectRaw('boulangerie_id, MIN(id) as id')->groupBy('boulangerie_id')->pluck('id', 'boulangerie_id');
        foreach ($premier as $boulangerieId => $produitId) {
            DB::table('consommations_abonnes')
                ->whereIn('client_abonne_id', DB::table('clients_abonnes')->where('boulangerie_id', $boulangerieId)->pluck('id'))
                ->update(['produit_id' => $produitId]);
        }
    }

    public function down(): void
    {
        Schema::table('consommations_abonnes', function (Blueprint $table) {
            $table->dropColumn('produit_id');
        });
    }
};
