<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Depense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DepenseController extends Controller
{
    // Catégories saisies manuellement (les achats stock — farine, levure,
    // produit, sel, huile, gasoil — sont auto-créés depuis le module Achats).
    const CATEGORIES_MANUELLES = [
        'salaire_gerant'  => 'Salaire gérant',
        'salaire_employe' => 'Salaire employé',
        'eau'             => 'Eau',
        'electricite'     => 'Électricité',
        'transport'       => 'Transport',
        'reparation'      => 'Réparation / Maintenance',
        'autre'           => 'Autre',
    ];

    /**
     * Catégories proposées à la modification d'une dépense : la liste
     * manuelle, plus la catégorie actuelle de la dépense si elle n'y figure
     * plus (ex. ancienne dépense « Carburant » / « Sel » / « Huile » saisie
     * avant leur passage dans le stock) pour ne jamais la perdre.
     */
    private function categoriesPour(Depense $depense): array
    {
        $categories = self::CATEGORIES_MANUELLES;

        if (! isset($categories[$depense->categorie]) && isset(self::CATEGORIES_LIBELLES[$depense->categorie])) {
            $categories[$depense->categorie] = self::CATEGORIES_LIBELLES[$depense->categorie];
        }

        return $categories;
    }

    // Dépenses créées automatiquement par un achat de stock : non modifiables
    // ici, on les gère depuis le module Achats.
    const CATEGORIES_AUTO = [
        'achat_farine',
        'achat_levure',
        'achat_produit',
        'achat_sel',
        'achat_huile',
        'achat_gasoil',
        'achat_stock',
    ];

    // Toutes les catégories (pour l'affichage, inclut les anciennes valeurs pour la rétro-compatibilité)
    const CATEGORIES_LIBELLES = [
        'achat_farine'   => 'Achat farine',
        'achat_levure'   => 'Achat levure',
        'achat_produit'  => 'Achat produit',
        'achat_sel'      => 'Achat sel',
        'achat_huile'    => 'Achat huile',
        'achat_gasoil'   => 'Achat gasoil',
        'achat_stock'    => 'Achat stock (autre)',
        'salaire'        => 'Salaire gérant',
        'salaire_gerant' => 'Salaire gérant',
        'salaire_employe'=> 'Salaire employé',
        'eau'            => 'Eau',
        'electricite'    => 'Électricité',
        'transport'      => 'Transport',
        'carburant'      => 'Carburant',
        'reparation'     => 'Réparation / Maintenance',
        'entretien'      => 'Entretien',
        'huile'          => 'Huile',
        'sel'            => 'Sel',
        'divers'         => 'Divers',
        'autre'          => 'Autre',
    ];

    // Icônes par catégorie
    const CATEGORIES_ICONES = [
        'achat_farine'   => '🌾',
        'achat_levure'   => '🧪',
        'achat_produit'  => '📦',
        'achat_sel'      => '🧂',
        'achat_huile'    => '🫙',
        'achat_gasoil'   => '⛽',
        'achat_stock'    => '📦',
        'salaire'        => '👤',
        'salaire_gerant' => '👤',
        'salaire_employe'=> '👥',
        'eau'            => '💧',
        'electricite'    => '⚡',
        'transport'      => '🚗',
        'carburant'      => '⛽',
        'reparation'     => '🔧',
        'entretien'      => '🔧',
        'huile'          => '🫙',
        'sel'            => '🧂',
        'divers'         => '📌',
        'autre'          => '📌',
    ];

    /**
     * Période (du… au…) lue dans l'URL : mois en cours par défaut, dates
     * inversées remises dans l'ordre, durée limitée à 1 an.
     *
     * @return array{0:string,1:string,2:bool} [du, au, periodeLimitee]
     */
    private function periode(Request $request): array
    {
        $valide = fn (?string $d) => $d && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;

        $du = $valide($request->query('du')) ? $request->query('du') : Carbon::today()->startOfMonth()->toDateString();
        $au = $valide($request->query('au')) ? $request->query('au') : Carbon::today()->toDateString();

        if ($du > $au) {
            [$du, $au] = [$au, $du];
        }

        $limitee = false;
        $minimum = Carbon::parse($au)->subYear()->addDay()->toDateString();
        if ($du < $minimum) {
            $du = $minimum;
            $limitee = true;
        }

        return [$du, $au, $limitee];
    }

    /**
     * Entrée du module : une carte par catégorie (total, nombre, part) sur
     * la période choisie. Toucher une carte ouvre le détail de la catégorie.
     */
    public function index(Request $request): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        [$du, $au, $periodeLimitee] = $this->periode($request);

        $groupes = Depense::where('boulangerie_id', $boulangerie_id)
            ->whereBetween('date_depense', [$du, $au])
            ->selectRaw('categorie, SUM(montant) as total, COUNT(*) as nombre')
            ->groupBy('categorie')
            ->get()
            ->map(fn ($g) => [
                'categorie' => $g->categorie,
                'libelle'   => self::CATEGORIES_LIBELLES[$g->categorie] ?? ucfirst(str_replace('_', ' ', $g->categorie)),
                'total'     => (float) $g->total,
                'nombre'    => (int) $g->nombre,
            ])
            ->sortByDesc('total')
            ->values();

        $totalPeriode = (float) $groupes->sum('total');

        return view('depenses.index', compact('du', 'au', 'periodeLimitee', 'groupes', 'totalPeriode'));
    }

    /**
     * Détail d'une catégorie sur la période choisie.
     */
    public function categorie(Request $request, string $categorie): View
    {
        abort_unless(isset(self::CATEGORIES_LIBELLES[$categorie]), 404);

        $boulangerie_id = auth()->user()->boulangerie_id;
        [$du, $au, $periodeLimitee] = $this->periode($request);

        $requete = Depense::where('boulangerie_id', $boulangerie_id)
            ->where('categorie', $categorie)
            ->whereBetween('date_depense', [$du, $au]);

        // Le total et le nombre portent sur toute la période, pas sur la page affichée.
        $total   = (float) (clone $requete)->sum('montant');
        $nombre  = (clone $requete)->count();

        $depenses = $requete
            ->orderByDesc('date_depense')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $libelle        = self::CATEGORIES_LIBELLES[$categorie];
        $estAutomatique = in_array($categorie, self::CATEGORIES_AUTO, true);

        return view('depenses.categorie', compact('categorie', 'libelle', 'depenses', 'total', 'nombre', 'du', 'au', 'periodeLimitee', 'estAutomatique'));
    }

    // Toutes les dépenses : sélecteur de période, 10 par page, total de la période.
    public function historique(Request $request): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        [$du, $au, $periodeLimitee] = \App\Support\Periode::depuis($request);

        $requete = Depense::where('boulangerie_id', $boulangerie_id)
            ->whereBetween('date_depense', [$du, $au]);

        // Total et nombre portent sur toute la période, pas sur la page affichée.
        $totalGlobal = (float) (clone $requete)->sum('montant');
        $nombre      = (clone $requete)->count();

        $depenses = $requete
            ->orderByDesc('date_depense')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('depenses.historique', compact('depenses', 'totalGlobal', 'nombre', 'du', 'au', 'periodeLimitee'));
    }

    public function jour(Request $request): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        $date = $request->query('date');
        $date = ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) ? $date : Carbon::today()->toDateString();

        $depenses = Depense::where('boulangerie_id', $boulangerie_id)
            ->whereDate('date_depense', $date)
            ->get();

        $totalDepense = (float) $depenses->sum('montant');

        return view('depenses.jour', compact('date', 'depenses', 'totalDepense'));
    }

    public function create(): View
    {
        return view('depenses.create', ['categories' => self::CATEGORIES_MANUELLES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'libelle'     => 'required|string|min:3|max:255',
            'categorie'   => 'required|string|in:' . implode(',', array_keys(self::CATEGORIES_MANUELLES)),
            'montant'     => 'required|numeric|min:0.01',
            'date_depense'=> 'required|date|before_or_equal:today',
        ], [
            'libelle.required'      => 'Le motif de la dépense est obligatoire.',
            'libelle.min'           => 'Le motif doit contenir au moins 3 caractères.',
            'categorie.required'    => 'La catégorie est obligatoire.',
            'categorie.in'          => 'Catégorie invalide.',
            'montant.required'      => 'Le montant est obligatoire.',
            'montant.min'           => 'Le montant doit être supérieur à 0.',
            'date_depense.required' => 'La date est obligatoire.',
            'date_depense.before_or_equal' => 'La date ne peut pas être dans le futur.',
        ]);

        $boulangerie_id = auth()->user()->boulangerie_id;

        Depense::create(array_merge($validated, [
            'boulangerie_id' => $boulangerie_id,
        ]));

        ActivityLog::record(
            'depense_creee',
            auth()->user()->name . ' a ajouté une dépense de ' . number_format($validated['montant'], 0, ',', ' ') . ' FCFA — ' . $validated['libelle'],
            $boulangerie_id,
            'depense'
        );

        return redirect()->route('depenses.index')
            ->with('success', 'Dépense ajoutée.');
    }

    public function show(Depense $depense): View
    {
        abort_if($depense->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        return view('depenses.show', compact('depense'));
    }

    public function edit(Depense $depense): View|RedirectResponse
    {
        abort_if($depense->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        // Les achats automatiques (farine/levure) ne sont pas modifiables ici
        if (in_array($depense->categorie, self::CATEGORIES_AUTO, true)) {
            return redirect()->route('achats-matieres-premieres.index')
                ->with('warning', 'Modifiez cette dépense depuis le module Achats.');
        }

        return view('depenses.edit', [
            'depense'    => $depense,
            'categories' => $this->categoriesPour($depense),
        ]);
    }

    public function update(Request $request, Depense $depense): RedirectResponse
    {
        abort_if($depense->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        if (in_array($depense->categorie, self::CATEGORIES_AUTO, true)) {
            return redirect()->route('achats-matieres-premieres.index')
                ->with('warning', 'Modifiez cette dépense depuis le module Achats.');
        }

        $validated = $request->validate([
            'libelle'      => 'required|string|min:3|max:255',
            'categorie'    => 'required|string|in:' . implode(',', array_keys($this->categoriesPour($depense))),
            'montant'      => 'required|numeric|min:0.01',
            'date_depense' => 'required|date|before_or_equal:today',
        ], [
            'libelle.required'             => 'Le motif de la dépense est obligatoire.',
            'libelle.min'                  => 'Le motif doit contenir au moins 3 caractères.',
            'categorie.in'                 => 'Catégorie invalide.',
            'montant.min'                  => 'Le montant doit être supérieur à 0.',
            'date_depense.before_or_equal' => 'La date ne peut pas être dans le futur.',
        ]);

        $depense->update($validated);

        return redirect()->route('depenses.index')
            ->with('success', 'Dépense modifiée.');
    }

    public function destroy(Depense $depense): RedirectResponse
    {
        abort_if($depense->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        if (in_array($depense->categorie, self::CATEGORIES_AUTO, true)) {
            return redirect()->route('depenses.index')
                ->with('warning', 'Supprimez cette dépense depuis le module Achats.');
        }

        $depense->delete();

        return redirect()->route('depenses.index')
            ->with('success', 'Dépense supprimée.');
    }
}
