<?php

namespace App\Support;

class Nombre
{
    /**
     * Formate une quantité pouvant être décimale (ex: sacs, paquets) en
     * évitant les zéros inutiles : 5.00 -> "5", 5.50 -> "5,5", 4.25 -> "4,25".
     */
    public static function qte(mixed $value): string
    {
        $formatted = number_format((float) $value, 2, ',', ' ');

        if (str_contains($formatted, ',')) {
            $formatted = rtrim($formatted, '0');
            $formatted = rtrim($formatted, ',');
        }

        return $formatted;
    }

    /**
     * Découpe une quantité de pain (toujours un multiple exact de 1/6) en
     * [pains entiers, reste en toutes lettres, reste composé de deux morceaux].
     * Le reste s'exprime avec les morceaux réellement vendus : moitié, tiers.
     *
     * @return array{0: int, 1: string, 2: bool}
     */
    private static function decouper(mixed $value): array
    {
        $parts   = (int) round((float) $value * 6);
        $entiers = intdiv($parts, 6);

        // Un reste de 1 sixième (ex. 1 moitié + 2 tiers = 1 pain et 1/6) ne
        // correspond à aucun morceau vendu : on le réécrit en reprenant un pain
        // entier, soit 7 sixièmes = 1 moitié + 2 tiers.
        if ($parts % 6 === 1 && $entiers >= 1) {
            return [$entiers - 1, '1 moitié et 2 tiers', true];
        }

        $reste = [
            0 => ['', false],
            1 => ['1 sixième', false],
            2 => ['1 tiers', false],
            3 => ['1 moitié', false],
            4 => ['2 tiers', false],
            5 => ['1 moitié et 1 tiers', true],
        ][$parts % 6];

        return [$entiers, $reste[0], $reste[1]];
    }

    /**
     * Affiche une quantité de pain sans jamais de décimale, avec les mots du
     * comptoir : "3 pains", "3 pains et 1 tiers", "3 pains, 1 moitié et
     * 1 tiers", "1 moitié".
     */
    public static function pains(mixed $value): string
    {
        [$entiers, $reste, $double] = self::decouper($value);

        if ($entiers === 0) {
            return $reste !== '' ? $reste : '0 pain';
        }

        $base = $entiers . ' pain' . ($entiers > 1 ? 's' : '');

        return $reste === '' ? $base : $base . ($double ? ', ' : ' et ') . $reste;
    }

    // Pour les grosses tuiles : le nombre de pains entiers en gros chiffre…
    public static function painsEntiers(mixed $value): int
    {
        return self::decouper($value)[0];
    }

    // … et le reste en légende dessous : "pains", "pains et 1 tiers",
    // "pains, 1 moitié et 1 tiers".
    public static function painsLegende(mixed $value): string
    {
        [$entiers, $reste, $double] = self::decouper($value);

        $base = $entiers > 1 ? 'pains' : 'pain';

        return $reste === '' ? $base : $base . ($double ? ', ' : ' et ') . $reste;
    }
}
