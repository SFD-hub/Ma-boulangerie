<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facture extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_abonne_id',
        'mois',
        'annee',
        'quantite_totale',
        'prix_unitaire',
        'montant_total',
        'date_facture',
        'statut',
        'date_paiement',
    ];

    protected $casts = [
        'date_facture'    => 'date',
        'date_paiement'   => 'date',
        'montant_total'   => 'decimal:2',
        'mois'            => 'integer',
        'annee'           => 'integer',
        'quantite_totale' => 'integer',
        'prix_unitaire'   => 'integer',
    ];

    public function clientAbonne(): BelongsTo
    {
        return $this->belongsTo(ClientAbonne::class);
    }

    public function paiementsFactures(): HasMany
    {
        return $this->hasMany(PaiementFacture::class);
    }

    public function nomMois(): string
    {
        return ucfirst(\Carbon\Carbon::create()->month($this->mois)->locale('fr')->monthName);
    }

    public function periodeLabel(): string
    {
        return $this->nomMois() . ' ' . $this->annee;
    }

    public function montantPaye(): float
    {
        // Utilise la relation chargée si disponible (evite le N+1 dans les listes)
        $paiements = $this->relationLoaded('paiementsFactures')
            ? $this->paiementsFactures
            : $this->paiementsFactures()->get();

        return (float) $paiements->sum('montant');
    }

    public function solde(): float
    {
        return max(0, round((float) $this->montant_total - $this->montantPaye(), 2));
    }

    // Recalcule et enregistre le statut (impayee/partielle/payee) à partir des
    // paiements réellement enregistrés — le statut n'est jamais modifié à la main.
    public function syncStatut(): void
    {
        $paye   = $this->montantPaye();
        $total  = (float) $this->montant_total;
        $statut = $paye <= 0 ? 'impayee' : ($paye >= $total ? 'payee' : 'partielle');

        $dernierPaiement = $this->paiementsFactures()->orderByDesc('date_paiement')->first();

        $this->update([
            'statut'        => $statut,
            'date_paiement' => $statut === 'payee' ? $dernierPaiement?->date_paiement : null,
        ]);
    }
}
