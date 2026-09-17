<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// "Livreur" et "Client" (revendeur direct : boutiquier, vendeur de petit-
// déjeuner) suivent exactement la même mécanique (attribution, retour,
// règlement) — ce champ ne sert qu'à les distinguer à l'affichage dans le
// nouveau module "Clients" unifié. Toutes les lignes existantes restent
// 'livreur' par défaut : aucun changement de comportement pour les données
// actuelles.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('livreurs', function (Blueprint $table) {
            $table->enum('type', ['livreur', 'client'])->default('livreur')->after('boulangerie_id');
        });
    }

    public function down(): void
    {
        Schema::table('livreurs', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
