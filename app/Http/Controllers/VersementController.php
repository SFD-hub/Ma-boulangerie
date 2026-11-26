<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Versement;
use App\Models\Livreur;
use App\Models\Distribution;
use App\Support\Nombre;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class VersementController extends Controller
{
    public function index(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        
        $versements = Versement::whereHas('livreur', function ($query) use ($boulangerie_id) {
            $query->where('boulangerie_id', $boulangerie_id);
        })
            ->with('livreur')
            ->orderBy('date_versement', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('versements.index', compact('versements'));
    }

    public function show(Versement $versement): View
    {
        $versement->load('livreur');
        abort_if(!$versement->livreur || $versement->livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        return view('versements.show', compact('versement'));
    }

    // Page de correction d'un versement : le montant et la date ; pour le premier versement d'une
    // distribution, aussi les restants (une erreur de saisie se corrige ici, sans tout supprimer).
    public function modifier(Versement $versement): View
    {
        abort_if(! $versement->livreur || $versement->livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $distribution = $versement->distribution_id ? Distribution::find($versement->distribution_id) : null;
        $livreur      = $versement->livreur;
        $estPremier   = $distribution && (int) $distribution->versements()->value('id') === (int) $versement->id;
        $restantsModifiables = $estPremier && ! $livreur->estVendeur();

        return view('versements.modifier', compact('versement', 'distribution', 'livreur', 'restantsModifiables'));
    }

    public function mettreAJour(Request $request, Versement $versement): RedirectResponse
    {
        abort_if(! $versement->livreur || $versement->livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $validated = $request->validate([
            'montant_verse'    => 'required|numeric|min:0.01|max:999999999',
            'date_versement'   => 'required|date|after_or_equal:2020-01-01|before_or_equal:today',
            'entiers_invendus' => 'nullable|integer|min:0|max:9999999',
            'moities_invendus' => 'nullable|integer|min:0|max:9999999',
            'tiers_invendus'   => 'nullable|integer|min:0|max:9999999',
        ], [
            'montant_verse.required'         => 'Le montant versé est obligatoire.',
            'montant_verse.min'              => 'Le montant versé doit être supérieur à 0.',
            'date_versement.required'        => 'La date est obligatoire.',
            'date_versement.before_or_equal' => 'La date ne peut pas être dans le futur.',
        ]);

        $livreur      = $versement->livreur;
        $distribution = $versement->distribution_id ? Distribution::where('id', $versement->distribution_id)->first() : null;
        $estPremier   = $distribution && (int) $distribution->versements()->value('id') === (int) $versement->id;

        $erreur = DB::transaction(function () use ($validated, $versement, $livreur, $distribution, $estPremier, $request) {
            Livreur::where('id', $livreur->id)->lockForUpdate()->first();   // un seul recalcul des reliquats à la fois pour ce livreur
            // Restants corrigés (premier versement d'un livreur ou d'un client seulement).
            if ($estPremier && ! $livreur->estVendeur() && $request->has('entiers_invendus')) {
                $entiers = (int) ($validated['entiers_invendus'] ?? 0);
                $moities = (int) ($validated['moities_invendus'] ?? 0);
                $tiers   = (int) ($validated['tiers_invendus'] ?? 0);
                $parts   = $entiers * 6 + $moities * 3 + $tiers * 2;

                if ($parts > $distribution->parts_confiees) {
                    return 'Les restants (' . Nombre::pains($parts / 6) . ') ne peuvent pas dépasser les pains confiés (' . Nombre::pains($distribution->parts_confiees / 6) . ').';
                }

                $prix = (int) ($distribution->prix_pain ?? 0);
                $distribution->update([
                    'entiers_invendus'    => $entiers,
                    'moities_invendus'    => $moities,
                    'tiers_invendus'      => $tiers,
                    'invendus_equivalent' => round($parts / 6, 2),
                    'invendus_parts'      => $parts,
                    'montant_attendu'     => round(($distribution->parts_confiees - $parts) * $prix / 6),
                ]);
            }

            $versement->update([
                'montant_verse'  => $validated['montant_verse'],
                'date_versement' => $validated['date_versement'],
            ]);

            // Les reliquats suivent : même règle qu'à l'enregistrement.
            $livreur->recalculerReliquats();

            return null;
        });

        if ($erreur) {
            return redirect()->back()->withInput()->with('error', $erreur);
        }

        \App\Models\ActivityLog::record('versement_corrige', auth()->user()->name . ' a corrigé un versement de ' . trim($livreur->prenom . ' ' . $livreur->nom) . ' — maintenant ' . number_format((float) $validated['montant_verse'], 0, ',', ' ') . ' FCFA', auth()->user()->boulangerie_id, 'paiement');

        return redirect()->route('livreurs.distributions', $livreur)->with('success', 'Versement corrigé.');
    }

    public function destroy(Versement $versement): RedirectResponse
    {
        abort_if(! $versement->livreur || $versement->livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $livreur      = $versement->livreur;
        $distribution = $versement->distribution_id ? Distribution::where('id', $versement->distribution_id)->first() : null;

        DB::transaction(function () use ($versement, $livreur, $distribution) {
            Livreur::where('id', $livreur->id)->lockForUpdate()->first();   // un seul recalcul des reliquats à la fois pour ce livreur
            $versement->delete();

            // Plus aucun versement : la distribution redevient « en attente » (restants oubliés,
            // montant attendu sur tous les pains confiés).
            if ($distribution && ! $distribution->versements()->exists()) {
                $distribution->update([
                    'entiers_invendus'    => 0,
                    'moities_invendus'    => 0,
                    'tiers_invendus'      => 0,
                    'invendus_equivalent' => 0,
                    'invendus_parts'      => 0,
                    'statut'              => 'en_attente',
                ]);
                $distribution->recalculerMontantAvantVersement();
            }

            // Le reliquat des distributions suit : l'argent revient « dû ».
            $livreur->recalculerReliquats();
        });

        \App\Models\ActivityLog::record('versement_annule', auth()->user()->name . ' a annulé un versement de ' . number_format((float) $versement->montant_verse, 0, ',', ' ') . ' FCFA — ' . trim($livreur->prenom . ' ' . $livreur->nom), auth()->user()->boulangerie_id, 'paiement');

        return redirect()->back()->with('success', 'Versement annulé.');
    }
}
