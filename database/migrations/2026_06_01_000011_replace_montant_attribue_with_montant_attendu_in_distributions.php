<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add the new column, copy values, then drop the old column.
        Schema::table('distributions', function (Blueprint $table) {
            $table->decimal('montant_attendu', 10, 2)->nullable();
        });

        DB::statement('UPDATE distributions SET montant_attendu = montant_attribue');

        Schema::table('distributions', function (Blueprint $table) {
            $table->dropColumn('montant_attribue');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distributions', function (Blueprint $table) {
            $table->decimal('montant_attribue', 10, 2)->nullable();
        });

        DB::statement('UPDATE distributions SET montant_attribue = montant_attendu');

        Schema::table('distributions', function (Blueprint $table) {
            $table->dropColumn('montant_attendu');
        });
    }
};
