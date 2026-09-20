<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepotVente extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'boulangerie_id',
        'produit_id',
        'date_vente',
        'nombre_entiers',
        'nombre_moities',
        'nombre_tiers',
        'quantite_equivalente',
        'montant',
    ];

    protected $casts = [
        'date_vente' => 'date',
        'nombre_entiers' => 'integer',
        'nombre_moities' => 'integer',
        'nombre_tiers' => 'integer',
        'quantite_equivalente' => 'decimal:2',
        'montant' => 'decimal:2',
    ];

    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    // Résumé lisible de la répartition par pièce, ex: "1 entier, 1 moitié, 1 tiers".
    public function descriptionPieces(): string
    {
        $morceaux = [];

        if ($this->nombre_entiers > 0) {
            $morceaux[] = $this->nombre_entiers . ' entier' . ($this->nombre_entiers > 1 ? 's' : '');
        }
        if ($this->nombre_moities > 0) {
            $morceaux[] = $this->nombre_moities . ' moitié' . ($this->nombre_moities > 1 ? 's' : '');
        }
        if ($this->nombre_tiers > 0) {
            $morceaux[] = $this->nombre_tiers . ' tiers';
        }

        return $morceaux ? implode(', ', $morceaux) : '—';
    }
}
