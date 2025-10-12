<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\User;
use App\Models\Classes;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Notifications\StudentWelcomeNotification;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            // Étudiants ESGIS Togo - Informatique L1
            [
                'first_name' => 'Kofi',
                'last_name' => 'Amani',
                'email' => 'kofi.amani@esgis.tg',
                'birth_date' => Carbon::createFromDate(2003, 5, 15),
                'phone' => '+228 90 12 34 56',
                'class_id' => Classes::where('name', 'L1 Informatique ESGIS Togo')->where('academic_year', '2024-2025')->first()->id,
                'is_active' => true,
                'metadata' => [
                    'address' => 'Rue des Étudiants, Lomé',
                    'emergency_contact' => 'Mme Amani',
                    'emergency_phone' => '+228 91 23 45 67',
                    'nationality' => 'Togolaise',
                    'registration_date' => Carbon::createFromDate(2024, 9, 1),
                    'scholarship' => false,
                ],
            ],
            [
                'first_name' => 'Fatima',
                'last_name' => 'Traore',
                'email' => 'fatima.traore@esgis.tg',
                'birth_date' => Carbon::createFromDate(2002, 7, 12),
                'phone' => '+228 91 23 45 67',
                'class_id' => Classes::where('name', 'L1 Informatique ESGIS Togo')->where('academic_year', '2024-2025')->first()->id,
                'is_active' => true,
                'metadata' => [
                    'address' => 'Avenue de la Paix, Lomé',
                    'emergency_contact' => 'M. Traore',
                    'emergency_phone' => '+228 92 34 56 78',
                    'nationality' => 'Togolaise',
                    'registration_date' => Carbon::createFromDate(2024, 9, 1),
                    'scholarship' => true,
                ],
            ],
            [
                'first_name' => 'Ahmed',
                'last_name' => 'Bamba',
                'email' => 'ahmed.bamba@esgis.tg',
                'birth_date' => Carbon::createFromDate(2003, 11, 8),
                'phone' => '+228 92 34 56 78',
                'class_id' => Classes::where('name', 'L1 Informatique ESGIS Togo')->where('academic_year', '2024-2025')->first()->id,
                'is_active' => true,
                'metadata' => [
                    'address' => 'Boulevard du 13 Janvier, Lomé',
                    'emergency_contact' => 'Mme Bamba',
                    'emergency_phone' => '+228 93 45 67 89',
                    'nationality' => 'Togolaise',
                    'registration_date' => Carbon::createFromDate(2024, 9, 1),
                    'scholarship' => false,
                ],
            ],
            [
                'first_name' => 'Amina',
                'last_name' => 'Sow',
                'email' => 'amina.sow@esgis.tg',
                'birth_date' => Carbon::createFromDate(2002, 1, 25),
                'phone' => '+228 93 45 67 89',
                'class_id' => Classes::where('name', 'L1 Informatique ESGIS Togo')->where('academic_year', '2024-2025')->first()->id,
                'is_active' => true,
                'metadata' => [
                    'address' => 'Rue de la Solidarité, Lomé',
                    'emergency_contact' => 'M. Sow',
                    'emergency_phone' => '+228 94 56 78 90',
                    'nationality' => 'Togolaise',
                    'registration_date' => Carbon::createFromDate(2024, 9, 1),
                    'scholarship' => true,
                ],
            ],
            [
                'first_name' => 'Youssef',
                'last_name' => 'Toure',
                'email' => 'youssef.toure@esgis.tg',
                'birth_date' => Carbon::createFromDate(2003, 9, 14),
                'phone' => '+228 94 56 78 90',
                'class_id' => Classes::where('name', 'L1 Informatique ESGIS Togo')->where('academic_year', '2024-2025')->first()->id,
                'is_active' => true,
                'metadata' => [
                    'address' => 'Avenue Mohammed V, Lomé',
                    'emergency_contact' => 'Mme Toure',
                    'emergency_phone' => '+228 95 67 89 01',
                    'nationality' => 'Togolaise',
                    'registration_date' => Carbon::createFromDate(2024, 9, 1),
                    'scholarship' => false,
                ],
            ],

            // Étudiants ESGIS Togo - Informatique L2
            [
                'first_name' => 'Mariam',
                'last_name' => 'Kone',
                'email' => 'mariam.kone@esgis.tg',
                'birth_date' => Carbon::createFromDate(2002, 5, 30),
                'phone' => '+228 95 67 89 01',
                'class_id' => Classes::where('name', 'L2 Informatique ESGIS Togo')->where('academic_year', '2024-2025')->first()->id,
                'is_active' => true,
                'metadata' => [
                    'address' => 'Rue des Commerçants, Lomé',
                    'emergency_contact' => 'M. Kone',
                    'emergency_phone' => '+228 96 78 90 12',
                    'nationality' => 'Togolaise',
                    'registration_date' => Carbon::createFromDate(2024, 9, 1),
                    'scholarship' => true,
                ],
            ],
            [
                'first_name' => 'Ibrahim',
                'last_name' => 'Diouf',
                'email' => 'ibrahim.diouf@esgis.tg',
                'birth_date' => Carbon::createFromDate(2001, 9, 12),
                'phone' => '+228 96 78 90 12',
                'class_id' => Classes::where('name', 'L2 Informatique ESGIS Togo')->where('academic_year', '2024-2025')->first()->id,
                'is_active' => true,
                'metadata' => [
                    'address' => 'Boulevard de la République, Lomé',
                    'emergency_contact' => 'Mme Diouf',
                    'emergency_phone' => '+228 97 89 01 23',
                    'nationality' => 'Togolaise',
                    'registration_date' => Carbon::createFromDate(2024, 9, 1),
                    'scholarship' => false,
                ],
            ],
            [
                'first_name' => 'Zara',
                'last_name' => 'Ouattara',
                'email' => 'zara.ouattara@esgis.tg',
                'birth_date' => Carbon::createFromDate(2002, 12, 8),
                'phone' => '+228 97 89 01 23',
                'class_id' => Classes::where('name', 'L2 Informatique ESGIS Togo')->where('academic_year', '2024-2025')->first()->id,
                'is_active' => true,
                'metadata' => [
                    'address' => 'Rue de l\'Indépendance, Lomé',
                    'emergency_contact' => 'M. Ouattara',
                    'emergency_phone' => '+228 98 90 12 34',
                    'nationality' => 'Togolaise',
                    'registration_date' => Carbon::createFromDate(2024, 9, 1),
                    'scholarship' => true,
                ],
            ],

            // Étudiants ESGIS Togo - Informatique L3
            [
                'first_name' => 'Omar',
                'last_name' => 'Traore',
                'email' => 'omar.traore@esgis.tg',
                'birth_date' => Carbon::createFromDate(2001, 6, 25),
                'phone' => '+228 98 90 12 34',
                'class_id' => Classes::where('name', 'L3 Informatique ESGIS Togo')->where('academic_year', '2024-2025')->first()->id,
                'is_active' => true,
                'metadata' => [
                    'address' => 'Avenue de la Victoire, Lomé',
                    'emergency_contact' => 'Mme Traore',
                    'emergency_phone' => '+228 99 01 23 45',
                    'nationality' => 'Togolaise',
                    'registration_date' => Carbon::createFromDate(2024, 9, 1),
                    'scholarship' => false,
                ],
            ],
            [
                'first_name' => 'Leila',
                'last_name' => 'Camara',
                'email' => 'leila.camara@esgis.tg',
                'birth_date' => Carbon::createFromDate(2001, 4, 14),
                'phone' => '+228 99 01 23 45',
                'class_id' => Classes::where('name', 'L3 Informatique ESGIS Togo')->where('academic_year', '2024-2025')->first()->id,
                'is_active' => true,
                'metadata' => [
                    'address' => 'Rue de la Liberté, Lomé',
                    'emergency_contact' => 'M. Camara',
                    'emergency_phone' => '+228 90 12 34 56',
                    'nationality' => 'Togolaise',
                    'registration_date' => Carbon::createFromDate(2024, 9, 1),
                    'scholarship' => true,
                ],
            ],
        ];

        $created = 0;
        foreach ($students as $index => $studentData) {
            // Déterminer l'institution en fonction de l'email
            $institutionId = null;
            if (str_contains($studentData['email'], '@esgis.tg')) {
                $institutionId = \App\Models\Institution::where('name', 'École Supérieure de Gestion et d\'Informatique du Togo')->first()->id;
            }

            $user = User::firstOrCreate(
                ['email' => $studentData['email']],
                [
                    'name' => $studentData['first_name'] . ' ' . $studentData['last_name'],
                    'password' => Hash::make('password123'),
                    'account_type' => 'student',
                    'is_active' => true,
                ]
            );

            // Vérifier si l'étudiant existe déjà
            $existingStudent = Student::where('email', $studentData['email'])->first();
            
            if ($existingStudent) {
                // Utiliser le numéro d'étudiant existant
                $studentNumber = $existingStudent->student_number;
            } else {
                // Générer un nouveau numéro unique
                $baseNumber = 'STD' . date('Y');
                $counter = 1;
                do {
                    $studentNumber = $baseNumber . str_pad($counter, 4, '0', STR_PAD_LEFT);
                    $counter++;
                } while (Student::where('student_number', $studentNumber)->exists());
            }

            $student = Student::updateOrCreate(
                ['email' => $studentData['email']],
                array_merge($studentData, [
                    'user_id' => $user->id,
                    'student_number' => $studentNumber,
                    'institution_id' => $institutionId,
                ])
            );

            // Envoi de l'email de bienvenue (seulement pour les nouveaux étudiants)
            if ($student->wasRecentlyCreated) {
                try {
                    // $student->notify(new StudentWelcomeNotification($student, 'password123'));
                    $this->command->info("Email désactivé temporairement pour {$student->email}");
                } catch (\Exception $e) {
                    $this->command->error("Erreur envoi email à {$student->email}: {$e->getMessage()}");
                }
            }

            $created++;
        }

        $this->command->info('Étudiants créés/mis à jour avec succès!');
        $this->command->info('Total: ' . $created . ' étudiants');
        $this->command->info('Mot de passe par défaut: password123');
    }
}