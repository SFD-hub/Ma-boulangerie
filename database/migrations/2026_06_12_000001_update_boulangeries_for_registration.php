<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boulangeries', function (Blueprint $table) {
            $table->string('statut_abonnement')->default('actif')->nullable()->change();
            $table->date('date_expiration_abonnement')->nullable()->change();
            $table->string('email')->nullable()->after('adresse');
        });
    }

    public function down(): void
    {
        Schema::table('boulangeries', function (Blueprint $table) {
            $table->string('statut_abonnement')->nullable(false)->change();
            $table->date('date_expiration_abonnement')->nullable(false)->change();
            $table->dropColumn('email');
        });
    }
};
