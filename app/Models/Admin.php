<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AdminRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Admin extends Model
{
    use HasFactory;

    protected $fillable = [
        'telegram_user_id',
        'username',
        'role',
        'is_active',
        'pending_action',
    ];

    protected $casts = [
        'telegram_user_id' => 'integer',
        'role' => AdminRole::class,
        'is_active' => 'boolean',
        'pending_action' => 'array',
    ];

    /**
     * Сессии, назначенные на этого админа
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    /**
     * История действий админа
     */
    public function sessionHistory(): HasMany
    {
        return $this->hasMany(SessionHistory::class);
    }

    /**
     * Домены, добавленные админом
     */
    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    /**
     * Проверка, является ли админ супер-админом
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === AdminRole::SUPER_ADMIN;
    }

    /**
     * Проверка, может ли админ добавлять других админов
     */
    public function canAddAdmins(): bool
    {
        return $this->role->canAddAdmins();
    }

    /**
     * Количество обработанных сессий
     */
    public function getCompletedSessionsCountAttribute(): int
    {
        return $this->sessions()
            ->where('status', 'completed')
            ->count();
    }

    /**
     * Количество активных сессий
     */
    public function getActiveSessionsCountAttribute(): int
    {
        return $this->sessions()
            ->where('status', 'processing')
            ->count();
    }

    /**
     * Найти админа по Telegram User ID
     */
    public static function findByTelegramId(int $telegramUserId): ?self
    {
        return self::where('telegram_user_id', $telegramUserId)->first();
    }

    /**
     * Найти активного админа по Telegram User ID
     */
    public static function findActiveByTelegramId(int $telegramUserId): ?self
    {
        return self::where('telegram_user_id', $telegramUserId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Установить ожидающее действие
     */
    public function setPendingAction(string $sessionId, string $actionType): void
    {
        $this->update([
            'pending_action' => [
                'session_id' => $sessionId,
                'action_type' => $actionType,
                'created_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Получить ожидающее действие
     */
    public function getPendingAction(): ?array
    {
        return $this->pending_action;
    }

    /**
     * Очистить ожидающее действие
     */
    public function clearPendingAction(): void
    {
        $this->update(['pending_action' => null]);
    }

    /**
     * Есть ли ожидающее действие
     */
    public function hasPendingAction(): bool
    {
        return $this->pending_action !== null;
    }
}
