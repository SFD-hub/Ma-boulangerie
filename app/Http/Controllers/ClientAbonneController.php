<?php

namespace App\Http\Controllers;

use App\Models\ClientAbonne;
use App\Models\ConsommationAbonne;
use App\Models\Facture;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ClientAbonneController extends Controller
{
    public function index(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        $clients = ClientAbonne::where('boulangerie_id', $boulangerie_id)
            ->orderBy('nom')
            ->get();

        return view('clients-abonnes.index', compact('clients'));
    }

    public function create(): View
    {
        return view('clients-abonnes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom_complet' => 'required|string|max:255',
            'telephone'   => 'required|string|max:20',
            'adresse'     => 'nullable|string|max:255',
            'actif'       => 'sometimes|boolean',
        ], [
            'nom_complet.required' => 'Le nom est obligatoire.',
            'telephone.required'   => 'Le téléphone est obligatoire.',
        ]);

        // Un seul champ "Prénom et nom" côté formulaire -- éclaté ici pour
        // rester compatible avec le reste de l'application, qui distingue
        // prenom/nom partout ailleurs.
        $parts  = preg_split('/\s+/', trim($validated['nom_complet']), 2);
        $prenom = count($parts) > 1 ? $parts[0] : '';
        $nom    = count($parts) > 1 ? $parts[1] : $parts[0];

        $boulangerie_id = auth()->user()->boulangerie_id;

        ClientAbonne::create([
            'prenom'         => $prenom,
            'nom'            => $nom,
            'telephone'      => $validated['telephone'],
            'adresse'        => $validated['adresse'] ?? null,
            'actif'          => $validated['actif'] ?? true,
            'boulangerie_id' => $boulangerie_id,
        ]);

        return redirect()->route('clients-abonnes.index')
            ->with('success', 'Abonné créé avec succès.');
    }

    public function show(ClientAbonne $clientAbonne): View
    {
        abort_if($clientAbonne->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        return view('clients-abonnes.show', compact('clientAbonne'));
    }

    public function edit(ClientAbonne $clientAbonne): View
    {
        abort_if($clientAbonne->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        return view('clients-abonnes.edit', compact('clientAbonne'));
    }

    public function update(Request $request, ClientAbonne $clientAbonne): RedirectResponse
    {
        abort_if($clientAbonne->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $validated = $request->validate([
            'nom'       => 'required|string|max:255',
            'prenom'    => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
            'adresse'   => 'nullable|string|max:255',
            'actif'     => 'sometimes|boolean',
        ]);

        $clientAbonne->update(array_merge($validated, [
            'actif' => $validated['actif'] ?? $clientAbonne->actif,
        ]));

        return redirect()->route('clients-abonnes.show', $clientAbonne)
            ->with('success', 'Abonné mis à jour.');
    }

    public function destroy(ClientAbonne $clientAbonne): RedirectResponse
    {
        abort_if($clientAbonne->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $clientAbonne->delete();

        return redirect()->route('clients-abonnes.index')
            ->with('success', 'Abonné supprimé.');
    }

    // ─── Pages dédiées (formulaires + historiques) ────────────────────────

    public function consoForm(ClientAbonne $clientAbonne): View
    {
        abort_if($clientAbonne->boulangerie_id !== auth()->user()->boulangerie_id, 403);
        return view('clients-abonnes.conso-form', compact('clientAbonne'));
    }

    public function factureForm(ClientAbonne $clientAbonne): View
    {
        abort_if($clientAbonne->boulangerie_id !== auth()->user()->boulangerie_id, 403);
        $prixPain = auth()->user()->boulangerie?->prix_pain ?? '';

        // Consommations groupées par YYYY-MM pour la prévisualisation JS
        $consommationsData = $clientAbonne->consommations()
            ->get()
            ->groupBy(fn ($c) => sprintf('%04d-%02d', $c->date_consommation->year, $c->date_consommation->month))
            ->map(fn ($items) => $items->sum('quantite'));

        return view('clients-abonnes.facture-form', compact('clientAbonne', 'prixPain', 'consommationsData'));
    }

    public function consoHistorique(ClientAbonne $clientAbonne): View
    {
        abort_if($clientAbonne->boulangerie_id !== auth()->user()->boulangerie_id, 403);
        $consommations = $clientAbonne->consommations()->orderByDesc('date_consommation')->get();
        $totalPains = $consommations->sum('quantite');
        return view('clients-abonnes.conso-historique', compact('clientAbonne', 'consommations', 'totalPains'));
    }

    public function facturesHistorique(ClientAbonne $clientAbonne): View
    {
        abort_if($clientAbonne->boulangerie_id !== auth()->user()->boulangerie_id, 403);
        $factures = $clientAbonne->factures()->with('paiementsFactures')->orderByDesc('annee')->orderByDesc('mois')->get();
        return view('clients-abonnes.factures-historique', compact('clientAbonne', 'factures'));
    }

    // ─── Consommation depuis le détail client ──────────────────────────────

    public function storeConso(Request $request, ClientAbonne $clientAbonne): RedirectResponse
    {
        abort_if($clientAbonne->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $validated = $request->validate([
            'date_consommation' => 'required|date|before_or_equal:today',
            'quantite'          => 'required|integer|min:1',
        ], [
            'date_consommation.required'     => 'La date est obligatoire.',
            'date_consommation.before_or_equal' => 'La date ne peut pas être dans le futur.',
            'quantite.required'              => 'La quantité est obligatoire.',
            'quantite.min'                   => 'La quantité doit être au moins 1.',
        ]);

        ConsommationAbonne::create([
            'client_abonne_id'  => $clientAbonne->id,
            'date_consommation' => $validated['date_consommation'],
            'quantite'          => $validated['quantite'],
        ]);

        return redirect()->route('clients-abonnes.show', $clientAbonne)
            ->with('success', 'Consommation enregistrée.');
    }

    // ─── Génération de facture depuis le détail client ─────────────────────

    public function storeFacture(Request $request, ClientAbonne $clientAbonne): RedirectResponse
    {
        abort_if($clientAbonne->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $validated = $request->validate([
            'mois'          => 'required|integer|min:1|max:12',
            'annee'         => 'required|integer|min:2020',
            'prix_unitaire' => 'required|integer|min:1',
        ], [
            'mois.required'          => 'Le mois est obligatoire.',
            'annee.required'         => 'L\'année est obligatoire.',
            'prix_unitaire.required' => 'Le prix du pain est obligatoire.',
            'prix_unitaire.min'      => 'Le prix doit être supérieur à 0.',
        ]);

        // Bloquer les doublons (même abonné, même mois, même année)
        $existe = Facture::where('client_abonne_id', $clientAbonne->id)
            ->where('mois', $validated['mois'])
            ->where('annee', $validated['annee'])
            ->exists();

        if ($existe) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Une facture existe déjà pour ce mois (' . $validated['mois'] . '/' . $validated['annee'] . ').');
        }

        // Calculer la quantité depuis les consommations du mois
        $quantiteTotale = (int) ConsommationAbonne::where('client_abonne_id', $clientAbonne->id)
            ->whereMonth('date_consommation', $validated['mois'])
            ->whereYear('date_consommation', $validated['annee'])
            ->sum('quantite');

        if ($quantiteTotale === 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Aucune consommation enregistrée pour ce mois. Ajoutez des consommations avant de générer la facture.');
        }

        $montantTotal = $quantiteTotale * $validated['prix_unitaire'];

        Facture::create([
            'client_abonne_id' => $clientAbonne->id,
            'mois'             => $validated['mois'],
            'annee'            => $validated['annee'],
            'quantite_totale'  => $quantiteTotale,
            'prix_unitaire'    => $validated['prix_unitaire'],
            'montant_total'    => $montantTotal,
            'date_facture'     => now()->toDateString(),
            'statut'           => 'impayee',
        ]);

        return redirect()->route('clients-abonnes.factures.historique', $clientAbonne)
            ->with('success', "Facture générée : {$quantiteTotale} pains × {$validated['prix_unitaire']} FCFA = " . number_format($montantTotal, 0, ',', ' ') . " FCFA");
    }
}
