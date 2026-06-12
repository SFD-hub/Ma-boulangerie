<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use App\Models\CategorieProduit;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProduitController extends Controller
{
    public function index(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        $produits = Produit::where('boulangerie_id', $boulangerie_id)
            ->with('categorieProduit')
            ->orderBy('nom')
            ->get();

        return view('produits.index', compact('produits'));
    }

    public function create(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        $categories = CategorieProduit::where('boulangerie_id', $boulangerie_id)
            ->orderBy('nom')
            ->get();

        return view('produits.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'prix' => 'required|numeric|min:0.01|decimal:0,2',
            'categorie_produit_id' => 'required|exists:categories_produits,id',
        ], [
            'nom.required' => 'Le nom du produit est obligatoire.',
            'nom.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'prix.required' => 'Le prix est obligatoire.',
            'prix.numeric' => 'Le prix doit être un nombre.',
            'prix.min' => 'Le prix doit être supérieur à 0.',
            'prix.decimal' => 'Le prix doit avoir au maximum 2 décimales.',
            'categorie_produit_id.required' => 'La catégorie est obligatoire.',
            'categorie_produit_id.exists' => 'La catégorie sélectionnée n\'existe pas.',
        ]);

        $boulangerie_id = auth()->user()->boulangerie_id;

        // Vérifier que la catégorie appartient à la boulangerie
        $categorie = CategorieProduit::find($validated['categorie_produit_id']);
        if ($categorie->boulangerie_id !== $boulangerie_id) {
            return redirect()->back()
                ->with('error', 'Catégorie invalide.');
        }

        Produit::create([
            'nom' => $validated['nom'],
            'description' => $validated['description'],
            'prix' => $validated['prix'],
            'categorie_produit_id' => $validated['categorie_produit_id'],
            'boulangerie_id' => $boulangerie_id,
        ]);

        return redirect()->route('produits.index')
            ->with('success', 'Produit créé avec succès.');
    }

    public function show(Produit $produit): View
    {
        return view('produits.show', compact('produit'));
    }

    public function edit(Produit $produit): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        $categories = CategorieProduit::where('boulangerie_id', $boulangerie_id)
            ->orderBy('nom')
            ->get();

        return view('produits.edit', compact('produit', 'categories'));
    }

    public function update(Request $request, Produit $produit): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'prix' => 'required|numeric|min:0.01|decimal:0,2',
            'categorie_produit_id' => 'required|exists:categories_produits,id',
        ], [
            'nom.required' => 'Le nom du produit est obligatoire.',
            'nom.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'prix.required' => 'Le prix est obligatoire.',
            'prix.numeric' => 'Le prix doit être un nombre.',
            'prix.min' => 'Le prix doit être supérieur à 0.',
            'prix.decimal' => 'Le prix doit avoir au maximum 2 décimales.',
            'categorie_produit_id.required' => 'La catégorie est obligatoire.',
            'categorie_produit_id.exists' => 'La catégorie sélectionnée n\'existe pas.',
        ]);

        $boulangerie_id = auth()->user()->boulangerie_id;

        // Vérifier que la catégorie appartient à la boulangerie
        $categorie = CategorieProduit::find($validated['categorie_produit_id']);
        if ($categorie->boulangerie_id !== $boulangerie_id) {
            return redirect()->back()
                ->with('error', 'Catégorie invalide.');
        }

        $produit->update([
            'nom' => $validated['nom'],
            'description' => $validated['description'],
            'prix' => $validated['prix'],
            'categorie_produit_id' => $validated['categorie_produit_id'],
        ]);

        return redirect()->route('produits.show', $produit)
            ->with('success', 'Produit mis à jour avec succès.');
    }

    public function destroy(Produit $produit): RedirectResponse
    {
        $produit->delete();

        return redirect()->route('produits.index')
            ->with('success', 'Produit supprimé avec succès.');
    }
}
