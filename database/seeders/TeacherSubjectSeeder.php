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
            // Salim Pereira - Informatique UL
            [
                'teacher_email' => 'salimpereira01@gmail.com',
                'subject_codes' => ['ALGO-INFO-UL-101', 'MATH-DISC-INFO-UL-102', 'ARCHI-INFO-UL-201', 'BDD-INFO-UL-202'],
                'class_names' => ['L1 Informatique', 'L2 Informatique', 'L3 Informatique'],
                'academic_year' => '2024-2025',
            ],
            // Marie Kouassi - Mathématiques UL
            [
                'teacher_email' => 'marie.kouassi@ul.edu.tg',
                'subject_codes' => ['MATH-DISC-INFO-UL-102'],
                'class_names' => ['L1 Informatique', 'L1 Génie Civil', 'L2 Informatique'],
                'academic_year' => '2024-2025',
            ],
            // Jean Diallo - Physique UL
            [
                'teacher_email' => 'jean.diallo@ul.edu.tg',
                'subject_codes' => ['ANALOG-ELEC-ISM-101'],
                'class_names' => ['L1 Informatique', 'L1 Génie Civil', 'L2 Informatique'],
                'academic_year' => '2024-2025',
            ],
            // Fatima Traore - Chimie UL
            [
                'teacher_email' => 'fatima.traore@ul.edu.tg',
                'subject_codes' => ['BIOCHIM-MED-UL-102'],
                'class_names' => ['L1 Informatique', 'L1 Génie Civil', 'L2 Informatique'],
                'academic_year' => '2024-2025',
            ],
            // Ahmed Bamba - Génie Civil UL
            [
                'teacher_email' => 'ahmed.bamba@ul.edu.tg',
                'subject_codes' => ['RESIST-GC-UL-101', 'TOPO-GC-UL-102', 'BETON-GC-UL-201'],
                'class_names' => ['L1 Génie Civil', 'L2 Génie Civil'],
                'academic_year' => '2024-2025',
            ],
            // Aminata Sow - Médecine UL
            [
                'teacher_email' => 'aminata.sow@ul.edu.tg',
                'subject_codes' => ['ANAT-MED-UL-101', 'BIOCHIM-MED-UL-102', 'PHYSIO-MED-UL-201'],
                'class_names' => ['L1 Médecine', 'L2 Médecine'],
                'academic_year' => '2024-2025',
            ],
            // Paul Konate - Développement Web ISM
            [
                'teacher_email' => 'paul.konate@ism.tg',
                'subject_codes' => ['HTML-WEB-ISM-101', 'PHP-WEB-ISM-102', 'REACT-WEB-ISM-201'],
                'class_names' => ['Promo 2024-2026 Web', 'Promo 2025-2027 Web'],
                'academic_year' => '2024-2025',
            ],
            // Sophie Ouattara - Électronique ISM
            [
                'teacher_email' => 'sophie.ouattara@ism.tg',
                'subject_codes' => ['ANALOG-ELEC-ISM-101', 'EMBARQUE-ELEC-ISM-102'],
                'class_names' => ['Promo 2024-2026 Électronique'],
                'academic_year' => '2024-2025',
            ],
            // Michel Camara - Comptabilité ISM
            [
                'teacher_email' => 'michel.camara@ism.tg',
                'subject_codes' => ['COMPTA-GEN-ISM-101', 'FINANCE-ISM-102'],
                'class_names' => ['Promo 2024-2026 Comptabilité'],
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