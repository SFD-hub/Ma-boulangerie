<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Inventaire d'un produit du stock : le stock réellement compté remplace le stock
// théorique. L'écart (perte ou gain) est valorisé au prix moyen et pèse sur le
// bénéfice du mois (écarts d'inventaire).
class InventaireStock extends Model
{
    use BelongsToTenant;

    protected $table = 'inventaires_stock';

    protected $fillable = [
        'boulangerie_id',
        'matiere_premiere_id',
        'user_id',
        'date_inventaire',
        'quantite_avant',
        'quantite_comptee',
        'ecart',
        'cout_unitaire',
        'valeur_ecart',
        'note',
    ];

    protected $casts = [
        'date_inventaire'  => 'date',
        'quantite_avant'   => 'decimal:2',
        'quantite_comptee' => 'decimal:2',
        'ecart'            => 'decimal:2',
        'cout_unitaire'    => 'decimal:4',
        'valeur_ecart'     => 'decimal:2',
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
     * Enregistre le comptage d'un produit : le stock devient la quantité comptée et
     * sa valeur suit (écart × prix moyen). Retourne null s'il n'y a aucun écart.
     * À appeler dans une transaction, sur un produit verrouillé (lockForUpdate).
     */
    public static function enregistrer(MatierePremiere $mp, float $compte, ?string $note = null): ?self
    {
        $avant = (float) $mp->stock_actuel;
        $ecart = round($compte - $avant, 2);

        if ($ecart == 0.0) {
            return null;
        }

        $cout   = $mp->coutMoyen();
        $valeur = round($ecart * $cout, 2);

        $inventaire = static::create([
            'boulangerie_id'      => $mp->boulangerie_id,
            'matiere_premiere_id' => $mp->id,
            'user_id'             => auth()->id(),
            'date_inventaire'     => now()->toDateString(),
            'quantite_avant'      => $avant,
            'quantite_comptee'    => $compte,
            'ecart'               => $ecart,
            'cout_unitaire'       => $cout,
            'valeur_ecart'        => $valeur,
            'note'                => $note !== null && trim($note) !== '' ? trim($note) : null,
        ]);

        $mp->stock_actuel = $compte;
        $mp->valeur_stock = $compte <= 0 ? 0 : max(0, (float) $mp->valeur_stock + $valeur);
        $mp->save();

        return $inventaire;
    }
}
