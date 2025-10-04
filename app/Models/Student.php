<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Student extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'student_number',
        'first_name',
        'last_name',
        'email',
        'birth_date',
        'date_of_birth',
        'phone',
        'address',
        'emergency_contact',
        'emergency_phone',
        'medical_info',
        'preferences',
        'profile_picture',
        'class_id',
        'institution_id',
        'is_active',
        'metadata',
        'user_id',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'date_of_birth' => 'date',
        'is_active' => 'boolean',
        'metadata' => 'array',
        'preferences' => 'array',
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

    // Notification routes
    public function routeNotificationForMail($notification)
    {
        return $this->email;
    }

    public function teachers()
    {
        return $this->classe?->teachers();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

}