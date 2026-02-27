<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AdminRole;
use App\Enums\SessionStatus;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Collection;

/**
 * Сервис для работы с админами
 */
class AdminService
{
    /**
     * Создание нового админа
     */
    public function create(
        int $telegramUserId,
        ?string $username = null,
        AdminRole $role = AdminRole::ADMIN,
    ): Admin {
        return Admin::create([
            'telegram_user_id' => $telegramUserId,
            'username' => $username,
            'role' => $role,
            'is_active' => true,
        ]);
    }

    /**
     * Поиск админа по Telegram User ID
     */
    public function findByTelegramId(int $telegramUserId): ?Admin
    {
        return Admin::findByTelegramId($telegramUserId);
    }

    /**
     * Поиск активного админа по Telegram User ID
     */
    public function findActiveByTelegramId(int $telegramUserId): ?Admin
    {
        return Admin::findActiveByTelegramId($telegramUserId);
    }

    /**
     * Проверка, является ли пользователь админом
     */
    public function isAdmin(int $telegramUserId): bool
    {
        return Admin::where('telegram_user_id', $telegramUserId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Проверка, является ли пользователь супер-админом
     */
    public function isSuperAdmin(int $telegramUserId): bool
    {
        return Admin::where('telegram_user_id', $telegramUserId)
            ->where('role', AdminRole::SUPER_ADMIN)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Получение статистики админа
     */
    public function getStatistics(Admin $admin): array
    {
        $completedCount = $admin->sessions()
            ->where('status', SessionStatus::COMPLETED)
            ->count();

        $activeCount = $admin->sessions()
            ->where('status', SessionStatus::PROCESSING)
            ->count();

        $totalCount = $admin->sessions()->count();

        $todayCount = $admin->sessions()
            ->whereDate('created_at', today())
            ->count();

        return [
            'completed_sessions' => $completedCount,
            'active_sessions' => $activeCount,
            'total_sessions' => $totalCount,
            'today_sessions' => $todayCount,
        ];
    }

    /**
     * Получение профиля админа с полной информацией
     */
    public function getProfile(Admin $admin): array
    {
        $statistics = $this->getStatistics($admin);

        return [
            'id' => $admin->id,
            'telegram_user_id' => $admin->telegram_user_id,
            'username' => $admin->username,
            'role' => $admin->role,
            'role_label' => $admin->role->label(),
            'role_emoji' => $admin->role->emoji(),
            'is_active' => $admin->is_active,
            'is_super_admin' => $admin->isSuperAdmin(),
            'can_add_admins' => $admin->canAddAdmins(),
            'created_at' => $admin->created_at,
            'statistics' => $statistics,
        ];
    }

    /**
     * Форматирование профиля для Telegram
     */
    public function formatProfileForTelegram(Admin $admin): string
    {
        $profile = $this->getProfile($admin);
        $stats = $profile['statistics'];

        $username = $profile['username']
            ? "@{$profile['username']}"
            : 'Не указан';

        $status = $profile['is_active'] ? '✅ Активен' : '❌ Неактивен';

        return <<<TEXT
👤 <b>Профиль администратора</b>

🆔 ID: <code>{$profile['telegram_user_id']}</code>
👤 Username: {$username}
{$profile['role_emoji']} Роль: {$profile['role_label']}
{$status}

📊 <b>Статистика:</b>
├ Обработано сессий: {$stats['completed_sessions']}
├ Активных сессий: {$stats['active_sessions']}
├ Всего сессий: {$stats['total_sessions']}
└ Сегодня: {$stats['today_sessions']}

📅 Зарегистрирован: {$profile['created_at']->format('d.m.Y')}
TEXT;
    }

    /**
     * Деактивация админа
     */
    public function deactivate(Admin $admin): Admin
    {
        $admin->update(['is_active' => false]);

        return $admin->fresh();
    }

    /**
     * Активация админа
     */
    public function activate(Admin $admin): Admin
    {
        $admin->update(['is_active' => true]);

        return $admin->fresh();
    }

    /**
     * Обновление username админа
     */
    public function updateUsername(Admin $admin, ?string $username): Admin
    {
        $admin->update(['username' => $username]);

        return $admin->fresh();
    }

    /**
     * Получение всех активных админов
     */
    public function getActiveAdmins(): Collection
    {
        return Admin::where('is_active', true)->get();
    }

    /**
     * Получение всех админов
     */
    public function getAllAdmins(): Collection
    {
        return Admin::orderBy('created_at', 'desc')->get();
    }

    /**
     * Проверка существования админа по Telegram User ID
     */
    public function exists(int $telegramUserId): bool
    {
        return Admin::where('telegram_user_id', $telegramUserId)->exists();
    }

    /**
     * Удаление админа (мягкое - деактивация)
     */
    public function delete(Admin $admin): bool
    {
        // Не удаляем супер-админа
        if ($admin->isSuperAdmin()) {
            throw new \RuntimeException('Нельзя удалить супер-админа');
        }

        return $admin->update(['is_active' => false]);
    }
}
