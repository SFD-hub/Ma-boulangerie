<?php

namespace App\Http\Controllers;

use App\Models\MatierePremiere;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class MatierePremiereController extends Controller
{
    public function index(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        $matieresPremières = MatierePremiere::where('boulangerie_id', $boulangerie_id)
            ->with('achats')
            ->orderBy('nom')
            ->get();

        return view('matieres-premieres.index', compact('matieresPremières'));
    }

    public function create(): View
    {
        return view('matieres-premieres.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:matieres_premieres,nom,NULL,id,boulangerie_id,' . auth()->user()->boulangerie_id,
            'stock_actuel' => 'required|integer|min:0',
            'seuil_alerte' => 'required|integer|min:0',
        ], [
            'nom.required' => 'Le nom de la matière première est obligatoire.',
            'nom.unique' => 'Cette matière première existe déjà.',
            'nom.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'stock_actuel.required' => 'Le stock actuel est obligatoire.',
            'stock_actuel.integer' => 'Le stock doit être un nombre entier.',
            'stock_actuel.min' => 'Le stock ne peut pas être négatif.',
            'seuil_alerte.required' => 'Le seuil d\'alerte est obligatoire.',
            'seuil_alerte.integer' => 'Le seuil doit être un nombre entier.',
            'seuil_alerte.min' => 'Le seuil ne peut pas être négatif.',
        ]);

        $boulangerie_id = auth()->user()->boulangerie_id;

        MatierePremiere::create([
            'nom' => $validated['nom'],
            'stock_actuel' => $validated['stock_actuel'],
            'seuil_alerte' => $validated['seuil_alerte'],
            'boulangerie_id' => $boulangerie_id,
        ]);

        return redirect()->route('matieres-premieres.index')
            ->with('success', 'Matière première créée avec succès.');
    }

    public function show(MatierePremiere $matierePremiere)
    {
        // Non utilisé pour les matières premières
    }

    public function edit(MatierePremiere $matierePremiere): View
    {
        return view('matieres-premieres.edit', compact('matierePremiere'));
    }

    public function update(Request $request, MatierePremiere $matierePremiere): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:matieres_premieres,nom,' . $matierePremiere->id . ',id,boulangerie_id,' . auth()->user()->boulangerie_id,
            'stock_actuel' => 'required|integer|min:0',
            'seuil_alerte' => 'required|integer|min:0',
        ], [
            'nom.required' => 'Le nom de la matière première est obligatoire.',
            'nom.unique' => 'Cette matière première existe déjà.',
            'nom.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'stock_actuel.required' => 'Le stock actuel est obligatoire.',
            'stock_actuel.integer' => 'Le stock doit être un nombre entier.',
            'stock_actuel.min' => 'Le stock ne peut pas être négatif.',
            'seuil_alerte.required' => 'Le seuil d\'alerte est obligatoire.',
            'seuil_alerte.integer' => 'Le seuil doit être un nombre entier.',
            'seuil_alerte.min' => 'Le seuil ne peut pas être négatif.',
        ]);

        $matierePremiere->update([
            'nom' => $validated['nom'],
            'stock_actuel' => $validated['stock_actuel'],
            'seuil_alerte' => $validated['seuil_alerte'],
        ]);

        return redirect()->route('matieres-premieres.index')
            ->with('success', 'Matière première mise à jour avec succès.');
    }

    public function destroy(MatierePremiere $matierePremiere): RedirectResponse
    {
        // Vérifier s'il y a des achats associés
        if ($matierePremiere->achats()->count() > 0) {
            return redirect()->route('matieres-premieres.index')
                ->with('error', 'Impossible de supprimer cette matière première car elle contient des achats.');
        }

        $matierePremiere->delete();

        return redirect()->route('matieres-premieres.index')
            ->with('success', 'Matière première supprimée avec succès.');
    }
}
