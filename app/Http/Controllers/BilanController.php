<?php

namespace App\Http\Controllers;

use App\Models\BilanFinancier;
use App\Models\DepotVente;
use App\Models\PaiementFacture;
use App\Models\VenteRestant;
use App\Models\Versement;
use App\Support\BilanCalcul;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BilanController extends Controller
{
    public function index(): View
    {
        return view('bilan.index', [
            'mois'   => now()->month,
            'annee'  => now()->year,
            'annees' => range(now()->year + 1, 2024),
        ]);
    }

    public function detail(Request $request): View
    {
        $mois   = (int) $request->input('mois',  now()->month);
        $annee  = (int) $request->input('annee', now()->year);

        $boulangerie_id = auth()->user()->boulangerie_id;
        $data           = $this->calculerBilan($mois, $annee, $boulangerie_id);
        $moisNom        = ucfirst(Carbon::create()->month($mois)->locale('fr')->monthName);
        $dejaEnregistre = BilanFinancier::where('boulangerie_id', $boulangerie_id)
                            ->where('mois', $mois)->where('annee', $annee)->exists();

        return view('bilan.detail', array_merge($data, $this->affichage($data), compact('mois', 'annee', 'moisNom', 'dejaEnregistre')));
    }

    public function enregistrer(Request $request): RedirectResponse
    {
        $request->validate([
            'mois'  => 'required|integer|between:1,12',
            'annee' => 'required|integer|min:2020',
        ]);

        $mois   = (int) $request->input('mois');
        $annee  = (int) $request->input('annee');
        $boulangerie_id = auth()->user()->boulangerie_id;
        $data   = $this->calculerBilan($mois, $annee, $boulangerie_id);

        BilanFinancier::updateOrCreate(
            ['boulangerie_id' => $boulangerie_id, 'mois' => $mois, 'annee' => $annee],
            [
                'methode'             => 'consommation',
                'versements_livreurs' => $data['versementsLivreurs'],
                'factures_abonnes'    => $data['facturesAbonnes'],
                'ventes_depot'        => $data['ventesDepot'],
                'ventes_restant'      => $data['ventesRestant'],
                'recettes_total'      => $data['totalRecettes'],
                // Matières consommées (et non achetées) : voir App\Support\BilanCalcul.
                'conso_farine'        => $data['consoFarine'],
                'conso_levure'        => $data['consoLevure'],
                'conso_produit'       => $data['consoProduit'],
                'conso_sel'           => $data['consoSel'],
                'conso_huile'         => $data['consoHuile'],
                'conso_gasoil'        => $data['consoGasoil'],
                'ecarts_stock'        => $data['ecartsStock'],
                'achats_stock'        => $data['achatsStock'],
                'achat_farine'        => 0,
                'achat_levure'        => 0,
                'achat_produit'       => 0,
                'achat_sel'           => 0,
                'achat_huile'         => 0,
                'achat_gasoil'        => 0,
                'salaire_gerant'      => $data['salaireGerant'],
                'salaire_employe'     => $data['salaireEmploye'],
                'eau'                 => $data['eau'],
                'electricite'         => $data['electricite'],
                'carburant'           => $data['carburant'],
                'transport'           => $data['transport'],
                'reparation'          => $data['reparation'],
                'autres'              => $data['autres'],
                'depenses_total'      => $data['totalDepenses'],
                'benefice'            => $data['totalRecettes'] - $data['totalDepenses'],
            ]
        );

        return redirect()->route('bilan.historique')
            ->with('success', 'Bilan enregistré.');
    }

    public function historique(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        $bilans = BilanFinancier::where('boulangerie_id', $boulangerie_id)
            ->orderByDesc('annee')
            ->orderByDesc('mois')
            ->paginate(10)
            ->withQueryString();

        return view('bilan.historique', compact('bilans'));
    }

    public function show(BilanFinancier $bilan): View
    {
        abort_if($bilan->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $data = $this->donneesDepuisBilan($bilan);

        return view('bilan.show', array_merge(compact('bilan'), $data, $this->affichage($data)));
    }

    public function imprimerDetail(Request $request)
    {
        $mois   = (int) $request->input('mois',  now()->month);
        $annee  = (int) $request->input('annee', now()->year);
        $boulangerie_id  = auth()->user()->boulangerie_id;
        $data            = $this->calculerBilan($mois, $annee, $boulangerie_id);
        $moisNom         = ucfirst(Carbon::create()->month($mois)->locale('fr')->monthName);
        $boulangerieName = auth()->user()->boulangerie?->nom ?? 'Ma Boulangerie';

        return view('bilan.imprimer', array_merge($data, $this->affichage($data), compact('mois', 'annee', 'moisNom', 'boulangerieName')));
    }

    public function imprimerSaved(BilanFinancier $bilan)
    {
        abort_if($bilan->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $mois    = $bilan->mois;
        $annee   = $bilan->annee;
        $moisNom = $bilan->nomMois();
        $boulangerieName = auth()->user()->boulangerie?->nom ?? 'Ma Boulangerie';

        $data = $this->donneesDepuisBilan($bilan);

        return view('bilan.imprimer', array_merge($data, $this->affichage($data), compact('mois', 'annee', 'moisNom', 'boulangerieName')));
    }

    // Données d'un bilan enregistré, sous la même forme que celles d'un bilan calculé.
    private function donneesDepuisBilan(BilanFinancier $bilan): array
    {
        $conso = [
            'consoFarine'  => (float) $bilan->conso_farine,
            'consoLevure'  => (float) $bilan->conso_levure,
            'consoProduit' => (float) $bilan->conso_produit,
            'consoSel'     => (float) $bilan->conso_sel,
            'consoHuile'   => (float) $bilan->conso_huile,
            'consoGasoil'  => (float) $bilan->conso_gasoil,
        ];

        return array_merge($conso, [
            'methode'            => $bilan->methode === 'consommation' ? 'consommation' : 'achats',
            'versementsLivreurs' => (float) $bilan->versements_livreurs,
            'facturesAbonnes'    => (float) $bilan->factures_abonnes,
            'ventesDepot'        => (float) $bilan->ventes_depot,
            'ventesRestant'      => (float) $bilan->ventes_restant,
            'totalRecettes'      => (float) $bilan->recettes_total,
            'totalConso'         => array_sum($conso),
            'ecartsStock'        => (float) $bilan->ecarts_stock,
            'achatsStock'        => (float) $bilan->achats_stock,
            // Anciens bilans enregistrés (calculés sur les achats) : lignes d'achat d'origine.
            'achatFarine'        => (float) $bilan->achat_farine,
            'achatLevure'        => (float) $bilan->achat_levure,
            'achatProduit'       => (float) $bilan->achat_produit,
            'achatSel'           => (float) $bilan->achat_sel,
            'achatHuile'         => (float) $bilan->achat_huile,
            'achatGasoil'        => (float) $bilan->achat_gasoil,
            'salaireGerant'      => (float) $bilan->salaire_gerant,
            'salaireEmploye'     => (float) $bilan->salaire_employe,
            'eau'                => (float) $bilan->eau,
            'electricite'        => (float) $bilan->electricite,
            'carburant'          => (float) $bilan->carburant,
            'transport'          => (float) $bilan->transport,
            'reparation'         => (float) $bilan->reparation,
            'autres'             => (float) $bilan->autres,
            'totalDepenses'      => (float) $bilan->depenses_total,
        ]);
    }

    /**
     * Lignes de dépenses à afficher, informations sur le stock et bénéfice.
     *  - bilan « consommation » : matières CONSOMMÉES + écarts d'inventaire + autres dépenses ;
     *    le bénéfice = recettes − dépenses, et les achats ne sont qu'une information.
     *  - ancien bilan enregistré (« achats ») : affiché tel qu'il était, sans bénéfice.
     */
    private function affichage(array $d): array
    {
        $consommation = ($d['methode'] ?? 'consommation') === 'consommation';

        $autres = [
            ['label' => 'Salaires gérant',           'icone' => '👤', 'montant' => $d['salaireGerant']],
            ['label' => 'Salaires employés',         'icone' => '👥', 'montant' => $d['salaireEmploye']],
            ['label' => 'Eau',                       'icone' => '💧', 'montant' => $d['eau']],
            ['label' => 'Électricité',               'icone' => '⚡', 'montant' => $d['electricite']],
            ['label' => 'Carburant',                 'icone' => '⛽', 'montant' => $d['carburant']],
            ['label' => 'Transport',                 'icone' => '🚗', 'montant' => $d['transport']],
            ['label' => 'Réparation / Maintenance',  'icone' => '🔧', 'montant' => $d['reparation']],
            ['label' => 'Autres',                    'icone' => '📌', 'montant' => $d['autres']],
        ];

        if ($consommation) {
            $matieres = [
                ['label' => 'Farine consommée',  'icone' => '🌾', 'montant' => $d['consoFarine']],
                ['label' => 'Levure consommée',  'icone' => '🧪', 'montant' => $d['consoLevure']],
                ['label' => 'Produit consommé',  'icone' => '📦', 'montant' => $d['consoProduit']],
                ['label' => 'Sel consommé',      'icone' => '🧂', 'montant' => $d['consoSel']],
                ['label' => 'Huile consommée',   'icone' => '🫙', 'montant' => $d['consoHuile']],
                ['label' => 'Gasoil consommé',   'icone' => '⛽', 'montant' => $d['consoGasoil']],
                ['label' => 'Écarts d\'inventaire', 'icone' => '📋', 'montant' => $d['ecartsStock']],
            ];
        } else {
            $matieres = [
                ['label' => 'Farine',  'icone' => '🌾', 'montant' => $d['achatFarine']],
                ['label' => 'Levure',  'icone' => '🧪', 'montant' => $d['achatLevure']],
                ['label' => 'Produit', 'icone' => '📦', 'montant' => $d['achatProduit']],
                ['label' => 'Sel',     'icone' => '🧂', 'montant' => $d['achatSel']],
                ['label' => 'Huile',   'icone' => '🫙', 'montant' => $d['achatHuile']],
                ['label' => 'Gasoil',  'icone' => '⛽', 'montant' => $d['achatGasoil']],
            ];
        }

        // Une ligne à 0 n'est pas affichée ; un écart négatif (gain d'inventaire) reste visible.
        $lignesDepenses = array_values(array_filter(
            array_merge($matieres, $autres),
            fn ($l) => abs($l['montant']) > 0.004
        ));

        $infoStock = null;
        $benefice  = null;

        if ($consommation) {
            // Pour information : achats du mois et variation du stock (achats − consommé − écarts).
            $infoStock = [
                'achats'    => $d['achatsStock'],
                'variation' => $d['achatsStock'] - $d['totalConso'] - $d['ecartsStock'],
            ];
            $benefice = $d['totalRecettes'] - $d['totalDepenses'];
        }

        return compact('lignesDepenses', 'infoStock', 'benefice');
    }

    private function calculerBilan(int $mois, int $annee, int $boulangerie_id): array
    {
        $debut = Carbon::create($annee, $mois, 1)->startOfMonth();
        $fin   = Carbon::create($annee, $mois, 1)->endOfMonth();

        // ── RECETTES (argent encaissé) ─────────────────────────────────────
        $recettes = BilanCalcul::recettes($boulangerie_id, $debut, $fin);

        // ── DÉPENSES : matières consommées + écarts d'inventaire + autres dépenses ──
        $depenses = BilanCalcul::depenses($boulangerie_id, $debut, $fin);

        return array_merge($depenses, $recettes, [
            'methode'            => 'consommation',
            // Valeurs d'achat d'origine : inutilisées dans ce mode (gardées pour les anciens bilans).
            'achatFarine' => 0.0, 'achatLevure' => 0.0, 'achatProduit' => 0.0,
            'achatSel' => 0.0, 'achatHuile' => 0.0, 'achatGasoil' => 0.0,
        ]);
    }
}
