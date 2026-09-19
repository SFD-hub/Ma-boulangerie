<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DepotVente;
use App\Models\Produit;
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

        $totalQuantite = (int) $ventes->sum('quantite');
        $totalMontant  = (float) $ventes->sum('montant');

        return view('depot.index', compact(
            'date', 'ventes', 'produits', 'defaultProduitId', 'totalQuantite', 'totalMontant'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        $validated = $request->validate([
            'produit_id' => ['required', Rule::exists('produits', 'id')->where('boulangerie_id', $boulangerie_id)],
            'date_vente' => 'required|date|before_or_equal:today',
            'quantite'   => 'required|integer|min:1',
            'montant'    => 'required|numeric|min:0.01',
        ], [
            'produit_id.required'        => 'Le produit est obligatoire.',
            'date_vente.required'        => 'La date est obligatoire.',
            'date_vente.before_or_equal' => 'La date ne peut pas être dans le futur.',
            'quantite.required'          => 'La quantité est obligatoire.',
            'quantite.min'               => 'La quantité doit être au moins 1.',
            'montant.required'           => 'Le montant est obligatoire.',
            'montant.min'                => 'Le montant doit être supérieur à 0.',
        ]);

        $produit    = Produit::findOrFail($validated['produit_id']);
        $disponible = $produit->painsDisponibles();

        if ($validated['quantite'] > $disponible) {
            $reste = max(0, $disponible);
            return redirect()->back()->withInput()->with('error',
                'Il ne reste que ' . $reste . ' pain' . ($reste > 1 ? 's' : '')
                . ' disponible' . ($reste > 1 ? 's' : '') . ' pour « ' . $produit->nom . ' ».'
            );
        }

        DepotVente::create([
            'boulangerie_id' => $boulangerie_id,
            'produit_id'     => $validated['produit_id'],
            'date_vente'     => $validated['date_vente'],
            'quantite'       => $validated['quantite'],
            'montant'        => $validated['montant'],
        ]);

        ActivityLog::record(
            'vente_depot',
            auth()->user()->name . ' a enregistré une vente Dépôt de ' . $validated['quantite'] . ' pain' . ($validated['quantite'] > 1 ? 's' : '') . ' (' . number_format($validated['montant'], 0, ',', ' ') . ' FCFA)',
            $boulangerie_id,
            'vente'
        );

        return redirect()->route('depot.index', ['date' => $validated['date_vente']])
            ->with('success', 'Vente de ' . $validated['quantite'] . ' pain' . ($validated['quantite'] > 1 ? 's' : '') . ' enregistrée.');
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
