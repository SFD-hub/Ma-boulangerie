<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Distribution;
use App\Models\Livreur;
use App\Models\RetourDistribution;
use App\Support\Nombre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * « Retour » : pains que le livreur / client / vendeur n'a pas réussi à donner
 * (ou à vendre, pour la Boutique). Intacts, ils retournent dans le stock du jour
 * (on peut les redistribuer) ; s'ils ne sont ni redistribués ni vendus avant la
 * fin de la journée, ils deviennent du pain restant. Différent des « restants »
 * des livreurs/clients (pain rendu le lendemain, compté au versement).
 *
 * Règles : un livreur/client rapporte des pains entiers ; un vendeur de la Boutique
 * peut aussi rapporter des moitiés et des tiers. Saisie uniquement le jour de la
 * distribution et avant son versement.
 */
class RetourDistributionController extends Controller
{
    private function verifier(Livreur $livreur): void
    {
        abort_if($livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);
    }

    public function store(Request $request, Livreur $livreur): RedirectResponse
    {
        $this->verifier($livreur);

        // La saisie se fait dans une fenêtre de la fiche : l'erreur s'affiche en notification.
        $validateur = Validator::make($request->all(), [
            'nombre_entiers' => 'nullable|integer|min:0|max:99999',
            'nombre_moities' => 'nullable|integer|min:0|max:99999',
            'nombre_tiers'   => 'nullable|integer|min:0|max:99999',
        ], [
            'nombre_entiers.integer' => 'Le nombre de pains doit être un nombre entier.',
            'nombre_moities.integer' => 'Le nombre de moitiés doit être un nombre entier.',
            'nombre_tiers.integer'   => 'Le nombre de tiers doit être un nombre entier.',
        ]);

        if ($validateur->fails()) {
            return redirect()->route('livreurs.show', $livreur)->with('error', $validateur->errors()->first());
        }

        $entiers = (int) ($request->input('nombre_entiers') ?? 0);
        // Moitiés et tiers : réservés à la Boutique (un livreur rapporte des pains entiers).
        $moities = $livreur->estVendeur() ? (int) ($request->input('nombre_moities') ?? 0) : 0;
        $tiers   = $livreur->estVendeur() ? (int) ($request->input('nombre_tiers') ?? 0) : 0;
        $parts   = $entiers * 6 + $moities * 3 + $tiers * 2;

        if ($parts === 0) {
            return redirect()->route('livreurs.show', $livreur)->with('error', 'Indiquez au moins un pain rapporté.');
        }

        $resultat = DB::transaction(function () use ($livreur, $entiers, $moities, $tiers, $parts) {
            $courante = $livreur->distributionDuJour();
            if (! $courante) {
                return ['ok' => false, 'message' => 'Aucune distribution à retourner aujourd\'hui. Un retour se saisit le jour de la distribution, avant le versement.'];
            }

            // Verrou : deux saisies simultanées ne peuvent pas dépasser ce qui a été confié.
            $distribution = Distribution::where('id', $courante->id)->lockForUpdate()->firstOrFail();
            if ($distribution->versement()->exists()) {
                return ['ok' => false, 'message' => 'Cette distribution vient d\'être réglée.'];
            }

            $maximum = $distribution->retourMaxParts($livreur->estVendeur());
            if ($parts > $maximum) {
                return ['ok' => false, 'message' => $maximum > 0
                    ? 'Il ne peut rapporter que ' . Nombre::pains($maximum / 6) . ' au maximum.'
                    : 'Il n\'a plus de pain à rapporter.'];
            }

            $retour = $distribution->ajouterRetour($livreur, $entiers, $moities, $tiers);

            ActivityLog::record(
                'retour_distribution',
                auth()->user()->name . ' a enregistré le retour de ' . Nombre::pains($parts / 6)
                    . ' de ' . trim($livreur->prenom . ' ' . $livreur->nom) . ' (remis au stock du jour)',
                $livreur->boulangerie_id,
                'vente'
            );

            return ['ok' => true, 'retour' => $retour];
        });

        if (! $resultat['ok']) {
            return redirect()->route('livreurs.show', $livreur)->with('error', $resultat['message']);
        }

        return redirect()->route('livreurs.show', $livreur)
            ->with('success', Nombre::pains($parts / 6) . ' remis au stock du jour.');
    }

    // Annulation d'un retour saisi par erreur : possible tant que la distribution n'est pas
    // réglée ET que ces pains ne sont pas déjà repartis ailleurs (sinon le stock du jour
    // deviendrait négatif).
    public function destroy(RetourDistribution $retourDistribution): RedirectResponse
    {
        return \App\Models\Produit::sousVerrou(fn () => $this->destroyVerrouille($retourDistribution));
    }

    private function destroyVerrouille(RetourDistribution $retourDistribution): RedirectResponse
    {
        abort_if($retourDistribution->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $livreur = $retourDistribution->livreur;

        $resultat = DB::transaction(function () use ($retourDistribution) {
            $distribution = Distribution::where('id', $retourDistribution->distribution_id)->lockForUpdate()->first();
            $retour = RetourDistribution::where('id', $retourDistribution->id)->lockForUpdate()->first();

            if (! $distribution || ! $retour) {
                return ['ok' => false, 'message' => 'Ce retour a déjà été annulé.'];
            }
            if ($distribution->versement()->exists()) {
                return ['ok' => false, 'message' => 'Cette distribution est déjà réglée : le retour ne peut plus être annulé.'];
            }

            // Annuler = ces pains redeviennent « sortis » : il faut qu'ils soient encore au stock du jour.
            $produit = $distribution->produit;
            $date = $distribution->date_distribution->toDateString();
            if ($produit && $produit->partsDisponibles(null, $date) < $retour->parts) {
                return ['ok' => false, 'message' => 'Ces pains ont déjà été redistribués : le retour ne peut plus être annulé.'];
            }

            $distribution->decrement('retours_parts', $retour->parts);
            $retour->delete();
            $distribution->refresh()->recalculerMontantAvantVersement();

            return ['ok' => true];
        });

        $retour = redirect()->route('livreurs.show', $livreur);

        return $resultat['ok']
            ? $retour->with('success', 'Retour annulé.')
            : $retour->with('error', $resultat['message']);
    }
}
