<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Global scope multi-tenant : filtre automatiquement toutes les requêtes
 * par la boulangerie de l'utilisateur connecté.
 *
 * Appliquer sur tout modèle possédant une colonne directe boulangerie_id.
 * Le scope est inactif si l'utilisateur n'a pas de boulangerie_id (super admin).
 */
trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $user = auth()->user();
            if ($user && $user->boulangerie_id) {
                $builder->where(
                    $builder->getModel()->getTable() . '.boulangerie_id',
                    $user->boulangerie_id
                );
            }
        });
    }
}
