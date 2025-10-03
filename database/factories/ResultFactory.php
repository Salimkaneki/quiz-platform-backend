<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Result>
 */
class ResultFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quizSession = \App\Models\QuizSession::factory()->create();
        $student = \App\Models\Student::factory()->create();
        
        $maxPoints = $quizSession->quiz->total_points ?? fake()->numberBetween(20, 100);
        $totalPoints = fake()->numberBetween(0, $maxPoints);
        $percentage = $maxPoints > 0 ? round(($totalPoints / $maxPoints) * 100, 2) : 0;
        $grade = round(($percentage / 100) * 20, 2); // Sur 20
        
        $status = fake()->randomElement(['in_progress', 'submitted', 'graded', 'published']);
        
        $startedAt = fake()->dateTimeBetween($quizSession->starts_at ?? '-1 hour', $quizSession->ends_at ?? 'now');
        $submittedAt = $status !== 'in_progress' ? fake()->dateTimeBetween($startedAt, $quizSession->ends_at ?? 'now') : null;
        $gradedAt = in_array($status, ['graded', 'published']) ? fake()->dateTimeBetween($submittedAt ?? $startedAt, '+1 day') : null;
        $publishedAt = $status === 'published' ? fake()->dateTimeBetween($gradedAt ?? $submittedAt ?? $startedAt, '+1 day') : null;
        
        return [
            'quiz_session_id' => $quizSession->id,
            'student_id' => $student->id,
            'total_points' => $totalPoints,
            'max_points' => $maxPoints,
            'percentage' => $percentage,
            'grade' => $grade,
            'status' => $status,
            'total_questions' => fake()->numberBetween(5, 20),
            'correct_answers' => fake()->numberBetween(0, 15),
            'time_spent_total' => fake()->numberBetween(300, 7200), // 5 minutes à 2 heures en secondes
            'started_at' => $startedAt,
            'submitted_at' => $submittedAt,
            'graded_at' => $gradedAt,
            'published_at' => $publishedAt,
            'detailed_stats' => [
                'time_per_question' => fake()->numberBetween(30, 300),
                'attempts_count' => fake()->numberBetween(1, 3),
                'hints_used' => fake()->numberBetween(0, 5),
                'difficulty_distribution' => [
                    'easy' => fake()->numberBetween(60, 100),
                    'medium' => fake()->numberBetween(40, 80),
                    'hard' => fake()->numberBetween(20, 60),
                ],
            ],
            'teacher_feedback' => fake()->optional(0.6)->paragraph(), // 60% des résultats ont un feedback
        ];
    }

    /**
     * Create a result that is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'submitted_at' => null,
            'graded_at' => null,
            'published_at' => null,
        ]);
    }

    /**
     * Create a submitted result.
     */
    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'submitted',
            'submitted_at' => fake()->dateTimeBetween($attributes['started_at'], 'now'),
            'graded_at' => null,
            'published_at' => null,
        ]);
    }

    /**
     * Create a graded result.
     */
    public function graded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'graded',
            'submitted_at' => fake()->dateTimeBetween($attributes['started_at'], '-1 day'),
            'graded_at' => fake()->dateTimeBetween($attributes['submitted_at'] ?? $attributes['started_at'], 'now'),
            'published_at' => null,
        ]);
    }

    /**
     * Create a published result.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'submitted_at' => fake()->dateTimeBetween($attributes['started_at'], '-2 days'),
            'graded_at' => fake()->dateTimeBetween($attributes['submitted_at'] ?? $attributes['started_at'], '-1 day'),
            'published_at' => fake()->dateTimeBetween($attributes['graded_at'] ?? $attributes['submitted_at'] ?? $attributes['started_at'], 'now'),
        ]);
    }

    /**
     * Create a perfect score result.
     */
    public function perfect(): static
    {
        return $this->state(function (array $attributes) {
            $maxPoints = $attributes['max_points'];
            return [
                'total_points' => $maxPoints,
                'percentage' => 100.00,
                'grade' => 20.00,
                'correct_answers' => $attributes['total_questions'],
            ];
        });
    }

    /**
     * Create a failed result.
     */
    public function failed(): static
    {
        return $this->state(function (array $attributes) {
            $maxPoints = $attributes['max_points'];
            $totalPoints = fake()->numberBetween(0, $maxPoints * 0.4); // Moins de 40%
            $percentage = $maxPoints > 0 ? round(($totalPoints / $maxPoints) * 100, 2) : 0;
            return [
                'total_points' => $totalPoints,
                'percentage' => $percentage,
                'grade' => round(($percentage / 100) * 20, 2),
                'correct_answers' => fake()->numberBetween(0, 2),
            ];
        });
    }

    /**
     * Create a result with high score.
     */
    public function excellent(): static
    {
        return $this->state(function (array $attributes) {
            $maxPoints = $attributes['max_points'];
            $totalPoints = fake()->numberBetween($maxPoints * 0.8, $maxPoints); // 80-100%
            $percentage = round(($totalPoints / $maxPoints) * 100, 2);
            return [
                'total_points' => $totalPoints,
                'percentage' => $percentage,
                'grade' => round(($percentage / 100) * 20, 2),
                'correct_answers' => fake()->numberBetween($attributes['total_questions'] * 0.8, $attributes['total_questions']),
            ];
        });
    }
}
