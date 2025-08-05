<?php
// app/Models/Classe.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'level',
        'academic_year',
        'formation_id',
        'max_students',
        'is_active',
    ];

    protected $casts = [
        'level' => 'integer',
        'max_students' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relations
    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    // Enseignants via la table pivot teacher_subject
    public function teachers()
    {
        return $this->belongsToMany(User::class, 'teacher_subject', 'classe_id', 'teacher_id')
                    ->withPivot('subject_id', 'academic_year', 'is_active')
                    ->withTimestamps();
    }

    // Matières enseignées dans cette classe
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subject', 'classe_id', 'subject_id')
                    ->withPivot('teacher_id', 'academic_year', 'is_active')
                    ->withTimestamps();
    }

    // Étudiants actifs
    public function activeStudents()
    {
        return $this->hasMany(Student::class)->where('is_active', true);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByAcademicYear($query, $year)
    {
        return $query->where('academic_year', $year);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    // Helper methods
    public function getStudentCountAttribute()
    {
        return $this->students()->count();
    }

    public function hasSpace()
    {
        return $this->student_count < $this->max_students;
    }

    public function getFullNameAttribute()
    {
        return $this->formation->code . ' - ' . $this->name . ' (' . $this->academic_year . ')';
    }
}