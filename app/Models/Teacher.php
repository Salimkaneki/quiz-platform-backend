<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    protected $fillable = [
        'user_id',
        'institution_id',
        'specialization',
        'grade',
        'is_permanent',
        'metadata'
    ];

    protected $casts = [
        'is_permanent' => 'boolean',
        'metadata' => 'array'
    ];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    // Si vous avez des quiz plus tard
    // public function quizzes(): HasMany
    // {
    //     return $this->hasMany(Quiz::class);
    // }

    // Scopes
    public function scopePermanent($query)
    {
        return $query->where('is_permanent', true);
    }

    public function scopeByInstitution($query, $institutionId)
    {
        return $query->where('institution_id', $institutionId);
    }

    public function scopeByGrade($query, $grade)
    {
        return $query->where('grade', $grade);
    }

    public function scopeBySpecialization($query, $specialization)
    {
        return $query->where('specialization', 'like', "%$specialization%");
    }

    // Helpers
    public function getFullName()
    {
        return $this->user->name;
    }

    public function getGradeLabel()
    {
        return match($this->grade) {
            'vacataire' => 'Vacataire',
            'certifié' => 'Certifié',
            'agrégé' => 'Agrégé',
            'maître_de_conférences' => 'Maître de Conférences',
            'professeur' => 'Professeur',
            default => ucfirst($this->grade)
        };
    }

    public function getStatusLabel()
    {
        return $this->is_permanent ? 'Permanent' : 'Contractuel';
    }
}