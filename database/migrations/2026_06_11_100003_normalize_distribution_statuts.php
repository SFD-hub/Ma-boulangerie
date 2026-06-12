<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Toute distribution liée à un versement → 'reglee'
        DB::statement("
            UPDATE distributions
            SET statut = 'reglee'
            WHERE EXISTS (SELECT 1 FROM versements v WHERE v.distribution_id = distributions.id)
        ");

        // Toute distribution sans versement → 'en_attente'
        DB::statement("
            UPDATE distributions
            SET statut = 'en_attente'
            WHERE NOT EXISTS (SELECT 1 FROM versements v WHERE v.distribution_id = distributions.id)
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
