<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Dépenses d'une période pour le bilan, calculées sur ce qui est CONSOMMÉ et non sur ce
 * qui est acheté :
 *
 *   dépenses = matières consommées (valorisées au prix moyen, enregistrées à chaque
 *              production et à chaque « bidon/sac terminé »)
 *            + écarts d'inventaire (pertes de stock, moins les gains)
 *            + autres dépenses (salaires, eau, électricité, transport, réparation, autres…)
 *
 *   bénéfice = recettes − dépenses
 *
 * Les achats de stock restent dans la page Dépenses (argent sorti) mais n'entrent pas dans
 * le bénéfice : ce qui est acheté et pas encore consommé est du stock, pas un coût.
 */
class BilanCalcul
{
    // Catégories de dépense créées automatiquement par un achat de stock : remplacées, dans le
    // bilan, par la consommation. (achat_stock = produit créé à la main : reste en « Autres ».)
    public const ACHATS_STOCK = [
        'achat_farine', 'achat_levure', 'achat_produit', 'achat_sel', 'achat_huile', 'achat_gasoil',
    ];

    /**
     * Recettes d'une période : argent encaissé (versements des livreurs, clients et vendeurs,
     * factures d'abonnés payées, anciennes ventes Boutique, ventes de pain restant).
     *
     * @return array<string, float>
     */
    public static function recettes(int $boulangerieId, Carbon $debut, Carbon $fin): array
    {
        $versements = (float) \App\Models\Versement::whereHas(
            'livreur', fn ($q) => $q->where('boulangerie_id', $boulangerieId)
        )->whereBetween('date_versement', [$debut, $fin])->sum('montant_verse');

        // Basé sur les paiements réellement reçus (pas sur le statut de la facture).
        $factures = (float) \App\Models\PaiementFacture::whereHas(
            'facture.clientAbonne', fn ($q) => $q->where('boulangerie_id', $boulangerieId)
        )->whereBetween('date_paiement', [$debut, $fin])->sum('montant');

        $depot = (float) \App\Models\DepotVente::where('boulangerie_id', $boulangerieId)
            ->whereBetween('date_vente', [$debut, $fin])->sum('montant');

        $restant = (float) \App\Models\VenteRestant::where('boulangerie_id', $boulangerieId)
            ->whereBetween('date_vente', [$debut, $fin])->sum('montant');

        return [
            'versementsLivreurs' => $versements,
            'facturesAbonnes'    => $factures,
            'ventesDepot'        => $depot,
            'ventesRestant'      => $restant,
            'totalRecettes'      => $versements + $factures + $depot + $restant,
        ];
    }

    /**
     * @return array<string, float>
     */
    public static function depenses(int $boulangerieId, Carbon $debut, Carbon $fin): array
    {
        // ── Matières consommées ──────────────────────────────────────────────
        $prod = DB::table('productions')
            ->where('boulangerie_id', $boulangerieId)
            ->whereBetween('date_production', [$debut->toDateString(), $fin->toDateString()])
            ->selectRaw('COALESCE(SUM(cout_farine),0) as f, COALESCE(SUM(cout_levure),0) as l, COALESCE(SUM(cout_produit),0) as p')
            ->first();

        $retraits = DB::table('retraits_stock as r')
            ->join('matieres_premieres as m', 'm.id', '=', 'r.matiere_premiere_id')
            ->where('m.boulangerie_id', $boulangerieId)
            ->whereBetween('r.date_retrait', [$debut->toDateString(), $fin->toDateString()])
            ->selectRaw('m.nom as nom, COALESCE(SUM(r.cout),0) as cout')
            ->groupBy('m.nom')
            ->pluck('cout', 'nom');

        $consoFarine  = (float) $prod->f;
        $consoLevure  = (float) $prod->l;
        $consoProduit = (float) $prod->p;
        $consoSel     = (float) ($retraits['Sel'] ?? 0);
        $consoHuile   = (float) ($retraits['Huile'] ?? 0);
        $consoGasoil  = (float) ($retraits['Gasoil'] ?? 0);

        // ── Écarts d'inventaire : positif = perte, négatif = gain ───────────
        $ecarts = -1 * (float) DB::table('inventaires_stock')
            ->where('boulangerie_id', $boulangerieId)
            ->whereBetween('date_inventaire', [$debut->toDateString(), $fin->toDateString()])
            ->sum('valeur_ecart');

        // ── Autres dépenses (par catégorie) et achats de stock (information) ─
        $d = DB::table('depenses')
            ->where('boulangerie_id', $boulangerieId)
            ->whereBetween('date_depense', [$debut->toDateString(), $fin->toDateString()])
            ->selectRaw('categorie, SUM(montant) as total')
            ->groupBy('categorie')
            ->pluck('total', 'categorie');

        $achatsStock = 0.0;
        foreach (self::ACHATS_STOCK as $cat) {
            $achatsStock += (float) ($d[$cat] ?? 0);
        }

        $salaireGerant  = (float) (($d['salaire_gerant'] ?? 0) + ($d['salaire'] ?? 0));
        $salaireEmploye = (float) ($d['salaire_employe'] ?? 0);
        $eau            = (float) ($d['eau'] ?? 0);
        $electricite    = (float) ($d['electricite'] ?? 0);
        $carburant      = (float) ($d['carburant'] ?? 0);
        $transport      = (float) ($d['transport'] ?? 0);
        $reparation     = (float) (($d['reparation'] ?? 0) + ($d['entretien'] ?? 0));
        // « Autres » : divers, anciennes catégories (sel/huile saisis à la main avant le module
        // Stock) et achats d'un produit créé à la main (non consommé automatiquement).
        $autres         = (float) (($d['autre'] ?? 0) + ($d['divers'] ?? 0)
                                + ($d['huile'] ?? 0) + ($d['sel'] ?? 0)
                                + ($d['achat_stock'] ?? 0));

        $totalConso = $consoFarine + $consoLevure + $consoProduit + $consoSel + $consoHuile + $consoGasoil;
        $autresTotal = $salaireGerant + $salaireEmploye + $eau + $electricite + $carburant + $transport + $reparation + $autres;

        return [
            'consoFarine'    => $consoFarine,
            'consoLevure'    => $consoLevure,
            'consoProduit'   => $consoProduit,
            'consoSel'       => $consoSel,
            'consoHuile'     => $consoHuile,
            'consoGasoil'    => $consoGasoil,
            'totalConso'     => $totalConso,
            'ecartsStock'    => $ecarts,
            'achatsStock'    => $achatsStock,
            'salaireGerant'  => $salaireGerant,
            'salaireEmploye' => $salaireEmploye,
            'eau'            => $eau,
            'electricite'    => $electricite,
            'carburant'      => $carburant,
            'transport'      => $transport,
            'reparation'     => $reparation,
            'autres'         => $autres,
            'totalDepenses'  => $totalConso + $ecarts + $autresTotal,
        ];
    }
}
