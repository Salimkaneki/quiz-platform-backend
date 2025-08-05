<?php
// app/Models/StudentResponse.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_session_id',
        'student_id',
        'question_id',
        'answer',
        'is_correct',
        'points_earned',
        'points_possible',
        'time_spent',
        'answered_at',
        'reviewed_at',
        'reviewed_by',
        'teacher_comment',
    ];

    protected $casts = [
        'answer' => 'array',
        'is_correct' => 'boolean',
        'points_earned' => 'decimal:2',
        'points_possible' => 'decimal:2',
        'time_spent' => 'integer',
        'answered_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    // Relations
    public function quizSession()
    {
        return $this->belongsTo(QuizSession::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes
    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    public function scopeIncorrect($query)
    {
        return $query->where('is_correct', false);
    }

    public function scopePendingReview($query)
    {
        return $query->whereNull('is_correct');
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeBySession($query, $sessionId)
    {
        return $query->where('quiz_session_id', $sessionId);
    }

    // Helper methods
    public function autoGrade()
    {
        if ($this->question->type === 'open_ended') {
            return; // Nécessite correction manuelle
        }

        $isCorrect = $this->question->checkAnswer($this->getAnswerText());
        
        $this->update([
            'is_correct' => $isCorrect,
            'points_earned' => $isCorrect ? $this->points_possible : 0,
        ]);
    }

    public function getAnswerText()
    {
        if (is_array($this->answer)) {
            return $this->answer['text'] ?? $this->answer[0] ?? '';
        }
        return $this->answer;
    }

    public function needsManualReview()
    {
        return $this->question->type === 'open_ended' && is_null($this->is_correct);
    }

    public function isReviewed()
    {
        return !is_null($this->reviewed_at);
    }
}