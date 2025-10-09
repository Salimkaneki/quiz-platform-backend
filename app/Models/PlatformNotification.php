<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformNotification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'expires_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeActive($query)
    {
        return $query->whereNull('read_at')
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    // Helpers
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function isRead()
    {
        return !is_null($this->read_at);
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    // Types de notifications
    const TYPE_REPORT_AVAILABLE = 'report_available';
    const TYPE_SESSION_COMPLETED = 'session_completed';
    const TYPE_QUIZ_SESSION_CREATED = 'quiz_session_created';
    const TYPE_SYSTEM_ALERT = 'system_alert';

    public static function getTypeLabel($type)
    {
        return match($type) {
            self::TYPE_REPORT_AVAILABLE => 'Rapport disponible',
            self::TYPE_SESSION_COMPLETED => 'Session terminée',
            self::TYPE_QUIZ_SESSION_CREATED => 'Session d\'examen créée',
            self::TYPE_SYSTEM_ALERT => 'Alerte système',
            default => ucfirst(str_replace('_', ' ', $type))
        };
    }
}