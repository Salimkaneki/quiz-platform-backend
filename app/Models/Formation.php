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

    public function classes()
    {
        return $this->hasMany(Classe::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    // Classes actives pour l'année en cours
    public function activeClasses()
    {
        return $this->hasMany(Classe::class)->where('is_active', true);
    }

    // Étudiants via les classes
    public function students()
    {
        return $this->hasManyThrough(Student::class, Classe::class);
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

    // Helper methods
    public function getTotalStudentsAttribute()
    {
        return $this->students()->count();
    }

    public function getTotalClassesAttribute()
    {
        return $this->classes()->count();
    }
}