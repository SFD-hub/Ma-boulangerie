<?php

namespace App\Http\Controllers;

use App\Models\Boulangerie;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ParametreController extends Controller
{
    public function edit(): View
    {
        $boulangerie = auth()->user()->boulangerie ?? new Boulangerie();
        return view('parametres.edit', compact('boulangerie'));
    }

    public function update(Request $request): RedirectResponse
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        if (!$boulangerie_id) {
            return redirect()->back()->with('error', 'Aucune boulangerie associée à votre compte.');
        }

        $validated = $request->validate([
            'nom'       => 'required|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'adresse'   => 'nullable|string|max:500',
            'prix_boutique_entier' => 'required|integer|min:1|max:100000',
            'prix_boutique_moitie' => 'required|integer|min:1|max:100000',
            'prix_boutique_tiers'  => 'required|integer|min:1|max:100000',
        ], [
            'nom.required' => 'Le nom de la boulangerie est obligatoire.',
            'prix_boutique_entier.required' => 'Le prix du pain entier est obligatoire.',
            'prix_boutique_moitie.required' => 'Le prix de la moitié est obligatoire.',
            'prix_boutique_tiers.required'  => 'Le prix du tiers est obligatoire.',
            'prix_boutique_entier.min' => 'Le prix du pain entier doit être au moins 1 FCFA.',
            'prix_boutique_moitie.min' => 'Le prix de la moitié doit être au moins 1 FCFA.',
            'prix_boutique_tiers.min'  => 'Le prix du tiers doit être au moins 1 FCFA.',
        ]);

        Boulangerie::where('id', $boulangerie_id)->update($validated);

        return redirect()->route('parametres.edit')
            ->with('success', 'Paramètres enregistrés.');
    }
}
