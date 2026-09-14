<?php

namespace App\Http\Controllers;

use App\Models\Distribution;
use App\Models\Livreur;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class DistributionController extends Controller
{
    public function show(Distribution $distribution): View
    {
        $distribution->load('livreur', 'produit');
        abort_if(!$distribution->livreur || $distribution->livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $totalVerse  = $distribution->livreur->versements()->sum('montant_verse');
        $reliquat    = $distribution->livreur->distributions()->sum('montant_attendu') - $totalVerse;

        return view('distributions.show', compact('distribution', 'totalVerse', 'reliquat'));
    }

    public function edit(Distribution $distribution): View|RedirectResponse
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        abort_if(!$distribution->livreur || $distribution->livreur->boulangerie_id !== $boulangerie_id, 403);

        if ($distribution->estRegle()) {
            return redirect()->route('distributions.show', $distribution)
                ->with('error', 'Cette distribution est déjà réglée, elle ne peut plus être modifiée.');
        }

        $produits = Produit::where('boulangerie_id', $boulangerie_id)
            ->where('actif', true)
            ->orderBy('nom')
            ->get();

        return view('distributions.edit', compact('distribution', 'produits'));
    }

    public function update(Request $request, Distribution $distribution): RedirectResponse
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        abort_if(!$distribution->livreur || $distribution->livreur->boulangerie_id !== $boulangerie_id, 403);

        if ($distribution->estRegle()) {
            return redirect()->route('distributions.show', $distribution)
                ->with('error', 'Cette distribution est déjà réglée, elle ne peut plus être modifiée.');
        }

        $validated = $request->validate([
            'produit_id'         => ['required', Rule::exists('produits', 'id')->where('boulangerie_id', $boulangerie_id)],
            'date_distribution'  => 'required|date|before_or_equal:today',
            'nombre_pains'       => 'required|integer|min:1',
            'prix_pain'          => 'required|integer|min:1',
        ], [
            'produit_id.required'              => 'Le produit est obligatoire.',
            'date_distribution.required'       => 'La date de distribution est obligatoire.',
            'date_distribution.before_or_equal'=> 'La date ne peut pas être dans le futur.',
            'nombre_pains.required'            => 'Le nombre de pains est obligatoire.',
            'nombre_pains.min'                 => 'Le nombre de pains doit être au moins 1.',
            'prix_pain.required'               => 'Le prix du pain est obligatoire.',
            'prix_pain.min'                    => 'Le prix du pain doit être au moins 1 FCFA.',
        ]);

        $produit    = Produit::findOrFail($validated['produit_id']);
        $disponible = $produit->painsDisponibles($distribution->id);

        if ($validated['nombre_pains'] > $disponible) {
            $reste = max(0, $disponible);
            return redirect()->back()->withInput()->with('error',
                'Il ne reste que ' . $reste . ' pain' . ($reste > 1 ? 's' : '')
                . ' disponible' . ($reste > 1 ? 's' : '') . ' pour « ' . $produit->nom . ' ».'
            );
        }

        $montantAttendu = $validated['nombre_pains'] * $validated['prix_pain'];

        $distribution->update([
            'produit_id'        => $validated['produit_id'],
            'date_distribution' => $validated['date_distribution'],
            'nombre_pains'      => $validated['nombre_pains'],
            'prix_pain'         => $validated['prix_pain'],
            'montant_attendu'   => $montantAttendu,
            'reliquat'          => $montantAttendu,
        ]);

        return redirect()->route('distributions.show', $distribution)
            ->with('success', 'Distribution mise à jour avec succès.');
    }

    public function destroy(Distribution $distribution): RedirectResponse
    {
        $livreur = $distribution->livreur;
        abort_if($livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        // Le versement lié utilise onDelete('set null') : on le supprime explicitement
        // pour éviter un versement orphelin qui fausserait les totaux.
        $distribution->versement()->delete();

        $distribution->delete();

        return redirect()->route('livreurs.distributions', $livreur)
            ->with('success', 'Distribution supprimée.');
    }
}
