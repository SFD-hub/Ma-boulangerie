<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Distribution;
use App\Models\Livreur;
use App\Models\VenteVendeur;
use App\Support\Nombre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Ventes d'un vendeur de la Boutique au fil de la journée : il vend « selon
 * ce qu'on lui a donné ». On ne compte que des pièces (entiers, moitiés,
 * tiers) sur sa distribution en cours ; l'argent n'est encaissé qu'au
 * versement, qui pré-remplit alors les invendus avec ce qu'il lui reste.
 */
class VenteVendeurController extends Controller
{
    private function verifier(Livreur $livreur): void
    {
        abort_if($livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);
        abort_unless($livreur->estVendeur(), 404);
    }

    public function create(Livreur $livreur): View
    {
        $this->verifier($livreur);

        $distribution = $livreur->distributionEnCours();
        $ventes = $distribution
            ? $distribution->ventesVendeur()->orderByDesc('id')->get()
            : collect();

        $partsDonnees = $distribution ? $distribution->parts_confiees : 0;
        $partsVendues = (int) $ventes->sum('parts');
        $prixPain     = $distribution ? (int) ($distribution->prix_pain ?? 0) : 0;

        return view('livreurs.vendre', compact(
            'livreur', 'distribution', 'ventes', 'partsDonnees', 'partsVendues', 'prixPain'
        ));
    }

    public function store(Request $request, Livreur $livreur): RedirectResponse
    {
        $this->verifier($livreur);

        $validated = $request->validate([
            'nombre_entiers' => 'nullable|integer|min:0|max:9999',
            'nombre_moities' => 'nullable|integer|min:0|max:9999',
            'nombre_tiers'   => 'nullable|integer|min:0|max:9999',
        ]);

        $entiers = (int) ($validated['nombre_entiers'] ?? 0);
        $moities = (int) ($validated['nombre_moities'] ?? 0);
        $tiers   = (int) ($validated['nombre_tiers'] ?? 0);
        $parts   = $entiers * 6 + $moities * 3 + $tiers * 2;

        if ($parts === 0) {
            return redirect()->back()->withInput()->with('error',
                'Indiquez au moins un pain vendu (entier, moitié ou tiers).'
            );
        }

        $resultat = DB::transaction(function () use ($livreur, $entiers, $moities, $tiers, $parts) {
            $enCours = $livreur->distributionEnCours();
            if (! $enCours) {
                return ['ok' => false, 'message' => 'Ce vendeur n\'a aucun pain en main. Attribuez-lui d\'abord des pains.'];
            }

            // Verrou : deux ventes simultanées ne peuvent pas dépasser ce qu'il a en main.
            $distribution = Distribution::where('id', $enCours->id)->lockForUpdate()->firstOrFail();
            if ($distribution->versement()->exists()) {
                return ['ok' => false, 'message' => 'Cette distribution vient d\'être réglée.'];
            }

            $enMain = $distribution->partsEnMain();
            if ($parts > $enMain) {
                return ['ok' => false, 'message' => 'Il ne lui reste que ' . Nombre::pains($enMain / 6) . ' en main.'];
            }

            $vente = VenteVendeur::create([
                'boulangerie_id'  => $livreur->boulangerie_id,
                'livreur_id'      => $livreur->id,
                'distribution_id' => $distribution->id,
                'user_id'         => auth()->id(),
                'nombre_entiers'  => $entiers,
                'nombre_moities'  => $moities,
                'nombre_tiers'    => $tiers,
                'parts'           => $parts,
            ]);

            ActivityLog::record(
                'vente_vendeur',
                auth()->user()->name . ' a enregistré une vente de ' . trim($livreur->prenom . ' ' . $livreur->nom) . ' (' . $vente->descriptionPieces() . ')',
                $livreur->boulangerie_id,
                'vente'
            );

            return ['ok' => true, 'vente' => $vente];
        });

        if (! $resultat['ok']) {
            return redirect()->back()->withInput()->with('error', $resultat['message']);
        }

        return redirect()->route('livreurs.vendre.form', $livreur)
            ->with('success', 'Vente enregistrée (' . $resultat['vente']->descriptionPieces() . ').');
    }

    // Annulation d'une saisie faite par erreur, tant que la distribution n'est pas réglée.
    public function destroy(VenteVendeur $venteVendeur): RedirectResponse
    {
        abort_if($venteVendeur->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $livreur      = $venteVendeur->livreur;
        $distribution = $venteVendeur->distribution;

        if ($distribution && $distribution->versement()->exists()) {
            return redirect()->route('livreurs.vendre.form', $livreur)
                ->with('error', 'Cette distribution est déjà réglée : la vente ne peut plus être supprimée.');
        }

        $venteVendeur->delete();

        return redirect()->route('livreurs.vendre.form', $livreur)
            ->with('success', 'Vente supprimée.');
    }
}
