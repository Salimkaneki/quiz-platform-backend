<?php
// app/Models/Institution.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'address',
        'phone',
        'email',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    // Relations
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function teachers()
    {
        return $this->hasMany(User::class)->where('role', 'teacher');
    }

    public function admins()
    {
        return $this->hasMany(User::class)->where('role', 'admin');
    }

    public function formations()
    {
        return $this->hasMany(Formation::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}