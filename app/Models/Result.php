<?php
// app/Models/Result.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;

        // Ajouter les constantes pour les statuts
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_GRADED = 'graded';
    const STATUS_PUBLISHED = 'published';

    // Définir les statuts valides
    public static $validStatuses = [
        self::STATUS_IN_PROGRESS,
        self::STATUS_SUBMITTED,
        self::STATUS_GRADED,
        self::STATUS_PUBLISHED
    ];

    protected $fillable = [
        'quiz_session_id',
        'student_id',
        'total_points',
        'max_points',
        'percentage',
        'grade',
        'status',
        'total_questions',
        'correct_answers',
        'time_spent_total',
        'started_at',
        'submitted_at',
        'graded_at',
        'published_at',
        'detailed_stats',
        'teacher_feedback',
    ];

    protected $casts = [
        'total_points' => 'decimal:2',
        'max_points' => 'decimal:2',
        'percentage' => 'decimal:2',
        'grade' => 'decimal:2',
        'total_questions' => 'integer',
        'correct_answers' => 'integer',
        'time_spent_total' => 'integer',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'published_at' => 'datetime',
        'detailed_stats' => 'array',
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

    public function studentResponses()
    {
        return $this->hasMany(StudentResponse::class, 'quiz_session_id', 'quiz_session_id')
                    ->where('student_id', $this->student_id);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeGraded($query)
    {
        return $query->where('status', 'graded');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    // Helper methods
    public function calculateGrade($maxGrade = 20)
    {
        return ($this->percentage / 100) * $maxGrade;
    }

    public function updateFromResponses()
    {
        $responses = $this->studentResponses;
        
        $totalPoints = $responses->sum('points_earned');
        $maxPoints = $responses->sum('points_possible');
        $correctAnswers = $responses->where('is_correct', true)->count();
        $totalQuestions = $responses->count();
        $percentage = $maxPoints > 0 ? ($totalPoints / $maxPoints) * 100 : 0;

        $this->update([
            'total_points' => $totalPoints,
            'max_points' => $maxPoints,
            'percentage' => $percentage,
            'grade' => $this->calculateGrade(),
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
        ]);
    }


    public function isCompleted()
    {
        return in_array($this->status, [
            self::STATUS_SUBMITTED,
            self::STATUS_GRADED,
            self::STATUS_PUBLISHED
        ]);
    }
    public function canBeViewed()
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    // Ajouter des méthodes utilitaires
    public function markAsSubmitted()
    {
        $this->status = self::STATUS_SUBMITTED;
        $this->submitted_at = now();
        $this->save();
    }






    // Ajouter les règles de validation
    public static function boot()
    {
        parent::boot();
        
        static::saving(function ($result) {
            if (!in_array($result->status, self::$validStatuses)) {
                throw new \InvalidArgumentException('Invalid status value');
            }
        });
    }

    // Modifier les méthodes pour utiliser les constantes
    public function isInProgress()
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }






    public function markAsGraded()
    {
        $this->status = self::STATUS_GRADED;
        $this->graded_at = now();
        $this->save();
    }

    public function markAsPublished()
    {
        $this->status = self::STATUS_PUBLISHED;
        $this->published_at = now();
        $this->save();
    }

}