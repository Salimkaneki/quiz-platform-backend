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
        // Récupérer toutes les institutions disponibles
        $institutions = \App\Models\Institution::all();
        
        if ($institutions->isEmpty()) {
            $this->command->error("Aucune institution trouvée. Veuillez exécuter InstitutionSeeder d'abord.");
            return;
        }

        $adminsCreated = 0;

        foreach ($institutions as $institution) {
            // Créer un email unique et court pour chaque institution
            $domains = [
                'Université de Lomé' => 'ul.edu.tg',
                'Institut Supérieur de Métiers' => 'ism.tg',
                'École Supérieure de Gestion et d\'Informatique du Sénégal' => 'esgis.sn',
                'École Supérieure de Gestion et d\'Informatique du Togo' => 'esgis.tg',
            ];
            
            $domain = $domains[$institution->name] ?? strtolower(str_replace([' ', 'É', 'é', 'à', 'â', 'ê', 'î', 'ô', 'û', 'ç'], ['_', 'e', 'e', 'a', 'a', 'e', 'i', 'o', 'u', 'c'], $institution->name)) . '.com';
            
            $email = 'pedago.admin@' . $domain;
            
            // Adapter le nom selon l'institution
            $adminName = 'Admin Pédagogique ' . $institution->name;
            
            // 1. Création ou récupération de l'utilisateur admin
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $adminName,
                    'password' => Hash::make('motdepasse123'),
                    'account_type' => 'admin'
                ]
            );

            // 2. Vérifier si cet admin pédagogique existe déjà
            $exists = Administrator::where('user_id', $user->id)
                ->where('institution_id', $institution->id)
                ->where('type', 'pedagogique')
                ->exists();

            if (! $exists) {
                Administrator::create([
                    'user_id' => $user->id,
                    'institution_id' => $institution->id,
                    'type' => 'pedagogique',
                    'permissions' => ['gestion_cours', 'planification', 'gestion_etudiants', 'gestion_professeurs']
                ]);

                $this->command->info("Administrateur pédagogique créé avec succès pour l'institution: {$institution->name}");
                $adminsCreated++;
            } else {
                $this->command->warn("Administrateur pédagogique existe déjà pour: {$institution->name}");
            }
        }

        $this->command->info("Total d'administrateurs pédagogiques créés: {$adminsCreated}");
    }
}
