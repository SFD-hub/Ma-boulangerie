<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'categorie',
        'description',
        'boulangerie_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }

    public function reads(): HasMany
    {
        return $this->hasMany(ActivityLogRead::class);
    }

    public static function record(string $action, string $description, ?int $boulangerieId = null, ?string $categorie = null): void
    {
        // Le journal ne doit JAMAIS faire échouer ni défaire l'action qu'il décrit : une panne
        // d'écriture ici est signalée dans les logs du serveur, mais le versement, l'achat ou la
        // production, eux, restent valides (sinon l'utilisateur voit une erreur, recommence, et
        // l'opération est enregistrée deux fois).
        try {
            static::create([
                'user_id'        => auth()->id(),
                'action'         => $action,
                'categorie'      => $categorie,
                'description'    => $description,
                'boulangerie_id' => $boulangerieId,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Métadonnées d'affichage par catégorie pour le centre de notifications.
     * 'tab' = null → visible uniquement sous "Toutes" (pas d'onglet dédié).
     */
    public static function categories(): array
    {
        return [
            'vente'        => ['label' => 'Vente enregistrée',       'icon' => '🧾', 'tab' => 'Ventes'],
            'achat'        => ['label' => 'Achat effectué',          'icon' => '📦', 'tab' => 'Achats'],
            'paiement'     => ['label' => 'Paiement reçu',           'icon' => '💰', 'tab' => 'Paiements'],
            'depense'      => ['label' => 'Dépense enregistrée',     'icon' => '💸', 'tab' => 'Dépenses'],
            'gerant'       => ['label' => 'Gérant ajouté',           'icon' => '👔', 'tab' => 'Gérants'],
            'production'   => ['label' => 'Production enregistrée',  'icon' => '🍞', 'tab' => null],
            'stock'        => ['label' => 'Stock mis à jour',        'icon' => '📦', 'tab' => null],
            'consommation' => ['label' => 'Consommation enregistrée','icon' => '👤', 'tab' => null],
            'systeme'      => ['label' => 'Action système',          'icon' => '⚙️', 'tab' => null],
        ];
    }
}
