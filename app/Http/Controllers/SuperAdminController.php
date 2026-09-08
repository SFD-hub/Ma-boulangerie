<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Boulangerie;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SuperAdminController extends Controller
{
    public function dashboard(): View
    {
        $roleProprietaire = Role::where('nom', 'proprietaire')->value('id');
        $roleGerant       = Role::where('nom', 'gerant')->value('id');

        return view('super-admin.dashboard', [
            'totalBoulangeries'     => Boulangerie::count(),
            'totalUtilisateurs'     => User::whereHas('role', fn ($q) => $q->whereIn('nom', ['proprietaire', 'gerant']))->count(),
            'totalProprietaires'    => User::where('role_id', $roleProprietaire)->count(),
            'totalGerants'          => User::where('role_id', $roleGerant)->count(),
            'dernieresBoulangeries' => Boulangerie::orderByDesc('created_at')->limit(5)->get(),
        ]);
    }

    public function boulangeries(): View
    {
        $boulangeries = Boulangerie::with('proprietaires')
            ->orderByDesc('created_at')
            ->get();

        // Regroupe par propriétaire (via la table pivot boulangerie_proprietaire,
        // seule source fiable maintenant qu'un propriétaire peut posséder
        // plusieurs boulangeries — la colonne users.boulangerie_id ne pointe
        // que vers sa boulangerie d'origine).
        $groupes = $boulangeries
            ->filter(fn ($b) => $b->proprietaires->isNotEmpty())
            ->groupBy(fn ($b) => $b->proprietaires->first()->id)
            ->map(fn ($items) => [
                'proprietaire' => $items->first()->proprietaires->first(),
                'boulangeries' => $items->values(),
            ])
            ->sortBy(fn ($g) => $g['proprietaire']->name)
            ->values();

        $sansProprietaire = $boulangeries->filter(fn ($b) => $b->proprietaires->isEmpty())->values();

        return view('super-admin.boulangeries.index', [
            'groupes'           => $groupes,
            'sansProprietaire'  => $sansProprietaire,
            'totalBoulangeries' => $boulangeries->count(),
        ]);
    }

    public function showBoulangerie(Boulangerie $boulangerie): View
    {
        $boulangerie->load(['proprietaires', 'users.role']);

        $proprietaire = $boulangerie->proprietaires->first();
        $gerants      = $boulangerie->users->filter(fn ($u) => $u->role?->nom === 'gerant')->values();

        return view('super-admin.boulangeries.show', compact('boulangerie', 'proprietaire', 'gerants'));
    }

    public function utilisateurs(): View
    {
        $users = User::with(['role', 'boulangerie', 'boulangeries'])
            ->whereHas('role', fn ($q) => $q->whereIn('nom', ['proprietaire', 'gerant']))
            ->get();

        $boulangeries = Boulangerie::with(['proprietaires', 'users.role'])
            ->orderBy('nom')
            ->get();

        // Regroupe les utilisateurs par boulangerie : propriétaire(s) (via la
        // pivot) + gérants (via la colonne fixe) de chaque boulangerie.
        $groupes = $boulangeries
            ->map(function ($b) {
                $gerants = $b->users->filter(fn ($u) => $u->role?->nom === 'gerant');
                $membres = $b->proprietaires->concat($gerants)
                    ->sortByDesc(fn ($u) => $u->role?->nom === 'proprietaire')
                    ->values();

                return ['boulangerie' => $b, 'membres' => $membres];
            })
            ->filter(fn ($g) => $g['membres']->isNotEmpty())
            ->values();

        // Utilisateurs rattachés à aucune boulangerie (ex: propriétaire dont
        // l'inscription est inachevée — assistant de configuration jamais fini).
        $idsRattaches = $groupes->flatMap(fn ($g) => $g['membres']->pluck('id'))->unique();
        $sansBoulangerie = $users->whereNotIn('id', $idsRattaches)->values();

        return view('super-admin.utilisateurs.index', [
            'groupes'          => $groupes,
            'sansBoulangerie'  => $sansBoulangerie,
            'totalUtilisateurs' => $users->count(),
        ]);
    }

    // ── Suspension ───────────────────────────────────────────────────────────

    public function suspendre(Boulangerie $boulangerie): RedirectResponse
    {
        $boulangerie->update(['suspendu' => true]);

        ActivityLog::record(
            'suspension',
            "Super Admin a suspendu la boulangerie « {$boulangerie->nom} »",
            $boulangerie->id
        );

        return back()->with('success', "Boulangerie « {$boulangerie->nom} » suspendue.");
    }

    public function reactiver(Boulangerie $boulangerie): RedirectResponse
    {
        $boulangerie->update(['suspendu' => false]);

        ActivityLog::record(
            'reactivation',
            "Super Admin a réactivé la boulangerie « {$boulangerie->nom} »",
            $boulangerie->id
        );

        return back()->with('success', "Boulangerie « {$boulangerie->nom} » réactivée.");
    }

    // ── Impersonation (accès support) ─────────────────────────────────────────

    public function accederBoulangerie(Boulangerie $boulangerie): RedirectResponse
    {
        $proprietaire = $boulangerie->proprietaires()->first();

        if (! $proprietaire) {
            return back()->with('error', 'Aucun propriétaire trouvé pour cette boulangerie.');
        }

        ActivityLog::record(
            'acces_support',
            "Super Admin a accédé à la boulangerie « {$boulangerie->nom} »",
            $boulangerie->id
        );

        // Sauvegarde l'ID du super admin avant le changement de session
        session(['impersonator_id' => auth()->id()]);

        Auth::login($proprietaire);
        session()->regenerate();

        // S'assure d'atterrir sur CETTE boulangerie précise, même si le
        // propriétaire en possède plusieurs.
        session(['active_boulangerie_id' => $boulangerie->id]);

        return redirect()->route('dashboard');
    }

    // ── Réinitialisation mot de passe ────────────────────────────────────────────

    public function resetPasswordForm(User $user): View
    {
        abort_if($user->role?->nom === 'super_admin', 403);

        $user->load(['role', 'boulangerie']);

        return view('super-admin.utilisateurs.reset-password', compact('user'));
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        abort_if($user->role?->nom === 'super_admin', 403);

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.required'  => 'Le mot de passe est obligatoire.',
            'password.min'       => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $user->update(['password' => Hash::make($request->password)]);

        ActivityLog::record(
            'reinitialisation_mot_de_passe',
            "Super Admin a réinitialisé le mot de passe de {$user->name}",
            $user->boulangerie_id
        );

        return redirect()->route('super-admin.utilisateurs')
            ->with('success', "Mot de passe de « {$user->name} » réinitialisé avec succès.");
    }

    // ── Mode support ─────────────────────────────────────────────────────────────

    public function quitterSupport(): RedirectResponse
    {
        $superAdminId = session('impersonator_id');

        if (! $superAdminId) {
            return redirect()->route('login');
        }

        $superAdmin = User::find($superAdminId);

        // Capturer les infos de la boulangerie avant de quitter la session
        $boulangerie = auth()->user()?->boulangerie;

        session()->forget('impersonator_id');
        session()->forget('active_boulangerie_id');

        // Revérifie que l'utilisateur retrouvé est bien super admin avant de
        // se reconnecter en tant que lui — ne fait jamais confiance à l'ID
        // de session seul.
        if (! $superAdmin || $superAdmin->role?->nom !== 'super_admin') {
            return redirect()->route('login');
        }

        Auth::login($superAdmin);
        session()->regenerate();

        ActivityLog::record(
            'quitter_support',
            "Super Admin a quitté le mode support" . ($boulangerie ? " (boulangerie : {$boulangerie->nom})" : ''),
            $boulangerie?->id
        );

        return redirect()->route('super-admin.dashboard');
    }
}
