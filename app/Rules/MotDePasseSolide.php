<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Mot de passe d'au moins 8 caractères, avec une lettre et un chiffre, et qui n'est pas l'un des mots
 * de passe les plus courants (ceux que les attaquants essaient en premier).
 */
class MotDePasseSolide implements ValidationRule
{
    // Les plus fréquents en français et en Afrique de l'Ouest, comparés sans majuscules ni accents.
    private const COURANTS = [
        'password', 'password1', 'password12', 'password123', 'passw0rd', 'motdepasse', 'motdepasse1', 'motdepasse12',
        'motdepasse123', 'azertyui', 'azerty123', 'azertyuiop', 'qwertyui', 'qwerty123', 'qwertyuiop', '12345678',
        '123456789', '1234567890', '123456789a', '12345678a', 'abcd1234', 'abc12345', 'abcdefg1', 'a1234567', 'aaaa1111',
        '11111111', '00000000', '87654321', '1q2w3e4r', 'iloveyou', 'bonjour1', 'bonjour123', 'senegal1', 'senegal123',
        'dakar123', 'dakar2024', 'dakar2025', 'dakar2026', 'boulangerie', 'boulangerie1', 'boulangerie123', 'pain1234',
        'admin123', 'admin1234', 'administrateur', 'bienvenue1', 'azerty12', 'passer123', 'passer1234', 'diop1234',
        'fall12345', 'ndiaye123', 'moussa123', 'fallou123', 'proprietaire1', 'gerant123', 'test1234', 'changeme1',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $mdp = (string) $value;

        if (mb_strlen($mdp) < 8) {
            $fail('Le mot de passe doit contenir au moins 8 caractères.');
            return;
        }

        if (! preg_match('/\p{L}/u', $mdp) || ! preg_match('/\d/', $mdp)) {
            $fail('Le mot de passe doit contenir au moins une lettre et un chiffre.');
            return;
        }

        $simple = preg_replace('/[^a-z0-9]/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $mdp) ?: $mdp));

        if (in_array($simple, self::COURANTS, true) || preg_match('/^(.)\1+$/', $simple) || count(array_unique(str_split($simple))) <= 2) {
            $fail('Ce mot de passe est trop courant ou trop simple. Choisissez-en un moins facile à deviner.');
        }
    }
}
