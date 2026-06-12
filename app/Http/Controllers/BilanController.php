<?php

namespace App\Http\Controllers;

use App\Models\BilanFinancier;
use App\Models\Depense;
use App\Models\Facture;
use App\Models\Versement;
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

        return view('bilan.detail', array_merge($data, compact('mois', 'annee', 'moisNom', 'dejaEnregistre')));
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
                'versements_livreurs' => $data['versementsLivreurs'],
                'factures_abonnes'    => $data['facturesAbonnes'],
                'recettes_total'      => $data['totalRecettes'],
                'achat_farine'        => $data['achatFarine'],
                'achat_levure'        => $data['achatLevure'],
                'salaire_gerant'      => $data['salaireGerant'],
                'salaire_employe'     => $data['salaireEmploye'],
                'eau'                 => $data['eau'],
                'electricite'         => $data['electricite'],
                'carburant'           => $data['carburant'],
                'transport'           => $data['transport'],
                'reparation'          => $data['reparation'],
                'autres'              => $data['autres'],
                'depenses_total'      => $data['totalDepenses'],
                'benefice'            => $data['benefice'],
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
            ->get();

        return view('bilan.historique', compact('bilans'));
    }

    public function show(BilanFinancier $bilan): View
    {
        abort_if($bilan->boulangerie_id !== auth()->user()->boulangerie_id, 403);
        return view('bilan.show', compact('bilan'));
    }

    public function imprimerDetail(Request $request)
    {
        $mois   = (int) $request->input('mois',  now()->month);
        $annee  = (int) $request->input('annee', now()->year);
        $boulangerie_id  = auth()->user()->boulangerie_id;
        $data            = $this->calculerBilan($mois, $annee, $boulangerie_id);
        $moisNom         = ucfirst(Carbon::create()->month($mois)->locale('fr')->monthName);
        $boulangerieName = auth()->user()->boulangerie?->nom ?? 'Ma Boulangerie';

        return view('bilan.imprimer', array_merge($data, compact('mois', 'annee', 'moisNom', 'boulangerieName')));
    }

    public function imprimerSaved(BilanFinancier $bilan)
    {
        abort_if($bilan->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $mois    = $bilan->mois;
        $annee   = $bilan->annee;
        $moisNom = $bilan->nomMois();
        $boulangerieName = auth()->user()->boulangerie?->nom ?? 'Ma Boulangerie';

        $versementsLivreurs = (float) $bilan->versements_livreurs;
        $facturesAbonnes    = (float) $bilan->factures_abonnes;
        $totalRecettes      = (float) $bilan->recettes_total;
        $achatFarine        = (float) $bilan->achat_farine;
        $achatLevure        = (float) $bilan->achat_levure;
        $salaireGerant      = (float) $bilan->salaire_gerant;
        $salaireEmploye     = (float) $bilan->salaire_employe;
        $eau                = (float) $bilan->eau;
        $electricite        = (float) $bilan->electricite;
        $carburant          = (float) $bilan->carburant;
        $transport          = (float) $bilan->transport;
        $reparation         = (float) $bilan->reparation;
        $autres             = (float) $bilan->autres;
        $totalDepenses      = (float) $bilan->depenses_total;
        $benefice           = (float) $bilan->benefice;

        return view('bilan.imprimer', compact(
            'mois', 'annee', 'moisNom', 'boulangerieName',
            'versementsLivreurs', 'facturesAbonnes', 'totalRecettes',
            'achatFarine', 'achatLevure', 'salaireGerant', 'salaireEmploye',
            'eau', 'electricite', 'carburant', 'transport', 'reparation', 'autres',
            'totalDepenses', 'benefice'
        ));
    }

    private function calculerBilan(int $mois, int $annee, int $boulangerie_id): array
    {
        $debut = Carbon::create($annee, $mois, 1)->startOfMonth();
        $fin   = Carbon::create($annee, $mois, 1)->endOfMonth();

        // ── RECETTES ──────────────────────────────────────────────────────
        $versementsLivreurs = (float) Versement::whereHas(
            'livreur', fn ($q) => $q->where('boulangerie_id', $boulangerie_id)
        )->whereBetween('date_versement', [$debut, $fin])->sum('montant_verse');

        $facturesAbonnes = (float) Facture::whereHas(
            'clientAbonne', fn ($q) => $q->where('boulangerie_id', $boulangerie_id)
        )->where('statut', 'payee')
         ->whereNotNull('date_paiement')
         ->whereBetween('date_paiement', [$debut, $fin])
         ->sum('montant_total');

        $totalRecettes = $versementsLivreurs + $facturesAbonnes;

        // ── DÉPENSES PAR CATÉGORIE ─────────────────────────────────────────
        $d = Depense::where('boulangerie_id', $boulangerie_id)
            ->whereBetween('date_depense', [$debut, $fin])
            ->selectRaw('categorie, SUM(montant) as total')
            ->groupBy('categorie')
            ->pluck('total', 'categorie');

        $achatFarine    = (float) ($d['achat_farine']    ?? 0);
        $achatLevure    = (float) ($d['achat_levure']    ?? 0);
        $salaireGerant  = (float) (($d['salaire_gerant'] ?? 0) + ($d['salaire'] ?? 0));
        $salaireEmploye = (float) ($d['salaire_employe'] ?? 0);
        $eau            = (float) ($d['eau']             ?? 0);
        $electricite    = (float) ($d['electricite']     ?? 0);
        $carburant      = (float) ($d['carburant']       ?? 0);
        $transport      = (float) ($d['transport']       ?? 0);
        $reparation     = (float) (($d['reparation']     ?? 0) + ($d['entretien'] ?? 0));
        $autres         = (float) (($d['autre']          ?? 0) + ($d['divers']    ?? 0));

        $totalDepenses = $achatFarine + $achatLevure + $salaireGerant + $salaireEmploye
                       + $eau + $electricite + $carburant + $transport + $reparation + $autres;

        $benefice = $totalRecettes - $totalDepenses;

        return compact(
            'versementsLivreurs', 'facturesAbonnes', 'totalRecettes',
            'achatFarine', 'achatLevure', 'salaireGerant', 'salaireEmploye',
            'eau', 'electricite', 'carburant', 'transport', 'reparation', 'autres',
            'totalDepenses', 'benefice'
        );
    }
}
