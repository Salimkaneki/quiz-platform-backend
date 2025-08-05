<?php
// app/Models/Subject.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'credits',
        'coefficient',
        'type',
        'formation_id',
        'semester',
        'is_active',
    ];

    protected $casts = [
        'credits' => 'integer',
        'coefficient' => 'integer',
        'semester' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relations
    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    // Enseignants de cette matière (Many-to-Many)
    public function teachers()
    {
        return $this->belongsToMany(User::class, 'teacher_subject')
                    ->withPivot('classe_id', 'academic_year', 'is_active')
                    ->withTimestamps();
    }

    // Classes où cette matière est enseignée
    public function classes()
    {
        return $this->belongsToMany(Classe::class, 'teacher_subject')
                    ->withPivot('teacher_id', 'academic_year', 'is_active')
                    ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBySemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByFormation($query, $formationId)
    {
        return $query->where('formation_id', $formationId);
    }

    // Helper methods
    public function getQuizCountAttribute()
    {
        return $this->quizzes()->count();
    }

    public function getActiveQuizzesAttribute()
    {
        return $this->quizzes()->where('status', 'published')->get();
    }
}