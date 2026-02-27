<?php

declare(strict_types=1);

namespace App\Telegram\Middleware;

use App\Models\Admin;
use SergiX44\Nutgram\Nutgram;

/**
 * Middleware для проверки авторизации админа
 * 
 * Проверяет telegram_user_id в таблице admins.
 * Блокирует доступ неавторизованным пользователям.
 */
class AdminAuthMiddleware
{
    /**
     * Обработка запроса
     */
    public function __invoke(Nutgram $bot, $next): void
    {
        $telegramUserId = $bot->userId();

        if ($telegramUserId === null) {
            $bot->sendMessage('❌ Не удалось определить пользователя.');
            return;
        }

        // Ищем админа в БД
        $admin = Admin::findByTelegramId($telegramUserId);

        if ($admin === null) {
            $bot->sendMessage('🚫 Доступ запрещен. Вы не являетесь администратором.');
            return;
        }

        if (!$admin->is_active) {
            $bot->sendMessage('🔒 Ваш аккаунт деактивирован. Обратитесь к супер-администратору.');
            return;
        }

        // Обновляем username, если изменился
        $currentUsername = $bot->user()?->username;
        if ($currentUsername !== null && $admin->username !== $currentUsername) {
            $admin->update(['username' => $currentUsername]);
        }

        // Сохраняем админа в контексте для использования в handlers
        $bot->set('admin', $admin);

        $next($bot);
    }
}
