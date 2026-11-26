<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\AchatMatierePremiere;
use App\Models\MatierePremiere;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Stock initial : le stock déjà présent quand on installe l'application (ou pour un
 * nouveau produit). Il entre dans le stock avec sa valeur, mais ne crée AUCUNE dépense
 * (rien n'est acheté). Réservé aux produits sans stock ni historique : ensuite, tout
 * ajout de stock passe par le module Achats.
 */
class StockInitialController extends Controller
{
    private function produitsEligibles()
    {
        return MatierePremiere::where('boulangerie_id', auth()->user()->boulangerie_id)
            ->get()
            ->filter(fn (MatierePremiere $m) => $m->peutRecevoirStockInitial())
            ->sortBy(fn (MatierePremiere $m) => $m->ordre())
            ->values();
    }

    public function index(): View
    {
        return view('stock-initial.index', ['produits' => $this->produitsEligibles()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $boulangerieId = auth()->user()->boulangerie_id;
        $quantites = (array) $request->input('quantite', []);
        $valeurs   = (array) $request->input('valeur', []);

        $eligibles = $this->produitsEligibles()->keyBy('id');

        // Lignes remplies (quantité et/ou valeur) : les deux sont alors obligatoires.
        $lignes = [];
        foreach ($eligibles as $id => $mp) {
            $q = trim((string) ($quantites[$id] ?? ''));
            $v = trim((string) ($valeurs[$id] ?? ''));

            if ($q === '' && $v === '') {
                continue;
            }
            if (! is_numeric($q) || (float) $q <= 0 || ! is_numeric($v) || (float) $v <= 0) {
                return redirect()->route('stock-initial.index')
                    ->withInput()
                    ->with('error', 'Pour ' . $mp->nom . ', indiquez la quantité et la valeur du stock.');
            }
            if ($mp->retrait_manuel && fmod((float) $q, 1.0) !== 0.0) {
                return redirect()->route('stock-initial.index')
                    ->withInput()
                    ->with('error', 'Pour ' . $mp->nom . ', saisissez un nombre entier de ' . $mp->uniteLibelle(2) . '.');
            }

            $lignes[$id] = ['quantite' => (float) $q, 'valeur' => (float) $v];
        }

        if (empty($lignes)) {
            return redirect()->route('stock-initial.index')->with('error', 'Remplissez au moins un produit.');
        }

        DB::transaction(function () use ($lignes, $boulangerieId) {
            foreach ($lignes as $id => $l) {
                $mp = MatierePremiere::where('id', $id)->where('boulangerie_id', $boulangerieId)->lockForUpdate()->first();
                // Revérifié après verrouillage : le produit a pu recevoir du stock entre-temps.
                if (! $mp || ! $mp->peutRecevoirStockInitial()) {
                    continue;
                }

                AchatMatierePremiere::create([
                    'matiere_premiere_id' => $mp->id,
                    'quantite'            => $l['quantite'],
                    'montant'             => $l['valeur'],
                    'date_achat'          => now()->toDateString(),
                    'depart'              => true,
                ]);

                $mp->ajouterAuStock($l['quantite'], $l['valeur']);
                $mp->save();

                ActivityLog::record(
                    'stock_depart',
                    auth()->user()->name . ' a enregistré un stock initial : ' . $l['quantite'] . ' ' . $mp->uniteLibelle($l['quantite']) . ' de ' . $mp->nom,
                    $boulangerieId,
                    'stock'
                );
            }
        });

        $n = count($lignes);

        return redirect()->route('matieres-premieres.index')
            ->with('success', 'Stock initial enregistré pour ' . $n . ' produit' . ($n > 1 ? 's' : '') . '.');
    }
}
