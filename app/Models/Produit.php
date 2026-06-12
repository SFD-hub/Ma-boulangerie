<?php

namespace App\Models;

use App\Models\DetailDistribution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Produit extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description',
        'prix',
        'categorie_produit_id',
        'boulangerie_id',
    ];

    protected $casts = [
        'prix' => 'decimal:2',
    ];

    public function categorieProduit(): BelongsTo
    {
        return $this->belongsTo(CategorieProduit::class);
    }

    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }

    public function stock(): HasOne
    {
        return $this->hasOne(Stock::class);
    }

    public function detailDistributions(): HasMany
    {
        return $this->hasMany(DetailDistribution::class);
    }

    public function detailVentes(): HasMany
    {
        return $this->hasMany(DetailVente::class);
    }
}
