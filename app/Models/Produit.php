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

    /**
     * Pains de ce produit encore disponibles pour distribution : tout ce qui
     * a été produit, moins tout ce qui a déjà été distribué (tous livreurs,
     * toutes dates confondues) — on ne peut jamais distribuer plus de pains
     * que ce que la production a réellement fourni.
     *
     * @param  int|null  $exclureDistributionId  Distribution à ignorer dans le
     *         total déjà distribué (modification d'une distribution
     *         existante : sa propre quantité ne doit pas se compter contre
     *         elle-même).
     */
    public function painsDisponibles(?int $exclureDistributionId = null): int
    {
        $totalProduit = $this->productions()->sum('nombre_pains_produits');

        $totalDistribueQuery = $this->distributions();
        if ($exclureDistributionId) {
            $totalDistribueQuery->where('id', '!=', $exclureDistributionId);
        }

        return (int) $totalProduit - (int) $totalDistribueQuery->sum('nombre_pains');
    }
}
