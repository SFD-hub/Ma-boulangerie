<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MatierePremiere extends Model
{
    use HasFactory;

    protected $table = 'matieres_premieres';

    protected $fillable = [
        'boulangerie_id',
        'nom',
        'stock_actuel',
        'seuil_alerte',
    ];

    protected $casts = [
        'stock_actuel' => 'integer',
        'seuil_alerte' => 'integer',
    ];

    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }

    public function achats(): HasMany
    {
        return $this->hasMany(AchatMatierePremiere::class);
    }
}
