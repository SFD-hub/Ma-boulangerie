<?php

namespace App\Http\Controllers;

use App\Models\AchatMatierePremiere;
use App\Models\ClientAbonne;
use App\Models\ConsommationAbonne;
use App\Models\Depense;
use App\Models\Distribution;
use App\Models\MatierePremiere;
use App\Models\Production;
use App\Models\User;
use App\Models\Versement;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        $today          = Carbon::today();

        // Production du jour (nombre de pains)
        $productionJour = (int) Production::where('boulangerie_id', $boulangerie_id)
            ->whereDate('date_production', $today)
            ->sum('nombre_pains_produits');

        // Pains distribués/consommés aujourd'hui (livreurs+clients ET abonnés)
        $painsDistribues = (int) Distribution::whereHas('livreur', fn ($q) => $q->where('boulangerie_id', $boulangerie_id))
                ->whereDate('date_distribution', $today)
                ->sum('nombre_pains')
            + (int) ConsommationAbonne::whereHas('clientAbonne', fn ($q) => $q->where('boulangerie_id', $boulangerie_id))
                ->whereDate('date_consommation', $today)
                ->sum('quantite');

        // Stock farine (en sacs)
        $farine = MatierePremiere::where('boulangerie_id', $boulangerie_id)
            ->where('nom', 'Farine')
            ->first();
        $farineStock = $farine ? $farine->stock_actuel : 0;
        $farineSeuil = $farine ? $farine->seuil_alerte : 0;

        // Stock levure (en paquets)
        $levure = MatierePremiere::where('boulangerie_id', $boulangerie_id)
            ->where('nom', 'Levure')
            ->first();
        $levureStock = $levure ? $levure->stock_actuel : 0;
        $levureSeuil = $levure ? $levure->seuil_alerte : 0;

        // Alertes stock
        $alertes = collect();
        if ($farine && $farine->stock_actuel <= $farine->seuil_alerte) {
            $alertes->push(['nom' => 'Farine', 'stock' => $farineStock, 'seuil' => $farineSeuil, 'unite' => 'sacs']);
        }
        if ($levure && $levure->stock_actuel <= $levure->seuil_alerte) {
            $alertes->push(['nom' => 'Levure', 'stock' => $levureStock, 'seuil' => $levureSeuil, 'unite' => 'paquets']);
        }

        // Distribution du jour (6 dernières, livreurs+clients et abonnés confondus)
        $distributionsJour = $this->derniereDistribution($boulangerie_id, $today);

        return view('dashboard.index', compact(
            'productionJour',
            'painsDistribues',
            'farineStock',
            'levureStock',
            'farineSeuil',
            'levureSeuil',
            'alertes',
            'distributionsJour'
        ));
    }

    private function derniereDistribution(int $boulangerie_id, Carbon $today): array
    {
        $livreurs = Distribution::whereHas('livreur', fn ($q) => $q->where('boulangerie_id', $boulangerie_id))
            ->whereDate('date_distribution', $today)
            ->with('livreur')
            ->get()
            ->map(fn ($d) => [
                'nom'       => trim($d->livreur->prenom . ' ' . $d->livreur->nom),
                'type'      => $d->livreur->type,
                'typeLabel' => $d->livreur->typeLabel(),
                'quantite'  => (int) $d->nombre_pains,
                'route'     => route('livreurs.show', $d->livreur),
                'at'        => $d->created_at,
            ]);

        $abonnes = ConsommationAbonne::whereHas('clientAbonne', fn ($q) => $q->where('boulangerie_id', $boulangerie_id))
            ->whereDate('date_consommation', $today)
            ->with('clientAbonne')
            ->get()
            ->map(fn ($c) => [
                'nom'       => trim($c->clientAbonne->prenom . ' ' . $c->clientAbonne->nom),
                'type'      => 'abonne',
                'typeLabel' => 'Abonné',
                'quantite'  => (int) $c->quantite,
                'route'     => route('clients-abonnes.show', $c->clientAbonne),
                'at'        => $c->created_at,
            ]);

        return $livreurs->concat($abonnes)
            ->sortByDesc('at')
            ->take(6)
            ->values()
            ->all();
    }

    public function activites(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        $all     = $this->collectActivites($boulangerie_id, limit: null);
        $perPage = 20;
        $page    = (int) request()->get('page', 1);
        $items   = array_slice($all, ($page - 1) * $perPage, $perPage);

        $paginator = new LengthAwarePaginator(
            $items,
            count($all),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('dashboard.activites', ['activites' => $paginator]);
    }

    private function derniereActivite(int $boulangerie_id): array
    {
        $events = $this->collectActivites($boulangerie_id, limit: 3);
        return array_slice($events, 0, 5);
    }

    /**
     * Collecte et trie toutes les activités d'une boulangerie.
     * $limit : nombre max de lignes chargées par table (null = toutes).
     */
    private function collectActivites(int $boulangerie_id, ?int $limit): array
    {
        // IMPORTANT : les arrow functions (fn) capturent par valeur en PHP —
        // On utilise map()->toArray() + array_merge() pour éviter ce piège.

        $events = [];

        // Productions
        $q = Production::where('boulangerie_id', $boulangerie_id)->orderByDesc('created_at');
        if ($limit) $q->limit($limit);
        $events = array_merge($events,
            $q->get()->map(fn ($p) => [
                'icon'  => '🍞',
                'label' => 'Production enregistrée',
                'sub'   => number_format($p->nombre_pains_produits, 0, ',', ' ') . ' pains — ' . $p->date_production->format('d/m/Y'),
                'at'    => $p->created_at,
            ])->toArray()
        );

        // Achats stock
        $q = AchatMatierePremiere::whereHas('matierePremiere', fn ($q) => $q->where('boulangerie_id', $boulangerie_id))
            ->with('matierePremiere')->orderByDesc('created_at');
        if ($limit) $q->limit($limit);
        $events = array_merge($events,
            $q->get()->map(fn ($a) => [
                'icon'  => '📦',
                'label' => 'Achat ' . $a->matierePremiere->nom,
                'sub'   => $a->quantite . ' ' . ($a->matierePremiere->nom === 'Farine' ? 'sacs' : 'paquets') . ' · ' . number_format($a->montant, 0, ',', ' ') . ' FCFA',
                'at'    => $a->created_at,
            ])->toArray()
        );

        // Versements livreurs
        $q = Versement::whereHas('livreur', fn ($q) => $q->where('boulangerie_id', $boulangerie_id))
            ->with('livreur')->orderByDesc('created_at');
        if ($limit) $q->limit($limit);
        $events = array_merge($events,
            $q->get()->map(fn ($v) => [
                'icon'  => '💰',
                'label' => 'Versement — ' . $v->livreur->prenom . ' ' . $v->livreur->nom,
                'sub'   => number_format($v->montant_verse, 0, ',', ' ') . ' FCFA — ' . $v->date_versement->format('d/m/Y'),
                'at'    => $v->created_at,
            ])->toArray()
        );

        // Dépenses manuelles (hors achats stock auto)
        $q = Depense::where('boulangerie_id', $boulangerie_id)
            ->whereNotIn('categorie', ['achat_farine', 'achat_levure'])
            ->orderByDesc('created_at');
        if ($limit) $q->limit($limit);
        $events = array_merge($events,
            $q->get()->map(fn ($d) => [
                'icon'  => '💸',
                'label' => 'Dépense — ' . ($d->libelle ?: ucfirst(str_replace('_', ' ', $d->categorie))),
                'sub'   => number_format($d->montant, 0, ',', ' ') . ' FCFA — ' . $d->date_depense->format('d/m/Y'),
                'at'    => $d->created_at,
            ])->toArray()
        );

        // Consommations abonnés
        $q = ConsommationAbonne::whereHas('clientAbonne', fn ($q) => $q->where('boulangerie_id', $boulangerie_id))
            ->with('clientAbonne')->orderByDesc('created_at');
        if ($limit) $q->limit($limit);
        $events = array_merge($events,
            $q->get()->map(fn ($c) => [
                'icon'  => '👤',
                'label' => 'Consommation — ' . $c->clientAbonne->nom,
                'sub'   => $c->quantite . ' pain' . ($c->quantite > 1 ? 's' : '') . ' — ' . $c->date_consommation->format('d/m/Y'),
                'at'    => $c->created_at,
            ])->toArray()
        );

        // Gérants créés
        $q = User::where('boulangerie_id', $boulangerie_id)
            ->whereHas('role', fn ($q) => $q->where('nom', 'gerant'))
            ->orderByDesc('created_at');
        if ($limit) $q->limit($limit);
        $events = array_merge($events,
            $q->get()->map(fn ($u) => [
                'icon'  => '👔',
                'label' => 'Gérant ajouté — ' . $u->name,
                'sub'   => 'Compte créé le ' . $u->created_at->format('d/m/Y'),
                'at'    => $u->created_at,
            ])->toArray()
        );

        usort($events, fn ($a, $b) => $b['at'] <=> $a['at']);

        return $events;
    }
}
