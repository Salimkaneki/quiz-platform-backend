<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /**
     * Relations
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(QuizSession::class);
    }

    public function activeSessions(): HasMany
    {
        return $this->hasMany(QuizSession::class)->where('status', 'active');
    }

    /**
     * Scopes
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    // NOUVEAU : Scope pour chercher par user_id du teacher
    public function scopeByTeacherUser($query, $userId)
    {
        return $query->whereHas('teacher', function($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Helpers
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function getTotalQuestionsAttribute(): int
    {
        return $this->questions()->count();
    }
}