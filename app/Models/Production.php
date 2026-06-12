<?php

namespace App\Models;

use App\Models\Boulangerie;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Production extends Model
{
    use HasFactory;

    protected $fillable = [
        'boulangerie_id',
        'date_production',
        'nombre_sacs',
        'quantite_farine',
        'quantite_levure',
        'nombre_pains_produits',
    ];

    protected $casts = [
        'date_production' => 'date',
        'nombre_sacs' => 'integer',
        'quantite_farine' => 'integer',
        'quantite_levure' => 'integer',
        'nombre_pains_produits' => 'integer',
    ];

    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }
}
