<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distributions', function (Blueprint $table) {
            $table->decimal('reliquat', 10, 2)->default(0)->after('montant_attendu');
            $table->string('statut', 20)->default('en_attente')->after('reliquat');
        });

        // ── Initialiser les données existantes ────────────────────────────────

        // Distributions avec versement → reliquat réel
        DB::statement("
            UPDATE distributions d
            JOIN versements v ON v.distribution_id = d.id
            SET d.reliquat = GREATEST(0, COALESCE(d.montant_attendu, 0) - v.montant_verse),
                d.statut   = CASE
                                 WHEN COALESCE(d.montant_attendu, 0) <= v.montant_verse
                                 THEN 'reglee'
                                 ELSE 'en_attente'
                             END
        ");

        // Distributions sans versement → reliquat = montant_attendu, statut = en_attente
        DB::statement("
            UPDATE distributions d
            LEFT JOIN versements v ON v.distribution_id = d.id
            SET d.reliquat = COALESCE(d.montant_attendu, 0),
                d.statut   = 'en_attente'
            WHERE v.id IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('distributions', function (Blueprint $table) {
            $table->dropColumn(['reliquat', 'statut']);
        });
    }
};
