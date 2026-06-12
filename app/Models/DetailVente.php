<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailVente extends Model
{
    use HasFactory;

    protected $fillable = [
        'vente_id',
        'produit_id',
        'quantite',
        'prix_unitaire',
    ];

    protected $casts = [
        'prix_unitaire' => 'decimal:2',
        'quantite' => 'integer',
    ];

    public function vente(): BelongsTo
    {
        return $this->belongsTo(Vente::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }
}
