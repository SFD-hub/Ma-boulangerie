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

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name'           => 'Propriétaire Test',
                'password'       => Hash::make('password'),
                'telephone'      => '77 000 00 00',
                'role_id'        => $proprietaire->id,
                'boulangerie_id' => $boulangerie->id,
                'actif'          => true,
            ]
        );
    }
}
