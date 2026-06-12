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
            UPDATE distributions d
            JOIN versements v ON v.distribution_id = d.id
            SET d.statut = 'reglee'
        ");
        DB::statement("
            UPDATE distributions d
            LEFT JOIN versements v ON v.distribution_id = d.id
            SET d.statut = 'en_attente'
            WHERE v.id IS NULL
        ");
    }
};
