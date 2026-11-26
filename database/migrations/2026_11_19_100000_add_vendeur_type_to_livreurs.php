<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Vendeur de la Boutique : même mécanique que Livreur/Client (attribution du
// matin, invendus, versement, reliquat). Seul le type s'ajoute ; toutes les
// lignes existantes gardent leur type actuel.
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE livreurs MODIFY type ENUM('livreur','client','vendeur') NOT NULL DEFAULT 'livreur'");
    }

    public function down(): void
    {
        // Les vendeurs redeviennent des clients avant de retirer la valeur.
        DB::table('livreurs')->where('type', 'vendeur')->update(['type' => 'client']);
        DB::statement("ALTER TABLE livreurs MODIFY type ENUM('livreur','client') NOT NULL DEFAULT 'livreur'");
    }
};
