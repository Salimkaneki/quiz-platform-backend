<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Administrator extends Model
{
    protected $fillable = [
        'user_id',
        'institution_id',
        'type',
        'permissions'
    ];

    protected $casts = [
        'permissions' => 'array'
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

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByInstitution($query, $institutionId)
    {
        return $query->where('institution_id', $institutionId);
    }

    // Helpers
    public function hasPermission($permission)
    {
        return in_array($permission, $this->permissions ?? []);
    }

    public function getTypeLabel()
    {
        return match($this->type) {
            'pedagogique' => 'Responsable Pédagogique',
            'scolarite' => 'Responsable Scolarité',
            'direction' => 'Direction',
            default => ucfirst($this->type)
        };
    }
}