<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subject>
 */
class SubjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subjects = [
            'Mathématiques' => ['code' => 'MATH', 'type' => 'cours'],
            'Physique' => ['code' => 'PHYS', 'type' => 'cours'],
            'Chimie' => ['code' => 'CHIM', 'type' => 'cours'],
            'Informatique' => ['code' => 'INFO', 'type' => 'cours'],
            'Anglais' => ['code' => 'ANGL', 'type' => 'td'],
            'Français' => ['code' => 'FRAN', 'type' => 'td'],
            'Histoire' => ['code' => 'HIST', 'type' => 'cours'],
            'Géographie' => ['code' => 'GEOG', 'type' => 'cours'],
            'Économie' => ['code' => 'ECON', 'type' => 'cours'],
            'Philosophie' => ['code' => 'PHIL', 'type' => 'cours'],
        ];
        
        $subjectData = fake()->randomElement($subjects);
        
        return [
            'name' => array_key_first($subjects),
            'code' => $subjectData['code'] . fake()->unique()->numberBetween(100, 999),
            'description' => fake()->sentence(),
            'credits' => fake()->numberBetween(2, 6),
            'coefficient' => fake()->numberBetween(1, 4),
            'type' => $subjectData['type'],
            'formation_id' => \App\Models\Formation::factory(),
            'semester' => fake()->numberBetween(1, 6),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the subject is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a fundamental subject.
     */
    public function fundamental(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->randomElement(['Mathématiques', 'Physique', 'Chimie', 'Informatique']),
            'type' => 'cours',
            'coefficient' => fake()->numberBetween(3, 4),
        ]);
    }

    /**
     * Create a language subject.
     */
    public function language(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->randomElement(['Anglais', 'Français', 'Espagnol', 'Allemand']),
            'type' => 'td',
            'coefficient' => fake()->numberBetween(1, 2),
        ]);
    }

    /**
     * Create a humanities subject.
     */
    public function humanities(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->randomElement(['Histoire', 'Géographie', 'Philosophie', 'Littérature']),
            'type' => 'cours',
            'coefficient' => fake()->numberBetween(2, 3),
        ]);
    }
}
