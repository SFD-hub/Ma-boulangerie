<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Facture;
use App\Models\PaiementFacture;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class PaiementFactureController extends Controller
{
    public function store(Request $request, Facture $facture): RedirectResponse
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        abort_if(!$facture->clientAbonne || $facture->clientAbonne->boulangerie_id !== $boulangerie_id, 403);

        $solde = $facture->solde();

        if ($solde <= 0) {
            return redirect()->back()->with('error', 'Cette facture est déjà entièrement payée.');
        }

        $validated = $request->validate([
            'montant'       => ['required', 'numeric', 'min:0.01', 'max:' . $solde],
            'date_paiement' => 'required|date|before_or_equal:today',
        ], [
            'montant.required'              => 'Le montant est obligatoire.',
            'montant.min'                   => 'Le montant doit être supérieur à 0.',
            'montant.max'                   => 'Le montant dépasse le solde restant (' . number_format($solde, 0, ',', ' ') . ' FCFA).',
            'date_paiement.required'        => 'La date de paiement est obligatoire.',
            'date_paiement.before_or_equal' => 'La date ne peut pas être dans le futur.',
        ]);

        PaiementFacture::create([
            'facture_id'    => $facture->id,
            'date_paiement' => $validated['date_paiement'],
            'montant'       => $validated['montant'],
        ]);

        $facture->syncStatut();

        ActivityLog::record(
            'paiement_facture_recu',
            auth()->user()->name . ' a enregistré un paiement de ' . number_format($validated['montant'], 0, ',', ' ') . ' FCFA — ' . $facture->clientAbonne->nom,
            $boulangerie_id,
            'paiement'
        );

        return redirect()->route('clients-abonnes.factures.historique', $facture->clientAbonne)
            ->with('success', 'Paiement enregistré.');
    }

    public function destroy(PaiementFacture $paiementFacture): RedirectResponse
    {
        $facture = $paiementFacture->facture;
        abort_if(!$facture->clientAbonne || $facture->clientAbonne->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $clientAbonne = $facture->clientAbonne;
        $paiementFacture->delete();
        $facture->syncStatut();

        return redirect()->route('clients-abonnes.factures.historique', $clientAbonne)
            ->with('success', 'Paiement supprimé.');
    }
}
