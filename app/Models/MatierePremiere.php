<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MatierePremiere extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'matieres_premieres';

    /**
     * Produits de stock d'une boulangerie, dans l'ordre d'affichage.
     * [nom => [unité, catégorie de dépense, retrait manuel, seuil d'alerte]]
     * - retrait manuel = non mesuré à la production : le gérant valide
     *   "bidon/sac terminé" (sel, huile, gasoil).
     */
    public const CATALOGUE = [
        'Farine'  => ['sac',    'achat_farine',  false, 15],
        'Levure'  => ['paquet', 'achat_levure',  false, 15],
        'Produit' => ['paquet', 'achat_produit', false, 15],
        'Sel'     => ['sac',    'achat_sel',     true,  2],
        'Huile'   => ['bidon',  'achat_huile',   true,  2],
        'Gasoil'  => ['bidon',  'achat_gasoil',  true,  2],
    ];

    // Icône et couleur de fond de la carte, par produit.
    private const APPARENCE = [
        'Farine'  => ['🌾', '#FFF3E0'],
        'Levure'  => ['🧪', '#FFF0F0'],
        'Produit' => ['📦', '#F5F3FF'],
        'Sel'     => ['🧂', '#F1F5F9'],
        'Huile'   => ['🫙', '#FFFBEB'],
        'Gasoil'  => ['⛽', '#FFEDD5'],
    ];

    protected $fillable = [
        'boulangerie_id',
        'nom',
        'unite',
        'categorie_depense',
        'retrait_manuel',
        'stock_actuel',
        'valeur_stock',
        'seuil_alerte',
    ];

    protected $casts = [
        'stock_actuel'   => 'decimal:2',
        'valeur_stock'   => 'decimal:2',
        'seuil_alerte'   => 'decimal:2',
        'retrait_manuel' => 'boolean',
    ];

    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }

    public function achats(): HasMany
    {
        return $this->hasMany(AchatMatierePremiere::class);
    }

    public function retraits(): HasMany
    {
        return $this->hasMany(RetraitStock::class);
    }

    /**
     * Crée les produits de stock manquants d'une boulangerie (sans toucher
     * à ceux qui existent déjà).
     */
    public static function creerCatalogue(int $boulangerieId): void
    {
        foreach (self::CATALOGUE as $nom => [$unite, $categorie, $retraitManuel, $seuil]) {
            static::withoutGlobalScopes()->firstOrCreate(
                ['boulangerie_id' => $boulangerieId, 'nom' => $nom],
                [
                    'unite'             => $unite,
                    'categorie_depense' => $categorie,
                    'retrait_manuel'    => $retraitManuel,
                    'stock_actuel'      => 0,
                    'seuil_alerte'      => $seuil,
                ]
            );
        }
    }

    /**
     * Pluriel régulier de l'unité selon la quantité : 1 bidon, 2 bidons.
     * "unité" est le repli pour les produits créés à la main.
     */
    public function uniteLibelle(float|int|string|null $quantite = 2): string
    {
        $unite = $this->unite ?: 'unité';

        return (float) $quantite > 1 ? $unite . 's' : $unite;
    }

    // Catégorie de dépense créée à l'achat ; achat_stock pour un produit
    // créé à la main (rangé dans « Autres » au bilan).
    public function categorieDepense(): string
    {
        return $this->categorie_depense ?: 'achat_stock';
    }

    public function icone(): string
    {
        return self::APPARENCE[$this->nom][0] ?? '📦';
    }

    public function fond(): string
    {
        return self::APPARENCE[$this->nom][1] ?? '#F3F4F6';
    }

    // Ordre d'affichage : le catalogue d'abord, puis les produits ajoutés à la main.
    public function ordre(): int
    {
        $pos = array_search($this->nom, array_keys(self::CATALOGUE), true);

        return $pos === false ? 100 : $pos;
    }

    // ── Valeur du stock (prix moyen pondéré) ────────────────────────────────
    // Le stock a une valeur en argent (`valeur_stock`) : elle monte à chaque achat,
    // baisse à chaque consommation au prix moyen du moment, et suit l'inventaire.
    // Le bénéfice est calculé sur ce qui est consommé, pas sur ce qui est acheté.

    // Dernier prix d'achat unitaire connu (repli quand le stock n'a pas de valeur).
    public function dernierCoutUnitaire(): float
    {
        $achat = $this->achats()
            ->where('quantite', '>', 0)
            ->orderByDesc('date_achat')
            ->orderByDesc('id')
            ->first();

        return $achat ? (float) $achat->montant / (float) $achat->quantite : 0.0;
    }

    // Prix moyen d'une unité en stock : valeur ÷ quantité.
    public function coutMoyen(): float
    {
        $stock  = (float) $this->stock_actuel;
        $valeur = (float) $this->valeur_stock;

        return ($stock > 0 && $valeur > 0) ? $valeur / $stock : $this->dernierCoutUnitaire();
    }

    /**
     * Retire une quantité du stock (production, « terminé ») et renvoie son COÛT,
     * valorisé au prix moyen. Si l'on vide le stock, on solde toute sa valeur (pas de
     * reste dû aux arrondis). L'appelant enregistre le modèle (save).
     */
    public function retirerDuStock(float $quantite): float
    {
        $stock  = (float) $this->stock_actuel;
        $valeur = (float) $this->valeur_stock;

        if ($stock > 0 && $valeur > 0) {
            $cout = $quantite >= $stock ? $valeur : round($valeur / $stock * $quantite, 2);
        } else {
            $cout = round($quantite * $this->dernierCoutUnitaire(), 2);
        }

        $this->stock_actuel = $stock - $quantite;
        $this->valeur_stock = max(0, $valeur - $cout);

        return $cout;
    }

    // Remet en stock (annulation d'une production / d'un « terminé ») : quantité + son coût.
    public function remettreEnStock(float $quantite, float $cout): void
    {
        $this->stock_actuel = (float) $this->stock_actuel + $quantite;
        $this->valeur_stock = (float) $this->valeur_stock + $cout;
    }

    // Entrée de stock (achat ou stock de départ) : quantité + montant payé / valeur déclarée.
    public function ajouterAuStock(float $quantite, float $valeur): void
    {
        $this->stock_actuel = (float) $this->stock_actuel + $quantite;
        $this->valeur_stock = (float) $this->valeur_stock + $valeur;
    }

    // Un produit peut recevoir son STOCK INITIAL (stock déjà présent à l'installation, sans
    // dépense) tant qu'il n'a aucun stock ni aucun historique d'achat : ensuite, tout ajout
    // passe par le module Achats.
    public function peutRecevoirStockInitial(): bool
    {
        return (float) $this->stock_actuel == 0.0 && ! $this->achats()->exists();
    }

    public function estEnAlerte(): bool
    {
        return (float) $this->stock_actuel <= (float) $this->seuil_alerte;
    }

    /**
     * Produits dont le stock est au seuil d'alerte ou en dessous, du plus bas
     * au plus haut (même règle que l'écran Stock et l'accueil).
     */
    public static function enAlerte(int $boulangerieId)
    {
        return static::where('boulangerie_id', $boulangerieId)
            ->whereColumn('stock_actuel', '<=', 'seuil_alerte')
            ->get()
            ->sortBy(fn (self $m) => [(float) $m->stock_actuel, $m->ordre()])
            ->values();
    }
}
