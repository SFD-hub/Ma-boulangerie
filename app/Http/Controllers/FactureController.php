<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use App\Models\ClientAbonne;
use App\Models\ConsommationAbonne;
use App\Models\PaiementFacture;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class FactureController extends Controller
{
    public function show(Facture $facture): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        abort_if(!$facture->clientAbonne || $facture->clientAbonne->boulangerie_id !== $boulangerie_id, 403);

        return view('factures.show', compact('facture'));
    }

    public function edit(Facture $facture): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        abort_if(!$facture->clientAbonne || $facture->clientAbonne->boulangerie_id !== $boulangerie_id, 403);

        return view('factures.edit', compact('facture'));
    }

    public function update(Request $request, Facture $facture): RedirectResponse
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        abort_if(!$facture->clientAbonne || $facture->clientAbonne->boulangerie_id !== $boulangerie_id, 403);

        $validated = $request->validate([
            'mois'            => 'required|integer|min:1|max:12',
            'annee'           => 'required|integer|min:2020',
            'prix_unitaire'   => 'required|integer|min:1',
            'quantite_totale' => 'required|integer|min:1',
            'montant_total'   => 'required|numeric|min:0.01',
        ], [
            'mois.required'          => 'Le mois est obligatoire.',
            'annee.required'         => 'L\'année est obligatoire.',
            'prix_unitaire.required' => 'Le prix unitaire est obligatoire.',
        ]);

        // Le statut n'est jamais modifiable à la main : il est toujours
        // recalculé à partir des paiements réellement enregistrés (voir
        // Facture::syncStatut()), y compris si le montant vient de changer ici.
        $facture->update($validated);
        $facture->syncStatut();

        return redirect()->route('clients-abonnes.factures.historique', $facture->clientAbonne)
            ->with('success', 'Facture mise à jour.');
    }

    public function destroy(Facture $facture): RedirectResponse
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        abort_if(!$facture->clientAbonne || $facture->clientAbonne->boulangerie_id !== $boulangerie_id, 403);

        $clientAbonne = $facture->clientAbonne;
        $facture->delete();

        return redirect()->route('clients-abonnes.factures.historique', $clientAbonne)
            ->with('success', 'Facture supprimée.');
    }

    // ─── Marquer une facture comme payée ───────────────────────────────────

    public function payer(Facture $facture): RedirectResponse
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        abort_if(!$facture->clientAbonne || $facture->clientAbonne->boulangerie_id !== $boulangerie_id, 403);

        $solde = $facture->solde();

        if ($solde <= 0) {
            return redirect()->back()->with('error', 'Cette facture est déjà payée.');
        }

        // "Solder en un clic" : enregistre un paiement couvrant tout le solde
        // restant, pour rester cohérent avec l'historique des paiements même
        // quand l'utilisateur ne veut pas détailler par tranche.
        PaiementFacture::create([
            'facture_id'    => $facture->id,
            'date_paiement' => now()->toDateString(),
            'montant'       => $solde,
        ]);

        $facture->syncStatut();

        return redirect()->back()
            ->with('success', 'Facture marquée comme payée.');
    }

    // ─── Vue impression / PDF ──────────────────────────────────────────────

    public function imprimer(Facture $facture): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        abort_if(!$facture->clientAbonne || $facture->clientAbonne->boulangerie_id !== $boulangerie_id, 403);

        $facture->load('clientAbonne');
        $boulangerie = auth()->user()->boulangerie;

        return view('factures.imprimer', compact('facture', 'boulangerie'));
    }
}
