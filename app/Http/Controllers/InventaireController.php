<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\InventaireStock;
use App\Models\MatierePremiere;
use App\Support\Nombre;
use App\Support\Periode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Inventaire du stock : on compte ce qu'il y a réellement, et l'application remplace son
 * stock théorique par ce chiffre. L'écart (perte ou gain) est valorisé au prix moyen,
 * enregistré (date, auteur) et pèse sur le bénéfice du mois (« écarts d'inventaire »).
 */
class InventaireController extends Controller
{
    public function index(): View
    {
        $boulangerieId = auth()->user()->boulangerie_id;

        $produits = MatierePremiere::where('boulangerie_id', $boulangerieId)
            ->get()
            ->sortBy(fn (MatierePremiere $m) => $m->ordre())
            ->values();

        // Les 3 derniers écarts ; l'historique complet est dans « Voir plus ».
        $historique = InventaireStock::where('boulangerie_id', $boulangerieId)
            ->with('matierePremiere')
            ->orderByDesc('date_inventaire')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        return view('inventaire.index', compact('produits', 'historique'));
    }

    // Historique des écarts : sélecteur de période, 10 par page, total de la période.
    public function historique(Request $request): View
    {
        $boulangerieId = auth()->user()->boulangerie_id;
        [$du, $au, $periodeLimitee] = Periode::depuis($request);

        // L'écran n'affiche que les quantités ; la valeur de chaque écart reste enregistrée
        // et continue d'alimenter le bilan (« Écarts d'inventaire »).
        $historique = InventaireStock::where('boulangerie_id', $boulangerieId)
            ->whereBetween('date_inventaire', [$du, $au])
            ->with('matierePremiere')
            ->orderByDesc('date_inventaire')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('inventaire.historique', compact('historique', 'du', 'au', 'periodeLimitee'));
    }

    public function store(Request $request): RedirectResponse
    {
        $boulangerieId = auth()->user()->boulangerie_id;

        $comptes = (array) $request->input('compte', []);
        $note    = $request->input('note');

        // Seuls les produits dont une quantité est saisie sont comptés.
        $saisies = [];
        foreach ($comptes as $id => $valeur) {
            if ($valeur === null || trim((string) $valeur) === '') {
                continue;
            }
            if (! is_numeric($valeur) || (float) $valeur < 0) {
                return redirect()->route('inventaire.index')->with('error', 'Les quantités comptées doivent être des nombres positifs.');
            }
            $saisies[(int) $id] = (float) $valeur;
        }

        if (empty($saisies)) {
            return redirect()->route('inventaire.index')->with('error', 'Saisissez au moins une quantité comptée.');
        }

        $ecarts = 0;

        $erreur = DB::transaction(function () use ($saisies, $boulangerieId, $note, &$ecarts) {
            foreach ($saisies as $id => $compte) {
                $mp = MatierePremiere::where('id', $id)->where('boulangerie_id', $boulangerieId)->lockForUpdate()->first();
                if (! $mp) {
                    continue;
                }

                // Sel, huile, gasoil : comptés en sacs/bidons entiers.
                if ($mp->retrait_manuel && fmod($compte, 1.0) !== 0.0) {
                    return 'Pour ' . $mp->nom . ', saisissez un nombre entier de ' . $mp->uniteLibelle(2) . '.';
                }

                $inventaire = InventaireStock::enregistrer($mp, $compte, $note);
                if ($inventaire) {
                    $ecarts++;

                    ActivityLog::record(
                        'inventaire_stock',
                        auth()->user()->name . ' a compté ' . $mp->nom . ' : ' . Nombre::qte($inventaire->quantite_avant)
                            . ' → ' . Nombre::qte($compte) . ' ' . $mp->uniteLibelle($compte),
                        $boulangerieId,
                        'stock'
                    );
                }
            }

            return null;
        });

        if ($erreur) {
            return redirect()->route('inventaire.index')->with('error', $erreur);
        }

        return redirect()->route('inventaire.index')->with('success', $ecarts > 0
            ? 'Inventaire enregistré : ' . $ecarts . ' écart' . ($ecarts > 1 ? 's' : '') . ' corrigé' . ($ecarts > 1 ? 's' : '') . '.'
            : 'Inventaire enregistré : aucun écart, le stock est conforme.');
    }
}
