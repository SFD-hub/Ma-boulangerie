<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Distribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'livreur_id',
        'date_distribution',
        'montant_attendu',
        'nombre_pains',
        'prix_pain',
        'nombre_invendus',
        'reliquat',
        'statut',
    ];

    protected $casts = [
        'date_distribution' => 'date',
        'montant_attendu'   => 'decimal:2',
        'reliquat'          => 'decimal:2',
        'nombre_pains'      => 'integer',
        'prix_pain'         => 'integer',
        'nombre_invendus'   => 'integer',
    ];

    public function livreur(): BelongsTo
    {
        return $this->belongsTo(Livreur::class);
    }

    public function detailDistributions(): HasMany
    {
        return $this->hasMany(DetailDistribution::class);
    }

    public function versement(): HasOne
    {
        return $this->hasOne(Versement::class);
    }

    public function estRegle(): bool
    {
        // Réglée dès qu'un versement existe, ou si le statut est explicitement 'reglee' (ex: soldé par surplus)
        return $this->versement !== null || $this->statut === 'reglee';
    }

    public function getPainsAttribuesAttribute(): int
    {
        if ($this->nombre_pains !== null) {
            return $this->nombre_pains;
        }
        return (int) $this->detailDistributions->sum('quantite_attribuee');
    }

    public function getInvendusAttribute(): int
    {
        if ($this->nombre_invendus !== null) {
            return $this->nombre_invendus;
        }
        return (int) $this->detailDistributions->sum('quantite_retournee');
    }

    public function getPainsVendusAttribute(): int
    {
        return max(0, $this->pains_attribues - $this->invendus);
    }

    public function getMontantCalculeAttribute(): float
    {
        return (float) $this->detailDistributions->sum(
            fn ($d) => max(0, $d->quantite_attribuee - $d->quantite_retournee) * ($d->produit->prix ?? 0)
        );
    }
}
