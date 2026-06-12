<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bilans_financiers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('boulangerie_id');
            $table->integer('mois');
            $table->integer('annee');
            $table->decimal('versements_livreurs', 12, 2)->default(0);
            $table->decimal('factures_abonnes',    12, 2)->default(0);
            $table->decimal('recettes_total',      12, 2)->default(0);
            $table->decimal('achat_farine',        12, 2)->default(0);
            $table->decimal('achat_levure',        12, 2)->default(0);
            $table->decimal('salaire_gerant',      12, 2)->default(0);
            $table->decimal('salaire_employe',     12, 2)->default(0);
            $table->decimal('eau',                 12, 2)->default(0);
            $table->decimal('electricite',         12, 2)->default(0);
            $table->decimal('carburant',           12, 2)->default(0);
            $table->decimal('transport',           12, 2)->default(0);
            $table->decimal('reparation',          12, 2)->default(0);
            $table->decimal('autres',              12, 2)->default(0);
            $table->decimal('depenses_total',      12, 2)->default(0);
            $table->decimal('benefice',            12, 2)->default(0);
            $table->timestamps();

            $table->foreign('boulangerie_id')->references('id')->on('boulangeries')->onDelete('cascade');
            $table->unique(['boulangerie_id', 'mois', 'annee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bilans_financiers');
    }
};
