<?php

namespace App\Http\Controllers;

use App\Models\Depense;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DepenseController extends Controller
{
    // Catégories saisies manuellement (les achats stock sont auto-créés)
    const CATEGORIES_MANUELLES = [
        'salaire_gerant'  => 'Salaire gérant',
        'salaire_employe' => 'Salaire employé',
        'eau'             => 'Eau',
        'electricite'     => 'Électricité',
        'transport'       => 'Transport',
        'carburant'       => 'Carburant',
        'reparation'      => 'Réparation / Maintenance',
        'autre'           => 'Autre',
    ];

    // Toutes les catégories (pour l'affichage, inclut les anciennes valeurs pour la rétro-compatibilité)
    const CATEGORIES_LIBELLES = [
        'achat_farine'   => 'Achat farine',
        'achat_levure'   => 'Achat levure',
        'salaire'        => 'Salaire gérant',
        'salaire_gerant' => 'Salaire gérant',
        'salaire_employe'=> 'Salaire employé',
        'eau'            => 'Eau',
        'electricite'    => 'Électricité',
        'transport'      => 'Transport',
        'carburant'      => 'Carburant',
        'reparation'     => 'Réparation / Maintenance',
        'entretien'      => 'Entretien',
        'divers'         => 'Divers',
        'autre'          => 'Autre',
    ];

    // Icônes par catégorie
    const CATEGORIES_ICONES = [
        'achat_farine'   => '🌾',
        'achat_levure'   => '🧪',
        'salaire'        => '👤',
        'salaire_gerant' => '👤',
        'salaire_employe'=> '👥',
        'eau'            => '💧',
        'electricite'    => '⚡',
        'transport'      => '🚗',
        'carburant'      => '⛽',
        'reparation'     => '🔧',
        'entretien'      => '🔧',
        'divers'         => '📌',
        'autre'          => '📌',
    ];

    public function index(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        $depenses = Depense::where('boulangerie_id', $boulangerie_id)
            ->orderByDesc('date_depense')
            ->orderByDesc('id')
            ->limit(7)
            ->get();

        $totalGlobal = (float) Depense::where('boulangerie_id', $boulangerie_id)->sum('montant');

        return view('depenses.index', compact('depenses', 'totalGlobal'));
    }

    public function historique(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        $depenses = Depense::where('boulangerie_id', $boulangerie_id)
            ->orderByDesc('date_depense')
            ->orderByDesc('id')
            ->get();

        $totalGlobal = (float) $depenses->sum('montant');

        return view('depenses.historique', compact('depenses', 'totalGlobal'));
    }

    public function create(): View
    {
        return view('depenses.create', ['categories' => self::CATEGORIES_MANUELLES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'libelle'     => 'nullable|string|max:255',
            'categorie'   => 'required|string|in:' . implode(',', array_keys(self::CATEGORIES_MANUELLES)),
            'montant'     => 'required|numeric|min:0.01',
            'date_depense'=> 'required|date|before_or_equal:today',
        ], [
            'categorie.required'    => 'La catégorie est obligatoire.',
            'categorie.in'          => 'Catégorie invalide.',
            'montant.required'      => 'Le montant est obligatoire.',
            'montant.min'           => 'Le montant doit être supérieur à 0.',
            'date_depense.required' => 'La date est obligatoire.',
            'date_depense.before_or_equal' => 'La date ne peut pas être dans le futur.',
        ]);

        Depense::create(array_merge($validated, [
            'boulangerie_id' => auth()->user()->boulangerie_id,
        ]));

        return redirect()->route('depenses.index')
            ->with('success', 'Dépense ajoutée.');
    }

    public function show(Depense $depense): View
    {
        abort_if($depense->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        return view('depenses.show', compact('depense'));
    }

    public function edit(Depense $depense): View
    {
        abort_if($depense->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        // Les achats automatiques (farine/levure) ne sont pas modifiables ici
        if (in_array($depense->categorie, ['achat_farine', 'achat_levure'])) {
            return redirect()->route('achats-matieres-premieres.index')
                ->with('warning', 'Modifiez cette dépense depuis le module Stock → Historique achats.');
        }

        return view('depenses.edit', [
            'depense'    => $depense,
            'categories' => self::CATEGORIES_MANUELLES,
        ]);
    }

    public function update(Request $request, Depense $depense): RedirectResponse
    {
        abort_if($depense->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        if (in_array($depense->categorie, ['achat_farine', 'achat_levure'])) {
            return redirect()->route('achats-matieres-premieres.index')
                ->with('warning', 'Modifiez cette dépense depuis le module Stock.');
        }

        $validated = $request->validate([
            'libelle'      => 'nullable|string|max:255',
            'categorie'    => 'required|string|in:' . implode(',', array_keys(self::CATEGORIES_MANUELLES)),
            'montant'      => 'required|numeric|min:0.01',
            'date_depense' => 'required|date|before_or_equal:today',
        ], [
            'categorie.in'                 => 'Catégorie invalide.',
            'montant.min'                  => 'Le montant doit être supérieur à 0.',
            'date_depense.before_or_equal' => 'La date ne peut pas être dans le futur.',
        ]);

        $depense->update($validated);

        return redirect()->route('depenses.index')
            ->with('success', 'Dépense modifiée.');
    }

    public function destroy(Depense $depense): RedirectResponse
    {
        abort_if($depense->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        if (in_array($depense->categorie, ['achat_farine', 'achat_levure'])) {
            return redirect()->route('depenses.index')
                ->with('warning', 'Supprimez cette dépense depuis le module Stock → Historique achats.');
        }

        $depense->delete();

        return redirect()->route('depenses.index')
            ->with('success', 'Dépense supprimée.');
    }
}
