<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('distributions', 'reliquat')) {
            Schema::table('distributions', function (Blueprint $table) {
                $table->decimal('reliquat', 10, 2)->default(0);
            });
        }

        if (!Schema::hasColumn('distributions', 'statut')) {
            Schema::table('distributions', function (Blueprint $table) {
                $table->string('statut', 20)->default('en_attente');
            });
        }

        // Distributions avec versement : reliquat = montant_attendu - total versé (min 0)
        DB::statement("
            UPDATE distributions
            SET reliquat = CASE
                    WHEN (COALESCE(montant_attendu, 0) - (SELECT COALESCE(SUM(v.montant_verse), 0) FROM versements v WHERE v.distribution_id = distributions.id)) < 0
                    THEN 0
                    ELSE (COALESCE(montant_attendu, 0) - (SELECT COALESCE(SUM(v.montant_verse), 0) FROM versements v WHERE v.distribution_id = distributions.id))
                END,
                statut = CASE
                    WHEN COALESCE(montant_attendu, 0) <= (SELECT COALESCE(SUM(v.montant_verse), 0) FROM versements v WHERE v.distribution_id = distributions.id)
                    THEN 'reglee'
                    ELSE 'en_attente'
                END
            WHERE EXISTS (SELECT 1 FROM versements v WHERE v.distribution_id = distributions.id)
        ");

        // Distributions sans versement : reliquat = montant_attendu, statut = en_attente
        DB::statement("
            UPDATE distributions
            SET reliquat = COALESCE(montant_attendu, 0),
                statut   = 'en_attente'
            WHERE NOT EXISTS (SELECT 1 FROM versements v WHERE v.distribution_id = distributions.id)
        ");
    }

    public function down(): void
    {
        Schema::table('distributions', function (Blueprint $table) {
            $table->dropColumn(['reliquat', 'statut']);
        });
    }
};
