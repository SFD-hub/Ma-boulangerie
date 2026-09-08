<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Remplit la table pivot boulangerie_proprietaire à partir des comptes
 * propriétaires existants (users.boulangerie_id). Idempotent (insertOrIgnore) :
 * peut être rejouée sans risque de doublons.
 *
 * down() vide la table pivot — opération destructrice sur les données
 * de rattachement multi-boulangerie ajoutées depuis (mais pas sur
 * users.boulangerie_id ni sur les boulangeries elles-mêmes).
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('users')
            ->join('roles', 'roles.id', '=', 'users.role_id')
            ->where('roles.nom', 'proprietaire')
            ->whereNotNull('users.boulangerie_id')
            ->select('users.id as user_id', 'users.boulangerie_id')
            ->get()
            ->each(function ($row) use ($now) {
                DB::table('boulangerie_proprietaire')->insertOrIgnore([
                    'user_id'        => $row->user_id,
                    'boulangerie_id' => $row->boulangerie_id,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);
            });
    }

    public function down(): void
    {
        DB::table('boulangerie_proprietaire')->truncate();
    }
};
