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
            // Classes pour Informatique ESGIS Togo
            [
                'name' => 'L1 Informatique ESGIS Togo',
                'level' => 1,
                'academic_year' => '2024-2025',
                'formation_id' => Formation::where('code', 'INFO-ESGIS-TOGO')->first()->id,
                'max_students' => 40,
                'is_active' => true,
            ],
            [
                'name' => 'L2 Informatique ESGIS Togo',
                'level' => 2,
                'academic_year' => '2024-2025',
                'formation_id' => Formation::where('code', 'INFO-ESGIS-TOGO')->first()->id,
                'max_students' => 35,
                'is_active' => true,
            ],
            [
                'name' => 'L3 Informatique ESGIS Togo',
                'level' => 3,
                'academic_year' => '2024-2025',
                'formation_id' => Formation::where('code', 'INFO-ESGIS-TOGO')->first()->id,
                'max_students' => 30,
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