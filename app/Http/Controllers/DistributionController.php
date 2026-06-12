<?php

namespace App\Http\Controllers;

use App\Models\DetailDistribution;
use App\Models\Distribution;
use App\Models\Livreur;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class DistributionController extends Controller
{
    public function index(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        $distributions = Distribution::whereHas('livreur', function ($query) use ($boulangerie_id) {
            $query->where('boulangerie_id', $boulangerie_id);
        })
            ->with('livreur', 'detailDistributions')
            ->orderBy('date_distribution', 'desc')
            ->get();

        return view('distributions.index', compact('distributions'));
    }

    public function create(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        $livreurs = Livreur::where('boulangerie_id', $boulangerie_id)
            ->where('actif', true)
            ->orderBy('nom')
            ->get();

        $produits = Produit::where('boulangerie_id', $boulangerie_id)
            ->orderBy('nom')
            ->get();

        return view('distributions.create', compact('livreurs', 'produits'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'livreur_id'                       => 'required|exists:livreurs,id',
            'date_distribution'                => 'required|date|before_or_equal:today',
            'montant_attendu'                  => 'required_without:produits|nullable|numeric|min:0',
            'produits'                         => 'nullable|array',
            'produits.*.produit_id'            => 'required_with:produits|exists:produits,id',
            'produits.*.quantite_attribuee'    => 'required_with:produits|integer|min:1',
            'produits.*.quantite_retournee'    => 'nullable|integer|min:0',
        ], [
            'livreur_id.required'              => 'Le livreur est obligatoire.',
            'date_distribution.required'       => 'La date de distribution est obligatoire.',
            'date_distribution.before_or_equal'=> 'La date ne peut pas être dans le futur.',
            'montant_attendu.required_without' => 'Saisissez un montant attendu ou ajoutez des produits.',
            'produits.*.quantite_attribuee.min'=> 'La quantité attribuée doit être au moins 1.',
        ]);

        $boulangerie_id = auth()->user()->boulangerie_id;

        $livreur = Livreur::find($validated['livreur_id']);
        if ($livreur->boulangerie_id !== $boulangerie_id) {
            return redirect()->back()->with('error', 'Livreur invalide.');
        }

        DB::transaction(function () use ($validated, $boulangerie_id) {
            $distribution = Distribution::create([
                'livreur_id'         => $validated['livreur_id'],
                'date_distribution'  => $validated['date_distribution'],
                'montant_attendu'    => $validated['montant_attendu'] ?? 0,
            ]);

            if (!empty($validated['produits'])) {
                $montantCalcule = 0;

                foreach ($validated['produits'] as $ligne) {
                    $produit    = Produit::find($ligne['produit_id']);
                    $retourne   = $ligne['quantite_retournee'] ?? 0;
                    $vendu      = max(0, $ligne['quantite_attribuee'] - $retourne);
                    $montantCalcule += $vendu * $produit->prix;

                    DetailDistribution::create([
                        'distribution_id'      => $distribution->id,
                        'produit_id'           => $ligne['produit_id'],
                        'quantite_attribuee'   => $ligne['quantite_attribuee'],
                        'quantite_retournee'   => $retourne,
                    ]);
                }

                // Montant attendu = calculé depuis les lignes produits
                $distribution->montant_attendu = round($montantCalcule, 2);
                $distribution->save();
            }
        });

        return redirect()->route('distributions.index')
            ->with('success', 'Distribution créée avec succès.');
    }

    public function show(Distribution $distribution): View
    {
        $distribution->load('livreur', 'detailDistributions.produit');

        $totalVerse  = $distribution->livreur->versements()->sum('montant_verse');
        $reliquat    = $distribution->livreur->distributions()->sum('montant_attendu') - $totalVerse;

        return view('distributions.show', compact('distribution', 'totalVerse', 'reliquat'));
    }

    public function edit(Distribution $distribution): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        $livreurs = Livreur::where('boulangerie_id', $boulangerie_id)
            ->where('actif', true)
            ->orderBy('nom')
            ->get();

        $produits = Produit::where('boulangerie_id', $boulangerie_id)
            ->orderBy('nom')
            ->get();

        $distribution->load('detailDistributions.produit');

        return view('distributions.edit', compact('distribution', 'livreurs', 'produits'));
    }

    public function update(Request $request, Distribution $distribution): RedirectResponse
    {
        $validated = $request->validate([
            'livreur_id'                       => 'required|exists:livreurs,id',
            'date_distribution'                => 'required|date|before_or_equal:today',
            'montant_attendu'                  => 'required_without:produits|nullable|numeric|min:0',
            'produits'                         => 'nullable|array',
            'produits.*.produit_id'            => 'required_with:produits|exists:produits,id',
            'produits.*.quantite_attribuee'    => 'required_with:produits|integer|min:1',
            'produits.*.quantite_retournee'    => 'nullable|integer|min:0',
        ], [
            'livreur_id.required'              => 'Le livreur est obligatoire.',
            'date_distribution.required'       => 'La date de distribution est obligatoire.',
            'date_distribution.before_or_equal'=> 'La date ne peut pas être dans le futur.',
            'montant_attendu.required_without' => 'Saisissez un montant attendu ou ajoutez des produits.',
        ]);

        $boulangerie_id = auth()->user()->boulangerie_id;

        $livreur = Livreur::find($validated['livreur_id']);
        if ($livreur->boulangerie_id !== $boulangerie_id) {
            return redirect()->back()->with('error', 'Livreur invalide.');
        }

        DB::transaction(function () use ($validated, $distribution) {
            $distribution->update([
                'livreur_id'        => $validated['livreur_id'],
                'date_distribution' => $validated['date_distribution'],
                'montant_attendu'   => $validated['montant_attendu'] ?? $distribution->montant_attendu,
            ]);

            if (!empty($validated['produits'])) {
                // Remplace toutes les lignes existantes
                $distribution->detailDistributions()->delete();

                $montantCalcule = 0;
                foreach ($validated['produits'] as $ligne) {
                    $produit  = Produit::find($ligne['produit_id']);
                    $retourne = $ligne['quantite_retournee'] ?? 0;
                    $vendu    = max(0, $ligne['quantite_attribuee'] - $retourne);
                    $montantCalcule += $vendu * $produit->prix;

                    DetailDistribution::create([
                        'distribution_id'    => $distribution->id,
                        'produit_id'         => $ligne['produit_id'],
                        'quantite_attribuee' => $ligne['quantite_attribuee'],
                        'quantite_retournee' => $retourne,
                    ]);
                }

                $distribution->montant_attendu = round($montantCalcule, 2);
                $distribution->save();
            }
        });

        return redirect()->route('distributions.show', $distribution)
            ->with('success', 'Distribution mise à jour avec succès.');
    }

    public function destroy(Distribution $distribution): RedirectResponse
    {
        $livreur = $distribution->livreur;

        // Le versement lié utilise onDelete('set null') : on le supprime explicitement
        // pour éviter un versement orphelin qui fausserait les totaux.
        $distribution->versement()->delete();

        // detail_distributions sont supprimés par cascade (onDelete cascadeOnDelete).
        $distribution->delete();

        return redirect()->route('livreurs.distributions', $livreur)
            ->with('success', 'Distribution supprimée.');
    }
}
