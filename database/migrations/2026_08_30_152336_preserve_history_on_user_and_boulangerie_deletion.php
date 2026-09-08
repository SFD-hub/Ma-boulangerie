<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Deux clés étrangères étaient en CASCADE alors qu'elles ne devraient pas
 * effacer de données :
 *
 * - users.boulangerie_id : ne représente plus "l'unique boulangerie" d'un
 *   propriétaire depuis le multi-boulangerie (juste sa boulangerie
 *   d'origine). Si une boulangerie est un jour supprimée, on ne veut pas
 *   que ça supprime le compte du propriétaire (et par ricochet ses autres
 *   boulangeries via la pivot) — on met juste la colonne à NULL.
 *
 * - activity_logs.user_id : si un compte (gérant supprimé définitivement,
 *   fonctionnalité déjà existante et volontaire) est effacé, on veut
 *   garder son historique d'activité pour la traçabilité — on met juste
 *   l'auteur à NULL au lieu d'effacer les lignes.
 *
 * SQL brut (pas de Schema::table()->change()) car doctrine/dbal n'est pas
 * installé — même approche que les migrations précédentes de ce type.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE users DROP FOREIGN KEY users_boulangerie_id_foreign');
        DB::statement('ALTER TABLE users ADD CONSTRAINT users_boulangerie_id_foreign FOREIGN KEY (boulangerie_id) REFERENCES boulangeries (id) ON DELETE SET NULL');

        DB::statement('ALTER TABLE activity_logs DROP FOREIGN KEY activity_logs_user_id_foreign');
        DB::statement('ALTER TABLE activity_logs MODIFY user_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE activity_logs ADD CONSTRAINT activity_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users DROP FOREIGN KEY users_boulangerie_id_foreign');
        DB::statement('ALTER TABLE users ADD CONSTRAINT users_boulangerie_id_foreign FOREIGN KEY (boulangerie_id) REFERENCES boulangeries (id) ON DELETE CASCADE');

        DB::statement('ALTER TABLE activity_logs DROP FOREIGN KEY activity_logs_user_id_foreign');
        // Lossy si des lignes ont déjà user_id NULL (comportement attendu au rollback).
        DB::statement('ALTER TABLE activity_logs MODIFY user_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE activity_logs ADD CONSTRAINT activity_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
    }
};
