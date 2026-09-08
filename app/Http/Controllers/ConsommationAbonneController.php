<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ConsommationAbonne;
use App\Models\ClientAbonne;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ConsommationAbonneController extends Controller
{
    public function index(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        $consommations = ConsommationAbonne::with('clientAbonne')
            ->whereHas('clientAbonne', function($query) use ($boulangerie_id) {
                $query->where('boulangerie_id', $boulangerie_id);
            })
            ->orderBy('date_consommation', 'desc')
            ->get();

        return view('consommations-abonnes.index', compact('consommations'));
    }

    public function create(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        $clients = ClientAbonne::where('boulangerie_id', $boulangerie_id)
            ->where('actif', true)
            ->orderBy('nom')
            ->get();

        return view('consommations-abonnes.create', compact('clients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_abonne_id' => 'required|exists:clients_abonnes,id',
            'date_consommation' => 'required|date',
            'quantite' => 'required|integer|min:1',
        ], [
            'client_abonne_id.required' => 'Le client est obligatoire.',
            'client_abonne_id.exists' => 'Le client sélectionné n\'existe pas.',
            'date_consommation.required' => 'La date de consommation est obligatoire.',
            'date_consommation.date' => 'La date de consommation doit être une date valide.',
            'quantite.required' => 'La quantité est obligatoire.',
            'quantite.integer' => 'La quantité doit être un nombre entier.',
            'quantite.min' => 'La quantité doit être au moins 1.',
        ]);

        $boulangerie_id = auth()->user()->boulangerie_id;

        // Vérifier que le client appartient à la boulangerie
        $client = ClientAbonne::find($validated['client_abonne_id']);
        if ($client->boulangerie_id !== $boulangerie_id) {
            return redirect()->back()
                ->with('error', 'Client invalide.');
        }

        ConsommationAbonne::create([
            'client_abonne_id' => $validated['client_abonne_id'],
            'date_consommation' => $validated['date_consommation'],
            'quantite' => $validated['quantite'],
        ]);

        ActivityLog::record(
            'consommation_enregistree',
            auth()->user()->name . ' a enregistré une consommation — ' . $client->nom . ' (' . $validated['quantite'] . ' pain' . ($validated['quantite'] > 1 ? 's' : '') . ')',
            $boulangerie_id,
            'consommation'
        );

        return redirect()->route('consommations-abonnes.index')
            ->with('success', 'Consommation enregistrée avec succès.');
    }

    public function show(ConsommationAbonne $consommationAbonne): View
    {
        abort_if($consommationAbonne->clientAbonne->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        return view('consommations-abonnes.show', compact('consommationAbonne'));
    }

    public function edit(ConsommationAbonne $consommationAbonne): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        abort_if($consommationAbonne->clientAbonne->boulangerie_id !== $boulangerie_id, 403);

        $clients = ClientAbonne::where('boulangerie_id', $boulangerie_id)
            ->where('actif', true)
            ->orderBy('nom')
            ->get();

        return view('consommations-abonnes.edit', compact('consommationAbonne', 'clients'));
    }

    public function update(Request $request, ConsommationAbonne $consommationAbonne): RedirectResponse
    {
        $validated = $request->validate([
            'client_abonne_id' => 'required|exists:clients_abonnes,id',
            'date_consommation' => 'required|date',
            'quantite' => 'required|integer|min:1',
        ], [
            'client_abonne_id.required' => 'Le client est obligatoire.',
            'client_abonne_id.exists' => 'Le client sélectionné n\'existe pas.',
            'date_consommation.required' => 'La date de consommation est obligatoire.',
            'date_consommation.date' => 'La date de consommation doit être une date valide.',
            'quantite.required' => 'La quantité est obligatoire.',
            'quantite.integer' => 'La quantité doit être un nombre entier.',
            'quantite.min' => 'La quantité doit être au moins 1.',
        ]);

        $boulangerie_id = auth()->user()->boulangerie_id;
        abort_if($consommationAbonne->clientAbonne->boulangerie_id !== $boulangerie_id, 403);

        // Vérifier que le client appartient à la boulangerie
        $client = ClientAbonne::find($validated['client_abonne_id']);
        if ($client->boulangerie_id !== $boulangerie_id) {
            return redirect()->back()
                ->with('error', 'Client invalide.');
        }

        $consommationAbonne->update([
            'client_abonne_id' => $validated['client_abonne_id'],
            'date_consommation' => $validated['date_consommation'],
            'quantite' => $validated['quantite'],
        ]);

        return redirect()->route('clients-abonnes.show', $consommationAbonne->client_abonne_id)
            ->with('success', 'Consommation mise à jour.');
    }

    public function destroy(ConsommationAbonne $consommationAbonne): RedirectResponse
    {
        abort_if($consommationAbonne->clientAbonne->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $clientId = $consommationAbonne->client_abonne_id;
        $consommationAbonne->delete();

        return redirect()->route('clients-abonnes.show', $clientId)
            ->with('success', 'Consommation supprimée.');
    }
}
