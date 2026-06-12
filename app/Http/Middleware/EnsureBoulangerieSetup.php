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

        if ($user && !$user->boulangerie_id && !$request->routeIs('setup.*', 'logout')) {
            return redirect()->route('setup.boulangerie');
        }

        return $next($request);
    }
}
