<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Vente de pain restant au sac (souvent à des éleveurs). Le contenu d'un sac varie
// (30 à 35 pains, avec des moitiés) : on n'enregistre qu'une estimation de pains,
// corrigeable par « Ajuster le stock ».
class VenteRestant extends Model
{
    use BelongsToTenant;

    protected $table = 'ventes_restant';

    // Contenu estimé d'un sac, proposé à chaque vente (modifiable) : milieu de 30 à 35.
    public const PAINS_PAR_SAC = 32;

    protected $fillable = [
        'boulangerie_id',
        'user_id',
        'date_vente',
        'nombre_sacs',
        'pains_estimes',
        'montant',
    ];

    protected $casts = [
        'date_vente'    => 'date',
        'nombre_sacs'   => 'integer',
        'pains_estimes' => 'integer',
        'montant'       => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
