<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Boulangerie;
use App\Models\Role;

#[Fillable(['name', 'email', 'telephone', 'password', 'role_id', 'boulangerie_id', 'actif', 'last_login_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'last_login_at'     => 'datetime',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }

    /**
     * Boulangeries possédées par ce compte (uniquement pertinent pour un
     * propriétaire qui peut en avoir plusieurs). Un gérant n'a jamais de
     * lignes ici : il reste rattaché à une seule boulangerie via la
     * colonne boulangerie_id classique.
     */
    public function boulangeries(): BelongsToMany
    {
        return $this->belongsToMany(Boulangerie::class, 'boulangerie_proprietaire')->withTimestamps();
    }

    private ?int $resolvedBoulangerieId = null;
    private bool $boulangerieIdResolved = false;

    /**
     * Accessor calculé : pour un propriétaire, retourne la boulangerie
     * "active" (session, validée contre la pivot boulangerie_proprietaire)
     * au lieu de la colonne brute — ce qui rend automatiquement conscients
     * de la boulangerie active tous les endroits de l'app qui lisent
     * auth()->user()->boulangerie_id (trait BelongsToTenant, contrôleurs,
     * relation boulangerie()), sans avoir à les modifier un par un.
     * Les gérants et le super admin gardent le comportement brut inchangé.
     * L'écriture (create/update) n'est pas interceptée : elle continue
     * de cibler la vraie colonne SQL normalement.
     *
     * Attention si l'app sérialise un jour ce modèle en JSON (API, logs) :
     * la valeur exposée sera la boulangerie active, pas la colonne brute.
     */
    protected function boulangerieId(): Attribute
    {
        return Attribute::make(get: function ($value) {
            if ($this->boulangerieIdResolved) {
                return $this->resolvedBoulangerieId;
            }
            $this->boulangerieIdResolved = true;

            if ($this->role?->nom !== 'proprietaire') {
                return $this->resolvedBoulangerieId = $value;
            }

            $ownedIds = $this->boulangeries()->pluck('boulangeries.id')->all();
            if (empty($ownedIds)) {
                return $this->resolvedBoulangerieId = $value;
            }

            $active = session('active_boulangerie_id');
            if ($active && in_array($active, $ownedIds, true)) {
                return $this->resolvedBoulangerieId = $active;
            }

            return $this->resolvedBoulangerieId = $ownedIds[0];
        });
    }
}
