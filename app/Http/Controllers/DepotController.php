<?php

namespace App\Http\Controllers;

use App\Models\DepotVente;
use App\Models\Distribution;
use App\Models\Livreur;
use App\Models\Versement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Page Boutique : les ventes se font uniquement depuis la fiche d'un vendeur
 * (Enregistrer une vente, puis Verser). Cette page rassemble les vendeurs et
 * cumule ce qui a été vendu et encaissé dans la Boutique, tous vendeurs confondus.
 */
class DepotController extends Controller
{
    public function index(Request $request): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        $date = $request->query('date');
        $date = ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) ? $date : Carbon::today()->toDateString();

        // Anciennes ventes saisies directement au comptoir (avant les vendeurs) :
        // conservées dans les totaux et visibles, mais plus de saisie ici.
        $ventes = DepotVente::where('boulangerie_id', $boulangerie_id)
            ->whereDate('date_vente', $date)
            ->with('produit')
            ->orderByDesc('created_at')
            ->get();

        // Vendeurs de la Boutique : pains reçus ce jour-là et reste à encaisser
        // (somme des reliquats de toutes leurs distributions).
        $vendeurs = Livreur::where('boulangerie_id', $boulangerie_id)
            ->where('type', 'vendeur')
            ->where('actif', true)
            ->orderBy('prenom')
            ->orderBy('nom')
            ->get();

        $painsDuJour = Distribution::whereIn('livreur_id', $vendeurs->pluck('id'))
            ->whereDate('date_distribution', $date)
            ->selectRaw('livreur_id, SUM(nombre_pains * 6 - retours_parts) / 6 as total')
            ->groupBy('livreur_id')
            ->pluck('total', 'livreur_id');

        $resteAEncaisser = Distribution::whereIn('livreur_id', $vendeurs->pluck('id'))
            ->where('reliquat', '>', 0)
            ->selectRaw('livreur_id, SUM(reliquat) as total')
            ->groupBy('livreur_id')
            ->pluck('total', 'livreur_id');

        // ── Cumul Boutique du jour (tous les vendeurs) ──────────────────────────
        // Pains vendus : pour chaque distribution du jour d'un vendeur, ce qui est
        // réglé (donnés − invendus) si le versement est fait, sinon ce qu'il a
        // saisi au fil de la journée. S'y ajoutent les anciennes ventes comptoir.
        $distributionsJour = Distribution::whereHas('livreur', fn ($q) => $q
                ->where('boulangerie_id', $boulangerie_id)->where('type', 'vendeur'))
            ->whereDate('date_distribution', $date)
            ->with('versement')
            ->withSum('ventesVendeur as parts_saisies', 'parts')
            ->get();

        $partsVendeurs = $distributionsJour->sum(
            fn (Distribution $d) => $d->versement ? $d->parts_vendues : (int) $d->parts_saisies
        );

        $totalQuantite = ($partsVendeurs + (int) $ventes->sum('parts')) / 6;

        // Argent encaissé : versements reçus ce jour-là des vendeurs + anciennes
        // ventes comptoir (même total que « Encaissé du jour »).
        $versementsVendeurs = (float) Versement::whereHas('livreur', fn ($q) => $q
                ->where('boulangerie_id', $boulangerie_id)->where('type', 'vendeur'))
            ->whereDate('date_versement', $date)
            ->sum('montant_verse');

        $totalMontant = $versementsVendeurs + (float) $ventes->sum('montant');

        return view('depot.index', compact(
            'date', 'ventes', 'totalQuantite', 'totalMontant',
            'vendeurs', 'painsDuJour', 'resteAEncaisser'
        ));
    }

    public function destroy(DepotVente $depotVente): RedirectResponse
    {
        abort_if($depotVente->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $date = $depotVente->date_vente->toDateString();
        $depotVente->delete();

        return redirect()->route('depot.index', ['date' => $date])
            ->with('success', 'Vente supprimée.');
    }
}
