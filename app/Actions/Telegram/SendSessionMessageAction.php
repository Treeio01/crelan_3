<?php

declare(strict_types=1);

namespace App\Actions\Telegram;

use App\Models\Admin;
use App\Models\Session;
use App\Services\SessionService;
use App\Services\TelegramService;

/**
 * Action для отправки сообщения о сессии в Telegram
 */
class SendSessionMessageAction
{
    public function __construct(
        private readonly TelegramService $telegramService,
        private readonly SessionService $sessionService,
    ) {}

    /**
     * Отправка сообщения о сессии конкретному админу
     */
    public function execute(Session $session, Admin $admin): ?int
    {
        $text = $this->telegramService->formatSessionMessage($session);
        $keyboard = $this->telegramService->buildSessionKeyboard($session);

        $dto = \App\DTOs\TelegramMessageDTO::create(
            chatId: $admin->telegram_user_id,
            text: $text,
            keyboard: $keyboard,
        );

        $messageId = $this->telegramService->sendMessage($dto);

        // Сохраняем message_id в сессии, если это первое сообщение
        if ($messageId !== null && $session->telegram_message_id === null) {
            $this->sessionService->updateTelegramMessageId($session, $messageId);
        }

        return $messageId;
    }

    /**
     * Отправка сообщения всем активным админам
     */
    public function toAllAdmins(Session $session): array
    {
        return $this->telegramService->sendNewSessionNotification($session);
    }

    /**
     * Отправка сообщения о сессии по ID
     */
    public function bySessionId(string $sessionId, int $telegramUserId): ?int
    {
        $session = $this->sessionService->findOrFail($sessionId);
        $admin = Admin::findActiveByTelegramId($telegramUserId);

        if ($admin === null) {
            throw new \RuntimeException('Админ не найден или неактивен');
        }

        return $this->execute($session, $admin);
    }
}
