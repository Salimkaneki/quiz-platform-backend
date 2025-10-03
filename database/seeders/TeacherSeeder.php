<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Institution;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = [
            [
                'first_name' => 'Salim',
                'last_name' => 'Pereira',
                'email' => 'salimpereira01@gmail.com',
                'password' => 'motdepasse123',
                'specialization' => 'Informatique',
                'grade' => 'maître_de_conférences',
                'is_permanent' => true,
                'institution_code' => 'UL',
                'metadata' => [
                    'phone' => '+228 90 12 34 56',
                    'address' => 'Université de Lomé',
                    'hire_date' => Carbon::createFromDate(2020, 9, 1),
                    'department' => 'Informatique',
                    'research_interests' => ['Intelligence Artificielle', 'Développement Web', 'Base de données'],
                ],
            ],
            [
                'first_name' => 'Marie',
                'last_name' => 'Kouassi',
                'email' => 'marie.kouassi@ul.edu.tg',
                'password' => 'password123',
                'specialization' => 'Mathématiques',
                'grade' => 'professeur',
                'is_permanent' => true,
                'institution_code' => 'UL',
                'metadata' => [
                    'phone' => '+228 91 23 45 67',
                    'address' => 'Université de Lomé',
                    'hire_date' => Carbon::createFromDate(2018, 9, 1),
                    'department' => 'Mathématiques',
                    'research_interests' => ['Algèbre', 'Analyse', 'Statistiques'],
                ],
            ],
            [
                'first_name' => 'Jean',
                'last_name' => 'Diallo',
                'email' => 'jean.diallo@ul.edu.tg',
                'password' => 'password123',
                'specialization' => 'Physique',
                'grade' => 'certifié',
                'is_permanent' => true,
                'institution_code' => 'UL',
                'metadata' => [
                    'phone' => '+228 92 34 56 78',
                    'address' => 'Université de Lomé',
                    'hire_date' => Carbon::createFromDate(2021, 9, 1),
                    'department' => 'Physique-Chimie',
                    'research_interests' => ['Mécanique', 'Électromagnétisme'],
                ],
            ],
            [
                'first_name' => 'Fatima',
                'last_name' => 'Traore',
                'email' => 'fatima.traore@ul.edu.tg',
                'password' => 'password123',
                'specialization' => 'Chimie',
                'grade' => 'maître_de_conférences',
                'is_permanent' => true,
                'institution_code' => 'UL',
                'metadata' => [
                    'phone' => '+228 93 45 67 89',
                    'address' => 'Université de Lomé',
                    'hire_date' => Carbon::createFromDate(2019, 9, 1),
                    'department' => 'Chimie',
                    'research_interests' => ['Chimie Organique', 'Biochimie'],
                ],
            ],
            [
                'first_name' => 'Ahmed',
                'last_name' => 'Bamba',
                'email' => 'ahmed.bamba@ul.edu.tg',
                'password' => 'password123',
                'specialization' => 'Génie Civil',
                'grade' => 'certifié',
                'is_permanent' => false,
                'institution_code' => 'UL',
                'metadata' => [
                    'phone' => '+228 94 56 78 90',
                    'address' => 'Université de Lomé',
                    'hire_date' => Carbon::createFromDate(2022, 9, 1),
                    'department' => 'Génie Civil',
                    'research_interests' => ['Structures', 'Matériaux'],
                ],
            ],
            [
                'first_name' => 'Aminata',
                'last_name' => 'Sow',
                'email' => 'aminata.sow@ul.edu.tg',
                'password' => 'password123',
                'specialization' => 'Médecine',
                'grade' => 'professeur',
                'is_permanent' => true,
                'institution_code' => 'UL',
                'metadata' => [
                    'phone' => '+228 95 67 89 01',
                    'address' => 'Université de Lomé',
                    'hire_date' => Carbon::createFromDate(2015, 9, 1),
                    'department' => 'Médecine',
                    'research_interests' => ['Anatomie', 'Physiologie'],
                ],
            ],
            [
                'first_name' => 'Paul',
                'last_name' => 'Konate',
                'email' => 'paul.konate@ism.tg',
                'password' => 'password123',
                'specialization' => 'Développement Web',
                'grade' => 'vacataire',
                'is_permanent' => false,
                'institution_code' => 'ISM-TOGO',
                'metadata' => [
                    'phone' => '+228 96 78 90 12',
                    'address' => 'ISM-TOGO, Lomé',
                    'hire_date' => Carbon::createFromDate(2023, 9, 1),
                    'department' => 'Informatique',
                    'research_interests' => ['Full Stack Development', 'JavaScript', 'React'],
                ],
            ],
            [
                'first_name' => 'Sophie',
                'last_name' => 'Ouattara',
                'email' => 'sophie.ouattara@ism.tg',
                'password' => 'password123',
                'specialization' => 'Électronique',
                'grade' => 'certifié',
                'is_permanent' => true,
                'institution_code' => 'ISM-TOGO',
                'metadata' => [
                    'phone' => '+228 97 89 01 23',
                    'address' => 'ISM-TOGO, Lomé',
                    'hire_date' => Carbon::createFromDate(2021, 9, 1),
                    'department' => 'Électronique',
                    'research_interests' => ['Circuits', 'Microcontrôleurs'],
                ],
            ],
            [
                'first_name' => 'Michel',
                'last_name' => 'Camara',
                'email' => 'michel.camara@ism.tg',
                'password' => 'password123',
                'specialization' => 'Comptabilité',
                'grade' => 'maître_de_conférences',
                'is_permanent' => true,
                'institution_code' => 'ISM-TOGO',
                'metadata' => [
                    'phone' => '+228 98 90 12 34',
                    'address' => 'ISM-TOGO, Lomé',
                    'hire_date' => Carbon::createFromDate(2019, 9, 1),
                    'department' => 'Comptabilité',
                    'research_interests' => ['Comptabilité Financière', 'Audit'],
                ],
            ],
            [
                'first_name' => 'Claire',
                'last_name' => 'Diouf',
                'email' => 'claire.diouf@ul.edu.tg',
                'password' => 'password123',
                'specialization' => 'Anglais',
                'grade' => 'certifié',
                'is_permanent' => true,
                'institution_code' => 'UL',
                'metadata' => [
                    'phone' => '+228 99 01 23 45',
                    'address' => 'Université de Lomé',
                    'hire_date' => Carbon::createFromDate(2020, 9, 1),
                    'department' => 'Langues',
                    'research_interests' => ['Linguistique', 'Littérature Anglaise'],
                ],
            ],
        ];

        $created = 0;
        foreach ($teachers as $teacherData) {
            $institution = Institution::where('code', $teacherData['institution_code'])->first();

            if (!$institution) {
                $this->command->error("Institution {$teacherData['institution_code']} non trouvée pour {$teacherData['first_name']} {$teacherData['last_name']}");
                continue;
            }

            $user = User::firstOrCreate(
                ['email' => $teacherData['email']],
                [
                    'name' => $teacherData['first_name'] . ' ' . $teacherData['last_name'],
                    'password' => Hash::make($teacherData['password']),
                    'account_type' => 'teacher',
                    'is_active' => true,
                ]
            );

            $teacher = Teacher::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'institution_id' => $institution->id,
                    'specialization' => $teacherData['specialization'],
                    'grade' => $teacherData['grade'],
                    'is_permanent' => $teacherData['is_permanent'],
                    'metadata' => $teacherData['metadata'],
                ]
            );

            $created++;
        }

        $this->command->info('Professeurs créés/mis à jour avec succès!');
        $this->command->info('Total: ' . $created . ' professeurs');
        $this->command->info('Mot de passe par défaut: password123 (sauf pour salimpereira01@gmail.com: motdepasse123)');
    }
}