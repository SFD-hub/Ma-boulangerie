<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Nouvelle règle : statut = 'reglee' dès qu'un versement existe, peu importe le reliquat.

        // 1. Toute distribution liée à un versement → 'reglee'
        DB::statement("
            UPDATE distributions d
            JOIN versements v ON v.distribution_id = d.id
            SET d.statut = 'reglee'
        ");

        // 2. Toute distribution sans versement → 'en_attente'
        DB::statement("
            UPDATE distributions d
            LEFT JOIN versements v ON v.distribution_id = d.id
            SET d.statut = 'en_attente'
            WHERE v.id IS NULL
        ");
    }

    public function down(): void
    {
        // Retour à l'ancienne règle : 'reglee' si reliquat = 0, sinon 'en_attente'
        DB::statement("
            UPDATE distributions
            SET statut = CASE
                WHEN reliquat <= 0 THEN 'reglee'
                ELSE 'en_attente'
            END
        ");
    }
};
