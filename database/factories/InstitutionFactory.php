<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Institution>
 */
class InstitutionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();
        $code = strtoupper(fake()->unique()->lexify('???'));
        
        return [
            'name' => $name,
            'code' => $code,
            'slug' => \Illuminate\Support\Str::slug($code),
            'description' => fake()->sentence(),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->companyEmail(),
            'website' => fake()->url(),
            'timezone' => fake()->randomElement(['Europe/Paris', 'America/New_York', 'Asia/Tokyo', 'Africa/Cairo']),
            'settings' => [
                'max_students_per_class' => fake()->numberBetween(20, 50),
                'allow_self_registration' => fake()->boolean(),
                'academic_year_start' => '09-01',
                'academic_year_end' => '06-30',
            ],
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the institution is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a university type institution.
     */
    public function university(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->company() . ' University',
            'settings' => array_merge($attributes['settings'] ?? [], [
                'type' => 'university',
                'max_students_per_class' => fake()->numberBetween(30, 60),
            ]),
        ]);
    }

    /**
     * Create a school type institution.
     */
    public function school(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->company() . ' School',
            'settings' => array_merge($attributes['settings'] ?? [], [
                'type' => 'school',
                'max_students_per_class' => fake()->numberBetween(20, 35),
            ]),
        ]);
    }
}
