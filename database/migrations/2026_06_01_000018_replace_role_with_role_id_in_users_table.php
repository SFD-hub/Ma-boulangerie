<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id')->nullable();
            });
        }

        if (Schema::hasColumn('users', 'role')) {
            DB::statement('UPDATE users SET role_id = (SELECT id FROM roles WHERE roles.nom = users.role)');

            Schema::table('users', function (Blueprint $table) {
                $table->foreign('role_id')->references('id')->on('roles')->restrictOnDelete();
                $table->dropColumn('role');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->nullable();
            });
        }

        if (Schema::hasColumn('users', 'role_id')) {
            DB::statement('UPDATE users SET role = (SELECT nom FROM roles WHERE roles.id = users.role_id)');

            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['role_id']);
                $table->dropColumn('role_id');
            });
        }
    }
};
