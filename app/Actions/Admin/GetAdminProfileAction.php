<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\Admin;
use App\Services\AdminService;

/**
 * Action для получения профиля админа
 */
class GetAdminProfileAction
{
    public function __construct(
        private readonly AdminService $adminService,
    ) {}

    /**
     * Получение профиля админа
     */
    public function execute(Admin $admin): array
    {
        return $this->adminService->getProfile($admin);
    }

    /**
     * Получение профиля по Telegram User ID
     */
    public function byTelegramId(int $telegramUserId): ?array
    {
        $admin = $this->adminService->findActiveByTelegramId($telegramUserId);

        if ($admin === null) {
            return null;
        }

        return $this->execute($admin);
    }

    /**
     * Получение форматированного профиля для Telegram
     */
    public function getFormattedForTelegram(Admin $admin): string
    {
        return $this->adminService->formatProfileForTelegram($admin);
    }

    /**
     * Получение форматированного профиля по Telegram User ID
     */
    public function getFormattedByTelegramId(int $telegramUserId): ?string
    {
        $admin = $this->adminService->findActiveByTelegramId($telegramUserId);

        if ($admin === null) {
            return null;
        }

        return $this->getFormattedForTelegram($admin);
    }
}
