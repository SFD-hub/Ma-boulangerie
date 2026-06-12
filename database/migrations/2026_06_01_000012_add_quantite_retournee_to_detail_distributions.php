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
        Schema::table('detail_distributions', function (Blueprint $table) {
            $table->integer('quantite_retournee')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_distributions', function (Blueprint $table) {
            $table->dropColumn('quantite_retournee');
        });
    }
};
