<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Retire l'ancien catalogue produit générique (catégories, ventes, lignes de
// distribution multi-produits, stock par produit) : jamais utilisé (0 ligne
// dans toutes ces tables), non lié dans la navigation, et incompatible avec
// le nouveau modèle "un type de pain par distribution" décidé avec l'utilisateur.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->dropForeign(['categorie_produit_id']);
            $table->dropColumn(['description', 'prix', 'categorie_produit_id']);
        });

        Schema::dropIfExists('stocks');
        Schema::dropIfExists('detail_ventes');
        Schema::dropIfExists('ventes');
        Schema::dropIfExists('detail_distributions');
        Schema::dropIfExists('categories_produits');

        Schema::table('produits', function (Blueprint $table) {
            $table->boolean('actif')->default(true)->after('boulangerie_id');
            $table->unique(['boulangerie_id', 'nom']);
        });
    }

    public function down(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->dropUnique(['boulangerie_id', 'nom']);
            $table->dropColumn('actif');
        });

        Schema::create('categories_produits', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->foreignId('boulangerie_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::table('produits', function (Blueprint $table) {
            $table->text('description')->nullable();
            $table->decimal('prix', 10, 2)->default(0);
            $table->foreignId('categorie_produit_id')->nullable()->constrained('categories_produits')->nullOnDelete();
        });

        Schema::create('ventes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boulangerie_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date_vente');
            $table->decimal('montant_total', 10, 2);
            $table->timestamps();
        });

        Schema::create('detail_ventes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vente_id')->constrained()->cascadeOnDelete();
            $table->foreignId('produit_id')->constrained()->cascadeOnDelete();
            $table->integer('quantite');
            $table->decimal('prix_unitaire', 10, 2);
            $table->timestamps();
        });

        Schema::create('detail_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('produit_id')->constrained()->cascadeOnDelete();
            $table->integer('quantite_attribuee');
            $table->integer('quantite_retournee')->default(0);
            $table->timestamps();
        });

        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produit_id')->constrained()->cascadeOnDelete();
            $table->integer('quantite_disponible');
            $table->integer('seuil_alerte');
            $table->timestamps();
        });
    }
};
