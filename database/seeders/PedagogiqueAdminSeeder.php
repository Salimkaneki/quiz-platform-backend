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

        // 2. Récupérer la première institution disponible
        $institution = \App\Models\Institution::first();
        
        if (!$institution) {
            $this->command->error("Aucune institution trouvée. Veuillez exécuter InstitutionSeeder d'abord.");
            return;
        }

        // 3. Vérifier si cet admin pédagogique existe déjà
        $exists = Administrator::where('user_id', $user->id)
            ->where('institution_id', $institution->id)
            ->where('type', 'pedagogique')
            ->exists();

        if (! $exists) {
            Administrator::create([
                'user_id' => $user->id,
                'institution_id' => $institution->id,
                'type' => 'pedagogique',
                'permissions' => ['gestion_cours', 'planification']
            ]);

            $this->command->info("Administrateur pédagogique créé avec succès pour l'institution: {$institution->name}");
        } else {
            $this->command->warn("Cet administrateur pédagogique existe déjà.");
        }
    }
}
