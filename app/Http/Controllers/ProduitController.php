<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProduitController extends Controller
{
    public function index(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        $produits = Produit::where('boulangerie_id', $boulangerie_id)
            ->orderByDesc('actif')
            ->orderBy('nom')
            ->get();

        return view('produits.index', compact('produits'));
    }

    public function create(): View
    {
        return view('produits.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:produits,nom,NULL,id,boulangerie_id,' . $boulangerie_id,
        ], [
            'nom.required' => 'Le nom du produit est obligatoire.',
            'nom.unique'   => 'Ce produit existe déjà.',
            'nom.max'      => 'Le nom ne doit pas dépasser 255 caractères.',
        ]);

        Produit::create([
            'nom'            => $validated['nom'],
            'actif'          => true,
            'boulangerie_id' => $boulangerie_id,
        ]);

        return redirect()->route('produits.index')
            ->with('success', 'Produit créé avec succès.');
    }

    public function edit(Produit $produit): View
    {
        abort_if($produit->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        return view('produits.edit', compact('produit'));
    }

    public function update(Request $request, Produit $produit): RedirectResponse
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        abort_if($produit->boulangerie_id !== $boulangerie_id, 403);

        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:produits,nom,' . $produit->id . ',id,boulangerie_id,' . $boulangerie_id,
        ], [
            'nom.required' => 'Le nom du produit est obligatoire.',
            'nom.unique'   => 'Ce produit existe déjà.',
            'nom.max'      => 'Le nom ne doit pas dépasser 255 caractères.',
        ]);

        $produit->update(['nom' => $validated['nom']]);

        return redirect()->route('produits.index')
            ->with('success', 'Produit mis à jour avec succès.');
    }

    public function desactiver(Produit $produit): RedirectResponse
    {
        abort_if($produit->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $produit->update(['actif' => false]);

        return redirect()->route('produits.index')->with('success', 'Produit désactivé.');
    }

    public function reactiver(Produit $produit): RedirectResponse
    {
        abort_if($produit->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $produit->update(['actif' => true]);

        return redirect()->route('produits.index')->with('success', 'Produit réactivé.');
    }
}
