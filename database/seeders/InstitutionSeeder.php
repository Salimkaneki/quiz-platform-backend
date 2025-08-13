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
                'name' => 'Université de Lomé',
                'code' => 'UL',
                'slug' => 'ul',
                'description' => 'Première université publique du Togo, créée en 1970. Elle offre une formation de qualité dans diverses disciplines.',
                'address' => 'Boulevard Eyadema, BP 1515, Lomé, Togo',
                'phone' => '+228 22 25 30 61',
                'email' => 'rectorat@univ-lome.tg',
                'website' => 'https://www.univ-lome.tg',
                'timezone' => 'Africa/Lome',
                'settings' => json_encode([
                    'academic_year_start' => '09-01',
                    'academic_year_end' => '07-31',
                    'max_students_per_class' => 50,
                    'languages' => ['fr', 'en'],
                    'notification_preferences' => [
                        'email' => true,
                        'sms' => false
                    ]
                ]),
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Institut Supérieur de Métiers',
                'code' => 'ISM-TOGO',
                'slug' => 'ism-togo',
                'description' => 'Institut spécialisé dans la formation professionnelle et technique, orienté vers l\'insertion professionnelle.',
                'address' => 'Rue des Artisans, Tokoin Wuiti, Lomé, Togo',
                'phone' => '+228 22 25 15 47',
                'email' => 'direction@ism-togo.com',
                'website' => 'https://www.ism-togo.com',
                'timezone' => 'Africa/Lome',
                'settings' => json_encode([
                    'academic_year_start' => '10-01',
                    'academic_year_end' => '08-31',
                    'max_students_per_class' => 30,
                    'languages' => ['fr'],
                    'specializations' => ['informatique', 'electricite', 'mecanique', 'batiment'],
                    'notification_preferences' => [
                        'email' => true,
                        'sms' => true
                    ]
                ]),
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ];

        // Utilisation de la façade DB pour insérer les données
        DB::table('institutions')->insert($institutions);

        $this->command->info('Institutions créées avec succès!');
        $this->command->info('Total: ' . count($institutions) . ' institutions');
    }
}