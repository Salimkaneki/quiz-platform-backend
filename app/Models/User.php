<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; 


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; 

    protected $fillable = [
        'name',
        'email',
        'password',
        'account_type',
        'is_super_admin',
        'phone',
        'address',
        'is_active'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean'
    ];

    /**
     * Relations
     */
    public function superAdmin(): HasOne
    {
        return $this->hasOne(SuperAdmin::class);
    }

    public function administrators(): HasMany
    {
        return $this->hasMany(Administrator::class);
    }

    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }

    /**
     * Vérifications de rôles
     */
    public function isSuperAdmin(): bool
    {
        return (
            (method_exists($this, 'superAdmin') && $this->superAdmin()->where('is_active', true)->exists())
            || ($this->account_type === 'admin' && $this->is_super_admin)
        );
    }


    public function isAdmin(): bool
    {
        return $this->account_type === 'admin';
    }

    public function isTeacher(): bool
    {
        return $this->account_type === 'teacher';
    }

    public function isStudent(): bool
    {
        return $this->account_type === 'student';
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope pour les super admins
    public function scopeSuperAdmins($query)
    {
        return $query->where('account_type', 'admin')->where('is_super_admin', true);
    }

    public function scopeByAccountType($query, $type)
    {
        return $query->where('account_type', $type);
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }
}