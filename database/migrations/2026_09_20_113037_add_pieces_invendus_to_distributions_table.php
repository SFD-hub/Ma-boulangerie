<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Additif uniquement : `nombre_invendus` reste tel quel pour les
        // distributions déjà réglées avant ce changement. `null` sur les
        // nouvelles colonnes = "pas encore réglé avec le système par pièce",
        // l'accessor Distribution::invendus() gère le repli.
        Schema::table('distributions', function (Blueprint $table) {
            $table->unsignedInteger('entiers_invendus')->nullable()->after('nombre_invendus');
            $table->unsignedInteger('moities_invendus')->nullable()->after('entiers_invendus');
            $table->unsignedInteger('tiers_invendus')->nullable()->after('moities_invendus');
            $table->decimal('invendus_equivalent', 8, 2)->nullable()->after('tiers_invendus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distributions', function (Blueprint $table) {
            $table->dropColumn(['entiers_invendus', 'moities_invendus', 'tiers_invendus', 'invendus_equivalent']);
        });
    }
};
