<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Ventes « au fil de la journée » d'un vendeur de la Boutique : suivi des
// pièces vendues sur ce qu'on lui a donné (distribution). Aucun argent ici :
// l'encaissement se fait au versement, comme pour un livreur.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventes_vendeur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boulangerie_id')->constrained()->cascadeOnDelete();
            $table->foreignId('livreur_id')->constrained('livreurs')->cascadeOnDelete();
            $table->foreignId('distribution_id')->constrained('distributions')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('nombre_entiers')->default(0);
            $table->unsignedInteger('nombre_moities')->default(0);
            $table->unsignedInteger('nombre_tiers')->default(0);
            $table->unsignedInteger('parts');
            $table->timestamps();

            $table->index('distribution_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventes_vendeur');
    }
};
