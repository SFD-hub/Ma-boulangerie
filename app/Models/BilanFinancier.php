<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BilanFinancier extends Model
{
    protected $table = 'bilans_financiers';

    protected $fillable = [
        'boulangerie_id', 'mois', 'annee',
        'versements_livreurs', 'factures_abonnes', 'recettes_total',
        'achat_farine', 'achat_levure',
        'salaire_gerant', 'salaire_employe',
        'eau', 'electricite', 'carburant', 'transport', 'reparation', 'autres',
        'depenses_total', 'benefice',
    ];

    protected function casts(): array
    {
        return [
            'mois'                => 'integer',
            'annee'               => 'integer',
            'versements_livreurs' => 'decimal:2',
            'factures_abonnes'    => 'decimal:2',
            'recettes_total'      => 'decimal:2',
            'achat_farine'        => 'decimal:2',
            'achat_levure'        => 'decimal:2',
            'salaire_gerant'      => 'decimal:2',
            'salaire_employe'     => 'decimal:2',
            'eau'                 => 'decimal:2',
            'electricite'         => 'decimal:2',
            'carburant'           => 'decimal:2',
            'transport'           => 'decimal:2',
            'reparation'          => 'decimal:2',
            'autres'              => 'decimal:2',
            'depenses_total'      => 'decimal:2',
            'benefice'            => 'decimal:2',
        ];
    }

    public function nomMois(): string
    {
        return ucfirst(Carbon::create()->month($this->mois)->locale('fr')->monthName);
    }

    public function periodeLabel(): string
    {
        return $this->nomMois() . ' ' . $this->annee;
    }

    public function boulangerie(): BelongsTo
    {
        return $this->belongsTo(Boulangerie::class);
    }
}
