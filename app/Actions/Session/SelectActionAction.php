<?php

declare(strict_types=1);

namespace App\Actions\Session;

use App\DTOs\SessionDTO;
use App\Enums\ActionType;
use App\Models\Admin;
use App\Models\Session;
use App\Services\SessionService;

/**
 * Action для выбора действия админом
 * 
 * События (Telegram, WebSocket, History) обрабатываются через Listeners.
 */
class SelectActionAction
{
    public function __construct(
        private readonly SessionService $sessionService,
    ) {}

    /**
     * Выбор действия
     */
    public function execute(Session $session, ActionType $actionType, Admin $admin): SessionDTO
    {
        $session = $this->sessionService->selectAction($session, $actionType, $admin);

        return SessionDTO::fromModel($session);
    }

    /**
     * Выбор действия по ID сессии, типу действия и Telegram User ID админа
     */
    public function byIds(string $sessionId, string $actionTypeValue, int $telegramUserId): SessionDTO
    {
        $session = $this->sessionService->findOrFail($sessionId);
        $actionType = ActionType::from($actionTypeValue);
        $admin = Admin::findActiveByTelegramId($telegramUserId);

        if ($admin === null) {
            throw new \RuntimeException('Админ не найден или неактивен');
        }

        return $this->execute($session, $actionType, $admin);
    }
}
