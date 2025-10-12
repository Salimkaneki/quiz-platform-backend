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
            // Formation pour l'École Supérieure de Gestion et d'Informatique du Togo
            [
                'name' => 'Informatique et Technologies Numériques',
                'code' => 'INFO-ESGIS-TOGO',
                'description' => 'Formation complète en informatique avec spécialisation en développement logiciel, cybersécurité et intelligence artificielle.',
                'duration_years' => 3,
                'institution_id' => Institution::where('code', 'ESGIS-TOGO')->first()->id,
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
