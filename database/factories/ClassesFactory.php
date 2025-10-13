<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Classes>
 */
class ClassesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $formation = \App\Models\Formation::factory()->create();
        
        return [
            'name' => fake()->randomElement(['A', 'B', 'C', 'D']) . fake()->numberBetween(1, 3),
            'level' => fake()->numberBetween(1, 5),
            'academic_year' => '2024-2025',
            'formation_id' => $formation->id,
            'institution_id' => $formation->institution_id,
            'max_students' => fake()->numberBetween(20, 40),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the class is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a first year class.
     */
    public function firstYear(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => 1,
            'name' => '1' . fake()->randomElement(['A', 'B', 'C']),
        ]);
    }

    /**
     * Create a second year class.
     */
    public function secondYear(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => 2,
            'name' => '2' . fake()->randomElement(['A', 'B', 'C']),
        ]);
    }

    /**
     * Create a third year class.
     */
    public function thirdYear(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => 3,
            'name' => '3' . fake()->randomElement(['A', 'B', 'C']),
        ]);
    }

    /**
     * Set the academic year.
     */
    public function academicYear(string $year): static
    {
        return $this->state(fn (array $attributes) => [
            'academic_year' => $year,
        ]);
    }
}
