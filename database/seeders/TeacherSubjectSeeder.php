<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TeacherSubject;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Classes;

class TeacherSubjectSeeder extends Seeder
{
    public function run(): void
    {
        $teacherSubjects = [
            // Salim Pereira - Informatique ESGIS Togo
            [
                'teacher_email' => 'salim.pereira@esgis.tg',
                'subject_codes' => ['ALGO-INFO-ESGIS-101', 'MATH-DISC-INFO-ESGIS-102', 'ARCHI-INFO-ESGIS-201', 'BDD-INFO-ESGIS-202'],
                'class_names' => ['L1 Informatique ESGIS Togo', 'L2 Informatique ESGIS Togo', 'L3 Informatique ESGIS Togo'],
                'academic_year' => '2024-2025',
            ],
            // Sophie Ouattara - Informatique ESGIS Togo
            [
                'teacher_email' => 'sophie.ouattara@esgis.tg',
                'subject_codes' => ['POO-INFO-ESGIS-301', 'RESEAUX-INFO-ESGIS-302', 'WEB-INFO-ESGIS-401', 'IA-INFO-ESGIS-402'],
                'class_names' => ['L1 Informatique ESGIS Togo', 'L2 Informatique ESGIS Togo', 'L3 Informatique ESGIS Togo'],
                'academic_year' => '2024-2025',
            ],
        ];

        $created = 0;
        foreach ($teacherSubjects as $assignment) {
            $teacher = Teacher::whereHas('user', function($query) use ($assignment) {
                $query->where('email', $assignment['teacher_email']);
            })->first();

            if (!$teacher) {
                $this->command->error("Professeur {$assignment['teacher_email']} non trouvé");
                continue;
            }

            foreach ($assignment['subject_codes'] as $subjectCode) {
                $subject = Subject::where('code', $subjectCode)->first();

                if (!$subject) {
                    $this->command->warn("Matière {$subjectCode} non trouvée pour {$assignment['teacher_email']}");
                    continue;
                }

                foreach ($assignment['class_names'] as $className) {
                    $class = Classes::where('name', $className)
                        ->where('academic_year', $assignment['academic_year'])
                        ->first();

                    if (!$class) {
                        $this->command->warn("Classe {$className} non trouvée pour {$assignment['teacher_email']}");
                        continue;
                    }

                    TeacherSubject::updateOrCreate(
                        [
                            'teacher_id' => $teacher->id,
                            'subject_id' => $subject->id,
                            'classe_id' => $class->id,
                            'academic_year' => $assignment['academic_year'],
                        ],
                        [
                            'is_active' => true,
                        ]
                    );

                    $created++;
                }
            }
        }

        $this->command->info('Assignations professeur-matière-classe créées/mises à jour avec succès!');
        $this->command->info('Total: ' . $created . ' assignations');
    }
}