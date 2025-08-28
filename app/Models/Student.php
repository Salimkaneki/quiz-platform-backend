<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_number',
        'first_name',
        'last_name',
        'email',
        'birth_date',
        'phone',
        'class_id',
        'institution_id', // Ajout important
        'is_active',
        'metadata',
        'user_id', // <-- ajoute ici

    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    // Relations
    public function classe()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }




    public function institution()
    {
        return $this->belongsTo(Institution::class);
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

    // Accesseurs
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function teachers()
    {
        return $this->classe?->teachers();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}