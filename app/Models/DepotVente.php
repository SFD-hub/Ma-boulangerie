<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepotVente extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'boulangerie_id',
        'produit_id',
        'date_vente',
        'quantite',
        'montant',
    ];

    protected $casts = [
        'date_vente' => 'date',
        'quantite' => 'integer',
        'montant' => 'decimal:2',
    ];

    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }
}
