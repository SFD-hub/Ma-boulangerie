<?php

namespace App\Http\Controllers;

use App\Models\InventaireStock;
use App\Models\MatierePremiere;
use App\Models\RetraitStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class MatierePremiereController extends Controller
{
    public function index(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        $matieresPremières = MatierePremiere::where('boulangerie_id', $boulangerie_id)
            ->with('achats')
            ->orderBy('nom')
            ->get()
            ->sortBy(fn (MatierePremiere $m) => $m->ordre())
            ->values();

        return view('matieres-premieres.index', compact('matieresPremières'));
    }

    public function create(): View
    {
        return view('matieres-premieres.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:matieres_premieres,nom,NULL,id,boulangerie_id,' . auth()->user()->boulangerie_id,
            'stock_actuel' => 'required|numeric|min:0|max:999999.99',
            'seuil_alerte' => 'required|numeric|min:0|max:999999.99',
        ], [
            'nom.required' => 'Le nom de la matière première est obligatoire.',
            'nom.unique' => 'Cette matière première existe déjà.',
            'nom.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'stock_actuel.required' => 'Le stock actuel est obligatoire.',
            'stock_actuel.numeric' => 'Le stock doit être un nombre (les demi-sacs sont acceptés).',
            'stock_actuel.min' => 'Le stock ne peut pas être négatif.',
            'stock_actuel.max' => 'Le stock est trop élevé.',
            'seuil_alerte.required' => 'Le seuil d\'alerte est obligatoire.',
            'seuil_alerte.numeric' => 'Le seuil doit être un nombre (les demi-sacs sont acceptés).',
            'seuil_alerte.min' => 'Le seuil ne peut pas être négatif.',
            'seuil_alerte.max' => 'Le seuil est trop élevé.',
        ]);

        $boulangerie_id = auth()->user()->boulangerie_id;

        MatierePremiere::create([
            'nom' => $validated['nom'],
            'stock_actuel' => $validated['stock_actuel'],
            'seuil_alerte' => $validated['seuil_alerte'],
            'boulangerie_id' => $boulangerie_id,
        ]);

        return redirect()->route('matieres-premieres.index')
            ->with('success', 'Matière première créée avec succès.');
    }

    // Page de détail d'un produit du stock : quantité, valeur, achats et,
    // pour sel/huile/gasoil, les validations « terminé ».
    public function show(MatierePremiere $matierePremiere): View
    {
        abort_if($matierePremiere->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        // Historique des achats (les 3 derniers) : seulement les vrais achats. Le stock
        // initial (sans dépense) est présenté à part.
        $achats = $matierePremiere->achats()
            ->where('depart', false)
            ->orderByDesc('date_achat')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        $stocksInitiaux = $matierePremiere->achats()
            ->where('depart', true)
            ->orderBy('date_achat')
            ->orderBy('id')
            ->get();

        $totaux = $matierePremiere->achats()
            ->selectRaw('COALESCE(SUM(quantite), 0) as quantite, COALESCE(SUM(montant), 0) as montant, COUNT(*) as nombre')
            ->first();

        // Prix moyen pondéré de toutes les unités achetées ; la valeur du stock
        // est une estimation (prix moyen × quantité restante).
        $prixMoyen = (float) $totaux->quantite > 0 ? (float) $totaux->montant / (float) $totaux->quantite : null;
        // Valeur réelle du stock suivie au prix moyen (achats, consommation, inventaire).
        $valeur    = $prixMoyen !== null ? (int) round((float) $matierePremiere->valeur_stock) : null;

        $dernierAchat = $achats->first();
        $dernierPrix  = $dernierAchat && (float) $dernierAchat->quantite > 0
            ? (float) $dernierAchat->montant / (float) $dernierAchat->quantite
            : null;

        // Dernière validation « terminé » (sel, huile, gasoil) : sert à la carte
        // « Dernier sac/bidon terminé » et au bouton Supprimer.
        $dernierRetrait = $matierePremiere->retrait_manuel
            ? $matierePremiere->retraits()->orderByDesc('date_retrait')->orderByDesc('id')->first()
            : null;

        return view('matieres-premieres.show', compact(
            'matierePremiere', 'achats', 'stocksInitiaux', 'totaux', 'prixMoyen', 'valeur', 'dernierRetrait'
        ));
    }

    public function edit(MatierePremiere $matierePremiere): View
    {
        abort_if($matierePremiere->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        return view('matieres-premieres.edit', compact('matierePremiere'));
    }

    public function update(Request $request, MatierePremiere $matierePremiere): RedirectResponse
    {
        abort_if($matierePremiere->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:matieres_premieres,nom,' . $matierePremiere->id . ',id,boulangerie_id,' . auth()->user()->boulangerie_id,
            'stock_actuel' => 'required|numeric|min:0|max:999999.99',
            'seuil_alerte' => 'required|numeric|min:0|max:999999.99',
        ], [
            'nom.required' => 'Le nom de la matière première est obligatoire.',
            'nom.unique' => 'Cette matière première existe déjà.',
            'nom.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'stock_actuel.required' => 'Le stock actuel est obligatoire.',
            'stock_actuel.numeric' => 'Le stock doit être un nombre (les demi-sacs sont acceptés).',
            'stock_actuel.min' => 'Le stock ne peut pas être négatif.',
            'stock_actuel.max' => 'Le stock est trop élevé.',
            'seuil_alerte.required' => 'Le seuil d\'alerte est obligatoire.',
            'seuil_alerte.numeric' => 'Le seuil doit être un nombre (les demi-sacs sont acceptés).',
            'seuil_alerte.min' => 'Le seuil ne peut pas être négatif.',
            'seuil_alerte.max' => 'Le seuil est trop élevé.',
        ]);

        DB::transaction(function () use ($matierePremiere, $validated) {
            $mp = MatierePremiere::where('id', $matierePremiere->id)->lockForUpdate()->firstOrFail();

            $mp->update([
                'nom' => $validated['nom'],
                'seuil_alerte' => $validated['seuil_alerte'],
            ]);

            // Une correction de stock à la main est un inventaire : l'écart est tracé et valorisé
            // (perte ou gain), il ne disparaît pas silencieusement.
            InventaireStock::enregistrer($mp, (float) $validated['stock_actuel']);
        });

        return redirect()->route('matieres-premieres.index')
            ->with('success', 'Matière première mise à jour avec succès.');
    }

    public function destroy(MatierePremiere $matierePremiere): RedirectResponse
    {
        abort_if($matierePremiere->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        // Vérifier s'il y a des achats associés
        if ($matierePremiere->achats()->count() > 0) {
            return redirect()->route('matieres-premieres.index')
                ->with('error', 'Impossible de supprimer cette matière première car elle contient des achats.');
        }

        $matierePremiere->delete();

        return redirect()->route('matieres-premieres.index')
            ->with('success', 'Matière première supprimée avec succès.');
    }
}
