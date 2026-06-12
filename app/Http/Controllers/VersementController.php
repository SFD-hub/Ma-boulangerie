<?php

namespace App\Http\Controllers;

use App\Models\Versement;
use App\Models\Livreur;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class VersementController extends Controller
{
    public function index(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        
        $versements = Versement::whereHas('livreur', function ($query) use ($boulangerie_id) {
            $query->where('boulangerie_id', $boulangerie_id);
        })
            ->with('livreur')
            ->orderBy('date_versement', 'desc')
            ->get();

        return view('versements.index', compact('versements'));
    }

    public function create(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        $livreurs = Livreur::where('boulangerie_id', $boulangerie_id)
            ->where('actif', true)
            ->orderBy('nom')
            ->get();

        return view('versements.create', compact('livreurs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'livreur_id' => 'required|exists:livreurs,id',
            'date_versement' => 'required|date|before_or_equal:today',
            'montant_verse' => 'required|numeric|min:0.01|decimal:0,2',
        ], [
            'livreur_id.required' => 'Le livreur est obligatoire.',
            'livreur_id.exists' => 'Le livreur sélectionné n\'existe pas.',
            'date_versement.required' => 'La date de versement est obligatoire.',
            'date_versement.date' => 'La date doit être une date valide.',
            'date_versement.before_or_equal' => 'La date ne peut pas être dans le futur.',
            'montant_verse.required' => 'Le montant versé est obligatoire.',
            'montant_verse.numeric' => 'Le montant doit être un nombre.',
            'montant_verse.min' => 'Le montant doit être supérieur à 0.',
            'montant_verse.decimal' => 'Le montant doit avoir au maximum 2 décimales.',
        ]);

        $boulangerie_id = auth()->user()->boulangerie_id;

        // Vérifier que le livreur appartient à la boulangerie
        $livreur = Livreur::find($validated['livreur_id']);
        if ($livreur->boulangerie_id !== $boulangerie_id) {
            return redirect()->back()
                ->with('error', 'Livreur invalide.');
        }

        Versement::create($validated);

        return redirect()->route('versements.index')
            ->with('success', 'Versement créé avec succès.');
    }

    public function show(Versement $versement): View
    {
        $versement->load('livreur');

        return view('versements.show', compact('versement'));
    }

    public function edit(Versement $versement): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        $livreurs = Livreur::where('boulangerie_id', $boulangerie_id)
            ->where('actif', true)
            ->orderBy('nom')
            ->get();

        return view('versements.edit', compact('versement', 'livreurs'));
    }

    public function update(Request $request, Versement $versement): RedirectResponse
    {
        $validated = $request->validate([
            'livreur_id' => 'required|exists:livreurs,id',
            'date_versement' => 'required|date|before_or_equal:today',
            'montant_verse' => 'required|numeric|min:0.01|decimal:0,2',
        ], [
            'livreur_id.required' => 'Le livreur est obligatoire.',
            'livreur_id.exists' => 'Le livreur sélectionné n\'existe pas.',
            'date_versement.required' => 'La date de versement est obligatoire.',
            'date_versement.date' => 'La date doit être une date valide.',
            'date_versement.before_or_equal' => 'La date ne peut pas être dans le futur.',
            'montant_verse.required' => 'Le montant versé est obligatoire.',
            'montant_verse.numeric' => 'Le montant doit être un nombre.',
            'montant_verse.min' => 'Le montant doit être supérieur à 0.',
            'montant_verse.decimal' => 'Le montant doit avoir au maximum 2 décimales.',
        ]);

        $boulangerie_id = auth()->user()->boulangerie_id;

        // Vérifier que le livreur appartient à la boulangerie
        $livreur = Livreur::find($validated['livreur_id']);
        if ($livreur->boulangerie_id !== $boulangerie_id) {
            return redirect()->back()
                ->with('error', 'Livreur invalide.');
        }

        $versement->update($validated);

        return redirect()->route('versements.show', $versement)
            ->with('success', 'Versement mis à jour avec succès.');
    }

    public function destroy(Versement $versement): RedirectResponse
    {
        $versement->delete();

        return redirect()->route('versements.index')
            ->with('success', 'Versement supprimé avec succès.');
    }
}
