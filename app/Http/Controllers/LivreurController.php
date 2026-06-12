<?php

namespace App\Http\Controllers;

use App\Models\Distribution;
use App\Models\Livreur;
use App\Models\Versement;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class LivreurController extends Controller
{
    public function index(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        $livreurs = Livreur::where('boulangerie_id', $boulangerie_id)
            ->orderBy('actif', 'desc')
            ->orderBy('nom')
            ->get();

        return view('livreurs.index', compact('livreurs'));
    }

    public function create(): View
    {
        return view('livreurs.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom'       => 'required|string|max:255',
            'prenom'    => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
        ], [
            'nom.required'       => 'Le nom est obligatoire.',
            'prenom.required'    => 'Le prénom est obligatoire.',
            'telephone.required' => 'Le téléphone est obligatoire.',
        ]);

        Livreur::create(array_merge($validated, [
            'actif'          => true,
            'boulangerie_id' => auth()->user()->boulangerie_id,
        ]));

        return redirect()->route('livreurs.index')
            ->with('success', 'Livreur créé.');
    }

    public function show(Livreur $livreur): View
    {
        abort_if($livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $livreur->load(['distributions.versement']);

        // Distributions avec reliquat > 0 : peuvent recevoir un versement (supplémentaire)
        $distributionsNonReglees = $livreur->distributions
            ->filter(fn ($d) => !$d->estRegle())
            ->sortByDesc('date_distribution');

        $prixPain = auth()->user()->boulangerie?->prix_pain ?? 0;

        return view('livreurs.show', compact(
            'livreur',
            'distributionsNonReglees',
            'prixPain'
        ));
    }

    public function edit(Livreur $livreur): View
    {
        abort_if($livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);
        return view('livreurs.edit', compact('livreur'));
    }

    public function update(Request $request, Livreur $livreur): RedirectResponse
    {
        abort_if($livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $validated = $request->validate([
            'nom'       => 'required|string|max:255',
            'prenom'    => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
        ]);

        $livreur->update($validated);

        return redirect()->route('livreurs.show', $livreur)
            ->with('success', 'Livreur mis à jour.');
    }

    public function destroy(Livreur $livreur): RedirectResponse
    {
        abort_if($livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);
        $livreur->update(['actif' => false]);
        return redirect()->route('livreurs.index')->with('success', 'Livreur désactivé.');
    }

    public function reactiver(Livreur $livreur): RedirectResponse
    {
        abort_if($livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);
        $livreur->update(['actif' => true]);
        return redirect()->route('livreurs.show', $livreur)->with('success', 'Livreur réactivé.');
    }

    // ─── Pages dédiées (formulaires + historique) ──────────────────────────

    public function attribuerForm(Livreur $livreur): View
    {
        abort_if($livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);
        $prixPain = auth()->user()->boulangerie?->prix_pain ?? 0;
        return view('livreurs.attribuer', compact('livreur', 'prixPain'));
    }

    public function verserForm(Livreur $livreur): View
    {
        abort_if($livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);
        $livreur->load(['distributions.versement']);

        // Distributions avec reliquat > 0 : peuvent recevoir un versement (même si partiel déjà)
        $distributionsNonReglees = $livreur->distributions
            ->filter(fn ($d) => !$d->estRegle())
            ->sortByDesc('date_distribution');

        // Reliquat total en attente (somme de tous les reliquats non soldés)
        $reliquatTotalEnAttente = (float) $livreur->distributions->sum('reliquat');

        $prixPain = auth()->user()->boulangerie?->prix_pain ?? 0;

        return view('livreurs.verser', compact(
            'livreur', 'distributionsNonReglees', 'prixPain', 'reliquatTotalEnAttente'
        ));
    }

    public function distributionsHistorique(Livreur $livreur): View
    {
        abort_if($livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);
        $livreur->load(['distributions.versement']);

        // Reliquat total en attente
        $reliquatTotalEnAttente = (float) $livreur->distributions->sum('reliquat');

        return view('livreurs.distributions', compact('livreur', 'reliquatTotalEnAttente'));
    }

    // ─── Attribution de pains ───────────────────────────────────────────────

    public function attribuer(Request $request, Livreur $livreur): RedirectResponse
    {
        abort_if($livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $validated = $request->validate([
            'date_distribution' => 'required|date|before_or_equal:today',
            'nombre_pains'      => 'required|integer|min:1',
            'prix_pain'         => 'required|integer|min:1',
        ], [
            'date_distribution.required'        => 'La date est obligatoire.',
            'date_distribution.before_or_equal' => 'La date ne peut pas être dans le futur.',
            'nombre_pains.required'             => 'Le nombre de pains est obligatoire.',
            'nombre_pains.min'                  => 'Le nombre de pains doit être au moins 1.',
            'prix_pain.required'                => 'Le prix du pain est obligatoire.',
            'prix_pain.min'                     => 'Le prix du pain doit être au moins 1 FCFA.',
        ]);

        $montantAttendu = $validated['nombre_pains'] * $validated['prix_pain'];

        Distribution::create([
            'livreur_id'        => $livreur->id,
            'date_distribution' => $validated['date_distribution'],
            'nombre_pains'      => $validated['nombre_pains'],
            'prix_pain'         => $validated['prix_pain'],
            'montant_attendu'   => $montantAttendu,
            'reliquat'          => $montantAttendu,
            'statut'            => 'en_attente',
        ]);

        return redirect()->route('livreurs.show', $livreur)
            ->with('success', 'Attribution de ' . $validated['nombre_pains'] . ' pains à ' . $validated['prix_pain'] . ' FCFA enregistrée.');
    }

    // ─── Versement (règlement d'une distribution) ───────────────────────────

    public function verser(Request $request, Livreur $livreur): RedirectResponse
    {
        abort_if($livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $validated = $request->validate([
            'distribution_id' => 'required|exists:distributions,id',
            'nombre_invendus' => 'required|integer|min:0',
            'montant_verse'   => 'required|numeric|min:0.01',
        ], [
            'distribution_id.required' => 'Sélectionnez une distribution.',
            'nombre_invendus.required' => 'Le nombre d\'invendus est obligatoire (0 si aucun).',
            'montant_verse.required'   => 'Le montant versé est obligatoire.',
            'montant_verse.min'        => 'Le montant versé doit être supérieur à 0.',
        ]);

        $distribution = Distribution::findOrFail($validated['distribution_id']);
        abort_if($distribution->livreur_id !== $livreur->id, 403);

        // Seules les distributions avec reliquat > 0 peuvent recevoir un versement
        if ($distribution->estRegle()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Cette distribution est déjà réglée (reliquat nul).');
        }

        $painsAttribues = $distribution->pains_attribues;
        if ($validated['nombre_invendus'] > $painsAttribues) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Les invendus (' . $validated['nombre_invendus'] . ') ne peuvent pas dépasser les pains attribués (' . $painsAttribues . ').');
        }

        $estPremierVersement = !$distribution->versement()->exists();

        // Utiliser le prix enregistré dans la distribution, jamais le prix global
        $prixPain = $distribution->prix_pain
            ?? auth()->user()->boulangerie?->prix_pain
            ?? 0;

        DB::transaction(function () use ($validated, $distribution, $livreur, $prixPain, $estPremierVersement) {
            $montantVerse = (float) $validated['montant_verse'];

            if ($estPremierVersement) {
                // ── 1a. Premier versement : calculs complets ─────────────────
                $painsVendus    = $distribution->pains_attribues - (int) $validated['nombre_invendus'];
                $montantAttendu = $painsVendus * $prixPain;
                $reliquatJour   = max(0.0, $montantAttendu - $montantVerse);
                $surplus        = max(0.0, $montantVerse - $montantAttendu);

                $distribution->update([
                    'nombre_invendus' => (int) $validated['nombre_invendus'],
                    'montant_attendu' => $montantAttendu,
                    'reliquat'        => $reliquatJour,
                    'statut'          => $reliquatJour <= 0 ? 'reglee' : 'en_attente',
                ]);
            } else {
                // ── 1b. Versement supplémentaire : on réduit le reliquat ─────
                $ancienReliquat = (float) $distribution->reliquat;
                $reliquatJour   = max(0.0, $ancienReliquat - $montantVerse);
                $surplus        = max(0.0, $montantVerse - $ancienReliquat);

                $distribution->update([
                    'reliquat' => $reliquatJour,
                    'statut'   => $reliquatJour <= 0 ? 'reglee' : 'en_attente',
                ]);
            }

            // ── 2. Enregistrer le versement (date automatique) ───────────────
            Versement::create([
                'livreur_id'      => $livreur->id,
                'distribution_id' => $distribution->id,
                'date_versement'  => now()->toDateString(),
                'montant_verse'   => $montantVerse,
            ]);

            // ── 4. Appliquer le surplus aux anciens reliquats (du + ancien au + récent) ─
            // Le statut n'est pas modifié ici : il dépend uniquement de l'existence d'un versement.
            if ($surplus > 0) {
                $distribsAvecReliquat = Distribution::where('livreur_id', $livreur->id)
                    ->where('id', '!=', $distribution->id)
                    ->where('reliquat', '>', 0)
                    ->orderBy('date_distribution', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

                foreach ($distribsAvecReliquat as $ancienne) {
                    if ($surplus <= 0) {
                        break;
                    }

                    $applied     = min($surplus, (float) $ancienne->reliquat);
                    $newReliquat = (float) $ancienne->reliquat - $applied;

                    $ancienne->update([
                        'reliquat' => $newReliquat,
                        // statut inchangé : 'reglee' si versement, 'en_attente' sinon
                    ]);

                    $surplus -= $applied;
                }
            }
        });

        return redirect()->route('livreurs.show', $livreur)
            ->with('success', 'Versement enregistré.');
    }
}
