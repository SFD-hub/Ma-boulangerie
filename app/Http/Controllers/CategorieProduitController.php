<?php

namespace App\Http\Controllers;

use App\Models\CategorieProduit;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CategorieProduitController extends Controller
{
    public function index(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        $categories = CategorieProduit::where('boulangerie_id', $boulangerie_id)
            ->with('produits')
            ->orderBy('nom')
            ->get();

        return view('categories-produits.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories-produits.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:categories_produits,nom,NULL,id,boulangerie_id,' . auth()->user()->boulangerie_id,
        ], [
            'nom.required' => 'Le nom de la catégorie est obligatoire.',
            'nom.unique' => 'Cette catégorie existe déjà.',
            'nom.max' => 'Le nom ne doit pas dépasser 255 caractères.',
        ]);

        $boulangerie_id = auth()->user()->boulangerie_id;

        CategorieProduit::create([
            'nom' => $validated['nom'],
            'boulangerie_id' => $boulangerie_id,
        ]);

        return redirect()->route('categories-produits.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    public function show(CategorieProduit $categorieProduit)
    {
        // Non utilisé pour les catégories
    }

    public function edit(CategorieProduit $categorieProduit): View
    {
        return view('categories-produits.edit', compact('categorieProduit'));
    }

    public function update(Request $request, CategorieProduit $categorieProduit): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:categories_produits,nom,' . $categorieProduit->id . ',id,boulangerie_id,' . auth()->user()->boulangerie_id,
        ], [
            'nom.required' => 'Le nom de la catégorie est obligatoire.',
            'nom.unique' => 'Cette catégorie existe déjà.',
            'nom.max' => 'Le nom ne doit pas dépasser 255 caractères.',
        ]);

        $categorieProduit->update([
            'nom' => $validated['nom'],
        ]);

        return redirect()->route('categories-produits.index')
            ->with('success', 'Catégorie mise à jour avec succès.');
    }

    public function destroy(CategorieProduit $categorieProduit): RedirectResponse
    {
        // Vérifier s'il y a des produits associés
        if ($categorieProduit->produits()->count() > 0) {
            return redirect()->route('categories-produits.index')
                ->with('error', 'Impossible de supprimer cette catégorie car elle contient des produits.');
        }

        $categorieProduit->delete();

        return redirect()->route('categories-produits.index')
            ->with('success', 'Catégorie supprimée avec succès.');
    }
}
