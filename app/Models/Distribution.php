<?php

namespace App\Models;

use App\Traits\HasMoment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Distribution extends Model
{
    use HasFactory, HasMoment;

    protected $fillable = [
        'livreur_id',
        'produit_id',
        'date_distribution',
        'montant_attendu',
        'nombre_pains',
        'retours_parts',
        'prix_pain',
        'nombre_invendus',
        'entiers_invendus',
        'moities_invendus',
        'tiers_invendus',
        'invendus_equivalent',
        'invendus_parts',
        'reliquat',
        'statut',
    ];

    protected $casts = [
        'date_distribution'    => 'date',
        'montant_attendu'      => 'decimal:2',
        'reliquat'             => 'decimal:2',
        'nombre_pains'         => 'integer',
        'retours_parts'        => 'integer',
        'prix_pain'            => 'integer',
        'nombre_invendus'      => 'integer',
        'entiers_invendus'     => 'integer',
        'moities_invendus'     => 'integer',
        'tiers_invendus'       => 'integer',
        'invendus_equivalent'  => 'decimal:2',
        'invendus_parts'       => 'integer',
    ];

    public function livreur(): BelongsTo
    {
        return $this->belongsTo(Livreur::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    public function versement(): HasOne
    {
        return $this->hasOne(Versement::class);
    }

    public function ventesVendeur(): HasMany
    {
        return $this->hasMany(VenteVendeur::class);
    }

    // Sixièmes de pain encore « en main » du vendeur : donnés − déjà vendus
    // (ventes saisies au fil de la journée sur la fiche du vendeur).
    public function partsEnMain(): int
    {
        return max(0, $this->parts_confiees - (int) $this->ventesVendeur()->sum('parts'));
    }

    /**
     * Propose les invendus [entiers, moitiés, tiers] à partir de ce qu'il reste
     * en main, quand des ventes ont été saisies ; null sinon (rien à pré-remplir).
     * Un reste de 1 sixième s'écrit en reprenant un entier (1 moitié + 2 tiers).
     *
     * @return array{0:int,1:int,2:int}|null
     */
    public function invendusSuggeres(): ?array
    {
        if (! $this->ventesVendeur()->exists()) {
            return null;
        }

        $parts   = $this->partsEnMain();
        $entiers = intdiv($parts, 6);
        $reste   = $parts % 6;

        if ($reste === 1 && $entiers >= 1) {
            return [$entiers - 1, 1, 2];
        }

        [$moities, $tiers] = [
            0 => [0, 0],
            1 => [0, 0],   // 1/6 seul : aucun morceau vendable, rien à proposer
            2 => [0, 1],
            3 => [1, 0],
            4 => [0, 2],
            5 => [1, 1],
        ][$reste];

        return [$entiers, $moities, $tiers];
    }

    public function estRegle(): bool
    {
        // Réglée uniquement quand il ne reste plus rien à payer -- un
        // versement partiel (reliquat > 0) ne suffit pas, sinon un
        // versement supplémentaire sur cette même distribution est
        // faussement refusé comme "déjà réglée" (LivreurController::verser()),
        // et elle disparaît à tort de la liste des distributions à régler.
        return (float) $this->reliquat <= 0;
    }

    // Sixièmes de pain réellement confiés : donnés − retours (pains rapportés
    // intacts le jour même). C'est la base du montant attendu, des ventes et des restants.
    public function getPartsConfieesAttribute(): int
    {
        return max(0, (int) $this->nombre_pains * 6 - (int) $this->retours_parts);
    }

    // Pains confiés en nombre de pains (parts ÷ 6) : entier sauf pour un vendeur
    // qui a rapporté une moitié ou un tiers — à afficher avec Nombre::pains().
    public function getPainsAttribuesAttribute(): float
    {
        return $this->parts_confiees / 6;
    }

    // Textes d'affichage : « 187 pains », « 186 pains et 1 moitié »…
    public function getPainsConfiesTexteAttribute(): string
    {
        return \App\Support\Nombre::pains($this->parts_confiees / 6);
    }

    public function getRetoursTexteAttribute(): string
    {
        return \App\Support\Nombre::pains((int) $this->retours_parts / 6);
    }

    // Pains donnés le matin, avant tout retour (affichage de l'historique).
    public function getPainsDonnesAttribute(): int
    {
        return (int) $this->nombre_pains;
    }

    public function retours(): HasMany
    {
        return $this->hasMany(RetourDistribution::class);
    }

    // Sixièmes qu'on peut encore rapporter (retour) : un livreur/client, en pains
    // entiers sur tout ce qui est confié ; un vendeur de la Boutique, en pièces
    // (entiers, moitiés, tiers) sur ce qu'il n'a pas déjà vendu.
    public function retourMaxParts(bool $vendeur): int
    {
        return $vendeur ? $this->partsEnMain() : intdiv($this->parts_confiees, 6) * 6;
    }

    /**
     * Enregistre un retour (pains rapportés intacts) : historique + total sur la
     * distribution + montant attendu recalculé. L'appelant a déjà vérifié le
     * maximum et verrouillé la distribution.
     */
    public function ajouterRetour(Livreur $livreur, int $entiers, int $moities, int $tiers): RetourDistribution
    {
        $parts = $entiers * 6 + $moities * 3 + $tiers * 2;

        $retour = RetourDistribution::create([
            'boulangerie_id'  => $livreur->boulangerie_id,
            'livreur_id'      => $livreur->id,
            'distribution_id' => $this->id,
            'user_id'         => auth()->id(),
            'nombre_entiers'  => $entiers,
            'nombre_moities'  => $moities,
            'nombre_tiers'    => $tiers,
            'parts'           => $parts,
        ]);

        $this->increment('retours_parts', $parts);
        $this->refresh()->recalculerMontantAvantVersement();

        return $retour;
    }

    // Avant tout versement, le montant attendu et le reliquat suivent les pains
    // confiés (donnés − retours) : à rappeler après chaque retour saisi/annulé.
    public function recalculerMontantAvantVersement(): void
    {
        $montant = (int) round($this->parts_confiees * (int) $this->prix_pain / 6);

        $this->update([
            'montant_attendu' => $montant,
            'reliquat'        => $montant,
        ]);
    }

    // Invendus en sixièmes de pain (entier = 6, moitié = 3, tiers = 2) : des
    // nombres entiers, donc aucun arrondi. Repli sur l'ancien compteur entier
    // pour les distributions réglées avant l'introduction de cette précision.
    public function getPartsInvenduesAttribute(): int
    {
        return $this->invendus_parts !== null
            ? (int) $this->invendus_parts
            : (int) $this->nombre_invendus * 6;
    }

    public function getPartsVenduesAttribute(): int
    {
        return max(0, $this->parts_confiees - $this->parts_invendues);
    }

    // Versions en nombre de pains (parts ÷ 6) pour l'affichage : toujours un
    // multiple exact de 1/6, à passer à Nombre::pains().
    public function getInvendusAttribute(): float
    {
        return $this->parts_invendues / 6;
    }

    public function getPainsVendusAttribute(): float
    {
        return $this->parts_vendues / 6;
    }
}
