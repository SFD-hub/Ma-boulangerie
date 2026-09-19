<?php

namespace App\Support;

use App\Models\Depense;
use App\Models\DepotVente;
use App\Models\PaiementFacture;
use App\Models\Versement;
use Illuminate\Support\Collection;

// Agrège, pour une boulangerie et une date données, tout l'argent encaissé
// (versements livreurs/clients, paiements de factures abonnés, ventes Dépôt)
// et toutes les dépenses — même logique de filtrage que
// BilanController::calculerBilan(), juste bornée à un seul jour au lieu d'un
// mois. Utilisé à la fois par le Dashboard (juste les totaux) et par la page
// Caisse du jour (le détail), pour que les deux affichent toujours le même
// chiffre.
class CaisseDuJour
{
    public static function resume(int $boulangerie_id, string $date): array
    {
        $versements = Versement::whereHas(
            'livreur', fn ($q) => $q->where('boulangerie_id', $boulangerie_id)
        )->whereDate('date_versement', $date)
            ->with('livreur')
            ->get()
            ->map(fn (Versement $v) => [
                'nom'       => trim($v->livreur->prenom . ' ' . $v->livreur->nom),
                'type'      => $v->livreur->type,
                'typeLabel' => $v->livreur->typeLabel(),
                'montant'   => (float) $v->montant_verse,
                'route'     => route('livreurs.show', $v->livreur),
            ]);

        $paiements = PaiementFacture::whereHas(
            'facture.clientAbonne', fn ($q) => $q->where('boulangerie_id', $boulangerie_id)
        )->whereDate('date_paiement', $date)
            ->with('facture.clientAbonne')
            ->get()
            ->map(fn (PaiementFacture $p) => [
                'nom'       => trim($p->facture->clientAbonne->prenom . ' ' . $p->facture->clientAbonne->nom),
                'type'      => 'abonne',
                'typeLabel' => 'Abonné',
                'montant'   => (float) $p->montant,
                'route'     => route('clients-abonnes.show', $p->facture->clientAbonne),
            ]);

        $encaissements = $versements->concat($paiements)
            ->sortByDesc('montant')
            ->values();

        $depotJour = DepotVente::where('boulangerie_id', $boulangerie_id)
            ->whereDate('date_vente', $date)
            ->selectRaw('COALESCE(SUM(quantite), 0) as quantite, COALESCE(SUM(montant), 0) as montant')
            ->first();

        $depotJour = [
            'quantite' => (int) $depotJour->quantite,
            'montant'  => (float) $depotJour->montant,
            'route'    => route('depot.index'),
        ];

        $depenses = Depense::where('boulangerie_id', $boulangerie_id)
            ->whereDate('date_depense', $date)
            ->get()
            ->map(fn (Depense $d) => [
                'libelle'   => $d->libelle,
                'categorie' => $d->categorie,
                'montant'   => (float) $d->montant,
            ]);

        $totalEncaisse = (float) $encaissements->sum('montant') + $depotJour['montant'];
        $totalDepense  = (float) $depenses->sum('montant');

        return [
            'encaissements'  => $encaissements,
            'depotJour'      => $depotJour,
            'depenses'       => $depenses,
            'totalEncaisse'  => $totalEncaisse,
            'totalDepense'   => $totalDepense,
            'solde'          => $totalEncaisse - $totalDepense,
        ];
    }
}
