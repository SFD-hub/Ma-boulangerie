<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('versements', function (Blueprint $table) {
            $table->foreignId('distribution_id')
                  ->nullable()
                  ->after('livreur_id')
                  ->constrained('distributions')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('versements', function (Blueprint $table) {
            $table->dropForeign(['distribution_id']);
            $table->dropColumn('distribution_id');
        });
    }
};
