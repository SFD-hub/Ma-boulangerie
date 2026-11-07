<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Prix de vente Boutique par type de pièce (FCFA), définis par le
        // propriétaire. Valeurs par défaut = tarifs courants, pour que la
        // Boutique reste utilisable sans réglage préalable.
        Schema::table('boulangeries', function (Blueprint $table) {
            $table->unsignedInteger('prix_boutique_entier')->default(150)->after('prix_pain');
            $table->unsignedInteger('prix_boutique_moitie')->default(75)->after('prix_boutique_entier');
            $table->unsignedInteger('prix_boutique_tiers')->default(50)->after('prix_boutique_moitie');
        });
    }

    public function down(): void
    {
        Schema::table('boulangeries', function (Blueprint $table) {
            $table->dropColumn(['prix_boutique_entier', 'prix_boutique_moitie', 'prix_boutique_tiers']);
        });
    }
};
