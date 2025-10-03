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
            // Matières pour Informatique UL
            [
                'name' => 'Algorithmique et Programmation',
                'code' => 'ALGO-INFO-UL-101',
                'description' => 'Introduction aux algorithmes et à la programmation structurée',
                'credits' => 6,
                'coefficient' => 3,
                'type' => 'cours',
                'formation_id' => Formation::where('code', 'INFO-UL')->first()->id,
                'semester' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Mathématiques Discrètes',
                'code' => 'MATH-DISC-INFO-UL-102',
                'description' => 'Logique, ensembles, graphes et combinatoire',
                'credits' => 5,
                'coefficient' => 2,
                'type' => 'cours',
                'formation_id' => Formation::where('code', 'INFO-UL')->first()->id,
                'semester' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Architecture des Ordinateurs',
                'code' => 'ARCHI-INFO-UL-201',
                'description' => 'Architecture matérielle et systèmes d\'exploitation',
                'credits' => 4,
                'coefficient' => 2,
                'type' => 'cours',
                'formation_id' => Formation::where('code', 'INFO-UL')->first()->id,
                'semester' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Bases de Données',
                'code' => 'BDD-INFO-UL-202',
                'description' => 'Conception et administration de bases de données',
                'credits' => 5,
                'coefficient' => 3,
                'type' => 'cours',
                'formation_id' => Formation::where('code', 'INFO-UL')->first()->id,
                'semester' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Programmation Orientée Objet',
                'code' => 'POO-INFO-UL-301',
                'description' => 'Concepts avancés de POO et design patterns',
                'credits' => 6,
                'coefficient' => 3,
                'type' => 'cours',
                'formation_id' => Formation::where('code', 'INFO-UL')->first()->id,
                'semester' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Réseaux Informatiques',
                'code' => 'RESEAUX-INFO-UL-302',
                'description' => 'Protocoles réseau et administration système',
                'credits' => 4,
                'coefficient' => 2,
                'type' => 'cours',
                'formation_id' => Formation::where('code', 'INFO-UL')->first()->id,
                'semester' => 3,
                'is_active' => true,
            ],

            // Matières pour Génie Civil UL
            [
                'name' => 'Résistance des Matériaux',
                'code' => 'RESIST-GC-UL-101',
                'description' => 'Étude de la résistance et de la déformation des matériaux',
                'credits' => 5,
                'coefficient' => 3,
                'type' => 'cours',
                'formation_id' => Formation::where('code', 'GC-UL')->first()->id,
                'semester' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Topographie',
                'code' => 'TOPO-GC-UL-102',
                'description' => 'Techniques de mesure et de représentation du terrain',
                'credits' => 4,
                'coefficient' => 2,
                'type' => 'tp',
                'formation_id' => Formation::where('code', 'GC-UL')->first()->id,
                'semester' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Béton Armé',
                'code' => 'BETON-GC-UL-201',
                'description' => 'Conception et calcul des structures en béton armé',
                'credits' => 6,
                'coefficient' => 3,
                'type' => 'cours',
                'formation_id' => Formation::where('code', 'GC-UL')->first()->id,
                'semester' => 2,
                'is_active' => true,
            ],

            // Matières pour Médecine UL
            [
                'name' => 'Anatomie Humaine',
                'code' => 'ANAT-MED-UL-101',
                'description' => 'Étude de l\'anatomie du corps humain',
                'credits' => 8,
                'coefficient' => 4,
                'type' => 'cours',
                'formation_id' => Formation::where('code', 'MED-UL')->first()->id,
                'semester' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Biochimie Médicale',
                'code' => 'BIOCHIM-MED-UL-102',
                'description' => 'Biochimie appliquée à la médecine',
                'credits' => 6,
                'coefficient' => 3,
                'type' => 'cours',
                'formation_id' => Formation::where('code', 'MED-UL')->first()->id,
                'semester' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Physiologie',
                'code' => 'PHYSIO-MED-UL-201',
                'description' => 'Fonctionnement des systèmes physiologiques',
                'credits' => 7,
                'coefficient' => 3,
                'type' => 'cours',
                'formation_id' => Formation::where('code', 'MED-UL')->first()->id,
                'semester' => 2,
                'is_active' => true,
            ],

            // Matières pour Développement Web ISM
            [
                'name' => 'HTML/CSS/JavaScript',
                'code' => 'HTML-WEB-ISM-101',
                'description' => 'Bases du développement web front-end',
                'credits' => 4,
                'coefficient' => 2,
                'type' => 'cours',
                'formation_id' => Formation::where('code', 'WEB-ISM')->first()->id,
                'semester' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'PHP/MySQL',
                'code' => 'PHP-WEB-ISM-102',
                'description' => 'Développement back-end avec PHP et bases de données',
                'credits' => 5,
                'coefficient' => 3,
                'type' => 'cours',
                'formation_id' => Formation::where('code', 'WEB-ISM')->first()->id,
                'semester' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'React Native',
                'code' => 'REACT-WEB-ISM-201',
                'description' => 'Développement d\'applications mobiles multiplateformes',
                'credits' => 4,
                'coefficient' => 2,
                'type' => 'tp',
                'formation_id' => Formation::where('code', 'WEB-ISM')->first()->id,
                'semester' => 2,
                'is_active' => true,
            ],

            // Matières pour Électronique ISM
            [
                'name' => 'Électronique Analogique',
                'code' => 'ANALOG-ELEC-ISM-101',
                'description' => 'Circuits électroniques analogiques et amplification',
                'credits' => 5,
                'coefficient' => 3,
                'type' => 'cours',
                'formation_id' => Formation::where('code', 'ELEC-ISM')->first()->id,
                'semester' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Systèmes Embarqués',
                'code' => 'EMBARQUE-ELEC-ISM-102',
                'description' => 'Programmation de microcontrôleurs et systèmes embarqués',
                'credits' => 4,
                'coefficient' => 2,
                'type' => 'tp',
                'formation_id' => Formation::where('code', 'ELEC-ISM')->first()->id,
                'semester' => 1,
                'is_active' => true,
            ],

            // Matières pour Comptabilité ISM
            [
                'name' => 'Comptabilité Générale',
                'code' => 'COMPTA-GEN-ISM-101',
                'description' => 'Principes fondamentaux de la comptabilité',
                'credits' => 4,
                'coefficient' => 2,
                'type' => 'cours',
                'formation_id' => Formation::where('code', 'COMPTA-ISM')->first()->id,
                'semester' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Analyse Financière',
                'code' => 'FINANCE-ISM-102',
                'description' => 'Analyse des états financiers et ratios',
                'credits' => 3,
                'coefficient' => 2,
                'type' => 'cours',
                'formation_id' => Formation::where('code', 'COMPTA-ISM')->first()->id,
                'semester' => 1,
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