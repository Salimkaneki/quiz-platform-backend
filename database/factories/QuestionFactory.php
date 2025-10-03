<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['multiple_choice', 'true_false', 'open_ended', 'fill_blank']);
        
        $questionData = $this->generateQuestionData($type);
        
        return [
            'quiz_id' => \App\Models\Quiz::factory(),
            'question_text' => $questionData['question'],
            'type' => $type,
            'options' => $questionData['options'],
            'correct_answer' => $questionData['correct_answer'],
            'points' => fake()->numberBetween(1, 5),
            'order' => fake()->numberBetween(1, 100),
            'explanation' => fake()->optional(0.7)->sentence(), // 70% des questions ont une explication
            'image_url' => fake()->optional(0.2)->imageUrl(), // 20% des questions ont une image
            'time_limit' => fake()->optional(0.3)->numberBetween(30, 300), // 30% ont une limite de temps
            'metadata' => [
                'difficulty' => fake()->randomElement(['easy', 'medium', 'hard']),
                'tags' => fake()->words(2, false),
                'created_by_ai' => fake()->boolean(10), // 10% générées par IA
            ],
        ];
    }

    /**
     * Generate question data based on type.
     */
    private function generateQuestionData(string $type): array
    {
        switch ($type) {
            case 'multiple_choice':
                $correctAnswer = fake()->randomElement(['A', 'B', 'C', 'D']);
                return [
                    'question' => fake()->sentence() . ' ?',
                    'options' => $this->generateMultipleChoiceOptions($correctAnswer),
                    'correct_answer' => $correctAnswer,
                ];
                
            case 'true_false':
                $correctAnswer = fake()->boolean() ? 'true' : 'false';
                return [
                    'question' => fake()->sentence() . ' (Vrai ou Faux) ?',
                    'options' => null,
                    'correct_answer' => $correctAnswer,
                ];
                
            case 'open_ended':
                return [
                    'question' => 'Expliquez : ' . fake()->sentence(),
                    'options' => null,
                    'correct_answer' => fake()->paragraph(),
                ];
                
            case 'fill_blank':
                $answer = fake()->word();
                return [
                    'question' => 'Complétez : Le ______ est la capitale de la France.',
                    'options' => null,
                    'correct_answer' => 'Paris',
                ];
                
            default:
                return [
                    'question' => fake()->sentence(),
                    'options' => null,
                    'correct_answer' => 'answer',
                ];
        }
    }

    /**
     * Generate multiple choice options.
     */
    private function generateMultipleChoiceOptions(string $correctAnswer): array
    {
        $options = [];
        $letters = ['A', 'B', 'C', 'D'];
        
        foreach ($letters as $letter) {
            $options[] = [
                'text' => fake()->sentence(3),
                'is_correct' => $letter === $correctAnswer,
            ];
        }
        
        return $options;
    }

    /**
     * Create a multiple choice question.
     */
    public function multipleChoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'multiple_choice',
            ...$this->generateQuestionData('multiple_choice')
        ]);
    }

    /**
     * Create a true/false question.
     */
    public function trueFalse(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'true_false',
            ...$this->generateQuestionData('true_false')
        ]);
    }

    /**
     * Create an open-ended question.
     */
    public function openEnded(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'open_ended',
            ...$this->generateQuestionData('open_ended')
        ]);
    }

    /**
     * Create a fill-in-the-blank question.
     */
    public function fillBlank(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'fill_blank',
            ...$this->generateQuestionData('fill_blank')
        ]);
    }

    /**
     * Create an easy question.
     */
    public function easy(): static
    {
        return $this->state(fn (array $attributes) => [
            'points' => fake()->numberBetween(1, 2),
            'metadata' => array_merge($attributes['metadata'] ?? [], ['difficulty' => 'easy']),
        ]);
    }

    /**
     * Create a hard question.
     */
    public function hard(): static
    {
        return $this->state(fn (array $attributes) => [
            'points' => fake()->numberBetween(4, 5),
            'metadata' => array_merge($attributes['metadata'] ?? [], ['difficulty' => 'hard']),
        ]);
    }
}
