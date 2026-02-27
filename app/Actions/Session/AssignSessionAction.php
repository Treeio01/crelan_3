<?php

declare(strict_types=1);

namespace App\Actions\Session;

use App\DTOs\SessionDTO;
use App\Models\Admin;
use App\Models\Session;
use App\Services\SessionService;

/**
 * Action для прикрепления админа к сессии
 * 
 * События (Telegram, WebSocket, History) обрабатываются через Listeners.
 */
class AssignSessionAction
{
    public function __construct(
        private readonly SessionService $sessionService,
    ) {}

    /**
     * Прикрепление админа к сессии
     */
    public function execute(Session $session, Admin $admin): SessionDTO
    {
        $session = $this->sessionService->assign($session, $admin);

        return SessionDTO::fromModel($session);
    }

    /**
     * Прикрепление по ID сессии и Telegram User ID админа
     */
    public function byIds(string $sessionId, int $telegramUserId): SessionDTO
    {
        $session = $this->sessionService->findOrFail($sessionId);
        $admin = Admin::findActiveByTelegramId($telegramUserId);

        if ($admin === null) {
            throw new \RuntimeException('Админ не найден или неактивен');
        }

        return $this->execute($session, $admin);
    }
}
