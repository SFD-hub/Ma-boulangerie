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
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_abonne_id')->constrained('clients_abonnes')->cascadeOnDelete();
            $table->integer('mois');
            $table->integer('annee');
            $table->integer('quantite_totale');
            $table->decimal('montant_total', 10, 2);
            $table->date('date_facture');
            $table->string('statut')->default('impayee');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};
