<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Versement;
use App\Models\Livreur;
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

    public function destroy(Versement $versement): RedirectResponse
    {
        abort_if(!$versement->livreur || $versement->livreur->boulangerie_id !== auth()->user()->boulangerie_id, 403);

        $livreur = $versement->livreur;

        \Illuminate\Support\Facades\DB::transaction(function () use ($versement, $livreur) {
            $versement->delete();
            // Le reliquat des distributions suit : l'argent revient « dû ».
            $livreur->recalculerReliquats();
        });

        return redirect()->route('versements.index')
            ->with('success', 'Versement supprimé avec succès.');
    }
}
