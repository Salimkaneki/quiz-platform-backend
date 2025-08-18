<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'slug',
        'description',
        'address',
        'phone',
        'email',
        'website',
        'timezone',
        'settings',
        'is_active'
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'deleted_at' => 'datetime'
    ];

    // Relation optimisée avec les utilisateurs
    public function users()
    {
        return $this->hasMany(User::class)->withTrashed();
    }

    public function activeUsers()
    {
        return $this->users()->where('is_active', true);
    }

        public function formations(): HasMany
    {
        return $this->hasMany(Formation::class);
    }

    // Nouvelle relation avec les paramètres système
    public function systemSettings()
    {
        return $this->hasOne(InstitutionSetting::class);
    }

    // Scope pour recherche avancée
    public function scopeSearch($query, $term)
    {
        return $query->where('name', 'like', "%$term%")
                    ->orWhere('code', 'like', "%$term%");
    }

    // Méthode helper pour les paramètres
    public function getSetting($key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    // Générateur de slug
    public static function boot()
    {
        parent::boot();

        static::creating(function ($institution) {
            $institution->slug = Str::slug($institution->code);
        });
    }
}