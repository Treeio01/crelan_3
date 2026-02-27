<?php

declare(strict_types=1);

namespace App\Actions\Telegram;

use App\Models\Session;
use App\Services\SessionService;
use App\Services\TelegramService;

/**
 * Action для обновления сообщения о сессии в Telegram
 */
class UpdateSessionMessageAction
{
    public function __construct(
        private readonly TelegramService $telegramService,
        private readonly SessionService $sessionService,
    ) {}

    /**
     * Обновление сообщения о сессии
     */
    public function execute(Session $session): bool
    {
        return $this->telegramService->updateSessionMessage($session);
    }

    /**
     * Обновление сообщения по ID сессии
     */
    public function bySessionId(string $sessionId): bool
    {
        $session = $this->sessionService->findOrFail($sessionId);

        return $this->execute($session);
    }

    /**
     * Отправка reply-уведомления об изменении
     */
    public function sendUpdateNotification(Session $session, string $updateText): ?int
    {
        return $this->telegramService->sendSessionUpdate($session, $updateText);
    }

    /**
     * Уведомление о получении данных формы
     */
    public function notifyFormSubmitted(Session $session, string $formType, array $data = []): ?int
    {
        return $this->telegramService->notifyFormSubmitted($session, $formType, $data);
    }

    /**
     * Уведомление об онлайн статусе пользователя
     */
    public function notifyOnlineStatus(Session $session, bool $isOnline): ?int
    {
        return $this->telegramService->notifyOnlineStatus($session, $isOnline);
    }
}
