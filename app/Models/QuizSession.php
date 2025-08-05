<?php
// app/Models/QuizSession.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QuizSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'teacher_id',
        'session_code',
        'title',
        'starts_at',
        'ends_at',
        'status',
        'allowed_students',
        'max_participants',
        'require_student_list',
        'settings',
        'activated_at',
        'completed_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'activated_at' => 'datetime',
        'completed_at' => 'datetime',
        'allowed_students' => 'array',
        'require_student_list' => 'boolean',
        'settings' => 'array',
    ];

    // Relations
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function studentResponses()
    {
        return $this->hasMany(StudentResponse::class);
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    // Participants actifs
    public function activeParticipants()
    {
        return $this->results()->where('status', 'in_progress');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByCode($query, $code)
    {
        return $query->where('session_code', $code);
    }

    // Helper methods
    public function generateSessionCode()
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (self::where('session_code', $code)->exists());
        
        $this->session_code = $code;
        return $code;
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function canJoin()
    {
        return $this->status === 'active' && 
               now()->between($this->starts_at, $this->ends_at);
    }

    public function getParticipantCountAttribute()
    {
        return $this->results()->count();
    }
}