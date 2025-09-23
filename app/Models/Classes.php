<?php
// app/Models/Classes.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'level',
        'academic_year',
        'formation_id',
        'institution_id', // Ajouté si pas déjà présent
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

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    // Relation avec les étudiants
    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    // Étudiants actifs uniquement
    public function activeStudents()
    {
        return $this->hasMany(Student::class, 'class_id')->where('is_active', true);
    }

    // Enseignants via la table pivot teacher_subject
    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_subject', 'class_id', 'teacher_id')
                    ->withPivot('subject_id', 'academic_year', 'is_active')
                    ->withTimestamps();
    }

    // Matières enseignées dans cette classe
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subject', 'class_id', 'subject_id')
                    ->withPivot('teacher_id', 'academic_year', 'is_active')
                    ->withTimestamps();
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

    public function scopeByInstitution($query, $institutionId)
    {
        return $query->where('institution_id', $institutionId);
    }

    // Accesseurs
    public function getStudentCountAttribute()
    {
        return $this->students()->count();
    }

    public function getActiveStudentCountAttribute()
    {
        return $this->activeStudents()->count();
    }

    public function getFullNameAttribute()
    {
        $formationCode = optional($this->formation)->code ?? 'N/A';
        return "{$formationCode} - {$this->name} ({$this->academic_year})";
    }

    // Helper methods
    public function hasSpace()
    {
        return $this->active_student_count < $this->max_students;
    }

    public function getAvailableSpacesAttribute()
    {
        return max(0, $this->max_students - $this->active_student_count);
    }

    public function getOccupationRateAttribute()
    {
        if ($this->max_students == 0) return 0;
        return round(($this->active_student_count / $this->max_students) * 100, 1);
    }

    public function isFull()
    {
        return $this->active_student_count >= $this->max_students;
    }
}