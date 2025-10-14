<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        
        return [
            'student_number' => 'STU' . fake()->unique()->numberBetween(10000, 99999),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => fake()->unique()->safeEmail(),
            'birth_date' => fake()->dateTimeBetween('-25 years', '-18 years'),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'emergency_contact' => fake()->name(),
            'emergency_phone' => fake()->phoneNumber(),
            'medical_info' => fake()->optional(0.1)->sentence(), // 10% des étudiants ont des infos médicales
            'preferences' => [
                'theme' => fake()->randomElement(['light', 'dark']),
                'language' => fake()->randomElement(['fr', 'en']),
                'notifications' => fake()->boolean(),
            ],
            'profile_picture' => null,
            'class_id' => \App\Models\Classes::factory(),
            'institution_id' => function (array $attributes) {
                return \App\Models\Classes::find($attributes['class_id'])->institution_id;
            },
            'is_active' => true,
            'metadata' => [
                'enrollment_date' => fake()->dateTimeBetween('-2 years', 'now'),
                'gpa' => fake()->optional()->randomFloat(2, 0, 4),
            ],
            'user_id' => \App\Models\User::factory()->student(),
        ];
    }

    /**
     * Indicate that the student is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a student with medical information.
     */
    public function withMedicalInfo(): static
    {
        return $this->state(fn (array $attributes) => [
            'medical_info' => fake()->sentence(),
        ]);
    }

    /**
     * Create a student with profile picture.
     */
    public function withProfilePicture(): static
    {
        return $this->state(fn (array $attributes) => [
            'profile_picture' => 'uploads/profiles/profile_' . fake()->uuid() . '.jpg',
        ]);
    }

    /**
     * Create a student with dark theme preference.
     */
    public function darkTheme(): static
    {
        return $this->state(fn (array $attributes) => [
            'preferences' => array_merge($attributes['preferences'] ?? [], ['theme' => 'dark']),
        ]);
    }
}
