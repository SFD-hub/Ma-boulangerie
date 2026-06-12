<?php

namespace App\Http\Controllers;

use App\Models\DetailVente;
use App\Models\Produit;
use App\Models\Vente;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VenteController extends Controller
{
    public function index(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        $ventes = Vente::where('boulangerie_id', $boulangerie_id)
            ->with('details.produit')
            ->orderByDesc('date_vente')
            ->paginate(20);

        $totalMois = Vente::where('boulangerie_id', $boulangerie_id)
            ->whereMonth('date_vente', now()->month)
            ->whereYear('date_vente', now()->year)
            ->sum('montant_total');

        return view('ventes.index', compact('ventes', 'totalMois'));
    }

    public function create(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        $produits = Produit::where('boulangerie_id', $boulangerie_id)->orderBy('nom')->get();

        return view('ventes.create', compact('produits'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date_vente'                   => ['required', 'date'],
            'produits'                     => ['required', 'array', 'min:1'],
            'produits.*.produit_id'        => ['required', 'exists:produits,id'],
            'produits.*.quantite'          => ['required', 'integer', 'min:1'],
            'produits.*.prix_unitaire'     => ['required', 'numeric', 'min:0'],
        ]);

        $boulangerie_id = auth()->user()->boulangerie_id;

        DB::transaction(function () use ($validated, $boulangerie_id) {
            $vente = Vente::create([
                'boulangerie_id' => $boulangerie_id,
                'user_id'        => auth()->id(),
                'date_vente'     => $validated['date_vente'],
                'montant_total'  => 0,
            ]);

            $montantTotal = 0;
            foreach ($validated['produits'] as $row) {
                $montant = (int) $row['quantite'] * (float) $row['prix_unitaire'];
                $montantTotal += $montant;
                DetailVente::create([
                    'vente_id'      => $vente->id,
                    'produit_id'    => $row['produit_id'],
                    'quantite'      => $row['quantite'],
                    'prix_unitaire' => $row['prix_unitaire'],
                ]);
            }

            $vente->montant_total = round($montantTotal, 2);
            $vente->save();
        });

        return redirect()->route('ventes.index')->with('success', 'Vente enregistrée avec succès.');
    }

    public function show(Vente $vente): View
    {
        $vente->load('details.produit');

        return view('ventes.show', compact('vente'));
    }

    public function destroy(Vente $vente): RedirectResponse
    {
        $vente->details()->delete();
        $vente->delete();

        return redirect()->route('ventes.index')->with('success', 'Vente supprimée.');
    }
}
