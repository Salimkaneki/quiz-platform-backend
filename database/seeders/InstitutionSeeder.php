<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InstitutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $institutions = [
            [
                'name' => 'École Supérieure de Gestion et d\'Informatique du Togo',
                'code' => 'ESGIS-TOGO',
                'slug' => 'esgis-togo',
                'description' => 'Institution d\'excellence spécialisée dans la formation en informatique et technologies numériques au Togo.',
                'address' => 'Lomé, Togo',
                'phone' => '+228 22 25 30 61',
                'email' => 'contact@esgis.tg',
                'website' => 'https://www.esgis.tg',
                'timezone' => 'Africa/Lome',
                'settings' => json_encode([
                    'academic_year_start' => '10-01',
                    'academic_year_end' => '09-30',
                    'max_students_per_class' => 35,
                    'languages' => ['fr', 'en'],
                    'specializations' => ['informatique'],
                    'notification_preferences' => [
                        'email' => true,
                        'sms' => true
                    ]
                ]),
                'is_active' => true,
            ]
        ];

        $created = 0;
        foreach ($institutions as $institutionData) {
            $institution = \App\Models\Institution::updateOrCreate(
                ['code' => $institutionData['code']],
                $institutionData
            );
            $created++;
        }

        $this->command->info('Institutions créées/mises à jour avec succès!');
        $this->command->info('Total: ' . $created . ' institutions');
    }
}