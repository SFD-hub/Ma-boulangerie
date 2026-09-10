<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

        $login = $request->input('login');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'telephone';

        $credentials = [
            $field     => $login,
            'password' => $request->input('password'),
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            Log::warning('Échec de connexion', ['login' => $login, 'ip' => $request->ip()]);

            return back()
                ->withErrors(['login' => 'Identifiants incorrects.'])
                ->onlyInput('login');
        }

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
