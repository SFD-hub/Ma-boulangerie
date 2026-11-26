<?php

namespace App\Models;

use App\Models\Boulangerie;
use App\Models\Distribution;
use App\Models\Versement;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Livreur extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'nom',
        'prenom',
        'telephone',
        'adresse',
        'actif',
        'type',
        'boulangerie_id',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class);
    }

    public function versements(): HasMany
    {
        return $this->hasMany(Versement::class);
    }

    // "Livreur" et "Client" partagent la même mécanique — ce libellé ne sert
    // qu'à l'affichage (badges, écrans d'attribution/règlement).
    // Étiquette des listes et badges : un vendeur de la Boutique s'affiche « Boutique ».
    public function typeLabel(): string
    {
        return match ($this->type) {
            'client'  => 'Client',
            'vendeur' => 'Boutique',
            default   => 'Livreur',
        };
    }

    // Nom de la personne dans les écrans et messages : « Vendeur » pour la Boutique.
    public function nomRole(): string
    {
        return match ($this->type) {
            'client'  => 'Client',
            'vendeur' => 'Vendeur',
            default   => 'Livreur',
        };
    }

    // Distribution « en cours » d'un vendeur : la plus récente qui n'a pas
    // encore reçu de versement. C'est sur elle que se comptent ses ventes.
    public function distributionEnCours(): ?Distribution
    {
        return $this->distributions()
            ->doesntHave('versement')
            ->orderByDesc('date_distribution')
            ->orderByDesc('id')
            ->first();
    }

    // Distribution d'AUJOURD'HUI pas encore réglée : c'est la seule sur laquelle on
    // peut saisir un retour (le pain rapporté le jour même retourne au stock du jour).
    public function distributionDuJour(): ?Distribution
    {
        return $this->distributions()
            ->whereDate('date_distribution', now()->toDateString())
            ->doesntHave('versement')
            ->orderByDesc('id')
            ->first();
    }

    // Vendeur de la Boutique (même mécanique, affiché dans la page Boutique).
    public function estVendeur(): bool
    {
        return $this->type === 'vendeur';
    }

    // Où revenir depuis la fiche : la Boutique pour un vendeur, sinon la liste Clients.
    public function urlRetour(): string
    {
        return $this->estVendeur()
            ? route('depot.index')
            : route('clients.index', ['type' => $this->type]);
    }

    /**
     * Recalcule les reliquats de toutes les distributions à partir des montants attendus et des
     * versements, dans l'ordre où ils ont été saisis (comme à l'enregistrement : un versement règle
     * sa distribution, puis son surplus règle les plus anciens reliquats). À utiliser après la
     * suppression d'un versement ou d'une distribution.
     *
     * @param  bool  $simulation  true = ne rien enregistrer, retourner seulement [id => reliquat].
     */
    public function recalculerReliquats(bool $simulation = false): array
    {
        $distributions = $this->distributions()->orderBy('date_distribution')->orderBy('id')->get();
        $reliquats = [];
        foreach ($distributions as $d) {
            $reliquats[$d->id] = (float) $d->montant_attendu;
        }

        $parDistribution = $this->versements()->orderBy('id')->get();
        $aVersement = [];
        foreach ($parDistribution as $v) {
            $montant = (float) $v->montant_verse;
            $cible = $v->distribution_id && isset($reliquats[$v->distribution_id]) ? $v->distribution_id : null;
            $surplus = $montant;

            if ($cible) {
                $aVersement[$cible] = true;
                $avant = $reliquats[$cible];
                $reliquats[$cible] = max(0.0, $avant - $montant);
                $surplus = max(0.0, $montant - $avant);
            }

            foreach ($distributions as $autre) {
                if ($surplus <= 0) {
                    break;
                }
                if ($autre->id === $cible || $reliquats[$autre->id] <= 0 || $autre->created_at > $v->created_at) {
                    continue;
                }
                $applique = min($surplus, $reliquats[$autre->id]);
                $reliquats[$autre->id] -= $applique;
                $surplus -= $applique;
            }
        }

        if (! $simulation) {
            foreach ($distributions as $d) {
                $nouveau = round($reliquats[$d->id], 2);
                $donnees = ['reliquat' => $nouveau];
                if (isset($aVersement[$d->id])) {
                    $donnees['statut'] = $nouveau <= 0 ? 'reglee' : 'en_attente';
                }
                if ((float) $d->reliquat !== $nouveau || isset($donnees['statut']) && $d->statut !== $donnees['statut']) {
                    $d->update($donnees);
                }
            }
        }

        return $reliquats;
    }
}
