<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Classes;
use App\Models\Formation;
use App\Models\Institution;

class ClasseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = [
            // Classes pour Informatique UL
            [
                'name' => 'L1 Informatique',
                'level' => 1,
                'academic_year' => '2024-2025',
                'formation_id' => Formation::where('code', 'INFO-UL')->first()->id,
                'max_students' => 45,
                'is_active' => true,
            ],
            [
                'name' => 'L2 Informatique',
                'level' => 2,
                'academic_year' => '2024-2025',
                'formation_id' => Formation::where('code', 'INFO-UL')->first()->id,
                'max_students' => 40,
                'is_active' => true,
            ],
            [
                'name' => 'L3 Informatique',
                'level' => 3,
                'academic_year' => '2024-2025',
                'formation_id' => Formation::where('code', 'INFO-UL')->first()->id,
                'max_students' => 35,
                'is_active' => true,
            ],

            // Classes pour Génie Civil UL
            [
                'name' => 'L1 Génie Civil',
                'level' => 1,
                'academic_year' => '2024-2025',
                'formation_id' => Formation::where('code', 'GC-UL')->first()->id,
                'max_students' => 50,
                'is_active' => true,
            ],
            [
                'name' => 'L2 Génie Civil',
                'level' => 2,
                'academic_year' => '2024-2025',
                'formation_id' => Formation::where('code', 'GC-UL')->first()->id,
                'max_students' => 45,
                'is_active' => true,
            ],

            // Classes pour Médecine UL
            [
                'name' => 'L1 Médecine',
                'level' => 1,
                'academic_year' => '2024-2025',
                'formation_id' => Formation::where('code', 'MED-UL')->first()->id,
                'max_students' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'L2 Médecine',
                'level' => 2,
                'academic_year' => '2024-2025',
                'formation_id' => Formation::where('code', 'MED-UL')->first()->id,
                'max_students' => 55,
                'is_active' => true,
            ],

            // Classes pour Développement Web ISM
            [
                'name' => 'Promo 2024-2026 Web',
                'level' => 1,
                'academic_year' => '2024-2025',
                'formation_id' => Formation::where('code', 'WEB-ISM')->first()->id,
                'max_students' => 25,
                'is_active' => true,
            ],
            [
                'name' => 'Promo 2025-2027 Web',
                'level' => 1,
                'academic_year' => '2025-2026',
                'formation_id' => Formation::where('code', 'WEB-ISM')->first()->id,
                'max_students' => 25,
                'is_active' => true,
            ],

            // Classes pour Électronique ISM
            [
                'name' => 'Promo 2024-2026 Électronique',
                'level' => 1,
                'academic_year' => '2024-2025',
                'formation_id' => Formation::where('code', 'ELEC-ISM')->first()->id,
                'max_students' => 30,
                'is_active' => true,
            ],

            // Classes pour Comptabilité ISM
            [
                'name' => 'Promo 2024-2026 Comptabilité',
                'level' => 1,
                'academic_year' => '2024-2025',
                'formation_id' => Formation::where('code', 'COMPTA-ISM')->first()->id,
                'max_students' => 35,
                'is_active' => true,
            ],
        ];

        $created = 0;
        foreach ($classes as $classData) {
            Classes::updateOrCreate(
                [
                    'name' => $classData['name'],
                    'academic_year' => $classData['academic_year'],
                    'formation_id' => $classData['formation_id']
                ],
                $classData
            );
            $created++;
        }

        $this->command->info('Classes créées/mises à jour avec succès!');
        $this->command->info('Total: ' . $created . ' classes');
    }
}