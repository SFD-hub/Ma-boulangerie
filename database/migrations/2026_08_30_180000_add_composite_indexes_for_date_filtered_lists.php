<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            $table->index(['boulangerie_id', 'date_production']);
        });

        Schema::table('ventes', function (Blueprint $table) {
            $table->index(['boulangerie_id', 'date_vente']);
        });

        Schema::table('depenses', function (Blueprint $table) {
            $table->index(['boulangerie_id', 'date_depense']);
        });

        Schema::table('distributions', function (Blueprint $table) {
            $table->index(['livreur_id', 'date_distribution']);
        });

        Schema::table('versements', function (Blueprint $table) {
            $table->index(['livreur_id', 'date_versement']);
        });

        Schema::table('consommations_abonnes', function (Blueprint $table) {
            $table->index(['client_abonne_id', 'date_consommation']);
        });

        Schema::table('achats_matieres_premieres', function (Blueprint $table) {
            $table->index(['matiere_premiere_id', 'date_achat']);
        });
    }

    public function down(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            $table->dropIndex(['boulangerie_id', 'date_production']);
        });

        Schema::table('ventes', function (Blueprint $table) {
            $table->dropIndex(['boulangerie_id', 'date_vente']);
        });

        Schema::table('depenses', function (Blueprint $table) {
            $table->dropIndex(['boulangerie_id', 'date_depense']);
        });

        Schema::table('distributions', function (Blueprint $table) {
            $table->dropIndex(['livreur_id', 'date_distribution']);
        });

        Schema::table('versements', function (Blueprint $table) {
            $table->dropIndex(['livreur_id', 'date_versement']);
        });

        Schema::table('consommations_abonnes', function (Blueprint $table) {
            $table->dropIndex(['client_abonne_id', 'date_consommation']);
        });

        Schema::table('achats_matieres_premieres', function (Blueprint $table) {
            $table->dropIndex(['matiere_premiere_id', 'date_achat']);
        });
    }
};
