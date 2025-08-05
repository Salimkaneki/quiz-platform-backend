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

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
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

    public function getCorrectOptionAttribute()
    {
        if ($this->isMultipleChoice() && $this->options) {
            return collect($this->options)->where('is_correct', true)->first();
        }
        return null;
    }

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
}