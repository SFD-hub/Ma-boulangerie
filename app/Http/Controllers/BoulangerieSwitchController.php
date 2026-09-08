<?php

namespace App\Http\Controllers;

use App\Models\Boulangerie;
use Illuminate\Http\RedirectResponse;

class BoulangerieSwitchController extends Controller
{
    /**
     * Bascule la boulangerie "active" du propriétaire connecté.
     * Le contrôle d'autorisation (le propriétaire possède-t-il réellement
     * cette boulangerie ?) s'appuie sur la table pivot, jamais sur la
     * session — c'est le premier des deux filets de sécurité, le second
     * étant la revérification défensive dans User::boulangerieId().
     */
    public function switch(Boulangerie $boulangerie): RedirectResponse
    {
        abort_unless(
            auth()->user()->boulangeries()->where('boulangeries.id', $boulangerie->id)->exists(),
            403
        );

        session(['active_boulangerie_id' => $boulangerie->id]);

        return redirect()->route('dashboard');
    }
}
