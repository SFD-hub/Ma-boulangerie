<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\EnsureBoulangerieSetup;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\SecurityHeaders;

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

        $middleware->alias([
            'role'  => EnsureUserHasRole::class,
            'setup' => EnsureBoulangerieSetup::class,
        ]);
        $middleware->web(append: [SecurityHeaders::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
