<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// 1. Retours en sixièmes de pain : un vendeur de la Boutique peut rapporter des
//    moitiés et des tiers intacts (un livreur/client reste en pains entiers).
//    distributions.nombre_retours (pains) → retours_parts (sixièmes) ;
//    retours_distribution.nombre_pains → entiers / moitiés / tiers / parts.
// 2. Module « Pain restant » : ventes de sacs + ajustements de stock.
// 3. Recettes du bilan : ligne « Ventes de pain restant ».
return new class extends Migration
{
    public function up(): void
    {
        // ── Retours en sixièmes ──────────────────────────────────────────────
        Schema::table('distributions', function (Blueprint $table) {
            $table->unsignedInteger('retours_parts')->default(0)->after('nombre_pains');
        });
        DB::statement('UPDATE distributions SET retours_parts = nombre_retours * 6');
        Schema::table('distributions', function (Blueprint $table) {
            $table->dropColumn('nombre_retours');
        });

        Schema::table('retours_distribution', function (Blueprint $table) {
            $table->unsignedInteger('nombre_entiers')->default(0)->after('user_id');
            $table->unsignedInteger('nombre_moities')->default(0)->after('nombre_entiers');
            $table->unsignedInteger('nombre_tiers')->default(0)->after('nombre_moities');
            $table->unsignedInteger('parts')->default(0)->after('nombre_tiers');
        });
        DB::statement('UPDATE retours_distribution SET nombre_entiers = nombre_pains, parts = nombre_pains * 6');
        Schema::table('retours_distribution', function (Blueprint $table) {
            $table->dropColumn('nombre_pains');
        });

        // ── Pain restant ─────────────────────────────────────────────────────
        Schema::create('ventes_restant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boulangerie_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date_vente');
            $table->unsignedInteger('nombre_sacs');
            // Estimation du pain sorti (un sac ≈ 30 à 35 pains, jamais compté exactement).
            $table->unsignedInteger('pains_estimes');
            $table->decimal('montant', 10, 2);
            $table->timestamps();

            $table->index(['boulangerie_id', 'date_vente']);
        });

        Schema::create('ajustements_restant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boulangerie_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date_ajustement');
            // Sixièmes de pain : stock estimé avant, stock compté, écart (peut être négatif).
            $table->integer('parts_avant');
            $table->integer('parts_apres');
            $table->integer('ecart_parts');
            $table->timestamps();

            $table->index(['boulangerie_id', 'date_ajustement']);
        });

        // ── Bilan ────────────────────────────────────────────────────────────
        Schema::table('bilans_financiers', function (Blueprint $table) {
            $table->decimal('ventes_restant', 10, 2)->default(0)->after('ventes_depot');
        });
    }

    public function down(): void
    {
        Schema::table('bilans_financiers', function (Blueprint $table) {
            $table->dropColumn('ventes_restant');
        });

        Schema::dropIfExists('ajustements_restant');
        Schema::dropIfExists('ventes_restant');

        Schema::table('retours_distribution', function (Blueprint $table) {
            $table->unsignedInteger('nombre_pains')->default(0)->after('user_id');
        });
        DB::statement('UPDATE retours_distribution SET nombre_pains = FLOOR(parts / 6)');
        Schema::table('retours_distribution', function (Blueprint $table) {
            $table->dropColumn(['nombre_entiers', 'nombre_moities', 'nombre_tiers', 'parts']);
        });

        Schema::table('distributions', function (Blueprint $table) {
            $table->unsignedInteger('nombre_retours')->default(0)->after('nombre_pains');
        });
        DB::statement('UPDATE distributions SET nombre_retours = FLOOR(retours_parts / 6)');
        Schema::table('distributions', function (Blueprint $table) {
            $table->dropColumn('retours_parts');
        });
    }
};
