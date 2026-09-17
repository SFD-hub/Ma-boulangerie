<?php

namespace App\Http\Controllers;

use App\Models\Distribution;
use App\Models\Livreur;
use App\Models\Produit;
use App\Models\Versement;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LivreurController extends Controller
{
    public function index(): RedirectResponse
    {
        // La vraie liste vit désormais dans le module "Clients" unifié —
        // cette route ne sert plus qu'à ne pas casser les liens "← Retour"
        // existants (create/show/_form) qui pointent encore vers son nom.
        return redirect()->route('clients.index', ['type' => 'livreur']);
    }

    public function create(): RedirectResponse
    {
        // Le formulaire d'ajout est désormais unique pour les 3 types
        // (Livreur/Client/Abonné), dans le module "Clients".
        return redirect()->route('clients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom_complet' => 'required|string|max:255',
            'telephone'   => 'required|string|max:20',
            'type'        => 'required|in:livreur,client',
        ], [
            'nom_complet.required' => 'Le nom est obligatoire.',
            'telephone.required'   => 'Le téléphone est obligatoire.',
            'type.required'        => 'Le type est obligatoire.',
        ]);

        // Un seul champ "Prénom et nom" côté formulaire, pour aller plus vite
        // à la saisie -- éclaté ici pour rester compatible avec le reste de
        // l'application, qui distingue prenom/nom partout ailleurs (avatar,
        // affichage "Prénom Nom", etc.).
        [$prenom, $nom] = $this->separerNomComplet($validated['nom_complet']);

        $livreur = Livreur::create([
            'prenom'         => $prenom,
            'nom'            => $nom,
            'telephone'      => $validated['telephone'],
            'type'           => $validated['type'],
            'actif'          => true,
            'boulangerie_id' => auth()->user()->boulangerie_id,
        ]);

        return redirect()->route('livreurs.show', $livreur)
            ->with('success', ($validated['type'] === 'client' ? 'Client créé.' : 'Livreur créé.'));
    }

    /**
     * Sépare un nom complet saisi en un seul champ en [prénom, nom] : le
     * premier mot est le prénom, tout le reste forme le nom (gère les noms
     * composés comme "Amadou Moussa Diallo" -> prénom "Amadou", nom "Moussa Diallo").
     *
     * @return array{0: string, 1: string}
     */
    private function separerNomComplet(string $nomComplet): array
    {
        $parts = preg_split('/\s+/', trim($nomComplet), 2);

        // Un seul mot saisi : la colonne "nom" est obligatoire en base
        // (contrairement à "prénom", nullable) -- on l'y met donc plutôt
        // que de laisser "nom" vide.
        if (count($parts) === 1) {
            return ['', $parts[0]];
        }

        return [$parts[0], $parts[1]];
    }

    public function show(Livreur $livreur): View
    {
        abort_if($livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $livreur->load(['distributions.versement', 'distributions.produit']);

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
            'type'      => 'required|in:livreur,client',
        ]);

        $livreur->update($validated);

        return redirect()->route('livreurs.show', $livreur)
            ->with('success', ($validated['type'] === 'client' ? 'Client mis à jour.' : 'Livreur mis à jour.'));
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
        $produits = Produit::where('boulangerie_id', $livreur->boulangerie_id)
            ->where('actif', true)
            ->orderBy('nom')
            ->get();
        $defaultProduitId = Produit::defaultIdPour($livreur->boulangerie_id);
        return view('livreurs.attribuer', compact('livreur', 'prixPain', 'produits', 'defaultProduitId'));
    }

    public function verserForm(Request $request, Livreur $livreur): View
    {
        abort_if($livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);
        $livreur->load(['distributions.versement', 'distributions.produit']);

        // Distributions avec reliquat > 0 : peuvent recevoir un versement (même si partiel déjà)
        $distributionsNonReglees = $livreur->distributions
            ->filter(fn ($d) => !$d->estRegle())
            ->sortByDesc('date_distribution');

        // Reliquat total en attente (somme de tous les reliquats non soldés)
        $reliquatTotalEnAttente = (float) $livreur->distributions->sum('reliquat');

        $prixPain = auth()->user()->boulangerie?->prix_pain ?? 0;

        // Pré-sélection depuis l'historique ("Régler le reliquat" sur une
        // distribution précise) — ignorée si elle ne correspond à aucune
        // distribution réellement en attente (évite de présélectionner une
        // distribution déjà réglée ou d'un autre livreur).
        $distributionPreselectionnee = $distributionsNonReglees
            ->firstWhere('id', (int) $request->query('distribution_id'));

        return view('livreurs.verser', compact(
            'livreur', 'distributionsNonReglees', 'prixPain', 'reliquatTotalEnAttente',
            'distributionPreselectionnee'
        ));
    }

    public function distributionsHistorique(Livreur $livreur): View
    {
        abort_if($livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);
        $livreur->load(['distributions.versement', 'distributions.produit']);

        // Reliquat total en attente
        $reliquatTotalEnAttente = (float) $livreur->distributions->sum('reliquat');

        return view('livreurs.distributions', compact('livreur', 'reliquatTotalEnAttente'));
    }

    // ─── Attribution de pains ───────────────────────────────────────────────

    public function attribuer(Request $request, Livreur $livreur): RedirectResponse
    {
        abort_if($livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $validated = $request->validate([
            'produit_id'         => ['required', Rule::exists('produits', 'id')->where('boulangerie_id', $livreur->boulangerie_id)],
            'date_distribution'  => 'required|date|before_or_equal:today',
            'nombre_pains'       => 'required|integer|min:1',
            'prix_pain'          => 'required|integer|min:1',
        ], [
            'produit_id.required'               => 'Le produit est obligatoire.',
            'date_distribution.required'        => 'La date est obligatoire.',
            'date_distribution.before_or_equal' => 'La date ne peut pas être dans le futur.',
            'nombre_pains.required'             => 'Le nombre de pains est obligatoire.',
            'nombre_pains.min'                  => 'Le nombre de pains doit être au moins 1.',
            'prix_pain.required'                => 'Le prix du pain est obligatoire.',
            'prix_pain.min'                     => 'Le prix du pain doit être au moins 1 FCFA.',
        ]);

        $produit    = Produit::findOrFail($validated['produit_id']);
        $disponible = $produit->painsDisponibles();

        if ($validated['nombre_pains'] > $disponible) {
            $reste = max(0, $disponible);
            return redirect()->back()->withInput()->with('error',
                'Il ne reste que ' . $reste . ' pain' . ($reste > 1 ? 's' : '')
                . ' disponible' . ($reste > 1 ? 's' : '') . ' pour « ' . $produit->nom . ' ».'
            );
        }

        $montantAttendu = $validated['nombre_pains'] * $validated['prix_pain'];

        Distribution::create([
            'livreur_id'        => $livreur->id,
            'produit_id'        => $validated['produit_id'],
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

        $distributionRef = Distribution::findOrFail($validated['distribution_id']);
        abort_if($distributionRef->livreur_id !== $livreur->id, 403);

        // Utiliser le prix enregistré dans la distribution, jamais le prix global
        $prixPain = $distributionRef->prix_pain
            ?? auth()->user()->boulangerie?->prix_pain
            ?? 0;

        $resultat = DB::transaction(function () use ($validated, $distributionRef, $livreur, $prixPain) {
            // Verrouille la distribution ciblée ET toutes les distributions du
            // livreur ayant un reliquat, avant toute lecture utilisée pour le
            // calcul — deux versements simultanés pour le même livreur ne
            // peuvent plus lire le même reliquat ni appliquer le même surplus
            // deux fois.
            $distribution = Distribution::where('id', $distributionRef->id)->lockForUpdate()->firstOrFail();

            // Revérifié après verrouillage : peut avoir changé entre l'affichage
            // du formulaire et l'enregistrement (un autre versement concurrent).
            if ($distribution->estRegle()) {
                return ['ok' => false, 'message' => 'Cette distribution est déjà réglée (reliquat nul).'];
            }

            $painsAttribues = $distribution->pains_attribues;
            if ($validated['nombre_invendus'] > $painsAttribues) {
                return ['ok' => false, 'message' => 'Les invendus (' . $validated['nombre_invendus'] . ') ne peuvent pas dépasser les pains attribués (' . $painsAttribues . ').'];
            }

            $estPremierVersement = !$distribution->versement()->exists();
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
                    ->lockForUpdate()
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

            return ['ok' => true];
        });

        if (!$resultat['ok']) {
            return redirect()->back()
                ->withInput()
                ->with('error', $resultat['message']);
        }

        return redirect()->route('livreurs.show', $livreur)
            ->with('success', 'Versement enregistré.');
    }
}
