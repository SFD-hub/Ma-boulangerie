<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un "bidon/sac terminé" validé par le gérant (sel, huile, gasoil).
 */
class RetraitStock extends Model
{
    use BelongsToTenant;

    protected $table = 'retraits_stock';

    protected $fillable = [
        'boulangerie_id',
        'matiere_premiere_id',
        'user_id',
        'date_retrait',
        'debut_le',
        'stock_apres',
        'note',
        'cout',
    ];

    protected $casts = [
        'date_retrait' => 'date',
        'debut_le'     => 'date',
        'stock_apres'  => 'decimal:2',
        'cout'         => 'decimal:2',
    ];

    public function matierePremiere(): BelongsTo
    {
        return $this->belongsTo(MatierePremiere::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Le propriétaire peut annuler n'importe quelle validation ; un gérant
     * seulement les siennes, dans les 24 heures (le temps de se rendre compte
     * d'une erreur, sans pouvoir réécrire l'historique).
     */
    public function peutEtreAnnuleePar(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if (in_array($user->role?->nom, ['proprietaire', 'super_admin'], true)) {
            return true;
        }

        return $this->user_id === $user->id && $this->created_at?->gte(now()->subDay());
    }

    // Nombre de jours d'utilisation de l'unité terminée (null si inconnu).
    public function dureeJours(): ?int
    {
        if (! $this->debut_le) {
            return null;
        }

        return max(0, (int) $this->debut_le->diffInDays($this->date_retrait));
    }
}
