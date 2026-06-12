<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'produit_id',
        'quantite_disponible',
        'seuil_alerte',
    ];

    protected $casts = [
        'quantite_disponible' => 'integer',
        'seuil_alerte' => 'integer',
    ];

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }
}
