<?php

namespace App\Models;

use App\Models\Boulangerie;
use App\Traits\BelongsToTenant;
use App\Traits\HasMoment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Production extends Model
{
    use HasFactory, BelongsToTenant, HasMoment;

    protected $fillable = [
        'boulangerie_id',
        'produit_id',
        'date_production',
        'nombre_sacs',
        'quantite_farine',
        'quantite_levure',
        'nombre_pains_produits',
    ];

    protected $casts = [
        'date_production' => 'date',
        'nombre_sacs' => 'decimal:2',
        'quantite_farine' => 'decimal:2',
        'quantite_levure' => 'decimal:2',
        'nombre_pains_produits' => 'integer',
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
