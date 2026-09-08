<?php

namespace App\Http\Controllers;

use App\Models\AchatMatierePremiere;
use App\Models\ActivityLog;
use App\Models\Depense;
use App\Models\MatierePremiere;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class AchatMatierePremiereController extends Controller
{
    public function index(): View
    {
        $boulangerie_id = auth()->user()->boulangerie_id;

        $achats = AchatMatierePremiere::whereHas('matierePremiere', fn ($q) => $q->where('boulangerie_id', $boulangerie_id))
            ->with('matierePremiere')
            ->orderByDesc('date_achat')
            ->get();

        $total = $achats->sum('montant');

        return view('achats-matieres-premieres.index', compact('achats', 'total'));
    }

    public function create(): View
    {
        $boulangerie_id   = auth()->user()->boulangerie_id;
        $matieresPremières = MatierePremiere::where('boulangerie_id', $boulangerie_id)
            ->orderBy('nom')
            ->get();

        return view('achats-matieres-premieres.create', compact('matieresPremières'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'matiere_premiere_id' => 'required|exists:matieres_premieres,id',
            'quantite'            => 'required|numeric|min:0.5|max:999999.99',
            'montant'             => 'required|numeric|min:0.01',
            'date_achat'          => 'required|date|before_or_equal:today',
        ], [
            'matiere_premiere_id.required' => 'La matière première est obligatoire.',
            'matiere_premiere_id.exists'   => 'La matière première sélectionnée n\'existe pas.',
            'quantite.required'            => 'La quantité est obligatoire.',
            'quantite.numeric'             => 'La quantité doit être un nombre (les demi-sacs sont acceptés, ex: 5.5).',
            'quantite.min'                 => 'La quantité doit être au moins 0,5.',
            'quantite.max'                 => 'La quantité est trop élevée.',
            'montant.required'             => 'Le prix total est obligatoire.',
            'montant.numeric'              => 'Le prix total doit être un nombre.',
            'montant.min'                  => 'Le prix total doit être supérieur à 0.',
            'date_achat.required'          => 'La date d\'achat est obligatoire.',
            'date_achat.before_or_equal'   => 'La date ne peut pas être dans le futur.',
        ]);

        $boulangerie_id   = auth()->user()->boulangerie_id;
        $matierePremiere  = MatierePremiere::findOrFail($validated['matiere_premiere_id']);

        abort_if($matierePremiere->boulangerie_id !== $boulangerie_id, 403);

        DB::transaction(function () use ($validated, $matierePremiere, $boulangerie_id) {
            // Verrouille la ligne de stock pour éviter qu'un achat concurrent
            // (même matière première, même instant) ne lise la même valeur
            // et n'en écrase un des deux à l'enregistrement.
            $mp = MatierePremiere::where('id', $matierePremiere->id)->lockForUpdate()->firstOrFail();

            // Enregistrer l'achat
            AchatMatierePremiere::create($validated);

            // Incrémenter le stock
            $mp->stock_actuel += $validated['quantite'];
            $mp->save();

            // Créer automatiquement la dépense correspondante
            $categorie = ($mp->nom === 'Farine') ? 'achat_farine' : 'achat_levure';

            Depense::create([
                'boulangerie_id' => $boulangerie_id,
                'libelle'        => 'Achat ' . $mp->nom . ' — ' . $validated['quantite'] . ' ' . ($mp->nom === 'Farine' ? 'sacs' : 'paquets'),
                'categorie'      => $categorie,
                'montant'        => $validated['montant'],
                'date_depense'   => $validated['date_achat'],
            ]);

            ActivityLog::record(
                'achat_matiere_premiere',
                auth()->user()->name . ' a acheté ' . $validated['quantite'] . ' ' . ($mp->nom === 'Farine' ? 'sacs' : 'paquets') . ' de ' . $mp->nom . ' — ' . number_format($validated['montant'], 0, ',', ' ') . ' FCFA',
                $boulangerie_id,
                'achat'
            );
        });

        return redirect()->route('matieres-premieres.index')
            ->with('success', 'Achat enregistré et dépense créée automatiquement.');
    }

    public function show(AchatMatierePremiere $achatMatierePremiere): View
    {
        $achatMatierePremiere->load('matierePremiere');
        abort_if($achatMatierePremiere->matierePremiere->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        return view('achats-matieres-premieres.show', compact('achatMatierePremiere'));
    }

    public function edit(AchatMatierePremiere $achatMatierePremiere): View
    {
        $boulangerie_id   = auth()->user()->boulangerie_id;
        $achatMatierePremiere->load('matierePremiere');
        abort_if($achatMatierePremiere->matierePremiere->boulangerie_id !== $boulangerie_id, 403);

        $matieresPremières = MatierePremiere::where('boulangerie_id', $boulangerie_id)
            ->orderBy('nom')
            ->get();

        return view('achats-matieres-premieres.edit', compact('achatMatierePremiere', 'matieresPremières'));
    }

    public function update(Request $request, AchatMatierePremiere $achatMatierePremiere): RedirectResponse
    {
        $validated = $request->validate([
            'matiere_premiere_id' => 'required|exists:matieres_premieres,id',
            'quantite'            => 'required|numeric|min:0.5|max:999999.99',
            'montant'             => 'required|numeric|min:0.01',
            'date_achat'          => 'required|date|before_or_equal:today',
        ], [
            'matiere_premiere_id.required' => 'La matière première est obligatoire.',
            'quantite.required'            => 'La quantité est obligatoire.',
            'quantite.min'                 => 'La quantité doit être au moins 0,5.',
            'quantite.max'                 => 'La quantité est trop élevée.',
            'montant.required'             => 'Le prix total est obligatoire.',
            'montant.min'                  => 'Le prix total doit être supérieur à 0.',
            'date_achat.required'          => 'La date d\'achat est obligatoire.',
            'date_achat.before_or_equal'   => 'La date ne peut pas être dans le futur.',
        ]);

        $boulangerie_id  = auth()->user()->boulangerie_id;
        // Vérifie que l'achat lui-même appartient à cette boulangerie
        $achatMatierePremiere->load('matierePremiere');
        abort_if($achatMatierePremiere->matierePremiere->boulangerie_id !== $boulangerie_id, 403);

        $matierePremiereRef = MatierePremiere::findOrFail($validated['matiere_premiere_id']);
        abort_if($matierePremiereRef->boulangerie_id !== $boulangerie_id, 403);

        $diff = $validated['quantite'] - $achatMatierePremiere->quantite;

        $resultat = DB::transaction(function () use ($validated, $achatMatierePremiere, $matierePremiereRef, $diff, $boulangerie_id) {
            // Verrouille la ligne de stock avant de revérifier la suffisance —
            // la vérification faite avant la transaction peut être obsolète
            // si un autre achat/production a modifié le stock entre-temps.
            $mp = MatierePremiere::where('id', $matierePremiereRef->id)->lockForUpdate()->firstOrFail();

            if ($mp->stock_actuel + $diff < 0) {
                return false;
            }

            $ancienMontant = $achatMatierePremiere->montant;
            $achatMatierePremiere->update($validated);

            $mp->stock_actuel += $diff;
            $mp->save();

            // Mettre à jour la dépense automatique liée (même date, même libellé contenant la matière)
            $categorie = ($mp->nom === 'Farine') ? 'achat_farine' : 'achat_levure';
            Depense::where('boulangerie_id', $boulangerie_id)
                ->where('categorie', $categorie)
                ->where('montant', $ancienMontant)
                ->where('date_depense', $achatMatierePremiere->getOriginal('date_achat'))
                ->orderByDesc('id')
                ->limit(1)
                ->update([
                    'libelle'      => 'Achat ' . $mp->nom . ' — ' . $validated['quantite'] . ' ' . ($mp->nom === 'Farine' ? 'sacs' : 'paquets'),
                    'montant'      => $validated['montant'],
                    'date_depense' => $validated['date_achat'],
                ]);

            return true;
        });

        if (! $resultat) {
            return redirect()->back()
                ->with('error', 'Stock insuffisant pour réduire la quantité de cet achat.');
        }

        return redirect()->route('achats-matieres-premieres.index')
            ->with('success', 'Achat mis à jour.');
    }

    public function destroy(AchatMatierePremiere $achatMatierePremiere): RedirectResponse
    {
        $boulangerie_id = auth()->user()->boulangerie_id;
        $achatMatierePremiere->load('matierePremiere');
        abort_if($achatMatierePremiere->matierePremiere->boulangerie_id !== $boulangerie_id, 403);

        DB::transaction(function () use ($achatMatierePremiere, $boulangerie_id) {
            $matierePremiere = MatierePremiere::where('id', $achatMatierePremiere->matiere_premiere_id)->lockForUpdate()->firstOrFail();
            $matierePremiere->stock_actuel = max(0, $matierePremiere->stock_actuel - $achatMatierePremiere->quantite);
            $matierePremiere->save();

            $categorie = ($matierePremiere->nom === 'Farine') ? 'achat_farine' : 'achat_levure';
            Depense::where('boulangerie_id', $boulangerie_id)
                ->where('categorie', $categorie)
                ->where('montant', $achatMatierePremiere->montant)
                ->where('date_depense', $achatMatierePremiere->date_achat)
                ->orderByDesc('id')
                ->limit(1)
                ->delete();

            $achatMatierePremiere->delete();
        });

        return redirect()->route('achats-matieres-premieres.index')
            ->with('success', 'Achat supprimé et stock ajusté.');
    }
}
