<?php

namespace App\Models;

use App\Models\Boulangerie;
use App\Models\Distribution;
use App\Models\Versement;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Livreur extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'nom',
        'prenom',
        'telephone',
        'adresse',
        'actif',
        'type',
        'boulangerie_id',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class);
    }

    public function versements(): HasMany
    {
        return $this->hasMany(Versement::class);
    }

    // "Livreur" et "Client" partagent la même mécanique — ce libellé ne sert
    // qu'à l'affichage (badges, écrans d'attribution/règlement).
    public function typeLabel(): string
    {
        return $this->type === 'client' ? 'Client' : 'Livreur';
    }
}
