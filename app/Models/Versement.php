<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Versement extends Model
{
    use HasFactory;

    protected $fillable = [
        'livreur_id',
        'distribution_id',
        'date_versement',
        'montant_verse',
    ];

    protected $casts = [
        'date_versement' => 'date',
        'montant_verse'  => 'decimal:2',
    ];

    public function livreur(): BelongsTo
    {
        return $this->belongsTo(Livreur::class);
    }

    public function distribution(): BelongsTo
    {
        return $this->belongsTo(Distribution::class);
    }
}
