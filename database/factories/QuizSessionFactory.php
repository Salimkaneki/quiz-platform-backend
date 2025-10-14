<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuizSession>
 */
class QuizSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quiz = \App\Models\Quiz::factory()->create();
        $startsAt = fake()->dateTimeBetween('now', '+1 week');
        $endsAt = fake()->dateTimeBetween($startsAt, (clone $startsAt)->modify('+4 hours'));
        
        return [
            'quiz_id' => $quiz->id,
            'teacher_id' => $quiz->teacher_id,
            'session_code' => strtoupper(fake()->unique()->lexify('??????')),
            'title' => $quiz->title . ' - Session ' . fake()->date('m/d'),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
                        'status' => fake()->randomElement(['scheduled', 'active', 'paused', 'completed', 'cancelled']),
            'allowed_students' => \App\Models\Student::count() > 0 && fake()->boolean(30) ? 
                fake()->randomElements(
                    \App\Models\Student::pluck('id')->toArray(), 
                    min(fake()->numberBetween(5, 20), \App\Models\Student::count())
                ) : null,
            'max_participants' => fake()->numberBetween(10, 100),
            'require_student_list' => fake()->boolean(40), // 40% nécessitent une liste d'étudiants
            'duration_override' => fake()->optional(0.2)->numberBetween(15, 180), // 20% ont une durée différente
            'attempts_allowed' => fake()->numberBetween(1, 3),
            'settings' => [
                'show_progress' => fake()->boolean(80),
                'allow_pause' => fake()->boolean(60),
                'time_warning' => fake()->boolean(70),
                'auto_submit' => fake()->boolean(50),
                'shuffle_questions' => fake()->boolean(30),
                'show_results' => fake()->randomElement(['immediate', 'after_completion', 'manual']),
            ],
            'activated_at' => null,
            'completed_at' => null,
        ];
    }

    /**
     * Create an active session.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'activated_at' => fake()->dateTimeBetween('-1 hour', 'now'),
        ]);
    }

    /**
     * Create a scheduled session.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'starts_at' => fake()->dateTimeBetween('+1 hour', '+1 week'),
        ]);
    }

    /**
     * Create a completed session.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'starts_at' => fake()->dateTimeBetween('-4 hours', '-3 hours'),
            'ends_at' => fake()->dateTimeBetween('-2 hours', '-1 hour'),
            'activated_at' => fake()->dateTimeBetween('-2 hours', '-1 hour'),
            'completed_at' => fake()->dateTimeBetween('-1 hour', 'now'),
        ]);
    }

    /**
     * Create an open access session.
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'allowed_students' => null,
        ]);
    }

    /**
     * Create a restricted access session.
     */
    public function restricted(): static
    {
        return $this->state(fn (array $attributes) => [
            'allowed_students' => \App\Models\Student::count() > 0 ? fake()->randomElements(
                \App\Models\Student::pluck('id')->toArray(), 
                min(fake()->numberBetween(10, 30), \App\Models\Student::count())
            ) : null,
        ]);
    }

    /**
     * Create a session with time override.
     */
    public function withTimeOverride(int $minutes): static
    {
        return $this->state(fn (array $attributes) => [
            'duration_override' => $minutes,
        ]);
    }

    /**
     * Set specific start time.
     */
    public function startsAt(\DateTime $dateTime): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_at' => $dateTime,
            'ends_at' => fake()->dateTimeBetween($dateTime, (clone $dateTime)->modify('+4 hours')),
        ]);
    }
}
