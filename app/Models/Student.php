<?php
// app/Models/Student.php

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
        'classe_id',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    // Relations
    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    public function responses()
    {
        return $this->hasMany(StudentResponse::class);
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByStudentNumber($query, $number)
    {
        return $query->where('student_number', $number);
    }

    // Helper methods
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getFormationAttribute()
    {
        return $this->classe->formation ?? null;
    }
}