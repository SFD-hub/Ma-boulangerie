<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsommationAbonne extends Model
{
    use HasFactory;

    protected $table = 'consommations_abonnes';

    protected $fillable = [
        'client_abonne_id',
        'date_consommation',
        'quantite',
    ];

    protected $casts = [
        'date_consommation' => 'date',
        'quantite' => 'integer',
    ];

    public function clientAbonne(): BelongsTo
    {
        return $this->belongsTo(ClientAbonne::class);
    }
}
