<?php

namespace App\Http\Controllers;

use App\Models\Production;
use App\Models\MatierePremiere;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductionController extends Controller
{
    public function index(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        
        $productions = Production::where('boulangerie_id', $boulangerie_id)
            ->orderBy('date_production', 'desc')
            ->get();

        $today = Carbon::today();
        $productionDuJour = Production::where('boulangerie_id', $boulangerie_id)
            ->whereDate('date_production', $today)
            ->first();

        $totalPains = Production::where('boulangerie_id', $boulangerie_id)
            ->sum('nombre_pains_produits');

        return view('productions.index', compact('productions', 'productionDuJour', 'totalPains'));
    }

    public function historique(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        $productions = Production::where('boulangerie_id', $boulangerie_id)
            ->orderBy('date_production', 'desc')
            ->orderByDesc('id')
            ->get();

        $totalPains = $productions->sum('nombre_pains_produits');

        return view('productions.historique', compact('productions', 'totalPains'));
    }

    public function create(): View
    {
        return view('productions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date_production' => 'required|date|before_or_equal:today',
            'nombre_sacs' => 'required|integer|min:1',
            'nombre_paquets_levure' => 'required|integer|min:1',
            'nombre_pains_produits' => 'required|integer|min:1',
        ], [
            'date_production.required' => 'La date de production est obligatoire.',
            'date_production.date' => 'La date doit être une date valide.',
            'date_production.before_or_equal' => 'La date ne peut pas être dans le futur.',
            'nombre_sacs.required' => 'Le nombre de sacs est obligatoire.',
            'nombre_sacs.integer' => 'Le nombre de sacs doit être un nombre entier.',
            'nombre_sacs.min' => 'Le nombre de sacs doit être au moins 1.',
            'nombre_paquets_levure.required' => 'Le nombre de paquets de levure est obligatoire.',
            'nombre_paquets_levure.integer' => 'Le nombre de paquets de levure doit être un nombre entier.',
            'nombre_paquets_levure.min' => 'Le nombre de paquets de levure doit être au moins 1.',
            'nombre_pains_produits.required' => 'Le nombre de pains produits est obligatoire.',
            'nombre_pains_produits.integer' => 'Le nombre de pains doit être un nombre entier.',
            'nombre_pains_produits.min' => 'Le nombre de pains doit être au moins 1.',
        ]);

        $boulangerie_id = auth()->user()->boulangerie_id;

        Log::info('[Production.store] Début', ['boulangerie_id' => $boulangerie_id, 'data' => $validated]);

        $quantite_farine_necessaire = $validated['nombre_sacs'];
        $quantite_levure_necessaire = $validated['nombre_paquets_levure'];

        $farine = MatierePremiere::where('boulangerie_id', $boulangerie_id)
            ->where('nom', 'Farine')
            ->first();

        $levure = MatierePremiere::where('boulangerie_id', $boulangerie_id)
            ->where('nom', 'Levure')
            ->first();

        Log::info('[Production.store] Matières', [
            'farine_trouvee' => $farine ? 'OUI (stock=' . $farine->stock_actuel . ')' : 'NON',
            'levure_trouvee' => $levure ? 'OUI (stock=' . $levure->stock_actuel . ')' : 'NON',
            'farine_necessaire' => $quantite_farine_necessaire,
            'levure_necessaire' => $quantite_levure_necessaire,
        ]);

        if (!$farine) {
            Log::warning('[Production.store] ÉCHEC: matière "Farine" introuvable pour boulangerie_id=' . $boulangerie_id);
            return redirect()->back()
                ->with('error', 'Matière première "Farine" introuvable. Vérifiez les matières premières.');
        }

        if ($farine->stock_actuel < $quantite_farine_necessaire) {
            Log::warning('[Production.store] ÉCHEC: stock farine insuffisant', ['stock' => $farine->stock_actuel, 'besoin' => $quantite_farine_necessaire]);
            return redirect()->back()
                ->with('error', 'Stock de farine insuffisant (disponible : ' . $farine->stock_actuel . ' sacs, nécessaire : ' . $quantite_farine_necessaire . ' sacs).');
        }

        if (!$levure) {
            Log::warning('[Production.store] ÉCHEC: matière "Levure" introuvable pour boulangerie_id=' . $boulangerie_id);
            return redirect()->back()
                ->with('error', 'Matière première "Levure" introuvable. Vérifiez les matières premières.');
        }

        if ($levure->stock_actuel < $quantite_levure_necessaire) {
            Log::warning('[Production.store] ÉCHEC: stock levure insuffisant', ['stock' => $levure->stock_actuel, 'besoin' => $quantite_levure_necessaire]);
            return redirect()->back()
                ->with('error', 'Stock de levure insuffisant (disponible : ' . $levure->stock_actuel . ' paquets, nécessaire : ' . $quantite_levure_necessaire . ' paquets).');
        }

        Log::info('[Production.store] Stocks OK — démarrage transaction');

        DB::transaction(function () use ($validated, $boulangerie_id, $farine, $levure, $quantite_farine_necessaire, $quantite_levure_necessaire) {
            Production::create([
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

            Log::info('[Production.store] Transaction terminée avec succès');
        });

        return redirect()->route('productions.index')
            ->with('success', 'Production créée avec succès.');
    }

    public function show(Production $production): View
    {
        return view('productions.show', compact('production'));
    }

    public function edit(Production $production): View
    {
        return view('productions.edit', compact('production'));
    }

    public function update(Request $request, Production $production): RedirectResponse
    {
        $validated = $request->validate([
            'date_production' => 'required|date|before_or_equal:today',
            'nombre_sacs' => 'required|integer|min:1',
            'nombre_paquets_levure' => 'required|integer|min:1',
            'nombre_pains_produits' => 'required|integer|min:1',
        ], [
            'date_production.required' => 'La date de production est obligatoire.',
            'date_production.date' => 'La date doit être une date valide.',
            'date_production.before_or_equal' => 'La date ne peut pas être dans le futur.',
            'nombre_sacs.required' => 'Le nombre de sacs est obligatoire.',
            'nombre_sacs.integer' => 'Le nombre de sacs doit être un nombre entier.',
            'nombre_sacs.min' => 'Le nombre de sacs doit être au moins 1.',
            'nombre_paquets_levure.required' => 'Le nombre de paquets de levure est obligatoire.',
            'nombre_paquets_levure.integer' => 'Le nombre de paquets de levure doit être un nombre entier.',
            'nombre_paquets_levure.min' => 'Le nombre de paquets de levure doit être au moins 1.',
            'nombre_pains_produits.required' => 'Le nombre de pains produits est obligatoire.',
            'nombre_pains_produits.integer' => 'Le nombre de pains doit être un nombre entier.',
            'nombre_pains_produits.min' => 'Le nombre de pains doit être au moins 1.',
        ]);

        $boulangerie_id = auth()->user()->boulangerie_id;
        $farine = MatierePremiere::where('boulangerie_id', $boulangerie_id)
            ->where('nom', 'Farine')
            ->first();
        $levure = MatierePremiere::where('boulangerie_id', $boulangerie_id)
            ->where('nom', 'Levure')
            ->first();

        $quantite_farine_necessaire = $validated['nombre_sacs'];
        $quantite_levure_necessaire = $validated['nombre_paquets_levure'];

        // Calculer les différences pour ajuster le stock
        $diffFarine = $quantite_farine_necessaire - $production->quantite_farine;
        $diffLevure = $quantite_levure_necessaire - $production->quantite_levure;

        if ($farine->stock_actuel + $diffFarine < 0) {
            return redirect()->back()
                ->with('error', 'Stock de farine insuffisant pour cette mise à jour.');
        }

        if ($levure->stock_actuel + $diffLevure < 0) {
            return redirect()->back()
                ->with('error', 'Stock de levure insuffisant pour cette mise à jour.');
        }

        DB::transaction(function () use ($validated, $production, $farine, $levure, $diffFarine, $diffLevure, $quantite_farine_necessaire, $quantite_levure_necessaire) {
            $production->update([
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
        });

        return redirect()->route('productions.show', $production)
            ->with('success', 'Production mise à jour avec succès.');
    }

    public function destroy(Production $production): RedirectResponse
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        $farine = MatierePremiere::where('boulangerie_id', $boulangerie_id)->where('nom', 'Farine')->first();
        $levure = MatierePremiere::where('boulangerie_id', $boulangerie_id)->where('nom', 'Levure')->first();

        DB::transaction(function () use ($production, $farine, $levure) {
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
