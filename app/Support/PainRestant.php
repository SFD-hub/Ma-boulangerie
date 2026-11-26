<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Stock de « pain restant » d'une boulangerie, calculé à la volée (en sixièmes de
 * pain, comme le reste de l'application) :
 *
 *   entrées
 *     + restants des livreurs et des clients (pain rendu le lendemain, compté au versement)
 *     + pain non vendu de la boulangerie : pour chaque jour passé, production du jour
 *       − pains sortis ce jour-là (distribués nets des retours, plus anciennes ventes
 *       au comptoir). Il devient restant au bout de 24 h. Les retours de la Boutique
 *       (et des livreurs) qui n'ont pas été redistribués en font partie.
 *   − sorties estimées : pains des sacs vendus (estimation par vente)
 *   ± ajustements : corrections après comptage
 *
 * La Boutique n'a pas de « restants » : le pain non vendu d'un vendeur revient en retour.
 */
class PainRestant
{
    /**
     * @return array{
     *   restants_livreurs:int, restants_clients:int, non_vendu:int, entrees:int,
     *   sorties_sacs:int, ajustements:int, brut:int, stock:int, a_ajuster:bool
     * }  Toutes les quantités sont en sixièmes de pain.
     */
    public static function etat(int $boulangerieId): array
    {
        // ── Restants rendus au versement (livreurs / clients, hors Boutique) ─────
        $restants = DB::table('distributions as d')
            ->join('livreurs as l', 'l.id', '=', 'd.livreur_id')
            ->where('l.boulangerie_id', $boulangerieId)
            ->whereIn('l.type', ['livreur', 'client'])
            ->whereExists(fn ($q) => $q->select(DB::raw(1))->from('versements as v')->whereColumn('v.distribution_id', 'd.id'))
            ->selectRaw('l.type as type, COALESCE(SUM(COALESCE(d.invendus_parts, d.nombre_invendus * 6, 0)), 0) as parts')
            ->groupBy('l.type')
            ->pluck('parts', 'type');

        $restantsLivreurs = (int) ($restants['livreur'] ?? 0);
        $restantsClients  = (int) ($restants['client'] ?? 0);

        // ── Pain non vendu de la boulangerie, jour par jour (jours passés) ───────
        $aujourdhui = now()->toDateString();

        $produit = DB::table('productions')
            ->where('boulangerie_id', $boulangerieId)
            ->whereDate('date_production', '<', $aujourdhui)
            ->selectRaw('produit_id, DATE(date_production) as jour, SUM(nombre_pains_produits) * 6 as parts')
            ->groupBy('produit_id', 'jour')
            ->get();

        $distribue = DB::table('distributions as d')
            ->join('livreurs as l', 'l.id', '=', 'd.livreur_id')
            ->where('l.boulangerie_id', $boulangerieId)
            ->whereDate('d.date_distribution', '<', $aujourdhui)
            ->selectRaw('d.produit_id as produit_id, DATE(d.date_distribution) as jour, SUM(d.nombre_pains * 6 - d.retours_parts) as parts')
            ->groupBy('d.produit_id', 'jour')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->produit_id . '|' . $r->jour => (int) $r->parts]);

        $comptoir = DB::table('depot_ventes')
            ->where('boulangerie_id', $boulangerieId)
            ->whereDate('date_vente', '<', $aujourdhui)
            ->selectRaw('produit_id, DATE(date_vente) as jour, SUM(parts) as parts')
            ->groupBy('produit_id', 'jour')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->produit_id . '|' . $r->jour => (int) $r->parts]);

        // Pain donné aux abonnés : il sort de la production du jour.
        $abonnes = DB::table('consommations_abonnes as c')
            ->join('clients_abonnes as a', 'a.id', '=', 'c.client_abonne_id')
            ->where('a.boulangerie_id', $boulangerieId)
            ->whereDate('c.date_consommation', '<', $aujourdhui)
            ->selectRaw('c.produit_id as produit_id, DATE(c.date_consommation) as jour, SUM(c.quantite) * 6 as parts')
            ->groupBy('c.produit_id', 'jour')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->produit_id . '|' . $r->jour => (int) $r->parts]);

        $nonVendu = 0;
        foreach ($produit as $p) {
            $cle    = $p->produit_id . '|' . $p->jour;
            $reste  = (int) $p->parts - ($distribue[$cle] ?? 0) - ($comptoir[$cle] ?? 0) - ($abonnes[$cle] ?? 0);
            $nonVendu += max(0, $reste);
        }

        $entrees = $restantsLivreurs + $restantsClients + $nonVendu;

        // ── Sorties (sacs vendus, estimées) et ajustements ───────────────────────
        $sortiesSacs = (int) DB::table('ventes_restant')->where('boulangerie_id', $boulangerieId)->sum('pains_estimes') * 6;
        $ajustements = (int) DB::table('ajustements_restant')->where('boulangerie_id', $boulangerieId)->sum('ecart_parts');

        $brut  = $entrees - $sortiesSacs + $ajustements;
        $stock = max(0, $brut);

        return [
            'restants_livreurs' => $restantsLivreurs,
            'restants_clients'  => $restantsClients,
            'non_vendu'         => $nonVendu,
            'entrees'           => $entrees,
            'sorties_sacs'      => $sortiesSacs,
            'ajustements'       => $ajustements,
            'brut'              => $brut,
            'stock'             => $stock,
            // Stock estimé épuisé alors qu'il y a eu du mouvement : il faut régler (compter et ajuster).
            'a_ajuster'         => $brut <= 0 && ($entrees > 0 || $sortiesSacs > 0),
        ];
    }

    /**
     * Ventes de sacs sur une période : [nombre de sacs, montant].
     *
     * @return array{sacs:int, montant:float}
     */
    public static function ventes(int $boulangerieId, string $du, string $au): array
    {
        $r = DB::table('ventes_restant')
            ->where('boulangerie_id', $boulangerieId)
            ->whereBetween('date_vente', [$du, $au])
            ->selectRaw('COALESCE(SUM(nombre_sacs), 0) as sacs, COALESCE(SUM(montant), 0) as montant')
            ->first();

        return ['sacs' => (int) $r->sacs, 'montant' => (float) $r->montant];
    }
}
