<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BlockedIp extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'blocked_by_admin_id',
        'reason',
        'blocked_at',
    ];

    protected $casts = [
        'blocked_at' => 'datetime',
    ];

    /**
     * Админ, который заблокировал IP
     */
    public function blockedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'blocked_by_admin_id');
    }

    /**
     * Проверка, заблокирован ли IP
     */
    public static function isBlocked(string $ipAddress): bool
    {
        try {
            return Cache::remember(
                self::cacheKey($ipAddress),
                now()->addMinutes(1),
                static fn (): bool => self::where('ip_address', $ipAddress)->exists(),
            );
        } catch (QueryException $e) {
            Log::warning('BlockedIp check skipped: table unavailable', [
                'ip' => $ipAddress,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Блокировка IP
     */
    public static function block(string $ipAddress, ?int $adminId = null, ?string $reason = null): self
    {
        $record = self::create([
            'ip_address' => $ipAddress,
            'blocked_by_admin_id' => $adminId,
            'reason' => $reason,
            'blocked_at' => now(),
        ]);

        Cache::forget(self::cacheKey($ipAddress));

        return $record;
    }

    /**
     * Разблокировка IP
     */
    public static function unblock(string $ipAddress): bool
    {
        $deleted = self::where('ip_address', $ipAddress)->delete() > 0;
        Cache::forget(self::cacheKey($ipAddress));

        return $deleted;
    }

    private static function cacheKey(string $ipAddress): string
    {
        return "blocked_ip:{$ipAddress}";
    }
}
