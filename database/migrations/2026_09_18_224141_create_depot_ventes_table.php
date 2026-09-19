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
        Schema::create('depot_ventes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boulangerie_id')->constrained()->cascadeOnDelete();
            $table->foreignId('produit_id')->nullable()->constrained('produits')->nullOnDelete();
            $table->date('date_vente');
            $table->integer('quantite');
            $table->decimal('montant', 10, 2);
            $table->timestamps();

            $table->index(['boulangerie_id', 'date_vente']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depot_ventes');
    }
};
