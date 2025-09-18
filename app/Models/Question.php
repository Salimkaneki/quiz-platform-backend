<?php
// app/Models/Question.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'question_text',
        'type',
        'options',
        'correct_answer',
        'points',
        'order',
        'explanation',
        'image_url',
        'time_limit',
        'metadata',
    ];

    protected $casts = [
        'options' => 'array',
        'points' => 'integer',
        'order' => 'integer',
        'time_limit' => 'integer',
        'metadata' => 'array',
    ];

    // Relations
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function studentResponses()
    {
        return $this->hasMany(StudentResponse::class);
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour ordonner les questions
     * Corrigé pour PostgreSQL - utilise des guillemets doubles au lieu de backticks
     */
    public function scopeOrdered($query)
    {
        // PostgreSQL utilise des guillemets doubles pour les identifiants, pas des backticks
        return $query->orderByRaw('COALESCE("order", 999999) ASC, id ASC');
    }

    /**
     * Scope pour récupérer toutes les questions d'un quiz
     */
    public function scopeAllQuestionsForQuiz($query, $quizId)
    {
        return $query->where('quiz_id', $quizId);
    }

    // Helper methods
    public function isMultipleChoice()
    {
        return $this->type === 'multiple_choice';
    }

    public function isTrueFalse()
    {
        return $this->type === 'true_false';
    }

    public function isOpenEnded()
    {
        return $this->type === 'open_ended';
    }

    public function isFillBlank()
    {
        return $this->type === 'fill_blank';
    }

    /**
     * Récupère l'option correcte pour les questions à choix multiples
     */
    public function getCorrectOptionAttribute()
    {
        if ($this->isMultipleChoice() && $this->options) {
            return collect($this->options)->where('is_correct', true)->first();
        }
        return null;
    }

    /**
     * Récupère toutes les options correctes (pour les QCM à réponses multiples)
     */
    public function getCorrectOptionsAttribute()
    {
        if ($this->isMultipleChoice() && $this->options) {
            return collect($this->options)->where('is_correct', true)->values()->toArray();
        }
        return [];
    }

    /**
     * Vérifie si une réponse est correcte
     */
    public function checkAnswer($studentAnswer)
    {
        switch ($this->type) {
            case 'multiple_choice':
                $correctOption = $this->getCorrectOptionAttribute();
                return $correctOption && $studentAnswer === $correctOption['text'];
                
            case 'true_false':
                return strtolower($studentAnswer) === strtolower($this->correct_answer);
                
            case 'open_ended':
                // Pour les questions ouvertes, nécessite une correction manuelle
                return null;
                
            case 'fill_blank':
                return strtolower(trim($studentAnswer)) === strtolower(trim($this->correct_answer));
                
            default:
                return false;
        }
    }

    /**
     * Calcule le score pour une réponse donnée
     */
    public function calculateScore($studentAnswer)
    {
        $isCorrect = $this->checkAnswer($studentAnswer);
        
        if ($isCorrect === true) {
            return $this->points ?? 1;
        } elseif ($isCorrect === false) {
            return 0;
        } else {
            // Réponse nécessitant une correction manuelle (questions ouvertes)
            return null;
        }
    }

    /**
     * Valide les options pour les questions à choix multiples
     */
    public function validateOptions()
    {
        if (!$this->isMultipleChoice()) {
            return true;
        }

        if (empty($this->options) || !is_array($this->options)) {
            return false;
        }

        // Vérifie qu'au moins une option est marquée comme correcte
        return collect($this->options)->contains('is_correct', true);
    }

    /**
     * Valide la réponse correcte pour les questions vrai/faux
     */
    public function validateTrueFalseAnswer()
    {
        if (!$this->isTrueFalse()) {
            return true;
        }

        $answer = strtolower(trim($this->correct_answer ?? ''));
        return in_array($answer, ['true', 'false', '1', '0']);
    }

    /**
     * Formate les options pour l'affichage
     */
    public function getFormattedOptionsAttribute()
    {
        if (!$this->isMultipleChoice() || empty($this->options)) {
            return [];
        }

        return collect($this->options)->map(function ($option, $index) {
            return [
                'id' => $index,
                'text' => $option['text'] ?? '',
                'is_correct' => $option['is_correct'] ?? false,
            ];
        })->values()->toArray();
    }
}