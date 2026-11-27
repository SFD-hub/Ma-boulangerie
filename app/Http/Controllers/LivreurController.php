<?php

namespace App\Http\Controllers;

use App\Models\Distribution;
use App\Models\Livreur;
use App\Models\Produit;
use App\Models\Versement;
use App\Support\Nombre;
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
            'telephone'   => ['required', 'string', 'max:20', 'regex:/^[0-9+().\s\-]{6,20}$/'],
            'type'        => 'required|in:livreur,client,vendeur',
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
            ->with('success', $livreur->nomRole() . ' créé.');
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
            'telephone' => ['required', 'string', 'max:20', 'regex:/^[0-9+().\s\-]{6,20}$/'],
            // Un vendeur reste vendeur ; un livreur/client ne devient pas vendeur ici.
            'type'      => 'required|in:' . ($livreur->estVendeur() ? 'vendeur' : 'livreur,client'),
        ]);

        $livreur->update($validated);

        return redirect()->route('livreurs.show', $livreur)
            ->with('success', $livreur->nomRole() . ' mis à jour.');
    }

    public function destroy(Livreur $livreur): RedirectResponse
    {
        abort_if($livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);
        $livreur->update(['actif' => false]);
        return redirect($livreur->urlRetour())->with('success', $livreur->nomRole() . ' désactivé.');
    }

    public function reactiver(Livreur $livreur): RedirectResponse
    {
        abort_if($livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);
        $livreur->update(['actif' => true]);
        return redirect()->route('livreurs.show', $livreur)->with('success', $livreur->nomRole() . ' réactivé.');
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

    // Historique des distributions d'un livreur/client/vendeur : sélecteur de période et
    // 10 par page. Le reliquat total en attente (en haut) porte sur TOUTES les distributions.
    public function distributionsHistorique(Request $request, Livreur $livreur): View
    {
        abort_if($livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        [$du, $au, $periodeLimitee] = \App\Support\Periode::depuis($request);

        $reliquatTotalEnAttente = (float) $livreur->distributions()->sum('reliquat');

        $distributions = $livreur->distributions()
            ->whereBetween('date_distribution', [$du, $au])
            ->with(['versement', 'versements', 'produit'])
            ->orderByDesc('date_distribution')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('livreurs.distributions', compact('livreur', 'distributions', 'reliquatTotalEnAttente', 'du', 'au', 'periodeLimitee'));
    }

    // ─── Attribution de pains ───────────────────────────────────────────────

    public function attribuer(Request $request, Livreur $livreur): RedirectResponse
    {
        abort_if($livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $validated = $request->validate([
            'produit_id'         => ['required', Rule::exists('produits', 'id')->where('boulangerie_id', $livreur->boulangerie_id)],
            'date_distribution'  => 'required|date|after_or_equal:2020-01-01|before_or_equal:today',
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

        $produit = Produit::findOrFail($validated['produit_id']);
        // On attribue toujours des pains entiers — si le Dépôt a déjà vendu
        // une fraction du stock, on arrondit à l'entier inférieur (on ne peut
        // pas donner 0,33 pain à un livreur).
        // Le stock est celui de la production du jour de la distribution : pas de
        // production ce jour-là, ou plus rien de disponible = pas de distribution.
        $date = \Carbon\Carbon::parse($validated['date_distribution'])->toDateString();

        $montantAttendu = $validated['nombre_pains'] * $validated['prix_pain'];

        // Deux personnes peuvent attribuer au même instant : on verrouille ce pain le temps de
        // vérifier ce qui reste et d'enregistrer, pour ne jamais dépasser la production du jour.
        $refus = \App\Models\Produit::sousVerrou(fn () => DB::transaction(function () use ($produit, $date, $validated, $livreur, $montantAttendu) {
            if ($validated['nombre_pains'] > $produit->painsEntiersDisponibles(null, $date)) {
                return $produit->messageStockInsuffisant($date);
            }

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

            return null;
        }));

        if ($refus) {
            return redirect()->back()->withInput()->with('error', $refus);
        }

        \App\Models\ActivityLog::record('attribution_pains', auth()->user()->name . ' a attribué ' . $validated['nombre_pains'] . ' pains à ' . trim($livreur->prenom . ' ' . $livreur->nom) . ' (' . $livreur->nomRole() . ')', auth()->user()->boulangerie_id, 'vente');

        return redirect()->route('livreurs.show', $livreur)
            ->with('success', 'Attribution de ' . $validated['nombre_pains'] . ' pains à ' . $validated['prix_pain'] . ' FCFA enregistrée.');
    }

    // ─── Versement (règlement d'une distribution) ───────────────────────────

    public function verser(Request $request, Livreur $livreur): RedirectResponse
    {
        abort_if($livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $validated = $request->validate([
            'distribution_id'  => 'required|exists:distributions,id',
            'entiers_invendus' => 'nullable|integer|min:0',
            'moities_invendus' => 'nullable|integer|min:0',
            'tiers_invendus'   => 'nullable|integer|min:0',
            'montant_verse'    => 'required|numeric|min:0.01',
        ], [
            'distribution_id.required' => 'Sélectionnez une distribution.',
            'montant_verse.required'   => 'Le montant versé est obligatoire.',
            'montant_verse.min'        => 'Le montant versé doit être supérieur à 0.',
        ]);

        // L'utilisateur compte des morceaux (des nombres entiers), jamais une
        // fraction — l'app fait le calcul précis derrière (entier=1, moitié=0,5,
        // tiers=1/3), comme pour la vente Boutique.
        $entiersInvendus    = (int) ($validated['entiers_invendus'] ?? 0);
        $moitiesInvendus    = (int) ($validated['moities_invendus'] ?? 0);
        $tiersInvendus      = (int) ($validated['tiers_invendus'] ?? 0);
        // Comptés en sixièmes de pain (entier = 6, moitié = 3, tiers = 2) :
        // des nombres entiers, donc aucun arrondi sur les montants ni le stock.
        $invendusParts = $entiersInvendus * 6 + $moitiesInvendus * 3 + $tiersInvendus * 2;

        $distributionRef = Distribution::findOrFail($validated['distribution_id']);
        abort_if($distributionRef->livreur_id !== $livreur->id, 403);

        // Utiliser le prix enregistré dans la distribution, jamais le prix global
        $prixPain = $distributionRef->prix_pain
            ?? auth()->user()->boulangerie?->prix_pain
            ?? 0;

        $resultat = DB::transaction(function () use ($validated, $distributionRef, $livreur, $prixPain, $entiersInvendus, $moitiesInvendus, $tiersInvendus, $invendusParts) {
            Livreur::where('id', $livreur->id)->lockForUpdate()->first();   // un seul recalcul des reliquats à la fois pour ce livreur
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

            // Boutique : un vendeur rend son pain dans la journée. Ce qu'il n'a pas vendu
            // n'est donc pas un « restant » mais un RETOUR : il retourne au stock du jour
            // (et devient du pain restant s'il n'est pas redistribué). Les champs saisis
            // sont alors enregistrés comme retour, et le montant porte sur ce qui reste.
            if ($livreur->estVendeur() && ! $distribution->versement()->exists() && $invendusParts > 0) {
                if ($invendusParts > $distribution->partsEnMain()) {
                    return ['ok' => false, 'message' => 'Il ne lui reste que ' . Nombre::pains($distribution->partsEnMain() / 6) . ' en main (après ses ventes saisies).'];
                }

                $distribution->ajouterRetour($livreur, $entiersInvendus, $moitiesInvendus, $tiersInvendus);
                $distribution->refresh();

                $entiersInvendus = $moitiesInvendus = $tiersInvendus = $invendusParts = 0;
            }

            // Sixièmes de pain confiés : donnés − retours.
            $partsConfiees = $distribution->parts_confiees;
            if ($invendusParts > $partsConfiees) {
                return ['ok' => false, 'message' => 'Les restants (' . Nombre::pains($invendusParts / 6) . ') ne peuvent pas dépasser les pains confiés (' . Nombre::pains($partsConfiees / 6) . ').'];
            }

            $estPremierVersement = !$distribution->versement()->exists();
            $montantVerse = (float) $validated['montant_verse'];

            if ($estPremierVersement) {
                // ── 1a. Premier versement : calculs complets ─────────────────
                // Montant en FCFA entiers, calculé sur des entiers (sixièmes)
                // puis arrondi une seule fois au franc le plus proche.
                $montantAttendu = round(($partsConfiees - $invendusParts) * $prixPain / 6);
                $reliquatJour   = max(0.0, $montantAttendu - $montantVerse);
                $surplus        = max(0.0, $montantVerse - $montantAttendu);

                $distribution->update([
                    'entiers_invendus'    => $entiersInvendus,
                    'moities_invendus'    => $moitiesInvendus,
                    'tiers_invendus'      => $tiersInvendus,
                    'invendus_equivalent' => round($invendusParts / 6, 2),
                    'invendus_parts'      => $invendusParts,
                    'montant_attendu'     => $montantAttendu,
                    'reliquat'            => $reliquatJour,
                    'statut'              => $reliquatJour <= 0 ? 'reglee' : 'en_attente',
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

            // ── 3. Reliquats : le surplus éventuel règle les plus anciens reliquats (du + ancien au + récent).
            // Le recalcul est la seule règle utilisée partout (versement, retour, suppression).
            $livreur->recalculerReliquats();

            return ['ok' => true];
        });

        if (!$resultat['ok']) {
            return redirect()->back()
                ->withInput()
                ->with('error', $resultat['message']);
        }

        \App\Models\ActivityLog::record('versement_enregistre', auth()->user()->name . ' a enregistré un versement de ' . number_format((float) $validated['montant_verse'], 0, ',', ' ') . ' FCFA — ' . trim($livreur->prenom . ' ' . $livreur->nom), auth()->user()->boulangerie_id, 'paiement');

        return redirect()->route('livreurs.show', $livreur)
            ->with('success', 'Versement enregistré.');
    }
}
