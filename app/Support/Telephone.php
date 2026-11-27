<?php

namespace App\Support;

/**
 * Numéros de téléphone : une seule écriture pour un même numéro, quelle que soit la façon de le saisir
 * (« 77 123 45 67 », « 77-123-45-67 », « +221 77 123 45 67 »…). Sans cela, le même numéro pourrait ouvrir
 * plusieurs comptes et il faudrait retaper exactement l'écriture de l'inscription pour se connecter.
 *
 * Forme enregistrée : uniquement des chiffres, sans l'indicatif du Sénégal (221) ; les numéros d'un autre
 * pays gardent leur indicatif précédé de « + ».
 */
class Telephone
{
    // Règle de validation d'un numéro déjà normalisé : 8 à 15 chiffres, « + » facultatif au début.
    public const REGLE = '/^\+?[0-9]{8,15}$/';

    public const MESSAGE = 'Le numéro de téléphone n\'est pas valide (chiffres uniquement, par exemple 77 123 45 67).';

    public static function normaliser(?string $valeur): ?string
    {
        if ($valeur === null) {
            return null;
        }

        $v = trim($valeur);
        if ($v === '') {
            return '';
        }

        $plus = str_starts_with($v, '+');
        // Espaces (y compris insécables), points, tirets, parenthèses et barres : simples séparateurs.
        $v = preg_replace('/[\s\x{00A0}\x{202F}.\-()\/]/u', '', $v);
        // Chiffres arabes ou indiens saisis par erreur → chiffres usuels.
        $v = strtr($v, ['٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9']);
        $v = ltrim($v, '+');

        // Rien d'autre que des chiffres : on laisse tel quel pour que la validation le refuse.
        if (! preg_match('/^[0-9]+$/', $v)) {
            return ($plus ? '+' : '') . $v;
        }

        // Indicatif du Sénégal : « 00221 », « +221 » ou « 221 » devant un numéro à 9 chiffres.
        foreach (['00221', '221'] as $prefixe) {
            if (str_starts_with($v, $prefixe) && strlen($v) - strlen($prefixe) === 9) {
                return substr($v, strlen($prefixe));
            }
        }

        if ($plus || str_starts_with($v, '00')) {
            return '+' . ltrim($v, '0');
        }

        return $v;
    }
}
