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
                'email' => 'salim.pereira@esgis.tg',
                'password' => 'motdepasse123',
                'specialization' => 'Informatique',
                'grade' => 'maître_de_conférences',
                'is_permanent' => true,
                'institution_code' => 'ESGIS-TOGO',
                'metadata' => [
                    'phone' => '+228 90 12 34 56',
                    'address' => 'Lomé, Togo',
                    'hire_date' => Carbon::createFromDate(2020, 9, 1),
                    'department' => 'Informatique',
                    'research_interests' => ['Intelligence Artificielle', 'Développement Web', 'Base de données'],
                ],
            ],
            [
                'first_name' => 'Sophie',
                'last_name' => 'Ouattara',
                'email' => 'sophie.ouattara@esgis.tg',
                'password' => 'password123',
                'specialization' => 'Informatique',
                'grade' => 'professeur',
                'is_permanent' => true,
                'institution_code' => 'ESGIS-TOGO',
                'metadata' => [
                    'phone' => '+228 91 23 45 67',
                    'address' => 'Lomé, Togo',
                    'hire_date' => Carbon::createFromDate(2018, 9, 1),
                    'department' => 'Informatique',
                    'research_interests' => ['Cybersécurité', 'Réseaux', 'Cloud Computing'],
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