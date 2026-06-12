<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vente extends Model
{
    use HasFactory;

    protected $fillable = [
        'boulangerie_id',
        'user_id',
        'date_vente',
        'montant_total',
    ];

    protected $casts = [
        'date_vente' => 'date',
        'montant_total' => 'decimal:2',
    ];

    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailVente::class);
    }
}
