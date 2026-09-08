<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boulangeries', function (Blueprint $table) {
            $table->boolean('suspendu')->default(false)->after('prix_pain');
        });
    }

    public function down(): void
    {
        Schema::table('boulangeries', function (Blueprint $table) {
            $table->dropColumn('suspendu');
        });
    }
};
