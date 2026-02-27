<?php

declare(strict_types=1);

namespace App\Telegram\Handlers;

use App\Models\PreSession;
use SergiX44\Nutgram\Nutgram;

class PreSessionHandler
{
    /**
     * Callback: presession:online:{preSessionId}
     */
    public function online(Nutgram $bot, string $preSessionId): void
    {
        try {
            $preSession = PreSession::query()->findOrFail($preSessionId);
            $isOnline = $preSession->isCurrentlyOnline();

            $bot->answerCallbackQuery(
                text: $isOnline ? '🟢 Пользователь онлайн' : '🔴 Пользователь оффлайн',
                show_alert: true,
            );
        } catch (\Throwable $e) {
            $bot->answerCallbackQuery(
                text: '❌ ' . $e->getMessage(),
                show_alert: true,
            );
        }
    }
}
