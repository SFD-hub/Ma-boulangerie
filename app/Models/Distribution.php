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
        'entiers_invendus',
        'moities_invendus',
        'tiers_invendus',
        'invendus_equivalent',
        'reliquat',
        'statut',
    ];

    protected $casts = [
        'date_distribution'    => 'date',
        'montant_attendu'      => 'decimal:2',
        'reliquat'             => 'decimal:2',
        'nombre_pains'         => 'integer',
        'prix_pain'            => 'integer',
        'nombre_invendus'      => 'integer',
        'entiers_invendus'     => 'integer',
        'moities_invendus'     => 'integer',
        'tiers_invendus'       => 'integer',
        'invendus_equivalent'  => 'decimal:2',
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
        // Réglée uniquement quand il ne reste plus rien à payer -- un
        // versement partiel (reliquat > 0) ne suffit pas, sinon un
        // versement supplémentaire sur cette même distribution est
        // faussement refusé comme "déjà réglée" (LivreurController::verser()),
        // et elle disparaît à tort de la liste des distributions à régler.
        return (float) $this->reliquat <= 0;
    }

    public function getPainsAttribuesAttribute(): int
    {
        return (int) $this->nombre_pains;
    }

    // Quantité invendue précise (entiers/moitiés/tiers) si le règlement a été
    // fait avec la répartition par pièce ; repli sur l'ancien compteur entier
    // pour les distributions réglées avant l'introduction de cette précision.
    public function getInvendusAttribute(): float
    {
        return $this->invendus_equivalent !== null
            ? (float) $this->invendus_equivalent
            : (float) $this->nombre_invendus;
    }

    public function getPainsVendusAttribute(): float
    {
        return max(0.0, $this->pains_attribues - $this->invendus);
    }
}
