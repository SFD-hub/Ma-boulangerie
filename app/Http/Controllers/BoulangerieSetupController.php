<?php

namespace App\Http\Controllers;

use App\Models\Boulangerie;
use App\Models\MatierePremiere;
use App\Models\Produit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BoulangerieSetupController extends Controller
{
    private const RULES = [
        'nom'       => 'required|string|max:255',
        'telephone' => 'required|string|max:20',
        'adresse'   => 'required|string|max:500',
        'email'     => 'nullable|string|email|max:255',
    ];

    private const MESSAGES = [
        'nom.required'       => 'Le nom de la boulangerie est obligatoire.',
        'telephone.required' => 'Le numéro de téléphone est obligatoire.',
        'adresse.required'   => "L'adresse est obligatoire.",
        'email.email'        => "L'email doit être une adresse valide.",
    ];

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

        $validated = $request->validate(self::RULES, self::MESSAGES);

        DB::transaction(function () use ($validated) {
            $boulangerie = $this->creerBoulangerie($validated);

            auth()->user()->update(['boulangerie_id' => $boulangerie->id]);
            auth()->user()->boulangeries()->attach($boulangerie->id);
        });

        return redirect()->route('dashboard');
    }

    /**
     * Formulaire d'ajout d'une boulangerie supplémentaire pour un
     * propriétaire qui en possède déjà au moins une.
     */
    public function createAdditional(): View
    {
        return view('boulangeries.ajouter');
    }

    public function storeAdditional(Request $request): RedirectResponse
    {
        $validated = $request->validate(self::RULES, self::MESSAGES);

        $boulangerie = DB::transaction(function () use ($validated) {
            $boulangerie = $this->creerBoulangerie($validated);

            auth()->user()->boulangeries()->attach($boulangerie->id);

            return $boulangerie;
        });

        session(['active_boulangerie_id' => $boulangerie->id]);

        return redirect()->route('dashboard')
            ->with('success', "Boulangerie « {$boulangerie->nom} » créée et sélectionnée.");
    }

    /**
     * Crée la boulangerie et son stock de départ (Farine/Levure).
     * Ne touche ni à users.boulangerie_id ni à la pivot : c'est aux
     * appelants (onboarding vs. ajout) de décider quoi faire de ces deux
     * rattachements.
     */
    private function creerBoulangerie(array $validated): Boulangerie
    {
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

        Produit::create([
            'boulangerie_id' => $boulangerie->id,
            'nom'            => 'Pain',
            'actif'          => true,
        ]);

        return $boulangerie;
    }
}
