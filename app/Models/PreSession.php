<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreSession extends Model
{
    use HasFactory;

    protected $table = 'pre_sessions';

    protected $fillable = [
        'ip_address',
        'country_code',
        'country_name',
        'city',
        'user_agent',
        'locale',
        'page_url',
        'page_name',
        'device_type',
        'is_online',
        'last_seen',
        'converted_to_session_id',
        'converted_at',
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'last_seen' => 'datetime',
        'converted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the main session if converted
     */
    public function mainSession(): BelongsTo
    {
        return $this->belongsTo(Session::class, 'converted_to_session_id');
    }

    /**
     * Check if session is currently online
     */
    public function isCurrentlyOnline(): bool
    {
        return $this->is_online &&
               $this->last_seen &&
               $this->last_seen->diffInMinutes(now()) < 5;
    }

    /**
     * Mark as online
     */
    public function markAsOnline(): bool
    {
        return $this->update([
            'is_online' => true,
            'last_seen' => now(),
        ]);
    }

    /**
     * Mark as offline
     */
    public function markAsOffline(): bool
    {
        return $this->update([
            'is_online' => false,
            'last_seen' => now(),
        ]);
    }

    /**
     * Scope for online sessions
     */
    public function scopeOnline(Builder $query): Builder
    {
        return $query->where('is_online', true)
            ->where('last_seen', '>=', now()->subMinutes(5));
    }

    /**
     * Scope for offline sessions
     */
    public function scopeOffline(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            $q->where('is_online', false)
                ->orWhere('last_seen', '<', now()->subMinutes(5));
        });
    }

    /**
     * Scope for converted sessions
     */
    public function scopeConverted(Builder $query): Builder
    {
        return $query->whereNotNull('converted_to_session_id');
    }

    /**
     * Scope for pending sessions
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('converted_to_session_id');
    }

    /**
     * Get device type icon
     */
    public function getDeviceIconAttribute(): string
    {
        return match ($this->device_type) {
            'desktop' => '🖥️',
            'mobile' => '📱',
            'tablet' => '📱',
            default => '💻',
        };
    }

    /**
     * Get country flag emoji
     */
    public function getCountryFlagAttribute(): string
    {
        $flags = [
            'BE' => '🇧🇪',
            'NL' => '🇳🇱',
            'FR' => '🇫🇷',
            'DE' => '🇩🇪',
            'LU' => '🇱🇺',
            'GB' => '🇬🇧',
            'US' => '🇺🇸',
            'CA' => '🇨🇦',
            'AU' => '🇦🇺',
            'JP' => '🇯🇵',
            'CN' => '🇨🇳',
            'IN' => '🇮🇳',
            'BR' => '🇧🇷',
            'RU' => '🇷🇺',
            'IT' => '🇮🇹',
            'ES' => '🇪🇸',
            'CH' => '🇨🇭',
            'AT' => '🇦🇹',
            'SE' => '🇸🇪',
            'NO' => '🇳🇴',
            'DK' => '🇩🇰',
            'FI' => '🇫🇮',
            'PL' => '🇵🇱',
            'CZ' => '🇨🇿',
            'SK' => '🇸🇰',
            'HU' => '🇭🇺',
            'RO' => '🇷🇴',
            'BG' => '🇧🇬',
            'GR' => '🇬🇷',
            'TR' => '🇹🇷',
            'IE' => '🇮🇪',
            'PT' => '🇵🇹',
        ];

        return $flags[$this->country_code] ?? '🌍';
    }
}
