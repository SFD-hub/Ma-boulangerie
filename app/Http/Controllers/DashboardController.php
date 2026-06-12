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

        // Pains distribués aujourd'hui (somme nombre_pains des distributions d'aujourd'hui)
        $painsDistribues = (int) Distribution::whereHas('livreur', fn ($q) => $q->where('boulangerie_id', $boulangerie_id))
            ->whereDate('date_distribution', $today)
            ->sum('nombre_pains');

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

        // Dernières activités
        $activites = $this->derniereActivite($boulangerie_id);

        return view('dashboard.index', compact(
            'productionJour',
            'painsDistribues',
            'farineStock',
            'levureStock',
            'farineSeuil',
            'levureSeuil',
            'alertes',
            'activites'
        ));
    }

    private function derniereActivite(int $boulangerie_id): array
    {
        // IMPORTANT : les arrow functions (fn) capturent par valeur en PHP —
        // $events[] = [...] à l'intérieur d'une fn ne modifie jamais l'original.
        // On utilise map()->toArray() + array_merge() pour éviter ce piège.

        $events = [];

        // Productions
        $events = array_merge($events,
            Production::where('boulangerie_id', $boulangerie_id)
                ->orderByDesc('created_at')->limit(3)->get()
                ->map(fn ($p) => [
                    'icon'  => '🍞',
                    'label' => 'Production enregistrée',
                    'sub'   => number_format($p->nombre_pains_produits, 0, ',', ' ') . ' pains — ' . $p->date_production->format('d/m/Y'),
                    'at'    => $p->created_at,
                ])->toArray()
        );

        // Achats stock
        $events = array_merge($events,
            AchatMatierePremiere::whereHas('matierePremiere', fn ($q) => $q->where('boulangerie_id', $boulangerie_id))
                ->with('matierePremiere')
                ->orderByDesc('created_at')->limit(3)->get()
                ->map(fn ($a) => [
                    'icon'  => '📦',
                    'label' => 'Achat ' . $a->matierePremiere->nom,
                    'sub'   => $a->quantite . ' ' . ($a->matierePremiere->nom === 'Farine' ? 'sacs' : 'paquets') . ' · ' . number_format($a->montant, 0, ',', ' ') . ' FCFA',
                    'at'    => $a->created_at,
                ])->toArray()
        );

        // Versements livreurs
        $events = array_merge($events,
            Versement::whereHas('livreur', fn ($q) => $q->where('boulangerie_id', $boulangerie_id))
                ->with('livreur')
                ->orderByDesc('created_at')->limit(3)->get()
                ->map(fn ($v) => [
                    'icon'  => '💰',
                    'label' => 'Versement — ' . $v->livreur->prenom . ' ' . $v->livreur->nom,
                    'sub'   => number_format($v->montant_verse, 0, ',', ' ') . ' FCFA — ' . $v->date_versement->format('d/m/Y'),
                    'at'    => $v->created_at,
                ])->toArray()
        );

        // Dépenses manuelles (salaire, divers — hors achats stock auto)
        $events = array_merge($events,
            Depense::where('boulangerie_id', $boulangerie_id)
                ->whereNotIn('categorie', ['achat_farine', 'achat_levure'])
                ->orderByDesc('created_at')->limit(3)->get()
                ->map(fn ($d) => [
                    'icon'  => '💸',
                    'label' => 'Dépense — ' . ($d->libelle ?: ucfirst(str_replace('_', ' ', $d->categorie))),
                    'sub'   => number_format($d->montant, 0, ',', ' ') . ' FCFA — ' . $d->date_depense->format('d/m/Y'),
                    'at'    => $d->created_at,
                ])->toArray()
        );

        // Consommations abonnés
        $events = array_merge($events,
            ConsommationAbonne::whereHas('clientAbonne', fn ($q) => $q->where('boulangerie_id', $boulangerie_id))
                ->with('clientAbonne')
                ->orderByDesc('created_at')->limit(3)->get()
                ->map(fn ($c) => [
                    'icon'  => '👤',
                    'label' => 'Consommation — ' . $c->clientAbonne->nom,
                    'sub'   => $c->quantite . ' pain' . ($c->quantite > 1 ? 's' : '') . ' — ' . $c->date_consommation->format('d/m/Y'),
                    'at'    => $c->created_at,
                ])->toArray()
        );

        // Gérants créés
        $events = array_merge($events,
            User::where('boulangerie_id', $boulangerie_id)
                ->whereHas('role', fn ($q) => $q->where('nom', 'gerant'))
                ->orderByDesc('created_at')->limit(2)->get()
                ->map(fn ($u) => [
                    'icon'  => '👔',
                    'label' => 'Gérant ajouté — ' . $u->name,
                    'sub'   => 'Compte créé le ' . $u->created_at->format('d/m/Y'),
                    'at'    => $u->created_at,
                ])->toArray()
        );

        // Trier par date décroissante et garder les 5 plus récentes
        usort($events, fn ($a, $b) => $b['at'] <=> $a['at']);

        return array_slice($events, 0, 5);
    }
}
