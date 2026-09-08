<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')->insertOrIgnore(['nom' => 'super_admin']);
    }

    public function down(): void
    {
        DB::table('roles')->where('nom', 'super_admin')->delete();
    }
};
