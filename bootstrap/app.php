<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\EnsureBoulangerieSetup;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\EviteDoublons;
use App\Http\Middleware\LimiteTailleRequete;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Traefik dechiffre le HTTPS et transfere en HTTP en interne -- sans
        // ceci, Laravel detecte une requete HTTP et genere des liens (assets,
        // routes) en http:// au lieu de https://, bloques par le navigateur
        // (contenu mixte) une fois la page chargee en HTTPS.
        $middleware->trustProxies(at: '*');

        // Le nom d'hôte reçu dans la requête n'est pas fiable : on n'accepte que celui de APP_URL (et ses
        // sous-domaines), plus ceux listés dans TRUSTED_HOSTS (séparés par des virgules). Sans cela, un
        // visiteur peut faire fabriquer au site de faux liens pointant vers un domaine qu'il contrôle.
        // (Désactivé en développement local et pendant les tests automatiques, par Laravel.)
        $middleware->trustHosts(
            at: fn () => array_values(array_filter(array_map('trim', explode(',', (string) env('TRUSTED_HOSTS', ''))))),
            subdomains: true,
        );

        $middleware->alias([
            'role'  => EnsureUserHasRole::class,
            'setup' => EnsureBoulangerieSetup::class,
        ]);
        // Plafond de taille : AVANT tout le reste (session comprise), sinon la session reçoit déjà la saisie géante.
        $middleware->prepend(LimiteTailleRequete::class);
        $middleware->web(append: [SecurityHeaders::class, EviteDoublons::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
