<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\Formation;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            // Matières pour Informatique ESGIS Togo
            [
                'name' => 'Algorithmique et Programmation',
                'code' => 'ALGO-INFO-ESGIS-101',
                'description' => 'Introduction aux algorithmes et à la programmation structurée',
                'credits' => 6,
                'coefficient' => 3,
                'type' => 'cours',
                'formation_id' => Formation::where('code', 'INFO-ESGIS-TOGO')->first()->id,
                'semester' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Mathématiques Discrètes',
                'code' => 'MATH-DISC-INFO-ESGIS-102',
                'description' => 'Logique, ensembles, graphes et combinatoire',
                'credits' => 5,
                'coefficient' => 2,
                'type' => 'cours',
                'formation_id' => Formation::where('code', 'INFO-ESGIS-TOGO')->first()->id,
                'semester' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Architecture des Ordinateurs',
                'code' => 'ARCHI-INFO-ESGIS-201',
                'description' => 'Architecture matérielle et systèmes d\'exploitation',
                'credits' => 4,
                'coefficient' => 2,
                'type' => 'cours',
                'formation_id' => Formation::where('code', 'INFO-ESGIS-TOGO')->first()->id,
                'semester' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Bases de Données',
                'code' => 'BDD-INFO-ESGIS-202',
                'description' => 'Conception et administration de bases de données',
                'credits' => 5,
                'coefficient' => 3,
                'type' => 'cours',
                'formation_id' => Formation::where('code', 'INFO-ESGIS-TOGO')->first()->id,
                'semester' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Programmation Orientée Objet',
                'code' => 'POO-INFO-ESGIS-301',
                'description' => 'Concepts avancés de POO et design patterns',
                'credits' => 6,
                'coefficient' => 3,
                'type' => 'cours',
                'formation_id' => Formation::where('code', 'INFO-ESGIS-TOGO')->first()->id,
                'semester' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Réseaux Informatiques',
                'code' => 'RESEAUX-INFO-ESGIS-302',
                'description' => 'Protocoles réseau et administration système',
                'credits' => 4,
                'coefficient' => 2,
                'type' => 'cours',
                'formation_id' => Formation::where('code', 'INFO-ESGIS-TOGO')->first()->id,
                'semester' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Développement Web',
                'code' => 'WEB-INFO-ESGIS-401',
                'description' => 'Développement d\'applications web modernes',
                'credits' => 6,
                'coefficient' => 3,
                'type' => 'cours',
                'formation_id' => Formation::where('code', 'INFO-ESGIS-TOGO')->first()->id,
                'semester' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Intelligence Artificielle',
                'code' => 'IA-INFO-ESGIS-402',
                'description' => 'Introduction aux algorithmes d\'IA et apprentissage automatique',
                'credits' => 5,
                'coefficient' => 3,
                'type' => 'cours',
                'formation_id' => Formation::where('code', 'INFO-ESGIS-TOGO')->first()->id,
                'semester' => 4,
                'is_active' => true,
            ],
        ];

        $created = 0;
        foreach ($subjects as $subjectData) {
            Subject::updateOrCreate(
                ['code' => $subjectData['code']],
                $subjectData
            );
            $created++;
        }

        $this->command->info('Matières créées/mises à jour avec succès!');
        $this->command->info('Total: ' . $created . ' matières');
    }
}