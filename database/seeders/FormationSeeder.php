<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Formation;
use App\Models\Institution;

class FormationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $formations = [
            // Formations pour l'Université de Lomé
            [
                'name' => 'Informatique',
                'code' => 'INFO-UL',
                'description' => 'Formation complète en informatique avec spécialisations en développement logiciel, réseaux et cybersécurité.',
                'duration_years' => 3,
                'institution_id' => Institution::where('code', 'UL')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Génie Civil',
                'code' => 'GC-UL',
                'description' => 'Formation en génie civil avec focus sur la construction, les infrastructures et l\'urbanisme.',
                'duration_years' => 5,
                'institution_id' => Institution::where('code', 'UL')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Médecine',
                'code' => 'MED-UL',
                'description' => 'Formation médicale complète préparant aux métiers de la santé.',
                'duration_years' => 7,
                'institution_id' => Institution::where('code', 'UL')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Droit',
                'code' => 'DROIT-UL',
                'description' => 'Formation juridique complète avec spécialisations en droit public et privé.',
                'duration_years' => 5,
                'institution_id' => Institution::where('code', 'UL')->first()->id,
                'is_active' => true,
            ],

            // Formations pour l'Institut Supérieur de Métiers
            [
                'name' => 'Développement Web et Mobile',
                'code' => 'WEB-ISM',
                'description' => 'Formation intensive en développement web et applications mobiles.',
                'duration_years' => 2,
                'institution_id' => Institution::where('code', 'ISM-TOGO')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Électronique et Télécommunications',
                'code' => 'ELEC-ISM',
                'description' => 'Formation en électronique, télécommunications et systèmes embarqués.',
                'duration_years' => 2,
                'institution_id' => Institution::where('code', 'ISM-TOGO')->first()->id,
                'is_active' => true,
            ],
            [
                'name' => 'Comptabilité et Gestion',
                'code' => 'COMPTA-ISM',
                'description' => 'Formation en comptabilité, finance et gestion d\'entreprise.',
                'duration_years' => 2,
                'institution_id' => Institution::where('code', 'ISM-TOGO')->first()->id,
                'is_active' => true,
            ],
        ];

        $created = 0;
        foreach ($formations as $formationData) {
            Formation::updateOrCreate(
                ['code' => $formationData['code']],
                $formationData
            );
            $created++;
        }

        $this->command->info('Formations créées/mises à jour avec succès!');
        $this->command->info('Total: ' . $created . ' formations');
    }
}
