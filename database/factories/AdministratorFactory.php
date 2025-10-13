<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Institution;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Administrator>
 */
class AdministratorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null, // Sera défini lors de la création
            'institution_id' => Institution::factory(),
            'type' => 'pedagogique',
        ];
    }

    /**
     * Create an administrative administrator
     */
    public function administratif(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'administratif',
        ]);
    }

    /**
     * Create a pedagogical administrator
     */
    public function pedagogique(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'pedagogique',
        ]);
    }
}