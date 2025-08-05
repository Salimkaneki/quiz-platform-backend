<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'permissions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions'=>'array',
            'is_active' => 'boolean',
        ];
    }

  // Relations
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    // Matières enseignées (Many-to-Many)
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subject')
                    ->withPivot('classe_id', 'academic_year', 'is_active')
                    ->withTimestamps();
    }

    // Quiz créés par cet enseignant
    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'teacher_id');
    }

    // Sessions de quiz lancées
    public function quizSessions()
    {
        return $this->hasMany(QuizSession::class, 'teacher_id');
    }

    // Scopes utiles
    public function scopeTeachers($query)
    {
        return $query->where('role', 'teacher');
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isTeacher()
    {
        return $this->role === 'teacher';
    }
}