<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quiz>
 */
class QuizFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $teacher = \App\Models\Teacher::factory()->create();
        $subject = \App\Models\Subject::factory()->create();
        
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->user_id,
            'duration_minutes' => fake()->numberBetween(15, 120),
            'total_points' => fake()->numberBetween(10, 100),
            'shuffle_questions' => fake()->boolean(30), // 30% des quiz mélangent les questions
            'show_results_immediately' => fake()->boolean(70), // 70% montrent les résultats immédiatement
            'allow_review' => fake()->boolean(60), // 60% permettent la révision
            'status' => fake()->randomElement(['draft', 'published']),
            'settings' => [
                'max_attempts' => fake()->numberBetween(1, 3),
                'time_limit' => fake()->boolean(80), // 80% ont une limite de temps
                'randomize_answers' => fake()->boolean(40), // 40% randomisent les réponses
                'show_correct_answers' => fake()->boolean(50), // 50% montrent les bonnes réponses
                'passing_score' => fake()->numberBetween(50, 80), // Score minimum pour réussir
            ],
        ];
    }

    /**
     * Create a published quiz.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }

    /**
     * Create a draft quiz.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    /**
     * Create a quiz with shuffled questions.
     */
    public function shuffled(): static
    {
        return $this->state(fn (array $attributes) => [
            'shuffle_questions' => true,
        ]);
    }

    /**
     * Create a short quiz (15-30 minutes).
     */
    public function short(): static
    {
        return $this->state(fn (array $attributes) => [
            'duration_minutes' => fake()->numberBetween(15, 30),
            'total_points' => fake()->numberBetween(10, 30),
        ]);
    }

    /**
     * Create a long quiz (60-120 minutes).
     */
    public function long(): static
    {
        return $this->state(fn (array $attributes) => [
            'duration_minutes' => fake()->numberBetween(60, 120),
            'total_points' => fake()->numberBetween(50, 100),
        ]);
    }

    /**
     * Set specific duration.
     */
    public function duration(int $minutes): static
    {
        return $this->state(fn (array $attributes) => [
            'duration_minutes' => $minutes,
        ]);
    }
}
