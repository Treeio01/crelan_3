<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        return self::where('ip_address', $ipAddress)->exists();
    }

    /**
     * Блокировка IP
     */
    public static function block(string $ipAddress, ?int $adminId = null, ?string $reason = null): self
    {
        return self::create([
            'ip_address' => $ipAddress,
            'blocked_by_admin_id' => $adminId,
            'reason' => $reason,
            'blocked_at' => now(),
        ]);
    }

    /**
     * Разблокировка IP
     */
    public static function unblock(string $ipAddress): bool
    {
        return self::where('ip_address', $ipAddress)->delete() > 0;
    }
}
