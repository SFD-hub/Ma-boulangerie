<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Pains rapportés (intacts) par un livreur/client/vendeur parce qu'il n'a pas pu
// les donner : ils retournent dans le stock du jour. Comptés en sixièmes de pain :
// un livreur/client rapporte des pains entiers, un vendeur de la Boutique peut
// aussi rapporter des moitiés et des tiers.
class RetourDistribution extends Model
{
    use BelongsToTenant;

    protected $table = 'retours_distribution';

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

    // Résumé lisible, ex : "2 entiers, 1 moitié".
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
