<?php

namespace App\Http\Controllers;

use App\Models\PaiementFacture;
use App\Models\Facture;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PaiementFactureController extends Controller
{
    public function index(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        $paiements = PaiementFacture::with('facture.clientAbonne')
            ->whereHas('facture.clientAbonne', function($query) use ($boulangerie_id) {
                $query->where('boulangerie_id', $boulangerie_id);
            })
            ->orderBy('date_paiement', 'desc')
            ->get();

        return view('paiements-factures.index', compact('paiements'));
    }

    public function create(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        $factures = Facture::whereHas('clientAbonne', function($query) use ($boulangerie_id) {
                $query->where('boulangerie_id', $boulangerie_id);
            })
            ->where('statut', 'envoyee')
            ->orderBy('date_facture', 'desc')
            ->get();

        return view('paiements-factures.create', compact('factures'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'facture_id' => 'required|exists:factures,id',
            'date_paiement' => 'required|date',
            'montant' => 'required|numeric|min:0.01',
        ], [
            'facture_id.required' => 'La facture est obligatoire.',
            'facture_id.exists' => 'La facture sélectionnée n\'existe pas.',
            'date_paiement.required' => 'La date de paiement est obligatoire.',
            'date_paiement.date' => 'La date de paiement doit être une date valide.',
            'montant.required' => 'Le montant est obligatoire.',
            'montant.numeric' => 'Le montant doit être un nombre.',
            'montant.min' => 'Le montant doit être supérieur à 0.',
        ]);

        $boulangerie_id = auth()->user()->boulangerie_id;

        // Vérifier que la facture appartient à la boulangerie
        $facture = Facture::find($validated['facture_id']);
        if ($facture->clientAbonne->boulangerie_id !== $boulangerie_id) {
            return redirect()->back()
                ->with('error', 'Facture invalide.');
        }

        PaiementFacture::create([
            'facture_id' => $validated['facture_id'],
            'date_paiement' => $validated['date_paiement'],
            'montant' => $validated['montant'],
        ]);

        return redirect()->route('paiements-factures.index')
            ->with('success', 'Paiement enregistré avec succès.');
    }

    public function show(PaiementFacture $paiementFacture): View
    {
        return view('paiements-factures.show', compact('paiementFacture'));
    }

    public function edit(PaiementFacture $paiementFacture): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        $factures = Facture::whereHas('clientAbonne', function($query) use ($boulangerie_id) {
                $query->where('boulangerie_id', $boulangerie_id);
            })
            ->where('statut', 'envoyee')
            ->orderBy('date_facture', 'desc')
            ->get();

        return view('paiements-factures.edit', compact('paiementFacture', 'factures'));
    }

    public function update(Request $request, PaiementFacture $paiementFacture): RedirectResponse
    {
        $validated = $request->validate([
            'facture_id' => 'required|exists:factures,id',
            'date_paiement' => 'required|date',
            'montant' => 'required|numeric|min:0.01',
        ], [
            'facture_id.required' => 'La facture est obligatoire.',
            'facture_id.exists' => 'La facture sélectionnée n\'existe pas.',
            'date_paiement.required' => 'La date de paiement est obligatoire.',
            'date_paiement.date' => 'La date de paiement doit être une date valide.',
            'montant.required' => 'Le montant est obligatoire.',
            'montant.numeric' => 'Le montant doit être un nombre.',
            'montant.min' => 'Le montant doit être supérieur à 0.',
        ]);

        $boulangerie_id = auth()->user()->boulangerie_id;

        // Vérifier que la facture appartient à la boulangerie
        $facture = Facture::find($validated['facture_id']);
        if ($facture->clientAbonne->boulangerie_id !== $boulangerie_id) {
            return redirect()->back()
                ->with('error', 'Facture invalide.');
        }

        $paiementFacture->update([
            'facture_id' => $validated['facture_id'],
            'date_paiement' => $validated['date_paiement'],
            'montant' => $validated['montant'],
        ]);

        return redirect()->route('paiements-factures.show', $paiementFacture)
            ->with('success', 'Paiement mis à jour avec succès.');
    }

    public function destroy(PaiementFacture $paiementFacture): RedirectResponse
    {
        $paiementFacture->delete();

        return redirect()->route('paiements-factures.index')
            ->with('success', 'Paiement supprimé avec succès.');
    }
}
