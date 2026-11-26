<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Pièces vendues par un vendeur de la Boutique sur une distribution (ce qu'on
// lui a donné le matin). Compte en sixièmes de pain, sans argent : l'encaissement
// se fait au versement.
class VenteVendeur extends Model
{
    use BelongsToTenant;

    protected $table = 'ventes_vendeur';

    protected $fillable = [
        'boulangerie_id',
        'livreur_id',
        'distribution_id',
        'user_id',
        'nombre_entiers',
        'nombre_moities',
        'nombre_tiers',
        'parts',
    ];

    protected $casts = [
        'nombre_entiers' => 'integer',
        'nombre_moities' => 'integer',
        'nombre_tiers'   => 'integer',
        'parts'          => 'integer',
    ];

    public function livreur(): BelongsTo
    {
        return $this->belongsTo(Livreur::class);
    }

    public function distribution(): BelongsTo
    {
        return $this->belongsTo(Distribution::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Résumé lisible, ex : "1 entier, 1 moitié, 1 tiers".
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
