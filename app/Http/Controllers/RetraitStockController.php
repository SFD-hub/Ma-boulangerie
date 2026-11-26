<?php

namespace App\Http\Controllers;

use App\Models\AchatMatierePremiere;
use App\Models\ActivityLog;
use App\Models\MatierePremiere;
use App\Models\RetraitStock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

/**
 * "Bidon / sac terminé" : pour le sel, l'huile et le gasoil, la
 * consommation n'est pas mesurée au jour le jour. Le gérant valide qu'une
 * unité est terminée ; le stock baisse de 1 et l'événement est tracé
 * (qui, quand, durée d'utilisation).
 */
class RetraitStockController extends Controller
{
    public function index(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        $retraits = RetraitStock::where('boulangerie_id', $boulangerie_id)
            ->with(['matierePremiere', 'user'])
            ->orderByDesc('date_retrait')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // Durée moyenne d'une unité, par produit (uniquement quand elle est connue).
        $moyennes = RetraitStock::where('boulangerie_id', $boulangerie_id)
            ->whereNotNull('debut_le')
            ->selectRaw('matiere_premiere_id, AVG(DATEDIFF(date_retrait, debut_le)) as jours, COUNT(*) as nombre')
            ->groupBy('matiere_premiere_id')
            ->get()
            ->keyBy('matiere_premiere_id');

        $matieres = MatierePremiere::where('boulangerie_id', $boulangerie_id)
            ->where('retrait_manuel', true)
            ->get()
            ->sortBy(fn (MatierePremiere $m) => $m->ordre())
            ->values();

        return view('retraits-stock.index', compact('retraits', 'moyennes', 'matieres'));
    }

    public function store(Request $request, MatierePremiere $matierePremiere): RedirectResponse
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        abort_if($matierePremiere->boulangerie_id !== $boulangerie_id, 403);
        abort_unless($matierePremiere->retrait_manuel, 404);

        $validated = $request->validate([
            'date_retrait' => 'nullable|date|before_or_equal:today',
            'note'         => 'nullable|string|max:255',
        ], [
            'date_retrait.date'            => 'La date n\'est pas valide.',
            'date_retrait.before_or_equal' => 'La date ne peut pas être dans le futur.',
            'note.max'                     => 'La remarque ne doit pas dépasser 255 caractères.',
        ]);

        $dateRetrait = $validated['date_retrait'] ?? now()->toDateString();
        $note        = isset($validated['note']) ? trim($validated['note']) : null;

        $resultat = DB::transaction(function () use ($matierePremiere, $boulangerie_id, $dateRetrait, $note) {
            // Verrou : deux validations simultanées ne doivent pas retirer
            // la même unité deux fois.
            $mp = MatierePremiere::where('id', $matierePremiere->id)->lockForUpdate()->firstOrFail();

            if ((float) $mp->stock_actuel < 1) {
                return ['ok' => false, 'message' => 'Il n\'y a plus de stock de ' . mb_strtolower($mp->nom) . ' à retirer.'];
            }

            $dernier = RetraitStock::where('matiere_premiere_id', $mp->id)
                ->orderByDesc('date_retrait')
                ->orderByDesc('id')
                ->first();

            // On ne peut pas dater une validation avant la précédente : les
            // durées d'utilisation n'auraient plus de sens.
            if ($dernier && $dateRetrait < $dernier->date_retrait->toDateString()) {
                return ['ok' => false, 'message' => 'La date ne peut pas être antérieure à la dernière validation (' . $dernier->date_retrait->format('d/m/Y') . ').'];
            }

            // Début d'utilisation de l'unité qui vient de se terminer : la
            // fin de la précédente, ou — si le stock était tombé à 0 — le
            // premier achat qui a suivi.
            if ($dernier && (float) $dernier->stock_apres > 0) {
                $debut = $dernier->date_retrait;
            } else {
                $achats = AchatMatierePremiere::where('matiere_premiere_id', $mp->id);
                if ($dernier) {
                    // >= : un achat saisi dans la même seconde que le retrait
                    // précédent (précision des horodatages) compte aussi.
                    $achats->where('created_at', '>=', $dernier->created_at);
                }
                $debut = $achats->orderBy('date_achat')->value('date_achat');
            }

            // Une unité terminée = une unité CONSOMMÉE : son coût (prix moyen du stock) pèse sur
            // le bénéfice du mois, à la place de l'achat.
            $cout = $mp->retirerDuStock(1.0);
            $mp->save();

            $retrait = RetraitStock::create([
                'boulangerie_id'      => $boulangerie_id,
                'matiere_premiere_id' => $mp->id,
                'user_id'             => auth()->id(),
                'date_retrait'        => $dateRetrait,
                'debut_le'            => $debut,
                'stock_apres'         => $mp->stock_actuel,
                'note'                => $note !== '' ? $note : null,
                'cout'                => $cout,
            ]);

            ActivityLog::record(
                'retrait_stock',
                auth()->user()->name . ' a validé : ' . $mp->unite . ' de ' . mb_strtolower($mp->nom) . ' terminé',
                $boulangerie_id,
                'stock'
            );

            return ['ok' => true, 'mp' => $mp, 'retrait' => $retrait];
        });

        if (! $resultat['ok']) {
            return redirect()->route('matieres-premieres.show', $matierePremiere)->with('error', $resultat['message']);
        }

        $mp = $resultat['mp'];
        $reste = (float) $mp->stock_actuel;

        $message = ucfirst($mp->unite) . ' de ' . mb_strtolower($mp->nom) . ' terminé. '
            . ($reste > 0
                ? 'Il en reste ' . \App\Support\Nombre::qte($reste) . ' ' . $mp->uniteLibelle($reste) . '.'
                : 'Le stock est épuisé : pensez à en racheter.');

        // L'écran Stock affiche ce message dans un bandeau avec « Annuler »
        // (à la place du message de succès habituel).
        return redirect()->route('matieres-premieres.show', $matierePremiere)
            ->with('retrait_annulable', ['id' => $resultat['retrait']->id, 'message' => $message]);
    }

    // Annulation d'une validation faite par erreur : le propriétaire toujours,
    // le gérant pour ses propres validations des dernières 24 h.
    public function destroy(RetraitStock $retraitStock): RedirectResponse
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        abort_if($retraitStock->boulangerie_id !== $boulangerie_id, 403);
        abort_unless($retraitStock->peutEtreAnnuleePar(auth()->user()), 403);

        $resultat = DB::transaction(function () use ($retraitStock) {
            // Verrou sur le retrait lui-même : un double clic sur « Annuler »
            // ne doit pas rendre deux unités au stock.
            $retrait = RetraitStock::withoutGlobalScopes()->where('id', $retraitStock->id)->lockForUpdate()->first();
            if (! $retrait) {
                return false;
            }

            $mp = MatierePremiere::where('id', $retrait->matiere_premiere_id)->lockForUpdate()->firstOrFail();
            $mp->remettreEnStock(1.0, (float) $retrait->cout);
            $mp->save();

            $retrait->delete();

            return true;
        });

        $retour = redirect()->back(fallback: route('matieres-premieres.index'));

        return $resultat
            ? $retour->with('success', 'Validation annulée, le stock a été rétabli.')
            : $retour->with('error', 'Cette validation a déjà été annulée.');
    }
}
