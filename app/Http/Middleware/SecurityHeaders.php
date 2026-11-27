<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// En-têtes de sécurité de base pour toutes les réponses HTML de l'application.
// L'app est bâtie entièrement en CSS/JS inline (aucun script/style externe) :
// la CSP autorise donc 'unsafe-inline' pour script/style uniquement, tout en
// bloquant le chargement de ressources depuis un domaine tiers.
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; frame-ancestors 'none'"
        );

        // Caméra, micro, géolocalisation, paiement, capteurs : jamais utilisés par l'application.
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), accelerometer=(), gyroscope=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        // Ne pas annoncer la version de PHP.
        $response->headers->remove('X-Powered-By');
        if (! headers_sent()) {
            header_remove('X-Powered-By');
        }

        // Pages connectées : jamais gardées en cache par le navigateur ni un intermédiaire. Après la
        // déconnexion, le bouton « Retour » ne peut ainsi pas réafficher des chiffres de la boulangerie.
        if ($request->user()) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
            $response->headers->set('Pragma', 'no-cache');
        }
        // Sans effet tant que le site n'est pas servi en HTTPS (les navigateurs
        // ignorent cet en-tête reçu en HTTP) — déjà prêt pour la mise en ligne.
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
