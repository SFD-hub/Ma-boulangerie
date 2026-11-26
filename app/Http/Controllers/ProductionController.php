<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Production;
use App\Models\MatierePremiere;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProductionController extends Controller
{
    public function index(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        // Cette page n'affiche jamais plus de 4 productions (la dernière +
        // un aperçu de 3) : inutile de charger tout l'historique en mémoire.
        $productions = Production::where('boulangerie_id', $boulangerie_id)
            ->with('produit')
            ->orderBy('date_production', 'desc')
            ->orderByDesc('created_at')
            ->limit(4)
            ->get();

        $totalPains = Production::where('boulangerie_id', $boulangerie_id)
            ->sum('nombre_pains_produits');

        return view('productions.index', compact('productions', 'totalPains'));
    }

    // Historique des productions : sélecteur de période, 10 par page, total de la période.
    public function historique(Request $request): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        [$du, $au, $periodeLimitee] = \App\Support\Periode::depuis($request);

        $requete = Production::where('boulangerie_id', $boulangerie_id)
            ->whereBetween('date_production', [$du, $au]);

        // Total et nombre portent sur toute la période, pas sur la page affichée.
        $totalPains = (int) (clone $requete)->sum('nombre_pains_produits');
        $nombre     = (clone $requete)->count();

        $productions = $requete
            ->with('produit')
            ->orderBy('date_production', 'desc')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('productions.historique', compact('productions', 'totalPains', 'nombre', 'du', 'au', 'periodeLimitee'));
    }

    public function create(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        $produits = Produit::where('boulangerie_id', $boulangerie_id)
            ->where('actif', true)
            ->orderBy('nom')
            ->get();

        $defaultProduitId = Produit::defaultIdPour($boulangerie_id);

        return view('productions.create', compact('produits', 'defaultProduitId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        $validated = $request->validate([
            'produit_id' => ['required', Rule::exists('produits', 'id')->where('boulangerie_id', $boulangerie_id)],
            'date_production' => 'required|date|after_or_equal:2020-01-01|before_or_equal:today',
            'nombre_sacs' => 'required|numeric|min:0.5|max:999999.99',
            'nombre_paquets_levure' => 'required|numeric|min:0.5|max:999999.99',
            'nombre_paquets_produit' => 'nullable|numeric|min:0|max:999999.99',
            'nombre_pains_produits' => 'required|integer|min:1',
        ], [
            'produit_id.required' => 'Le produit est obligatoire.',
            'date_production.required' => 'La date de production est obligatoire.',
            'date_production.date' => 'La date doit être une date valide.',
            'date_production.before_or_equal' => 'La date ne peut pas être dans le futur.',
            'nombre_sacs.required' => 'Le nombre de sacs est obligatoire.',
            'nombre_sacs.numeric' => 'Le nombre de sacs doit être un nombre (les demi-sacs sont acceptés, ex: 5.5).',
            'nombre_sacs.min' => 'Le nombre de sacs doit être au moins 0,5.',
            'nombre_sacs.max' => 'Le nombre de sacs est trop élevé.',
            'nombre_paquets_levure.required' => 'Le nombre de paquets de levure est obligatoire.',
            'nombre_paquets_levure.numeric' => 'Le nombre de paquets de levure doit être un nombre (les demi-paquets sont acceptés).',
            'nombre_paquets_levure.min' => 'Le nombre de paquets de levure doit être au moins 0,5.',
            'nombre_paquets_levure.max' => 'Le nombre de paquets de levure est trop élevé.',
            'nombre_paquets_produit.numeric' => 'Le nombre de paquets de produit doit être un nombre (les demi-paquets sont acceptés).',
            'nombre_paquets_produit.min' => 'Le nombre de paquets de produit ne peut pas être négatif.',
            'nombre_paquets_produit.max' => 'Le nombre de paquets de produit est trop élevé.',
            'nombre_pains_produits.required' => 'Le nombre de pains produits est obligatoire.',
            'nombre_pains_produits.integer' => 'Le nombre de pains doit être un nombre entier.',
            'nombre_pains_produits.min' => 'Le nombre de pains doit être au moins 1.',
        ]);

        $quantite_farine_necessaire = $validated['nombre_sacs'];
        $quantite_levure_necessaire = $validated['nombre_paquets_levure'];
        $quantite_produit_necessaire = (float) ($validated['nombre_paquets_produit'] ?? 0);

        $resultat = DB::transaction(function () use ($validated, $boulangerie_id, $quantite_farine_necessaire, $quantite_levure_necessaire, $quantite_produit_necessaire) {
            // Verrouille les deux lignes de stock avant de vérifier la
            // suffisance : évite que deux productions saisies au même
            // instant ne lisent le même stock et ne s'écrasent l'une
            // l'autre à l'enregistrement.
            $farine = MatierePremiere::where('boulangerie_id', $boulangerie_id)
                ->where('nom', 'Farine')
                ->lockForUpdate()
                ->first();

            $levure = MatierePremiere::where('boulangerie_id', $boulangerie_id)
                ->where('nom', 'Levure')
                ->lockForUpdate()
                ->first();

            // "Produit" (paquets) : déduit comme la levure, seulement s'il est utilisé.
            $produitStock = $quantite_produit_necessaire > 0
                ? MatierePremiere::where('boulangerie_id', $boulangerie_id)->where('nom', 'Produit')->lockForUpdate()->first()
                : null;

            if (!$farine) {
                return ['ok' => false, 'message' => 'Matière première "Farine" introuvable. Vérifiez les matières premières.'];
            }

            if ($farine->stock_actuel < $quantite_farine_necessaire) {
                return ['ok' => false, 'message' => 'Stock de farine insuffisant (disponible : ' . $farine->stock_actuel . ' sacs, nécessaire : ' . $quantite_farine_necessaire . ' sacs).'];
            }

            if (!$levure) {
                return ['ok' => false, 'message' => 'Matière première "Levure" introuvable. Vérifiez les matières premières.'];
            }

            if ($levure->stock_actuel < $quantite_levure_necessaire) {
                return ['ok' => false, 'message' => 'Stock de levure insuffisant (disponible : ' . $levure->stock_actuel . ' paquets, nécessaire : ' . $quantite_levure_necessaire . ' paquets).'];
            }

            if ($quantite_produit_necessaire > 0) {
                if (!$produitStock) {
                    return ['ok' => false, 'message' => 'Matière première "Produit" introuvable. Vérifiez le stock.'];
                }
                if ($produitStock->stock_actuel < $quantite_produit_necessaire) {
                    return ['ok' => false, 'message' => 'Stock de produit insuffisant (disponible : ' . $produitStock->stock_actuel . ' paquets, nécessaire : ' . $quantite_produit_necessaire . ' paquets).'];
                }
            }

            // Coût des matières consommées par cette production : valorisé au prix moyen du
            // stock, et enregistré ici (il sert au bénéfice du mois, pas les achats).
            $coutFarine  = $farine->retirerDuStock((float) $quantite_farine_necessaire);
            $coutLevure  = $levure->retirerDuStock((float) $quantite_levure_necessaire);
            $coutProduit = $produitStock ? $produitStock->retirerDuStock($quantite_produit_necessaire) : 0.0;

            Production::create([
                'produit_id' => $validated['produit_id'],
                'date_production' => $validated['date_production'],
                'nombre_sacs' => $validated['nombre_sacs'],
                'quantite_farine' => $quantite_farine_necessaire,
                'quantite_levure' => $quantite_levure_necessaire,
                'quantite_produit' => $quantite_produit_necessaire,
                'nombre_pains_produits' => $validated['nombre_pains_produits'],
                'cout_farine' => $coutFarine,
                'cout_levure' => $coutLevure,
                'cout_produit' => $coutProduit,
                'boulangerie_id' => $boulangerie_id,
            ]);

            $farine->save();
            $levure->save();

            if ($produitStock) {
                $produitStock->save();
            }

            ActivityLog::record(
                'production_enregistree',
                auth()->user()->name . ' a enregistré une production de ' . number_format($validated['nombre_pains_produits'], 0, ',', ' ') . ' pains',
                $boulangerie_id,
                'production'
            );

            return ['ok' => true];
        });

        if (!$resultat['ok']) {
            Log::warning('[Production.store] Échec', ['boulangerie_id' => $boulangerie_id, 'raison' => $resultat['message']]);

            return redirect()->back()->with('error', $resultat['message']);
        }

        return redirect()->route('productions.index')
            ->with('success', 'Production créée avec succès.');
    }

    public function show(Production $production): View
    {
        abort_if($production->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        return view('productions.show', compact('production'));
    }

    public function edit(Production $production): View
    {
        abort_if($production->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $produits = Produit::where('boulangerie_id', $production->boulangerie_id)
            ->where('actif', true)
            ->orderBy('nom')
            ->get();

        return view('productions.edit', compact('production', 'produits'));
    }

    public function update(Request $request, Production $production): RedirectResponse
    {
        abort_if($production->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $validated = $request->validate([
            'produit_id' => ['required', Rule::exists('produits', 'id')->where('boulangerie_id', $production->boulangerie_id)],
            'date_production' => 'required|date|after_or_equal:2020-01-01|before_or_equal:today',
            'nombre_sacs' => 'required|numeric|min:0.5|max:999999.99',
            'nombre_paquets_levure' => 'required|numeric|min:0.5|max:999999.99',
            'nombre_paquets_produit' => 'nullable|numeric|min:0|max:999999.99',
            'nombre_pains_produits' => 'required|integer|min:1',
        ], [
            'produit_id.required' => 'Le produit est obligatoire.',
            'date_production.required' => 'La date de production est obligatoire.',
            'date_production.date' => 'La date doit être une date valide.',
            'date_production.before_or_equal' => 'La date ne peut pas être dans le futur.',
            'nombre_sacs.required' => 'Le nombre de sacs est obligatoire.',
            'nombre_sacs.numeric' => 'Le nombre de sacs doit être un nombre (les demi-sacs sont acceptés, ex: 5.5).',
            'nombre_sacs.min' => 'Le nombre de sacs doit être au moins 0,5.',
            'nombre_sacs.max' => 'Le nombre de sacs est trop élevé.',
            'nombre_paquets_levure.required' => 'Le nombre de paquets de levure est obligatoire.',
            'nombre_paquets_levure.numeric' => 'Le nombre de paquets de levure doit être un nombre (les demi-paquets sont acceptés).',
            'nombre_paquets_levure.min' => 'Le nombre de paquets de levure doit être au moins 0,5.',
            'nombre_paquets_levure.max' => 'Le nombre de paquets de levure est trop élevé.',
            'nombre_paquets_produit.numeric' => 'Le nombre de paquets de produit doit être un nombre (les demi-paquets sont acceptés).',
            'nombre_paquets_produit.min' => 'Le nombre de paquets de produit ne peut pas être négatif.',
            'nombre_paquets_produit.max' => 'Le nombre de paquets de produit est trop élevé.',
            'nombre_pains_produits.required' => 'Le nombre de pains produits est obligatoire.',
            'nombre_pains_produits.integer' => 'Le nombre de pains doit être un nombre entier.',
            'nombre_pains_produits.min' => 'Le nombre de pains doit être au moins 1.',
        ]);

        $boulangerie_id = auth()->user()->boulangerie_id;

        if ($refus = $this->refusProductionDejaDistribuee($production, [
            'produit_id' => (int) $validated['produit_id'],
            'date'       => \Carbon\Carbon::parse($validated['date_production'])->toDateString(),
            'pains'      => (int) $validated['nombre_pains_produits'],
        ])) {
            return redirect()->back()->withInput()->with('error', $refus);
        }

        $quantite_farine_necessaire = $validated['nombre_sacs'];
        $quantite_levure_necessaire = $validated['nombre_paquets_levure'];

        // Calculer les différences pour ajuster le stock
        $diffFarine = $quantite_farine_necessaire - $production->quantite_farine;
        $diffLevure = $quantite_levure_necessaire - $production->quantite_levure;
        $quantite_produit_necessaire = (float) ($validated['nombre_paquets_produit'] ?? 0);
        $diffProduit = $quantite_produit_necessaire - (float) $production->quantite_produit;

        $resultat = DB::transaction(function () use ($validated, $production, $boulangerie_id, $diffFarine, $diffLevure, $diffProduit, $quantite_farine_necessaire, $quantite_levure_necessaire, $quantite_produit_necessaire) {
            $farine = MatierePremiere::where('boulangerie_id', $boulangerie_id)
                ->where('nom', 'Farine')
                ->lockForUpdate()
                ->first();
            $levure = MatierePremiere::where('boulangerie_id', $boulangerie_id)
                ->where('nom', 'Levure')
                ->lockForUpdate()
                ->first();

            // "Produit" : verrouillé seulement s'il y a quelque chose à ajuster.
            $produitStock = $diffProduit != 0
                ? MatierePremiere::where('boulangerie_id', $boulangerie_id)->where('nom', 'Produit')->lockForUpdate()->first()
                : null;

            if ($diffProduit != 0 && !$produitStock) {
                return ['ok' => false, 'message' => 'Matière première "Produit" introuvable. Vérifiez le stock.'];
            }

            if ($produitStock && $produitStock->stock_actuel - $diffProduit < 0) {
                return ['ok' => false, 'message' => 'Stock de produit insuffisant pour cette mise à jour.'];
            }

            if ($farine->stock_actuel - $diffFarine < 0) {
                return ['ok' => false, 'message' => 'Stock de farine insuffisant pour cette mise à jour.'];
            }

            if ($levure->stock_actuel - $diffLevure < 0) {
                return ['ok' => false, 'message' => 'Stock de levure insuffisant pour cette mise à jour.'];
            }

            // Coût des matières consommées : si la quantité d'une matière change, on rend d'abord au
            // stock ce que l'ancienne production avait consommé (quantité + coût), puis on retire la
            // nouvelle quantité au prix moyen du moment. Sans changement, le coût reste celui d'origine.
            $recalculer = function (?MatierePremiere $mp, float $ancienneQte, float $ancienCout, float $nouvelleQte, float $diff): float {
                if (! $mp || $diff == 0.0) {
                    return $ancienCout;
                }

                $mp->remettreEnStock($ancienneQte, $ancienCout);

                return $mp->retirerDuStock($nouvelleQte);
            };

            $coutFarine  = $recalculer($farine, (float) $production->quantite_farine, (float) $production->cout_farine, (float) $quantite_farine_necessaire, (float) $diffFarine);
            $coutLevure  = $recalculer($levure, (float) $production->quantite_levure, (float) $production->cout_levure, (float) $quantite_levure_necessaire, (float) $diffLevure);
            $coutProduit = $recalculer($produitStock, (float) $production->quantite_produit, (float) $production->cout_produit, (float) $quantite_produit_necessaire, (float) $diffProduit);

            $production->update([
                'produit_id' => $validated['produit_id'],
                'date_production' => $validated['date_production'],
                'nombre_sacs' => $validated['nombre_sacs'],
                'quantite_farine' => $quantite_farine_necessaire,
                'quantite_levure' => $quantite_levure_necessaire,
                'quantite_produit' => $quantite_produit_necessaire,
                'nombre_pains_produits' => $validated['nombre_pains_produits'],
                'cout_farine' => $coutFarine,
                'cout_levure' => $coutLevure,
                'cout_produit' => $coutProduit,
            ]);

            $farine->save();
            $levure->save();

            if ($produitStock) {
                $produitStock->save();
            }

            return ['ok' => true];
        });

        if (!$resultat['ok']) {
            return redirect()->back()->with('error', $resultat['message']);
        }

        return redirect()->route('productions.index')
            ->with('success', 'Production mise à jour avec succès.');
    }

    /**
     * Le pain distribué un jour ne doit jamais dépasser la production de ce jour :
     * on refuse de supprimer ou de réduire une production si des pains en ont
     * déjà été distribués. Retourne le message de refus, ou null si tout va bien.
     *
     * @param  array{produit_id:int,date:string,pains:int}|null  $nouveau  Nouvelle
     *         valeur (modification) ; null = suppression.
     */
    private function refusProductionDejaDistribuee(Production $production, ?array $nouveau = null): ?string
    {
        $verifier = function (int $produitId, string $date, int $painsAjoutes) use ($production): ?string {
            $produit = Produit::find($produitId);
            if (! $produit) {
                return null;
            }

            $produits = $produit->partsProduitesLe($date, $production->id) + $painsAjoutes * 6;
            $sorties  = $produit->partsSortiesLe($date);

            if ($produits < $sorties) {
                return 'Des pains de cette production ont déjà été distribués ('
                    . intdiv($sorties, 6) . ' pains le ' . \Carbon\Carbon::parse($date)->format('d/m/Y')
                    . '). La production ne peut pas descendre en dessous.';
            }

            return null;
        };

        $ancienneDate = \Carbon\Carbon::parse($production->date_production)->toDateString();
        $memeCle = $nouveau
            && (int) $nouveau['produit_id'] === (int) $production->produit_id
            && $nouveau['date'] === $ancienneDate;

        // Ancien jour/produit : ce que cette production fournissait n'existe plus
        // (sauf si la modification le reconduit, vérifié juste après avec la nouvelle quantité).
        if (! $memeCle && ($refus = $verifier((int) $production->produit_id, $ancienneDate, 0))) {
            return $refus;
        }

        return $nouveau
            ? $verifier((int) $nouveau['produit_id'], $nouveau['date'], (int) $nouveau['pains'])
            : null;
    }

    public function destroy(Production $production): RedirectResponse
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        abort_if($production->boulangerie_id !== $boulangerie_id, 403);

        if ($refus = $this->refusProductionDejaDistribuee($production)) {
            return redirect()->back()->with('error', $refus);
        }

        DB::transaction(function () use ($production, $boulangerie_id) {
            $farine = MatierePremiere::where('boulangerie_id', $boulangerie_id)->where('nom', 'Farine')->lockForUpdate()->first();
            $levure = MatierePremiere::where('boulangerie_id', $boulangerie_id)->where('nom', 'Levure')->lockForUpdate()->first();

            // On rend au stock la quantité ET le coût que cette production avait consommés.
            if ($farine) {
                $farine->remettreEnStock((float) $production->quantite_farine, (float) $production->cout_farine);
                $farine->save();
            }
            if ($levure) {
                $levure->remettreEnStock((float) $production->quantite_levure, (float) $production->cout_levure);
                $levure->save();
            }
            if ((float) $production->quantite_produit > 0) {
                $produitStock = MatierePremiere::where('boulangerie_id', $boulangerie_id)->where('nom', 'Produit')->lockForUpdate()->first();
                if ($produitStock) {
                    $produitStock->remettreEnStock((float) $production->quantite_produit, (float) $production->cout_produit);
                    $produitStock->save();
                }
            }
            $production->delete();
        });

        return redirect()->route('productions.index')
            ->with('success', 'Production supprimée et stock restauré.');
    }
}
