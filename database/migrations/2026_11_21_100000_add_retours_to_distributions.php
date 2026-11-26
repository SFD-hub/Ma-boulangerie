<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// « Retour » : pains que le livreur (ou le vendeur) n'a pas réussi à donner le
// jour même. Ils sont intacts et retournent dans le stock du jour. À ne pas
// confondre avec les « restants » (invendus des clients, rendus le lendemain et
// comptés au versement).
//
// distributions.nombre_retours = total des retours (somme de retours_distribution),
// tenu à jour à chaque saisie/annulation pour que les totaux restent de simples SUM.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distributions', function (Blueprint $table) {
            $table->unsignedInteger('nombre_retours')->default(0)->after('nombre_pains');
        });

        Schema::create('retours_distribution', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boulangerie_id')->constrained()->cascadeOnDelete();
            $table->foreignId('livreur_id')->constrained('livreurs')->cascadeOnDelete();
            $table->foreignId('distribution_id')->constrained('distributions')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('nombre_pains');
            $table->timestamps();

            $table->index('distribution_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retours_distribution');

        Schema::table('distributions', function (Blueprint $table) {
            $table->dropColumn('nombre_retours');
        });
    }
};
