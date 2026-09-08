<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AchatMatierePremiere extends Model
{
    use HasFactory;

    protected $table = 'achats_matieres_premieres';

    protected $fillable = [
        'matiere_premiere_id',
        'quantite',
        'montant',
        'date_achat',
    ];

    protected $casts = [
        'date_achat' => 'date',
        'montant' => 'decimal:2',
        'quantite' => 'decimal:2',
    ];

    public function matierePremiere(): BelongsTo
    {
        return $this->belongsTo(MatierePremiere::class);
    }
}
