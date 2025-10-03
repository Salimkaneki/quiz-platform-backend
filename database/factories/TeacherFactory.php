<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Teacher>
 */
class TeacherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $institution = \App\Models\Institution::factory()->create();
        
        $specializations = [
            'Mathématiques',
            'Physique',
            'Chimie',
            'Informatique',
            'Anglais',
            'Français',
            'Histoire',
            'Géographie',
            'Économie',
            'Philosophie',
            'Biologie',
            'Sciences de la Vie et de la Terre'
        ];
        
        $grades = ['vacataire', 'certifié', 'agrégé', 'maître_de_conférences', 'professeur'];
        
        return [
            'user_id' => \App\Models\User::factory()->teacher(),
            'institution_id' => $institution->id,
            'specialization' => fake()->randomElement($specializations),
            'grade' => fake()->randomElement($grades),
            'is_permanent' => fake()->boolean(70), // 70% sont permanents
            'metadata' => [
                'hire_date' => fake()->dateTimeBetween('-20 years', '-1 year'),
                'experience_years' => fake()->numberBetween(1, 30),
                'publications' => fake()->optional(0.3)->numberBetween(0, 20), // 30% ont des publications
                'certifications' => fake()->optional(0.5)->words(2, true), // 50% ont des certifications
            ],
        ];
    }

    /**
     * Create a permanent teacher.
     */
    public function permanent(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_permanent' => true,
            'grade' => fake()->randomElement(['certifié', 'agrégé', 'maître_de_conférences', 'professeur']),
        ]);
    }

    /**
     * Create a contract teacher.
     */
    public function contract(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_permanent' => false,
            'grade' => 'vacataire',
        ]);
    }

    /**
     * Create a professor.
     */
    public function professor(): static
    {
        return $this->state(fn (array $attributes) => [
            'grade' => 'professeur',
            'is_permanent' => true,
        ]);
    }

    /**
     * Create a certified teacher.
     */
    public function certified(): static
    {
        return $this->state(fn (array $attributes) => [
            'grade' => 'certifié',
            'is_permanent' => true,
        ]);
    }

    /**
     * Set specialization.
     */
    public function specialization(string $subject): static
    {
        return $this->state(fn (array $attributes) => [
            'specialization' => $subject,
        ]);
    }
}
