<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Double appui sur « Enregistrer » (réseau lent, téléphone) : la même action, envoyée deux fois
 * en quelques secondes par le même utilisateur, n'est exécutée qu'une fois.
 *
 * Si la première tentative est refusée (erreur de saisie, stock insuffisant…), la protection est
 * levée tout de suite : l'utilisateur peut renvoyer après correction.
 */
class EviteDoublons
{
    // Fenêtre pendant laquelle un envoi identique est considéré comme un double appui.
    private const SECONDES = 6;

    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true) || ! $request->user()) {
            return $next($request);
        }

        // La connexion et la déconnexion ont leurs propres protections.
        if ($request->routeIs('login.store', 'logout', 'register.store')) {
            return $next($request);
        }

        $cle = 'doublon:' . sha1(implode('|', [
            $request->user()->id,
            $request->method(),
            $request->path(),
            json_encode($request->except(['_token', '_method'])),
        ]));

        if (! Cache::add($cle, 1, self::SECONDES)) {
            return redirect()->back()->with('warning', 'Cette action vient déjà d\'être enregistrée.');
        }

        $reponse = $next($request);

        // Action refusée (validation, règle métier, erreur) : on peut la renvoyer aussitôt.
        $session = $request->hasSession() ? $request->session() : null;
        if ($reponse->getStatusCode() >= 400 || $session?->has('errors') || $session?->has('error') || $session?->has('warning')) {
            Cache::forget($cle);
        }

        return $reponse;
    }
}
