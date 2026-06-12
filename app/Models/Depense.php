<?php

namespace App\Models;

use App\Models\Boulangerie;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Depense extends Model
{
    use HasFactory;

    protected $fillable = [
        'boulangerie_id',
        'libelle',
        'categorie',
        'montant',
        'date_depense',
    ];

    protected $casts = [
        'date_depense' => 'date',
        'montant' => 'decimal:2',
    ];

    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }
}
