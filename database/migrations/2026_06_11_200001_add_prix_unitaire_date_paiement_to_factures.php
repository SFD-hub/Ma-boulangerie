<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->integer('prix_unitaire')->nullable()->after('quantite_totale');
            $table->date('date_paiement')->nullable()->after('statut');
        });

        // Normaliser les anciens statuts → seuls 'impayee' et 'payee' autorisés
        DB::statement("UPDATE factures SET statut = 'impayee' WHERE statut IN ('envoyee', 'annulee')");
    }

    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropColumn(['prix_unitaire', 'date_paiement']);
        });
    }
};
