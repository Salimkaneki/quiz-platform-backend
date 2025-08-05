<?php
// app/Models/Quiz.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'subject_id',
        'teacher_id',
        'duration_minutes',
        'total_points',
        'shuffle_questions',
        'show_results_immediately',
        'allow_review',
        'status',
        'settings',
    ];

    protected $casts = [
        'shuffle_questions' => 'boolean',
        'show_results_immediately' => 'boolean',
        'allow_review' => 'boolean',
        'settings' => 'array',
    ];

    // Relations
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function sessions()
    {
        return $this->hasMany(QuizSession::class);
    }

    // Sessions actives
    public function activeSessions()
    {
        return $this->hasMany(QuizSession::class)->where('status', 'active');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    // Helper methods
    public function isPublished()
    {
        return $this->status === 'published';
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function getTotalQuestionsAttribute()
    {
        return $this->questions()->count();
    }
}