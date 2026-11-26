<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Période (du… au…) des pages d'historique : lue dans l'URL (?du=…&au=…), mois en cours par
 * défaut, dates inversées remises dans l'ordre, durée limitée à 1 an. Utilisée avec le
 * sélecteur `partials/periode` pour que toutes les pages se comportent de la même façon.
 */
class Periode
{
    /**
     * @return array{0:string,1:string,2:bool} [du, au, periodeLimitee]
     */
    public static function depuis(Request $request): array
    {
        $valide = fn (?string $d) => $d && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;

        $du = $valide($request->query('du')) ? $request->query('du') : Carbon::today()->startOfMonth()->toDateString();
        $au = $valide($request->query('au')) ? $request->query('au') : Carbon::today()->toDateString();

        if ($du > $au) {
            [$du, $au] = [$au, $du];
        }

        $limitee = false;
        $minimum = Carbon::parse($au)->subYear()->addDay()->toDateString();
        if ($du < $minimum) {
            $du = $minimum;
            $limitee = true;
        }

        return [$du, $au, $limitee];
    }
}
