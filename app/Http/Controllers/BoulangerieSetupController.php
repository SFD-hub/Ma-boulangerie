<?php

namespace App\Http\Controllers;

use App\Models\Boulangerie;
use App\Models\MatierePremiere;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BoulangerieSetupController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (auth()->user()->boulangerie_id) {
            return redirect()->route('dashboard');
        }

        return view('setup.boulangerie');
    }

    public function store(Request $request): RedirectResponse
    {
        if (auth()->user()->boulangerie_id) {
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'nom'       => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
            'adresse'   => 'required|string|max:500',
            'email'     => 'nullable|string|email|max:255',
        ], [
            'nom.required'       => 'Le nom de la boulangerie est obligatoire.',
            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'adresse.required'   => "L'adresse est obligatoire.",
            'email.email'        => "L'email doit être une adresse valide.",
        ]);

        DB::transaction(function () use ($validated) {
            $boulangerie = Boulangerie::create([
                'nom'                        => $validated['nom'],
                'telephone'                  => $validated['telephone'],
                'adresse'                    => $validated['adresse'],
                'email'                      => $validated['email'] ?? null,
                'statut_abonnement'          => 'actif',
                'date_expiration_abonnement' => now()->addYear(),
            ]);

            MatierePremiere::create([
                'boulangerie_id' => $boulangerie->id,
                'nom'            => 'Farine',
                'stock_actuel'   => 0,
                'seuil_alerte'   => 3,
            ]);

            MatierePremiere::create([
                'boulangerie_id' => $boulangerie->id,
                'nom'            => 'Levure',
                'stock_actuel'   => 0,
                'seuil_alerte'   => 1,
            ]);

            auth()->user()->update(['boulangerie_id' => $boulangerie->id]);
        });

        return redirect()->route('dashboard');
    }
}
