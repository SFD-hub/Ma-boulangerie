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
}
