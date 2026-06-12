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
        Schema::create('consommations_abonnes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_abonne_id')->constrained('clients_abonnes')->cascadeOnDelete();
            $table->date('date_consommation');
            $table->integer('quantite');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consommations_abonnes');
    }
};
