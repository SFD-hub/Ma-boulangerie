<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Prepare le filtrage par roles sans definir de permissions metier detaillees.
        $userRole = $request->user()?->role?->nom;

        if (! $userRole || ! in_array($userRole, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
