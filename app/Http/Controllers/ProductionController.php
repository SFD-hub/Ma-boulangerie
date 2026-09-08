<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Production;
use App\Models\MatierePremiere;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProductionController extends Controller
{
    public function index(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        // Cette page n'affiche jamais plus de 5 productions (la dernière +
        // un aperçu de 4) : inutile de charger tout l'historique en mémoire.
        $productions = Production::where('boulangerie_id', $boulangerie_id)
            ->with('produit')
            ->orderBy('date_production', 'desc')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $totalPains = Production::where('boulangerie_id', $boulangerie_id)
            ->sum('nombre_pains_produits');

        return view('productions.index', compact('productions', 'totalPains'));
    }

    public function historique(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        $totalPains = (int) Production::where('boulangerie_id', $boulangerie_id)->sum('nombre_pains_produits');

        $productions = Production::where('boulangerie_id', $boulangerie_id)
            ->with('produit')
            ->orderBy('date_production', 'desc')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('productions.historique', compact('productions', 'totalPains'));
    }

    public function create(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        $produits = Produit::where('boulangerie_id', $boulangerie_id)
            ->where('actif', true)
            ->orderBy('nom')
            ->get();

        $defaultProduitId = Produit::defaultIdPour($boulangerie_id);

        return view('productions.create', compact('produits', 'defaultProduitId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        $validated = $request->validate([
            'produit_id' => ['required', Rule::exists('produits', 'id')->where('boulangerie_id', $boulangerie_id)],
            'date_production' => 'required|date|before_or_equal:today',
            'nombre_sacs' => 'required|numeric|min:0.5|max:999999.99',
            'nombre_paquets_levure' => 'required|numeric|min:0.5|max:999999.99',
            'nombre_pains_produits' => 'required|integer|min:1',
        ], [
            'produit_id.required' => 'Le produit est obligatoire.',
            'date_production.required' => 'La date de production est obligatoire.',
            'date_production.date' => 'La date doit être une date valide.',
            'date_production.before_or_equal' => 'La date ne peut pas être dans le futur.',
            'nombre_sacs.required' => 'Le nombre de sacs est obligatoire.',
            'nombre_sacs.numeric' => 'Le nombre de sacs doit être un nombre (les demi-sacs sont acceptés, ex: 5.5).',
            'nombre_sacs.min' => 'Le nombre de sacs doit être au moins 0,5.',
            'nombre_sacs.max' => 'Le nombre de sacs est trop élevé.',
            'nombre_paquets_levure.required' => 'Le nombre de paquets de levure est obligatoire.',
            'nombre_paquets_levure.numeric' => 'Le nombre de paquets de levure doit être un nombre (les demi-paquets sont acceptés).',
            'nombre_paquets_levure.min' => 'Le nombre de paquets de levure doit être au moins 0,5.',
            'nombre_paquets_levure.max' => 'Le nombre de paquets de levure est trop élevé.',
            'nombre_pains_produits.required' => 'Le nombre de pains produits est obligatoire.',
            'nombre_pains_produits.integer' => 'Le nombre de pains doit être un nombre entier.',
            'nombre_pains_produits.min' => 'Le nombre de pains doit être au moins 1.',
        ]);

        $quantite_farine_necessaire = $validated['nombre_sacs'];
        $quantite_levure_necessaire = $validated['nombre_paquets_levure'];

        $resultat = DB::transaction(function () use ($validated, $boulangerie_id, $quantite_farine_necessaire, $quantite_levure_necessaire) {
            // Verrouille les deux lignes de stock avant de vérifier la
            // suffisance : évite que deux productions saisies au même
            // instant ne lisent le même stock et ne s'écrasent l'une
            // l'autre à l'enregistrement.
            $farine = MatierePremiere::where('boulangerie_id', $boulangerie_id)
                ->where('nom', 'Farine')
                ->lockForUpdate()
                ->first();

            $levure = MatierePremiere::where('boulangerie_id', $boulangerie_id)
                ->where('nom', 'Levure')
                ->lockForUpdate()
                ->first();

            if (!$farine) {
                return ['ok' => false, 'message' => 'Matière première "Farine" introuvable. Vérifiez les matières premières.'];
            }

            if ($farine->stock_actuel < $quantite_farine_necessaire) {
                return ['ok' => false, 'message' => 'Stock de farine insuffisant (disponible : ' . $farine->stock_actuel . ' sacs, nécessaire : ' . $quantite_farine_necessaire . ' sacs).'];
            }

            if (!$levure) {
                return ['ok' => false, 'message' => 'Matière première "Levure" introuvable. Vérifiez les matières premières.'];
            }

            if ($levure->stock_actuel < $quantite_levure_necessaire) {
                return ['ok' => false, 'message' => 'Stock de levure insuffisant (disponible : ' . $levure->stock_actuel . ' paquets, nécessaire : ' . $quantite_levure_necessaire . ' paquets).'];
            }

            Production::create([
                'produit_id' => $validated['produit_id'],
                'date_production' => $validated['date_production'],
                'nombre_sacs' => $validated['nombre_sacs'],
                'quantite_farine' => $quantite_farine_necessaire,
                'quantite_levure' => $quantite_levure_necessaire,
                'nombre_pains_produits' => $validated['nombre_pains_produits'],
                'boulangerie_id' => $boulangerie_id,
            ]);

            $farine->stock_actuel -= $quantite_farine_necessaire;
            $farine->save();

            $levure->stock_actuel -= $quantite_levure_necessaire;
            $levure->save();

            ActivityLog::record(
                'production_enregistree',
                auth()->user()->name . ' a enregistré une production de ' . number_format($validated['nombre_pains_produits'], 0, ',', ' ') . ' pains',
                $boulangerie_id,
                'production'
            );

            return ['ok' => true];
        });

        if (!$resultat['ok']) {
            Log::warning('[Production.store] Échec', ['boulangerie_id' => $boulangerie_id, 'raison' => $resultat['message']]);

            return redirect()->back()->with('error', $resultat['message']);
        }

        return redirect()->route('productions.index')
            ->with('success', 'Production créée avec succès.');
    }

    public function show(Production $production): View
    {
        abort_if($production->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        return view('productions.show', compact('production'));
    }

    public function edit(Production $production): View
    {
        abort_if($production->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $produits = Produit::where('boulangerie_id', $production->boulangerie_id)
            ->where('actif', true)
            ->orderBy('nom')
            ->get();

        return view('productions.edit', compact('production', 'produits'));
    }

    public function update(Request $request, Production $production): RedirectResponse
    {
        abort_if($production->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $validated = $request->validate([
            'produit_id' => ['required', Rule::exists('produits', 'id')->where('boulangerie_id', $production->boulangerie_id)],
            'date_production' => 'required|date|before_or_equal:today',
            'nombre_sacs' => 'required|numeric|min:0.5|max:999999.99',
            'nombre_paquets_levure' => 'required|numeric|min:0.5|max:999999.99',
            'nombre_pains_produits' => 'required|integer|min:1',
        ], [
            'produit_id.required' => 'Le produit est obligatoire.',
            'date_production.required' => 'La date de production est obligatoire.',
            'date_production.date' => 'La date doit être une date valide.',
            'date_production.before_or_equal' => 'La date ne peut pas être dans le futur.',
            'nombre_sacs.required' => 'Le nombre de sacs est obligatoire.',
            'nombre_sacs.numeric' => 'Le nombre de sacs doit être un nombre (les demi-sacs sont acceptés, ex: 5.5).',
            'nombre_sacs.min' => 'Le nombre de sacs doit être au moins 0,5.',
            'nombre_sacs.max' => 'Le nombre de sacs est trop élevé.',
            'nombre_paquets_levure.required' => 'Le nombre de paquets de levure est obligatoire.',
            'nombre_paquets_levure.numeric' => 'Le nombre de paquets de levure doit être un nombre (les demi-paquets sont acceptés).',
            'nombre_paquets_levure.min' => 'Le nombre de paquets de levure doit être au moins 0,5.',
            'nombre_paquets_levure.max' => 'Le nombre de paquets de levure est trop élevé.',
            'nombre_pains_produits.required' => 'Le nombre de pains produits est obligatoire.',
            'nombre_pains_produits.integer' => 'Le nombre de pains doit être un nombre entier.',
            'nombre_pains_produits.min' => 'Le nombre de pains doit être au moins 1.',
        ]);

        $boulangerie_id = auth()->user()->boulangerie_id;

        $quantite_farine_necessaire = $validated['nombre_sacs'];
        $quantite_levure_necessaire = $validated['nombre_paquets_levure'];

        // Calculer les différences pour ajuster le stock
        $diffFarine = $quantite_farine_necessaire - $production->quantite_farine;
        $diffLevure = $quantite_levure_necessaire - $production->quantite_levure;

        $resultat = DB::transaction(function () use ($validated, $production, $boulangerie_id, $diffFarine, $diffLevure, $quantite_farine_necessaire, $quantite_levure_necessaire) {
            $farine = MatierePremiere::where('boulangerie_id', $boulangerie_id)
                ->where('nom', 'Farine')
                ->lockForUpdate()
                ->first();
            $levure = MatierePremiere::where('boulangerie_id', $boulangerie_id)
                ->where('nom', 'Levure')
                ->lockForUpdate()
                ->first();

            if ($farine->stock_actuel + $diffFarine < 0) {
                return ['ok' => false, 'message' => 'Stock de farine insuffisant pour cette mise à jour.'];
            }

            if ($levure->stock_actuel + $diffLevure < 0) {
                return ['ok' => false, 'message' => 'Stock de levure insuffisant pour cette mise à jour.'];
            }

            $production->update([
                'produit_id' => $validated['produit_id'],
                'date_production' => $validated['date_production'],
                'nombre_sacs' => $validated['nombre_sacs'],
                'quantite_farine' => $quantite_farine_necessaire,
                'quantite_levure' => $quantite_levure_necessaire,
                'nombre_pains_produits' => $validated['nombre_pains_produits'],
            ]);

            $farine->stock_actuel -= $diffFarine;
            $farine->save();

            $levure->stock_actuel -= $diffLevure;
            $levure->save();

            return ['ok' => true];
        });

        if (!$resultat['ok']) {
            return redirect()->back()->with('error', $resultat['message']);
        }

        return redirect()->route('productions.index')
            ->with('success', 'Production mise à jour avec succès.');
    }

    public function destroy(Production $production): RedirectResponse
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        abort_if($production->boulangerie_id !== $boulangerie_id, 403);

        DB::transaction(function () use ($production, $boulangerie_id) {
            $farine = MatierePremiere::where('boulangerie_id', $boulangerie_id)->where('nom', 'Farine')->lockForUpdate()->first();
            $levure = MatierePremiere::where('boulangerie_id', $boulangerie_id)->where('nom', 'Levure')->lockForUpdate()->first();

            if ($farine) {
                $farine->stock_actuel += $production->quantite_farine;
                $farine->save();
            }
            if ($levure) {
                $levure->stock_actuel += $production->quantite_levure;
                $levure->save();
            }
            $production->delete();
        });

        return redirect()->route('productions.index')
            ->with('success', 'Production supprimée et stock restauré.');
    }
}
