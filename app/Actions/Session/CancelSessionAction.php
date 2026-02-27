<?php

declare(strict_types=1);

namespace App\Actions\Session;

use App\DTOs\SessionDTO;
use App\Models\Admin;
use App\Models\Session;
use App\Services\SessionService;

/**
 * Action для отмены сессии
 * 
 * События (Telegram, WebSocket, History) обрабатываются через Listeners.
 */
class CancelSessionAction
{
    public function __construct(
        private readonly SessionService $sessionService,
    ) {}

    /**
     * Отмена сессии
     */
    public function execute(Session $session, ?Admin $admin = null, ?string $reason = null): SessionDTO
    {
        $session = $this->sessionService->cancel($session, $admin, $reason);

        return SessionDTO::fromModel($session);
    }

    /**
     * Отмена по ID сессии
     */
    public function bySessionId(string $sessionId, ?int $telegramUserId = null, ?string $reason = null): SessionDTO
    {
        $session = $this->sessionService->findOrFail($sessionId);
        
        $admin = null;
        if ($telegramUserId !== null) {
            $admin = Admin::findActiveByTelegramId($telegramUserId);
        }

        return $this->execute($session, $admin, $reason);
    }
}
