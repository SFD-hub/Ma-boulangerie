<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Plafond de taille d'une requête. Les formulaires de l'application sont petits (quelques centaines
 * d'octets) : une requête de plusieurs centaines de Ko n'est jamais légitime. Sans plafond, une saisie
 * géante est renvoyée dans la session (stockée en base) et dépasse la limite d'un paquet MySQL, ce qui
 * fait échouer la requête et permet de provoquer des erreurs à volonté.
 */
class LimiteTailleRequete
{
    // 128 Ko : large pour un inventaire de plusieurs centaines de produits, très en dessous des limites de la base.
    private const MAX_OCTETS = 131072;

    public function handle(Request $request, Closure $next): Response
    {
        // Adresse démesurée : refusée comme le font déjà les serveurs web (8 Ko), mais aussi ici car le site
        // retient la dernière adresse visitée dans la session.
        if (strlen($request->getRequestUri()) > 8192) {
            abort(414, 'Adresse trop longue.');
        }

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $taille = max((int) $request->header('Content-Length', 0), strlen($request->getContent()));

            // Les paramètres déjà décodés (tests, serveurs qui ne transmettent pas le corps brut).
            if ($taille === 0 && $request->request->count() > 0) {
                $taille = strlen(serialize($request->request->all()));
            }

            if ($taille > self::MAX_OCTETS) {
                abort(413, 'La saisie est trop volumineuse.');
            }
        }

        return $next($request);
    }
}
