<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Permet les quantités décimales (ex: 5.5 sacs de farine) là où seuls les
 * entiers étaient acceptés. Utilise du SQL brut (MODIFY COLUMN) plutôt que
 * Schema::table(...)->change() car doctrine/dbal n'est pas installé.
 *
 * nombre_pains_produits reste un entier : on ne produit jamais un demi-pain.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE matieres_premieres MODIFY stock_actuel DECIMAL(8,2) NOT NULL');
        DB::statement('ALTER TABLE matieres_premieres MODIFY seuil_alerte DECIMAL(8,2) NOT NULL');

        DB::statement('ALTER TABLE achats_matieres_premieres MODIFY quantite DECIMAL(8,2) NOT NULL');

        DB::statement('ALTER TABLE productions MODIFY nombre_sacs DECIMAL(8,2) NOT NULL');
        DB::statement('ALTER TABLE productions MODIFY quantite_farine DECIMAL(8,2) NOT NULL');
        DB::statement('ALTER TABLE productions MODIFY quantite_levure DECIMAL(8,2) NOT NULL DEFAULT 0');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE matieres_premieres MODIFY stock_actuel INT NOT NULL');
        DB::statement('ALTER TABLE matieres_premieres MODIFY seuil_alerte INT NOT NULL');

        DB::statement('ALTER TABLE achats_matieres_premieres MODIFY quantite INT NOT NULL');

        DB::statement('ALTER TABLE productions MODIFY nombre_sacs INT NOT NULL');
        DB::statement('ALTER TABLE productions MODIFY quantite_farine INT NOT NULL');
        DB::statement('ALTER TABLE productions MODIFY quantite_levure INT NOT NULL DEFAULT 0');
    }
};
