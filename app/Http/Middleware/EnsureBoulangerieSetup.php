<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBoulangerieSetup
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Le super admin n'a pas de boulangerie et n'en a pas besoin
        if ($user && $user->role?->nom === 'super_admin') {
            return $next($request);
        }

        if ($user && !$user->boulangerie_id && !$request->routeIs('setup.*', 'logout')) {
            return redirect()->route('setup.boulangerie');
        }

        // Coupe l'accès immédiatement si la boulangerie est suspendue entre-temps,
        // même pour une session déjà ouverte (pas seulement à la connexion).
        if ($user && $user->boulangerie_id && $user->boulangerie?->suspendu && !$request->routeIs('logout')) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['login' => 'Cette boulangerie est suspendue. Contactez le support.']);
        }

        return $next($request);
    }
}
