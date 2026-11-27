<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required'    => 'Le téléphone ou email est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        $login = trim((string) $request->input('login'));
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'telephone';
        if ($field === 'telephone') {
            $login = \App\Support\Telephone::normaliser($login);   // « 77 123 45 67 », « +221 77… » : même compte
        }

        $credentials = [
            $field     => $login,
            'password' => $request->input('password'),
        ];

        // Verrou par COMPTE (en plus de la limite par adresse IP, que l'on peut contourner en changeant
        // d'adresse) : 8 échecs sur un même identifiant bloquent les essais pendant 15 minutes.
        $cleLimite = 'connexion:' . sha1(mb_strtolower(trim((string) $login)));
        if (RateLimiter::tooManyAttempts($cleLimite, 8)) {
            $minutes = (int) ceil(RateLimiter::availableIn($cleLimite) / 60);

            return back()
                ->withErrors(['login' => 'Trop de tentatives sur ce compte. Réessayez dans ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '') . '.'])
                ->onlyInput('login');
        }

        // Un identifiant inconnu doit coûter autant de temps qu'un identifiant connu avec un mauvais
        // mot de passe : sinon la durée de réponse révèle quels numéros ont un compte.
        if (! User::where($field, $login)->exists()) {
            Hash::make((string) $request->input('password'));
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($cleLimite, 900);
            Log::warning('Échec de connexion', ['login' => $login, 'ip' => $request->ip()]);

            return back()
                ->withErrors(['login' => 'Identifiants incorrects.'])
                ->onlyInput('login');
        }

        RateLimiter::clear($cleLimite);
        $request->session()->regenerate();

        $user = auth()->user();

        // Un gérant désactivé ne doit plus pouvoir se connecter, même avec
        // les bons identifiants — seule une réactivation par le propriétaire
        // le permet à nouveau.
        if (! $user->actif) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['login' => 'Ce compte est désactivé. Contactez votre propriétaire.'])
                ->onlyInput('login');
        }

        $user->update(['last_login_at' => now()]);

        if ($user->role?->nom === 'super_admin') {
            return redirect()->route('super-admin.dashboard');
        }

        if (! $user->boulangerie_id) {
            return redirect()->route('setup.boulangerie');
        }

        // Bloquer la connexion si la boulangerie est suspendue
        if ($user->boulangerie?->suspendu) {
            Auth::logout();
            $request->session()->invalidate();
            return back()
                ->withErrors(['login' => 'Cette boulangerie est suspendue. Contactez le support.'])
                ->onlyInput('login');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
