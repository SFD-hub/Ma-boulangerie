<?php

namespace App\Models;

use App\Models\Boulangerie;
use App\Models\ConsommationAbonne;
use App\Models\Facture;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientAbonne extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'clients_abonnes';

    protected $fillable = [
        'nom',
        'prenom',
        'telephone',
        'adresse',
        'actif',
        'boulangerie_id',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }

    public function consommations(): HasMany
    {
        return $this->hasMany(ConsommationAbonne::class);
    }

    public function factures(): HasMany
    {
        return $this->hasMany(Facture::class);
    }
}
