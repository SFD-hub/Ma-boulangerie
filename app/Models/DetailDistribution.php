<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'distribution_id',
        'produit_id',
        'quantite_attribuee',
        'quantite_retournee',
    ];

    protected $casts = [
        'quantite_attribuee' => 'integer',
        'quantite_retournee' => 'integer',
    ];

    public function distribution(): BelongsTo
    {
        return $this->belongsTo(Distribution::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }
}
