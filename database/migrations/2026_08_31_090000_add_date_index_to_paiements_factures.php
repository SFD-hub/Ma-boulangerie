<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paiements_factures', function (Blueprint $table) {
            $table->index(['facture_id', 'date_paiement']);
        });
    }

    public function down(): void
    {
        Schema::table('paiements_factures', function (Blueprint $table) {
            $table->dropIndex(['facture_id', 'date_paiement']);
        });
    }
};
