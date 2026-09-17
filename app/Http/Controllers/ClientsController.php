<?php

namespace App\Http\Controllers;

use App\Models\ClientAbonne;
use App\Models\ConsommationAbonne;
use App\Models\Distribution;
use App\Models\Livreur;
use App\Models\Production;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Agrège Livreurs (types livreur/client) et Abonnés en une seule liste pour
// l'affichage — ne fusionne pas leurs données/mécaniques : chaque carte
// renvoie vers sa propre page de détail (livreurs.show ou clients-abonnes.show),
// strictement inchangée.
class ClientsController extends Controller
{
    public function index(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        $livreurs = Livreur::where('boulangerie_id', $boulangerie_id)
            ->get()
            ->map(fn (Livreur $l) => [
                'id'        => $l->id,
                'nom'       => $l->nom,
                'prenom'    => $l->prenom,
                'telephone' => $l->telephone,
                'actif'     => $l->actif,
                'type'      => $l->type,
                'typeLabel' => $l->typeLabel(),
                'route'     => route('livreurs.show', $l),
            ]);

        $abonnes = ClientAbonne::where('boulangerie_id', $boulangerie_id)
            ->get()
            ->map(fn (ClientAbonne $c) => [
                'id'        => $c->id,
                'nom'       => $c->nom,
                'prenom'    => $c->prenom,
                'telephone' => $c->telephone,
                'actif'     => $c->actif,
                'type'      => 'abonne',
                'typeLabel' => 'Abonné',
                'route'     => route('clients-abonnes.show', $c),
            ]);

        // Actifs d'abord, puis ordre alphabétique — même logique de tri que
        // les listes livreurs/abonnés existantes.
        $clients = $livreurs->concat($abonnes)
            ->sortBy(fn ($c) => ($c['actif'] ? '0' : '1') . '_' . mb_strtolower($c['nom']))
            ->values();

        return view('clients.index', compact('clients'));
    }

    public function create(): View
    {
        // Formulaire d'ajout unique pour les 3 types (Livreur/Client/Abonné) :
        // selon le choix, il soumet vers livreurs.store (Livreur/Client) ou
        // clients-abonnes.store (Abonné) — ces deux contrôleurs restent
        // inchangés, seul le point d'entrée est unifié.
        return view('clients.create');
    }

    // ─── Rapport "Distribution du jour" ─────────────────────────────────────
    // Page dédiée, distincte du répertoire (index) : ici on regarde une seule
    // journée (n'importe laquelle), on trie par quantité prise, et l'objectif
    // est "qui a pris combien aujourd'hui", pas "gérer mes clients".
    public function distribution(Request $request): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        $date = $request->query('date');
        $date = ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) ? $date : Carbon::today()->toDateString();

        $livreurs = Livreur::where('boulangerie_id', $boulangerie_id)->get();
        $abonnes  = ClientAbonne::where('boulangerie_id', $boulangerie_id)->get();

        // Somme des pains attribués/consommés ce jour-là — un même livreur
        // peut en théorie avoir plusieurs distributions le même jour, d'où le
        // regroupement/somme plutôt qu'une simple lecture.
        $quantiteParLivreur = Distribution::whereIn('livreur_id', $livreurs->pluck('id'))
            ->where('date_distribution', $date)
            ->selectRaw('livreur_id, SUM(nombre_pains) as total')
            ->groupBy('livreur_id')
            ->pluck('total', 'livreur_id');

        $quantiteParAbonne = ConsommationAbonne::whereIn('client_abonne_id', $abonnes->pluck('id'))
            ->where('date_consommation', $date)
            ->selectRaw('client_abonne_id, SUM(quantite) as total')
            ->groupBy('client_abonne_id')
            ->pluck('total', 'client_abonne_id');

        $lignes = $livreurs
            ->filter(fn (Livreur $l) => $quantiteParLivreur->has($l->id))
            ->map(fn (Livreur $l) => [
                'nom'       => trim($l->prenom . ' ' . $l->nom),
                'type'      => $l->type,
                'typeLabel' => $l->typeLabel(),
                'route'     => route('livreurs.show', $l),
                'quantite'  => (int) $quantiteParLivreur[$l->id],
            ])
            ->concat(
                $abonnes->filter(fn (ClientAbonne $c) => $quantiteParAbonne->has($c->id))
                    ->map(fn (ClientAbonne $c) => [
                        'nom'       => trim($c->prenom . ' ' . $c->nom),
                        'type'      => 'abonne',
                        'typeLabel' => 'Abonné',
                        'route'     => route('clients-abonnes.show', $c),
                        'quantite'  => (int) $quantiteParAbonne[$c->id],
                    ])
            )
            // Le plus gros preneur en premier — c'est ce qui rend ce rapport
            // utile, contrairement au répertoire (index) trié alphabétiquement.
            ->sortByDesc('quantite')
            ->values();

        $totalProduit   = (int) Production::where('boulangerie_id', $boulangerie_id)
            ->where('date_production', $date)
            ->sum('nombre_pains_produits');
        $totalDistribue = (int) $quantiteParLivreur->sum() + (int) $quantiteParAbonne->sum();

        return view('clients.distribution', [
            'date'           => $date,
            'lignes'         => $lignes,
            'totalProduit'   => $totalProduit,
            'totalDistribue' => $totalDistribue,
        ]);
    }
}
