<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Règle finale : statut dépend du reliquat, pas de l'existence d'un versement.
        DB::statement("
            UPDATE distributions
            SET statut = CASE
                WHEN reliquat > 0 THEN 'en_attente'
                ELSE 'reglee'
            END
        ");
    }

    public function down(): void
    {
        // Retour à la règle versement-based (tâche précédente)
        DB::statement("
            UPDATE distributions
            SET statut = 'reglee'
            WHERE EXISTS (SELECT 1 FROM versements v WHERE v.distribution_id = distributions.id)
        ");
        DB::statement("
            UPDATE distributions
            SET statut = 'en_attente'
            WHERE NOT EXISTS (SELECT 1 FROM versements v WHERE v.distribution_id = distributions.id)
        ");
    }
};
