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
        Schema::create('achats_matieres_premieres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matiere_premiere_id')->constrained('matieres_premieres')->cascadeOnDelete();
            $table->integer('quantite');
            $table->decimal('montant', 10, 2);
            $table->date('date_achat');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achats_matieres_premieres');
    }
};
