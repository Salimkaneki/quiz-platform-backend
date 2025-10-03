<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Formation>
 */
class FormationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Informatique',
            'Mathématiques',
            'Physique-Chimie',
            'Lettres Modernes',
            'Sciences Économiques',
            'Droit',
            'Médecine',
            'Ingénierie',
            'Arts Plastiques',
            'Langues Étrangères'
        ]);
        
        $code = strtoupper(fake()->unique()->lexify('???'));
        
        return [
            'name' => $name,
            'code' => $code,
            'description' => fake()->sentence(),
            'duration_years' => fake()->numberBetween(1, 5),
            'institution_id' => \App\Models\Institution::factory(),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the formation is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a bachelor's degree formation.
     */
    public function bachelor(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->randomElement(['Licence Informatique', 'Licence Mathématiques', 'Licence Physique']),
            'duration_years' => 3,
        ]);
    }

    /**
     * Create a master's degree formation.
     */
    public function master(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->randomElement(['Master Informatique', 'Master Sciences', 'Master Arts']),
            'duration_years' => 2,
        ]);
    }
}
