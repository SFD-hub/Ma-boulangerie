<?php

namespace App\Models;

use App\Models\Boulangerie;
use App\Models\Distribution;
use App\Models\Versement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Livreur extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'telephone',
        'adresse',
        'actif',
        'boulangerie_id',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class);
    }

    public function versements(): HasMany
    {
        return $this->hasMany(Versement::class);
    }
}
