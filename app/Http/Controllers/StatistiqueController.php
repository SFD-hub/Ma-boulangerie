<?php

namespace App\Http\Controllers;

use App\Models\ClientAbonne;
use App\Models\Depense;
use App\Models\Distribution;
use App\Models\Livreur;
use App\Models\MatierePremiere;
use App\Models\PaiementFacture;
use App\Models\Production;
use App\Models\Versement;
use Carbon\Carbon;
use Illuminate\View\View;

class StatistiqueController extends Controller
{
    public function index(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        $debutMois      = now()->copy()->startOfMonth();
        $finMois        = now()->copy()->endOfMonth();
        $moisNomCourant = ucfirst(now()->locale('fr')->monthName);

        // ── 1. PRODUCTION (cumulatif total) ────────────────────────────────
        $totalPainsProduits = (int) Production::where('boulangerie_id', $boulangerie_id)
            ->sum('nombre_pains_produits');

        $totalPainsDistribues = (int) Distribution::whereHas(
            'livreur', fn ($q) => $q->where('boulangerie_id', $boulangerie_id)
        )->sum('nombre_pains');

        $totalInvendus = (int) Distribution::whereHas(
            'livreur', fn ($q) => $q->where('boulangerie_id', $boulangerie_id)
        )->sum('nombre_invendus');

        $totalPainsVendus = max(0, $totalPainsDistribues - $totalInvendus);

        $tauxVente = $totalPainsDistribues > 0
            ? round(($totalPainsVendus / $totalPainsDistribues) * 100)
            : 0;

        // ── 2. FINANCES DU MOIS (mois courant) ─────────────────────────────
        $versementsMois = (float) Versement::whereHas(
            'livreur', fn ($q) => $q->where('boulangerie_id', $boulangerie_id)
        )->whereBetween('date_versement', [$debutMois, $finMois])->sum('montant_verse');

        $facturesMois = (float) PaiementFacture::whereHas(
            'facture.clientAbonne', fn ($q) => $q->where('boulangerie_id', $boulangerie_id)
        )->whereBetween('date_paiement', [$debutMois, $finMois])
         ->sum('montant');

        $recettesMois = $versementsMois + $facturesMois;

        $depensesMois = (float) Depense::where('boulangerie_id', $boulangerie_id)
            ->whereBetween('date_depense', [$debutMois, $finMois])
            ->sum('montant');

        $resultatMois = $recettesMois - $depensesMois;

        // ── 3. MEILLEUR LIVREUR DU MOIS ────────────────────────────────────
        $livreursMois = Livreur::where('boulangerie_id', $boulangerie_id)
            ->with([
                'distributions' => fn ($q) => $q->whereBetween('date_distribution', [$debutMois, $finMois]),
                'versements'    => fn ($q) => $q->whereBetween('date_versement', [$debutMois, $finMois]),
            ])
            ->get()
            ->map(fn ($livreur) => [
                'livreur'         => $livreur,
                'pains_vendus'    => (int) $livreur->distributions->sum(
                    fn ($d) => max(0, (int) ($d->nombre_pains ?? 0) - (int) ($d->nombre_invendus ?? 0))
                ),
                'montant_encaisse'=> (float) $livreur->versements->sum('montant_verse'),
            ])
            ->sortByDesc('pains_vendus');

        $meilleursLivreur = $livreursMois->first();

        // ── 4. LIVREUR À SURVEILLER (reliquat total le plus élevé) ─────────
        $livreursParReliquat = Livreur::where('boulangerie_id', $boulangerie_id)
            ->with(['distributions' => fn ($q) => $q->where('reliquat', '>', 0)])
            ->get()
            ->map(fn ($livreur) => [
                'livreur'        => $livreur,
                'reliquat_total' => (float) $livreur->distributions->sum('reliquat'),
            ])
            ->filter(fn ($item) => $item['reliquat_total'] > 0)
            ->sortByDesc('reliquat_total');

        $livreurASurveiller = $livreursParReliquat->first();

        // ── 5. TOP 5 ABONNÉS (par consommation totale) ─────────────────────
        $topAbonnes = ClientAbonne::where('boulangerie_id', $boulangerie_id)
            ->withSum('consommations as total_pains', 'quantite')
            ->orderByDesc('total_pains')
            ->limit(5)
            ->get();

        // ── 6. ÉVOLUTION MENSUELLE (6 derniers mois, ordre chronologique) ──
        $evolutionsMensuelles = Production::where('boulangerie_id', $boulangerie_id)
            ->selectRaw('YEAR(date_production) as annee, MONTH(date_production) as mois, SUM(nombre_pains_produits) as total_pains')
            ->groupByRaw('YEAR(date_production), MONTH(date_production)')
            ->orderByRaw('annee DESC, mois DESC')
            ->limit(6)
            ->get()
            ->reverse()
            ->values();

        // ── 7. ALERTES ─────────────────────────────────────────────────────
        $distributionsEnAttente = (int) Distribution::whereHas(
            'livreur', fn ($q) => $q->where('boulangerie_id', $boulangerie_id)
        )->where('statut', 'en_attente')->count();

        $totalReliquats = (float) Distribution::whereHas(
            'livreur', fn ($q) => $q->where('boulangerie_id', $boulangerie_id)
        )->where('reliquat', '>', 0)->sum('reliquat');

        $farine      = MatierePremiere::where('boulangerie_id', $boulangerie_id)->where('nom', 'Farine')->first();
        $levure      = MatierePremiere::where('boulangerie_id', $boulangerie_id)->where('nom', 'Levure')->first();
        $farineFaible = $farine && $farine->stock_actuel <= $farine->seuil_alerte;
        $levureFaible = $levure && $levure->stock_actuel <= $levure->seuil_alerte;

        return view('statistiques.index', compact(
            'moisNomCourant',
            'totalPainsProduits', 'totalPainsDistribues', 'totalPainsVendus', 'totalInvendus', 'tauxVente',
            'recettesMois', 'depensesMois', 'resultatMois',
            'meilleursLivreur',
            'livreurASurveiller',
            'topAbonnes',
            'evolutionsMensuelles',
            'distributionsEnAttente', 'totalReliquats', 'farine', 'levure', 'farineFaible', 'levureFaible'
        ));
    }
}
