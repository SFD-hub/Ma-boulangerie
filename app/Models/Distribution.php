<?php

namespace App\Models;

use App\Traits\HasMoment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Distribution extends Model
{
    use HasFactory, HasMoment;

    protected $fillable = [
        'livreur_id',
        'produit_id',
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

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
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
        return (int) $this->nombre_pains;
    }

    public function getInvendusAttribute(): int
    {
        return (int) $this->nombre_invendus;
    }

    public function getPainsVendusAttribute(): int
    {
        return max(0, $this->pains_attribues - $this->invendus);
    }
}
