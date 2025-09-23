<?php
// app/Models/Formation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'duration_years',
        'institution_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'duration_years' => 'integer',
    ];

    // Relations
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    // CORRECTION: Utiliser Classes au lieu de Classe
    public function classes()
    {
        return $this->hasMany(Classes::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    // Classes actives pour l'année en cours
    public function activeClasses()
    {
        return $this->hasMany(Classes::class)->where('is_active', true);
    }

    // CORRECTION: Étudiants via les classes avec les bonnes clés étrangères
    public function students()
    {
        return $this->hasManyThrough(
            Student::class,      // Modèle final
            Classes::class,      // Modèle intermédiaire
            'formation_id',      // Clé étrangère sur classes (classes.formation_id)
            'class_id',          // Clé étrangère sur students (students.class_id)
            'id',               // Clé locale sur formations (formations.id)
            'id'                // Clé locale sur classes (classes.id)
        );
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByInstitution($query, $institutionId)
    {
        return $query->where('institution_id', $institutionId);
    }

    // Étudiants actifs via les classes
    public function activeStudents()
    {
        return $this->hasManyThrough(
            Student::class,
            Classes::class,
            'formation_id',      // Clé étrangère sur classes
            'class_id',          // Clé étrangère sur students  
            'id',               // Clé locale sur formations
            'id'                // Clé locale sur classes
        )->where('students.is_active', true);
    }

    // Helper methods
    public function getTotalStudentsAttribute()
    {
        return $this->students()->count();
    }

    public function getTotalActiveStudentsAttribute()
    {
        return $this->activeStudents()->count();
    }

    public function getTotalClassesAttribute()
    {
        return $this->classes()->count();
    }
}