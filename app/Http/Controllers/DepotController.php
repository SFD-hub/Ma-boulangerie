<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DepotVente;
use App\Models\Produit;
use App\Support\Nombre;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepotController extends Controller
{
    public function index(Request $request): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        $date = $request->query('date');
        $date = ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) ? $date : Carbon::today()->toDateString();

        $ventes = DepotVente::where('boulangerie_id', $boulangerie_id)
            ->whereDate('date_vente', $date)
            ->with('produit')
            ->orderByDesc('created_at')
            ->get();

        $produits = Produit::where('boulangerie_id', $boulangerie_id)
            ->where('actif', true)
            ->orderBy('nom')
            ->get();
        $defaultProduitId = Produit::defaultIdPour($boulangerie_id);

        $totalQuantite = $ventes->sum('parts') / 6;
        $totalMontant  = (float) $ventes->sum('montant');

        $boulangerie = auth()->user()->boulangerie;
        $prix = [
            'entier' => (int) ($boulangerie->prix_boutique_entier ?? 150),
            'moitie' => (int) ($boulangerie->prix_boutique_moitie ?? 75),
            'tiers'  => (int) ($boulangerie->prix_boutique_tiers ?? 50),
        ];

        return view('depot.index', compact(
            'date', 'ventes', 'produits', 'defaultProduitId', 'totalQuantite', 'totalMontant', 'prix'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        $validated = $request->validate([
            'produit_id'     => ['required', Rule::exists('produits', 'id')->where('boulangerie_id', $boulangerie_id)],
            'date_vente'     => 'required|date|before_or_equal:today',
            'nombre_entiers' => 'nullable|integer|min:0',
            'nombre_moities' => 'nullable|integer|min:0',
            'nombre_tiers'   => 'nullable|integer|min:0',
        ], [
            'produit_id.required'        => 'Le produit est obligatoire.',
            'date_vente.required'        => 'La date est obligatoire.',
            'date_vente.before_or_equal' => 'La date ne peut pas être dans le futur.',
        ]);

        $entiers = (int) ($validated['nombre_entiers'] ?? 0);
        $moities = (int) ($validated['nombre_moities'] ?? 0);
        $tiers   = (int) ($validated['nombre_tiers'] ?? 0);

        if ($entiers + $moities + $tiers === 0) {
            return redirect()->back()->withInput()->with('error',
                'Indiquez au moins un pain vendu (entier, moitié ou tiers).'
            );
        }

        // Le montant n'est jamais saisi : il est calculé ici à partir des prix
        // définis par le propriétaire (Paramètres), donc impossible à falsifier.
        $boulangerie = auth()->user()->boulangerie;
        $montant = $entiers * (int) $boulangerie->prix_boutique_entier
                 + $moities * (int) $boulangerie->prix_boutique_moitie
                 + $tiers   * (int) $boulangerie->prix_boutique_tiers;

        // Quantité en sixièmes de pain (entier = 6, moitié = 3, tiers = 2) :
        // nombres entiers uniquement, donc aucun arrondi cumulé sur le stock.
        $parts = $entiers * 6 + $moities * 3 + $tiers * 2;

        $produit    = Produit::findOrFail($validated['produit_id']);
        $disponible = $produit->partsDisponibles();

        if ($parts > $disponible) {
            $reste = max(0, $disponible) / 6;
            return redirect()->back()->withInput()->with('error',
                'Il ne reste que ' . Nombre::pains($reste) . ' disponible pour « ' . $produit->nom . ' ».'
            );
        }

        $vente = DepotVente::create([
            'boulangerie_id'       => $boulangerie_id,
            'produit_id'           => $validated['produit_id'],
            'date_vente'           => $validated['date_vente'],
            'nombre_entiers'       => $entiers,
            'nombre_moities'       => $moities,
            'nombre_tiers'         => $tiers,
            'parts'                => $parts,
            'quantite_equivalente' => round($parts / 6, 2),
            'montant'              => $montant,
        ]);

        ActivityLog::record(
            'vente_depot',
            auth()->user()->name . ' a enregistré une vente Boutique (' . $vente->descriptionPieces() . ') pour ' . number_format($montant, 0, ',', ' ') . ' FCFA',
            $boulangerie_id,
            'vente'
        );

        return redirect()->route('depot.index', ['date' => $validated['date_vente']])
            ->with('success', 'Vente enregistrée (' . $vente->descriptionPieces() . ').');
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
