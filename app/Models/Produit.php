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

    public function depotVentes(): HasMany
    {
        return $this->hasMany(DepotVente::class);
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
     * Pains produits ce jour-là (en sixièmes de pain).
     *
     * @param  int|null  $exclureProductionId  Production à ignorer (modification
     *         ou suppression de cette production).
     */
    public function partsProduitesLe(string $date, ?int $exclureProductionId = null): int
    {
        $requete = $this->productions()->whereDate('date_production', $date);
        if ($exclureProductionId) {
            $requete->where('id', '!=', $exclureProductionId);
        }

        return (int) $requete->sum('nombre_pains_produits') * 6;
    }

    /**
     * Pains déjà sortis ce jour-là (en sixièmes) : distribués aux livreurs,
     * clients et vendeurs, plus les anciennes ventes saisies au comptoir.
     *
     * @param  int|null  $exclureDistributionId  Distribution à ignorer dans le
     *         total (modification d'une distribution existante : sa propre
     *         quantité ne doit pas se compter contre elle-même).
     */
    public function partsSortiesLe(string $date, ?int $exclureDistributionId = null, ?int $exclureConsommationId = null): int
    {
        $distribue = $this->distributions()->whereDate('date_distribution', $date);
        if ($exclureDistributionId) {
            $distribue->where('id', '!=', $exclureDistributionId);
        }

        $depot = (int) $this->depotVentes()->whereDate('date_vente', $date)->sum('parts');

        // Un retour (pains rapportés intacts le jour même) remet ces pains au stock du jour.
        $net = (int) $distribue->sum(\Illuminate\Support\Facades\DB::raw('nombre_pains * 6 - retours_parts'));

        // Le pain donné aux abonnés sort aussi de la production du jour.
        $abonnes = ConsommationAbonne::where('produit_id', $this->id)->whereDate('date_consommation', $date);
        if ($exclureConsommationId) {
            $abonnes->where('id', '!=', $exclureConsommationId);
        }

        return $net + $depot + (int) $abonnes->sum('quantite') * 6;
    }

    /**
     * Le pain donné aux abonnés : le premier type de pain de la boulangerie.
     */
    public static function painAbonnes(): ?self
    {
        return static::orderBy('id')->first();
    }

    /**
     * Message de refus si la production du jour ne couvre pas la consommation
     * d'un abonné ; null si c'est possible.
     */
    public static function refusConsommationAbonne(string $date, int $quantite, ?int $exclureConsommationId = null, ?int $produitId = null): ?string
    {
        $produit = $produitId ? static::find($produitId) : static::painAbonnes();
        if (! $produit) {
            return 'Aucun type de pain n\'est défini.';
        }

        if ($produit->partsDisponibles(null, $date, $exclureConsommationId) < $quantite * 6) {
            return str_replace(
                ['avant de distribuer', 'sont déjà distribués'],
                ['avant d\'enregistrer la consommation', 'sont déjà distribués ou consommés'],
                $produit->messageStockInsuffisant($date, null, $exclureConsommationId)
            );
        }

        return null;
    }

    /**
     * Pains disponibles pour le jour donné (aujourd'hui par défaut) : la
     * production de CE jour moins ce qui est déjà sorti ce jour-là. Pas de
     * production enregistrée ce jour-là = rien à distribuer, même s'il restait
     * du pain d'un autre jour (le pain ne se conserve pas : l'ancien stock
     * cumulé n'existe plus).
     */
    public function partsDisponibles(?int $exclureDistributionId = null, ?string $date = null, ?int $exclureConsommationId = null): int
    {
        $date ??= now()->toDateString();

        return $this->partsProduitesLe($date) - $this->partsSortiesLe($date, $exclureDistributionId, $exclureConsommationId);
    }

    // Nombre de pains (multiple exact de 1/6) encore disponibles, pour
    // l'affichage via Nombre::pains().
    public function painsDisponibles(?int $exclureDistributionId = null, ?string $date = null): float
    {
        return $this->partsDisponibles($exclureDistributionId, $date) / 6;
    }

    // Pains ENTIERS encore attribuables (on ne donne jamais une fraction de
    // pain à un livreur) : division entière exacte, sans passer par un float.
    public function painsEntiersDisponibles(?int $exclureDistributionId = null, ?string $date = null): int
    {
        return (int) floor($this->partsDisponibles($exclureDistributionId, $date) / 6);
    }

    /**
     * Message de refus quand on veut sortir plus de pains que le stock du jour.
     * Distingue « aucune production » de « stock du jour insuffisant ».
     */
    public function messageStockInsuffisant(string $date, ?int $exclureDistributionId = null, ?int $exclureConsommationId = null): string
    {
        $quand = $date === now()->toDateString()
            ? 'aujourd\'hui'
            : 'le ' . \Carbon\Carbon::parse($date)->format('d/m/Y');

        $produits = $this->partsProduitesLe($date);
        if ($produits === 0) {
            return 'Aucune production de « ' . $this->nom . ' » enregistrée ' . $quand
                . '. Enregistrez d\'abord la production avant de distribuer.';
        }

        $reste = max(0, intdiv($this->partsDisponibles($exclureDistributionId, $date, $exclureConsommationId), 6));

        return $reste === 0
            ? 'Tous les pains « ' . $this->nom . ' » produits ' . $quand . ' (' . intdiv($produits, 6) . ') sont déjà distribués.'
            : 'Il ne reste que ' . $reste . ' pain' . ($reste > 1 ? 's' : '') . ' « ' . $this->nom . ' » ' . $quand
                . ' (production du jour : ' . intdiv($produits, 6) . ').';
    }
}
