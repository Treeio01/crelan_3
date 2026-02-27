<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Enums\AdminRole;
use App\Models\Admin;
use App\Services\AdminService;

/**
 * Action для добавления нового админа
 */
class AddAdminAction
{
    public function __construct(
        private readonly AdminService $adminService,
    ) {}

    /**
     * Добавление нового админа
     *
     * @throws \RuntimeException если вызывающий не супер-админ или админ уже существует
     */
    public function execute(
        int $newAdminTelegramId,
        Admin $requestingAdmin,
        ?string $username = null,
        AdminRole $role = AdminRole::ADMIN,
    ): Admin {
        // Проверяем права
        if (!$requestingAdmin->canAddAdmins()) {
            throw new \RuntimeException('У вас нет прав для добавления админов');
        }

        // Проверяем, не существует ли уже такой админ
        if ($this->adminService->exists($newAdminTelegramId)) {
            throw new \RuntimeException('Админ с таким Telegram ID уже существует');
        }

        // Нельзя создать супер-админа
        if ($role === AdminRole::SUPER_ADMIN) {
            throw new \RuntimeException('Нельзя создать супер-админа через этот метод');
        }

        // Создаем нового админа
        return $this->adminService->create(
            telegramUserId: $newAdminTelegramId,
            username: $username,
            role: $role,
        );
    }

    /**
     * Добавление админа по Telegram User ID запрашивающего
     */
    public function byTelegramId(
        int $newAdminTelegramId,
        int $requestingTelegramId,
        ?string $username = null,
    ): Admin {
        $requestingAdmin = $this->adminService->findActiveByTelegramId($requestingTelegramId);

        if ($requestingAdmin === null) {
            throw new \RuntimeException('Вы не являетесь активным администратором');
        }

        return $this->execute(
            newAdminTelegramId: $newAdminTelegramId,
            requestingAdmin: $requestingAdmin,
            username: $username,
        );
    }
}
