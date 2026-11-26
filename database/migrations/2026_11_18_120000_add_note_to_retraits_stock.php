<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remarque facultative saisie par le gérant quand il valide qu'un bidon /
 * sac est terminé (ex. « bidon percé », « fini plus vite car gros service »).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('retraits_stock', function (Blueprint $table) {
            $table->string('note', 255)->nullable()->after('stock_apres');
        });
    }

    public function down(): void
    {
        Schema::table('retraits_stock', function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }
};
