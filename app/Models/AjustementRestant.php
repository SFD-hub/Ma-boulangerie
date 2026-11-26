<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Correction du stock de pain restant après comptage : enregistre l'écart entre le
// stock estimé et le stock réellement compté (en sixièmes de pain, peut être négatif).
class AjustementRestant extends Model
{
    use BelongsToTenant;

    protected $table = 'ajustements_restant';

    protected $fillable = [
        'boulangerie_id',
        'user_id',
        'date_ajustement',
        'parts_avant',
        'parts_apres',
        'ecart_parts',
    ];

    protected $casts = [
        'date_ajustement' => 'date',
        'parts_avant'     => 'integer',
        'parts_apres'     => 'integer',
        'ecart_parts'     => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
