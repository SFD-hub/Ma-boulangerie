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
        Schema::create('matieres_premieres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boulangerie_id')->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->integer('stock_actuel');
            $table->integer('seuil_alerte');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matieres_premieres');
    }
};
