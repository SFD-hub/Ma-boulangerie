<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Rétrocompatibilité : avant cette fonctionnalité, tout était implicitement
// "du pain". On crée un produit "Pain" par boulangerie et on l'assigne à
// tout l'historique existant, pour que rien ne se retrouve sans type.
return new class extends Migration
{
    public function up(): void
    {
        $boulangerieIds = DB::table('boulangeries')->pluck('id');

        foreach ($boulangerieIds as $boulangerieId) {
            $painId = DB::table('produits')
                ->where('boulangerie_id', $boulangerieId)
                ->where('nom', 'Pain')
                ->value('id');

            if (! $painId) {
                $painId = DB::table('produits')->insertGetId([
                    'nom'            => 'Pain',
                    'boulangerie_id' => $boulangerieId,
                    'actif'          => true,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }

            DB::table('productions')
                ->where('boulangerie_id', $boulangerieId)
                ->whereNull('produit_id')
                ->update(['produit_id' => $painId]);

            $livreurIds = DB::table('livreurs')->where('boulangerie_id', $boulangerieId)->pluck('id');

            if ($livreurIds->isNotEmpty()) {
                DB::table('distributions')
                    ->whereIn('livreur_id', $livreurIds)
                    ->whereNull('produit_id')
                    ->update(['produit_id' => $painId]);
            }
        }
    }

    public function down(): void
    {
        // Pas de retour arrière : les produits "Pain" créés ici peuvent déjà
        // être référencés par de nouvelles productions/distributions.
    }
};
