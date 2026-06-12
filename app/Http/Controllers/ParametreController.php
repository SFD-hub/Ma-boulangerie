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
        ], [
            'nom.required' => 'Le nom de la boulangerie est obligatoire.',
        ]);

        Boulangerie::where('id', $boulangerie_id)->update($validated);

        return redirect()->route('parametres.edit')
            ->with('success', 'Paramètres enregistrés.');
    }
}
