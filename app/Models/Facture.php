<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function nomMois(): string
    {
        return ucfirst(\Carbon\Carbon::create()->month($this->mois)->locale('fr')->monthName);
    }

    public function periodeLabel(): string
    {
        return $this->nomMois() . ' ' . $this->annee;
    }
}
