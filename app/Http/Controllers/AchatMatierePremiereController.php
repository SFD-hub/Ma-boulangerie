<?php

namespace App\Http\Controllers;

use App\Models\AchatMatierePremiere;
use App\Models\ActivityLog;
use App\Models\Depense;
use App\Models\MatierePremiere;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class AchatMatierePremiereController extends Controller
{
    /**
     * Accueil du module Achats : les 3 derniers achats (le stock initial n'est pas un achat) ;
     * l'historique complet est dans « Voir plus ».
     */
    public function index(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        $achats = AchatMatierePremiere::whereHas('matierePremiere', fn ($q) => $q->where('boulangerie_id', $boulangerie_id))
            ->where('depart', false)
            ->with('matierePremiere')
            ->orderByDesc('date_achat')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        return view('achats-matieres-premieres.index', compact('achats'));
    }

    /**
     * Historique des achats, filtrable par période (défaut : le mois en cours,
     * jusqu'à 1 an) et par produit (?matiere=ID, ex. depuis la page d'un produit).
     */
    public function historique(Request $request): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        [$du, $au, $periodeLimitee] = \App\Support\Periode::depuis($request);

        $matieres = MatierePremiere::where('boulangerie_id', $boulangerie_id)->get()
            ->sortBy(fn (MatierePremiere $m) => $m->ordre())
            ->values();

        $matiere = $request->filled('matiere')
            ? $matieres->firstWhere('id', (int) $request->query('matiere'))
            : null;

        $requete = AchatMatierePremiere::whereHas('matierePremiere', fn ($q) => $q->where('boulangerie_id', $boulangerie_id))
            ->when($matiere, fn ($q) => $q->where('matiere_premiere_id', $matiere->id))
            // Le stock initial n'est pas un achat (aucune dépense) : il reste dans le module Stock.
            ->where('depart', false)
            ->whereBetween('date_achat', [$du, $au]);

        // Total et nombre portent sur toute la période, pas sur la page affichée.
        $total  = (float) (clone $requete)->sum('montant');
        $nombre = (clone $requete)->count();

        $achats = $requete
            ->with('matierePremiere')
            ->orderByDesc('date_achat')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('achats-matieres-premieres.historique', compact(
            'achats', 'total', 'nombre', 'du', 'au', 'matiere', 'matieres', 'periodeLimitee'
        ));
    }

    public function create(): View
    {
        $boulangerie_id   = auth()->user()->boulangerie_id;
        $matieresPremières = MatierePremiere::where('boulangerie_id', $boulangerie_id)
            ->orderBy('nom')
            ->get();

        return view('achats-matieres-premieres.create', compact('matieresPremières'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'matiere_premiere_id' => 'required|exists:matieres_premieres,id',
            'quantite'            => 'required|numeric|min:0.5|max:999999.99',
            'montant'             => 'required|numeric|min:0.01',
            'date_achat'          => 'required|date|after_or_equal:2020-01-01|before_or_equal:today',
        ], [
            'matiere_premiere_id.required' => 'La matière première est obligatoire.',
            'matiere_premiere_id.exists'   => 'La matière première sélectionnée n\'existe pas.',
            'quantite.required'            => 'La quantité est obligatoire.',
            'quantite.numeric'             => 'La quantité doit être un nombre (les demi-sacs sont acceptés, ex: 5.5).',
            'quantite.min'                 => 'La quantité doit être au moins 0,5.',
            'quantite.max'                 => 'La quantité est trop élevée.',
            'montant.required'             => 'Le prix total est obligatoire.',
            'montant.numeric'              => 'Le prix total doit être un nombre.',
            'montant.min'                  => 'Le prix total doit être supérieur à 0.',
            'date_achat.required'          => 'La date d\'achat est obligatoire.',
            'date_achat.before_or_equal'   => 'La date ne peut pas être dans le futur.',
        ]);

        $boulangerie_id   = auth()->user()->boulangerie_id;
        $matierePremiere  = MatierePremiere::findOrFail($validated['matiere_premiere_id']);

        abort_if($matierePremiere->boulangerie_id !== $boulangerie_id, 403);

        // Sel, huile, gasoil : comptés en sacs/bidons entiers (« terminé » retire 1).
        if ($matierePremiere->retrait_manuel && fmod((float) $validated['quantite'], 1.0) !== 0.0) {
            return redirect()->back()->withInput()
                ->withErrors(['quantite' => 'Pour ' . $matierePremiere->nom . ', saisissez un nombre entier de ' . $matierePremiere->uniteLibelle(2) . '.']);
        }

        // Un achat augmente le stock (et sa valeur) et crée la dépense. Le stock déjà présent à
        // l'installation, lui, passe par « Stock initial » (module Stock), sans dépense.
        DB::transaction(function () use ($validated, $matierePremiere, $boulangerie_id) {
            // Verrouille la ligne de stock pour éviter qu'un achat concurrent
            // (même matière première, même instant) ne lise la même valeur
            // et n'en écrase un des deux à l'enregistrement.
            $mp = MatierePremiere::where('id', $matierePremiere->id)->lockForUpdate()->firstOrFail();

            $achat = AchatMatierePremiere::create($validated);

            // Incrémenter le stock et sa valeur
            $mp->ajouterAuStock((float) $validated['quantite'], (float) $validated['montant']);
            $mp->save();

            // Créer automatiquement la dépense correspondante
            $categorie = $mp->categorieDepense();

            Depense::create([
                'boulangerie_id' => $boulangerie_id,
                'libelle'        => 'Achat ' . $mp->nom . ' — ' . $validated['quantite'] . ' ' . $mp->uniteLibelle($validated['quantite']),
                'categorie'      => $categorie,
                'achat_matiere_premiere_id' => $achat->id,
                'montant'        => $validated['montant'],
                'date_depense'   => $validated['date_achat'],
            ]);

            ActivityLog::record(
                'achat_matiere_premiere',
                auth()->user()->name . ' a acheté ' . $validated['quantite'] . ' ' . $mp->uniteLibelle($validated['quantite']) . ' de ' . $mp->nom . ' — ' . number_format($validated['montant'], 0, ',', ' ') . ' FCFA',
                $boulangerie_id,
                'achat'
            );
        });

        // Achat saisi depuis la page d'un produit : on y reste. Sinon, retour au module Achats.
        $retour = $request->boolean('depuis_produit')
            ? redirect()->route('matieres-premieres.show', $matierePremiere)
            : redirect()->route('achats-matieres-premieres.index');

        return $retour->with('success', 'Achat enregistré et dépense créée automatiquement.');
    }

    public function show(AchatMatierePremiere $achatMatierePremiere): View
    {
        $achatMatierePremiere->load('matierePremiere');
        abort_if($achatMatierePremiere->matierePremiere?->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        return view('achats-matieres-premieres.show', compact('achatMatierePremiere'));
    }

    public function edit(AchatMatierePremiere $achatMatierePremiere): View
    {
        $boulangerie_id   = auth()->user()->boulangerie_id;
        $achatMatierePremiere->load('matierePremiere');
        abort_if($achatMatierePremiere->matierePremiere?->boulangerie_id !== $boulangerie_id, 403);

        $matieresPremières = MatierePremiere::where('boulangerie_id', $boulangerie_id)
            ->orderBy('nom')
            ->get();

        return view('achats-matieres-premieres.edit', compact('achatMatierePremiere', 'matieresPremières'));
    }

    public function update(Request $request, AchatMatierePremiere $achatMatierePremiere): RedirectResponse
    {
        $validated = $request->validate([
            'matiere_premiere_id' => 'required|exists:matieres_premieres,id',
            'quantite'            => 'required|numeric|min:0.5|max:999999.99',
            'montant'             => 'required|numeric|min:0.01',
            'date_achat'          => 'required|date|after_or_equal:2020-01-01|before_or_equal:today',
        ], [
            'matiere_premiere_id.required' => 'La matière première est obligatoire.',
            'quantite.required'            => 'La quantité est obligatoire.',
            'quantite.min'                 => 'La quantité doit être au moins 0,5.',
            'quantite.max'                 => 'La quantité est trop élevée.',
            'montant.required'             => 'Le prix total est obligatoire.',
            'montant.min'                  => 'Le prix total doit être supérieur à 0.',
            'date_achat.required'          => 'La date d\'achat est obligatoire.',
            'date_achat.before_or_equal'   => 'La date ne peut pas être dans le futur.',
        ]);

        $boulangerie_id  = auth()->user()->boulangerie_id;
        // Vérifie que l'achat lui-même appartient à cette boulangerie
        $achatMatierePremiere->load('matierePremiere');
        abort_if($achatMatierePremiere->matierePremiere?->boulangerie_id !== $boulangerie_id, 403);

        // Le produit d'un achat ne change pas (sinon le stock et sa valeur se mélangeraient) :
        // on corrige la quantité, le prix ou la date.
        $validated['matiere_premiere_id'] = $achatMatierePremiere->matiere_premiere_id;

        $matierePremiereRef = MatierePremiere::findOrFail($validated['matiere_premiere_id']);
        abort_if($matierePremiereRef->boulangerie_id !== $boulangerie_id, 403);

        if ($matierePremiereRef->retrait_manuel && fmod((float) $validated['quantite'], 1.0) !== 0.0) {
            return redirect()->back()->withInput()
                ->withErrors(['quantite' => 'Pour ' . $matierePremiereRef->nom . ', saisissez un nombre entier de ' . $matierePremiereRef->uniteLibelle(2) . '.']);
        }

        $diff = $validated['quantite'] - $achatMatierePremiere->quantite;

        $resultat = DB::transaction(function () use ($validated, $achatMatierePremiere, $matierePremiereRef, $diff, $boulangerie_id) {
            // Verrouille la ligne de stock avant de revérifier la suffisance —
            // la vérification faite avant la transaction peut être obsolète
            // si un autre achat/production a modifié le stock entre-temps.
            $mp = MatierePremiere::where('id', $matierePremiereRef->id)->lockForUpdate()->firstOrFail();

            if ($mp->stock_actuel + $diff < 0) {
                return false;
            }

            // Baisser le prix d'un achat déjà consommé fausserait le coût des pains déjà produits.
            if ((float) $mp->valeur_stock + ((float) $validated['montant'] - (float) $achatMatierePremiere->montant) < -0.5) {
                return 'prix';
            }

            $ancienMontant = $achatMatierePremiere->montant;
            $achatMatierePremiere->update($validated);

            $mp->stock_actuel += $diff;
            // La valeur du stock suit le montant corrigé de l'achat.
            $mp->valeur_stock = max(0, (float) $mp->valeur_stock + ((float) $validated['montant'] - (float) $ancienMontant));
            $mp->save();

            // Un stock de départ n'a pas de dépense liée.
            if ($achatMatierePremiere->depart) {
                return true;
            }

            // Mettre à jour la dépense automatique liée (même date, même libellé contenant la matière)
            $categorie = $mp->categorieDepense();
            $this->depenseLiee($achatMatierePremiere, $boulangerie_id, $categorie, $ancienMontant, $achatMatierePremiere->getOriginal('date_achat'))
                ?->update([
                    'libelle'      => 'Achat ' . $mp->nom . ' — ' . $validated['quantite'] . ' ' . $mp->uniteLibelle($validated['quantite']),
                    'montant'      => $validated['montant'],
                    'date_depense' => $validated['date_achat'],
                    'achat_matiere_premiere_id' => $achatMatierePremiere->id,
                ]);

            return true;
        });

        if ($resultat === 'prix') {
            return redirect()->back()->withInput()
                ->with('error', 'Prix trop bas : une grande partie de cet achat a déjà été utilisée. Le prix ne peut pas descendre en dessous de la valeur déjà consommée.');
        }

        if (! $resultat) {
            return redirect()->back()
                ->with('error', 'Stock insuffisant pour réduire la quantité de cet achat.');
        }

        // Modifié depuis la page d'un produit : on y reste.
        $retour = $request->boolean('depuis_produit')
            ? redirect()->route('matieres-premieres.show', $achatMatierePremiere->matiere_premiere_id)
            : redirect()->route('achats-matieres-premieres.index');

        return $retour->with('success', 'Achat mis à jour.');
    }

    public function destroy(Request $request, AchatMatierePremiere $achatMatierePremiere): RedirectResponse
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        $achatMatierePremiere->load('matierePremiere');
        abort_if($achatMatierePremiere->matierePremiere?->boulangerie_id !== $boulangerie_id, 403);

        $refus = DB::transaction(function () use ($achatMatierePremiere, $boulangerie_id) {
            $matierePremiere = MatierePremiere::where('id', $achatMatierePremiere->matiere_premiere_id)->lockForUpdate()->firstOrFail();

            // Cet achat a déjà été (en partie) consommé : le supprimer fausserait le stock et le bénéfice.
            if ((float) $matierePremiere->stock_actuel + 0.0001 < (float) $achatMatierePremiere->quantite
                || (float) $matierePremiere->valeur_stock - (float) $achatMatierePremiere->montant < -0.5) {
                return true;
            }

            $matierePremiere->stock_actuel = $matierePremiere->stock_actuel - $achatMatierePremiere->quantite;
            $matierePremiere->valeur_stock = max(0, (float) $matierePremiere->valeur_stock - (float) $achatMatierePremiere->montant);
            $matierePremiere->save();

            // Un stock de départ n'a pas de dépense liée.
            if (! $achatMatierePremiere->depart) {
                $categorie = $matierePremiere->categorieDepense();
                $this->depenseLiee($achatMatierePremiere, $boulangerie_id, $categorie, $achatMatierePremiere->montant, $achatMatierePremiere->date_achat)?->delete();
            }

            $achatMatierePremiere->delete();

            return false;
        });

        if ($refus) {
            return redirect()->back()
                ->with('error', 'Suppression impossible : une partie de cet achat a déjà été utilisée (production, retrait). Corrigez plutôt la quantité.');
        }

        // Supprimé depuis la page d'un produit : on y reste.
        $retour = $request->boolean('depuis_produit')
            ? redirect()->route('matieres-premieres.show', $achatMatierePremiere->matiere_premiere_id)
            : redirect()->route('achats-matieres-premieres.index');

        return $retour->with('success', 'Achat supprimé et stock ajusté.');
    }

    /**
     * La dépense automatique d'un achat : retrouvée par son lien ; pour les anciens achats
     * sans lien, par catégorie, montant et date (comportement d'origine).
     */
    private function depenseLiee(AchatMatierePremiere $achat, int $boulangerieId, string $categorie, $montant, $date): ?Depense
    {
        $liee = Depense::where('boulangerie_id', $boulangerieId)->where('achat_matiere_premiere_id', $achat->id)->first();
        if ($liee) {
            return $liee;
        }

        return Depense::where('boulangerie_id', $boulangerieId)
            ->whereNull('achat_matiere_premiere_id')
            ->where('categorie', $categorie)
            ->where('montant', $montant)
            ->whereDate('date_depense', substr((string) $date, 0, 10))
            ->orderByDesc('id')
            ->first();
    }
}
