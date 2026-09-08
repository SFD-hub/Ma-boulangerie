<?php

namespace Database\Seeders;

use App\Models\Boulangerie;
use App\Models\MatierePremiere;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $proprietaire = Role::firstOrCreate(['nom' => 'proprietaire']);
        Role::firstOrCreate(['nom' => 'gerant']);
        $superAdmin   = Role::firstOrCreate(['nom' => 'super_admin']);

        // ── Compte Super Admin ──────────────────────────────────────────────
        // Jamais d'identifiant en dur ici : uniquement via SUPERADMIN_EMAIL /
        // SUPERADMIN_PASSWORD dans .env. Si absents, aucun compte n'est créé
        // (et ce, quel que soit l'environnement) — évite de planter un accès
        // super admin à identifiants connus si le seeder tourne un jour sur
        // une vraie base.
        $superAdminEmail    = env('SUPERADMIN_EMAIL');
        $superAdminPassword = env('SUPERADMIN_PASSWORD');

        if ($superAdminEmail && $superAdminPassword) {
            User::firstOrCreate(
                ['email' => $superAdminEmail],
                [
                    'name'           => 'Super Admin',
                    'password'       => Hash::make($superAdminPassword),
                    'telephone'      => null,
                    'role_id'        => $superAdmin->id,
                    'boulangerie_id' => null,
                    'actif'          => true,
                ]
            );
        } elseif ($this->command) {
            $this->command->warn('SUPERADMIN_EMAIL / SUPERADMIN_PASSWORD absents du .env — aucun compte super admin créé.');
        }

        // ── Données de démonstration ─────────────────────────────────────────
        // Uniquement en local/dev, jamais sur une vraie base de production.
        if (app()->isProduction()) {
            return;
        }

        $boulangerie = Boulangerie::firstOrCreate(
            ['nom' => 'Ma Boulangerie'],
            [
                'telephone' => '77 000 00 00',
                'adresse'   => 'Dakar, Sénégal',
                'prix_pain' => 200,
            ]
        );

        MatierePremiere::firstOrCreate(
            ['boulangerie_id' => $boulangerie->id, 'nom' => 'Farine'],
            ['stock_actuel' => 12, 'seuil_alerte' => 3]
        );

        MatierePremiere::firstOrCreate(
            ['boulangerie_id' => $boulangerie->id, 'nom' => 'Levure'],
            ['stock_actuel' => 4, 'seuil_alerte' => 1]
        );

        $testPassword = env('TEST_ACCOUNT_PASSWORD', 'password');

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name'           => 'Propriétaire Test',
                'password'       => Hash::make($testPassword),
                'telephone'      => '77 000 00 00',
                'role_id'        => $proprietaire->id,
                'boulangerie_id' => $boulangerie->id,
                'actif'          => true,
            ]
        );
    }
}
