<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\AjustementRestant;
use App\Models\VenteRestant;
use App\Support\Nombre;
use App\Support\PainRestant;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

/**
 * Module « Pain restant » : le pain invendu (restants des livreurs/clients et
 * pain non vendu de la boulangerie au bout de 24 h) est mis en sacs et vendu au
 * sac. On suit le stock estimé, les sacs vendus et l'argent encaissé.
 */
class PainRestantController extends Controller
{
    /**
     * Période (du… au…) lue dans l'URL : mois en cours par défaut, dates inversées
     * remises dans l'ordre, durée limitée à 1 an (même logique que Dépenses).
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

    public function index(Request $request): View
    {
        $boulangerieId = auth()->user()->boulangerie_id;
        [$du, $au, $periodeLimitee] = $this->periode($request);

        $etat   = PainRestant::etat($boulangerieId);
        $ventes = PainRestant::ventes($boulangerieId, $du, $au);

        // Les 3 dernières ventes de la période ; la liste complète est dans « Voir plus ».
        $liste = VenteRestant::where('boulangerie_id', $boulangerieId)
            ->whereBetween('date_vente', [$du, $au])
            ->orderByDesc('date_vente')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        return view('pain-restant.index', [
            'etat'           => $etat,
            'ventes'         => $ventes,
            'liste'          => $liste,
            'du'             => $du,
            'au'             => $au,
            'periodeLimitee' => $periodeLimitee,
            'painsParSac'    => VenteRestant::PAINS_PAR_SAC,
        ]);
    }

    // Historique des ventes de sacs : 10 par page, avec sélecteur de période et total.
    public function historique(Request $request): View
    {
        $boulangerieId = auth()->user()->boulangerie_id;
        [$du, $au, $periodeLimitee] = $this->periode($request);

        $ventes = PainRestant::ventes($boulangerieId, $du, $au);

        $liste = VenteRestant::where('boulangerie_id', $boulangerieId)
            ->whereBetween('date_vente', [$du, $au])
            ->orderByDesc('date_vente')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('pain-restant.historique', compact('ventes', 'liste', 'du', 'au', 'periodeLimitee'));
    }

    // Vente de sacs : nombre de sacs, montant total encaissé, pains estimés (facultatif).
    public function store(Request $request): RedirectResponse
    {
        $boulangerieId = auth()->user()->boulangerie_id;

        $validateur = Validator::make($request->all(), [
            'date_vente'    => 'required|date|before_or_equal:today',
            'nombre_sacs'   => 'required|integer|min:1|max:99999',
            'montant'       => 'required|numeric|min:0|max:99999999',
            'pains_estimes' => 'nullable|integer|min:0|max:9999999',
        ], [
            'date_vente.required'        => 'La date est obligatoire.',
            'date_vente.before_or_equal' => 'La date ne peut pas être dans le futur.',
            'nombre_sacs.required'       => 'Indiquez le nombre de sacs.',
            'nombre_sacs.integer'        => 'Le nombre de sacs doit être un nombre entier.',
            'nombre_sacs.min'            => 'Le nombre de sacs doit être au moins 1.',
            'montant.required'           => 'Indiquez le montant encaissé.',
            'montant.numeric'            => 'Le montant doit être un nombre.',
            'pains_estimes.integer'      => 'Le nombre de pains doit être un nombre entier.',
        ]);

        if ($validateur->fails()) {
            return redirect()->route('pain-restant.index')->with('error', $validateur->errors()->first());
        }

        $v = $validateur->validated();

        $sacs   = (int) $v['nombre_sacs'];
        // Estimation du pain sorti : celle saisie, sinon la valeur habituelle par sac.
        $pains  = ($v['pains_estimes'] ?? null) !== null && $v['pains_estimes'] !== ''
            ? (int) $v['pains_estimes']
            : $sacs * VenteRestant::PAINS_PAR_SAC;
        $montant = (float) $v['montant'];

        VenteRestant::create([
            'boulangerie_id' => $boulangerieId,
            'user_id'        => auth()->id(),
            'date_vente'     => $v['date_vente'],
            'nombre_sacs'    => $sacs,
            'pains_estimes'  => $pains,
            'montant'        => $montant,
        ]);

        ActivityLog::record(
            'vente_restant',
            auth()->user()->name . ' a vendu ' . $sacs . ' sac' . ($sacs > 1 ? 's' : '') . ' de pain restant pour '
                . number_format($montant, 0, ',', ' ') . ' FCFA',
            $boulangerieId,
            'vente'
        );

        return redirect()->route('pain-restant.index')
            ->with('success', $sacs . ' sac' . ($sacs > 1 ? 's' : '') . ' vendu' . ($sacs > 1 ? 's' : '') . ' : ' . number_format($montant, 0, ',', ' ') . ' FCFA.');
    }

    // Annulation d'une vente saisie par erreur : le stock estimé et les totaux se recalculent.
    public function destroy(VenteRestant $venteRestant): RedirectResponse
    {
        abort_if($venteRestant->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $venteRestant->delete();

        return redirect()->back(fallback: route('pain-restant.index'))
            ->with('success', 'Vente supprimée.');
    }

    // Ajuster le stock après comptage : on saisit le nombre de pains réellement restants,
    // l'écart avec l'estimation est enregistré (date, auteur) et le stock repart du bon chiffre.
    public function ajuster(Request $request): RedirectResponse
    {
        $boulangerieId = auth()->user()->boulangerie_id;

        $validateur = Validator::make($request->all(), [
            'nombre_pains' => 'required|integer|min:0|max:9999999',
        ], [
            'nombre_pains.required' => 'Indiquez le nombre de pains comptés.',
            'nombre_pains.integer'  => 'Le nombre de pains doit être un nombre entier.',
            'nombre_pains.min'      => 'Le nombre de pains ne peut pas être négatif.',
        ]);

        if ($validateur->fails()) {
            return redirect()->route('pain-restant.index')->with('error', $validateur->errors()->first());
        }

        $apres = (int) $validateur->validated()['nombre_pains'] * 6;

        DB::transaction(function () use ($boulangerieId, $apres) {
            // Ce que l'application estimait (peut être négatif si l'estimation était trop forte).
            $avant = PainRestant::etat($boulangerieId)['brut'];

            AjustementRestant::create([
                'boulangerie_id'  => $boulangerieId,
                'user_id'         => auth()->id(),
                'date_ajustement' => now()->toDateString(),
                'parts_avant'     => $avant,
                'parts_apres'     => $apres,
                'ecart_parts'     => $apres - $avant,
            ]);

            ActivityLog::record(
                'ajustement_restant',
                auth()->user()->name . ' a ajusté le pain restant : ' . Nombre::pains(max(0, $avant) / 6) . ' estimés → ' . Nombre::pains($apres / 6) . ' comptés',
                $boulangerieId,
                'stock'
            );
        });

        return redirect()->route('pain-restant.index')
            ->with('success', 'Stock ajusté : ' . Nombre::pains($apres / 6) . '.');
    }
}
