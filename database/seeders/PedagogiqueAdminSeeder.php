<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Administrator;
use Illuminate\Support\Facades\Hash;

class PedagogiqueAdminSeeder extends Seeder
{
    public function run()
    {
        // 1. Création ou récupération de l'utilisateur admin
        $user = User::firstOrCreate(
            ['email' => 'pedago.admin@ecole.com'],
            [
                'name' => 'Admin Pédagogique',
                'password' => Hash::make('motdepasse123'),
                'account_type' => 'admin'
            ]
        );

        // 2. Vérifier si cet admin pédagogique existe déjà
        $exists = Administrator::where('user_id', $user->id)
            ->where('institution_id', 1)
            ->where('type', 'pedagogique')
            ->exists();

        if (! $exists) {
            Administrator::create([
                'user_id' => $user->id,
                'institution_id' => 1,
                'type' => 'pedagogique',
                'permissions' => ['gestion_cours', 'planification']
            ]);

            $this->command->info("Administrateur pédagogique créé avec succès.");
        } else {
            $this->command->warn("Cet administrateur pédagogique existe déjà.");
        }
    }
}
