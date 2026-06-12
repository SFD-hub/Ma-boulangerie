<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('productions', function (Blueprint $table) {
            $table->integer('quantite_levure')->default(0);
        });
    }

    public function down()
    {
        Schema::table('productions', function (Blueprint $table) {
            $table->dropColumn('quantite_levure');
        });
    }
};