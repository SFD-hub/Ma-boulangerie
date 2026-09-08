<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produit extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'nom',
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

    public function productions(): HasMany
    {
        return $this->hasMany(Production::class);
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class);
    }

    // Produit présélectionné dans les formulaires de production/attribution :
    // "Pain" reste le cas d'usage habituel, les autres types sont un choix
    // volontaire de l'utilisateur.
    public static function defaultIdPour(int $boulangerie_id): ?int
    {
        return static::where('boulangerie_id', $boulangerie_id)
            ->where('actif', true)
            ->orderByRaw("nom = 'Pain' desc")
            ->orderBy('id')
            ->value('id');
    }
}
