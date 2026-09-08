<?php

namespace App\Traits;

/**
 * Distingue automatiquement si un enregistrement a été fait le matin ou le
 * soir, à partir de son heure d'enregistrement (created_at) — jamais saisie
 * manuellement par l'utilisateur. Avant midi = Matin, à partir de midi = Soir.
 */
trait HasMoment
{
    public function getMomentAttribute(): ?string
    {
        return $this->created_at ? ($this->created_at->hour < 12 ? 'Matin' : 'Soir') : null;
    }

    public function getMomentIconAttribute(): string
    {
        return $this->created_at && $this->created_at->hour < 12 ? '🌅' : '🌙';
    }

    public function getHeureEnregistrementAttribute(): ?string
    {
        return $this->created_at?->format('H:i');
    }
}
