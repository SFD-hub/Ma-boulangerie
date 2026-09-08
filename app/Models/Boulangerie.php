<?php

namespace App\Models;

use App\Models\ClientAbonne;
use App\Models\Depense;
use App\Models\Livreur;
use App\Models\MatierePremiere;
use App\Models\Produit;
use App\Models\User;
use App\Models\Production;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'nom',
    'telephone',
    'adresse',
    'email',
    'prix_pain',
    'suspendu',
    'statut_abonnement',
    'date_expiration_abonnement',
])]
class Boulangerie extends Model
{
    protected function casts(): array
    {
        return [
            'date_expiration_abonnement' => 'date',
            'suspendu'                   => 'boolean',
        ];
    }

    public function livreurs(): HasMany
    {
        return $this->hasMany(Livreur::class);
    }

    public function clientsAbonnes(): HasMany
    {
        return $this->hasMany(ClientAbonne::class);
    }

    public function productions(): HasMany
    {
        return $this->hasMany(Production::class);
    }

    public function depenses(): HasMany
    {
        return $this->hasMany(Depense::class);
    }

    public function matieresPremieres(): HasMany
    {
        return $this->hasMany(MatierePremiere::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function produits(): HasMany
    {
        return $this->hasMany(Produit::class);
    }

    /**
     * Propriétaire(s) rattachés via la pivot (un compte propriétaire peut
     * posséder plusieurs boulangeries). Relation inverse de User::boulangeries().
     */
    public function proprietaires(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'boulangerie_proprietaire')->withTimestamps();
    }
}

