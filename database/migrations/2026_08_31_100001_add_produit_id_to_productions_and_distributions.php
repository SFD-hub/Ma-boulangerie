<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            $table->foreignId('produit_id')->nullable()->after('boulangerie_id')
                ->constrained('produits')->nullOnDelete();
        });

        Schema::table('distributions', function (Blueprint $table) {
            $table->foreignId('produit_id')->nullable()->after('livreur_id')
                ->constrained('produits')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            $table->dropForeign(['produit_id']);
            $table->dropColumn('produit_id');
        });

        Schema::table('distributions', function (Blueprint $table) {
            $table->dropForeign(['produit_id']);
            $table->dropColumn('produit_id');
        });
    }
};
