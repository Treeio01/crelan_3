<?php

declare(strict_types=1);

namespace App\Telegram\Handlers;

use App\Actions\Session\AssignSessionAction;
use App\Actions\Session\CompleteSessionAction;
use App\Actions\Session\UnassignSessionAction;
use App\Models\Admin;
use App\Services\SessionService;
use App\Services\TelegramService;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

/**
 * Handler для работы с сессиями
 * 
 * Обрабатывает callback'и:
 * - assign:{session_id} — прикрепиться к сессии
 * - unassign:{session_id} — открепиться от сессии
 * - complete:{session_id} — завершить сессию
 */
class SessionHandler
{
    public function __construct(
        private readonly SessionService $sessionService,
        private readonly TelegramService $telegramService,
        private readonly AssignSessionAction $assignAction,
        private readonly UnassignSessionAction $unassignAction,
        private readonly CompleteSessionAction $completeAction,
    ) {}

    /**
     * Прикрепиться к сессии
     * Callback: assign:{session_id}
     */
    public function assign(Nutgram $bot, string $sessionId): void
    {
        /** @var Admin $admin */
        $admin = $bot->get('admin');

        try {
            $session = $this->sessionService->findOrFail($sessionId);

            // Проверяем, можно ли прикрепиться
            if (!$session->isPending()) {
                $bot->answerCallbackQuery(
                    text: '❌ Сессия уже в обработке',
                    show_alert: true,
                );
                return;
            }

            // Выполняем прикрепление
            $this->assignAction->execute($session, $admin);

            // Обновляем сообщение с новой клавиатурой
            $session = $session->fresh();
            
            // Сохраняем message_id и chat_id для этой сессии
            $callbackQuery = $bot->callbackQuery();
            $messageId = $callbackQuery->message->message_id;
            $chatId = $callbackQuery->message->chat->id;
            
            $this->sessionService->updateTelegramMessage(
                $session,
                $messageId,
                $chatId
            );

            $text = $this->telegramService->formatSessionMessage($session);
            $keyboard = $this->telegramService->buildSessionKeyboard($session);

            $bot->editMessageText(
                text: $text,
                parse_mode: 'HTML',
                reply_markup: $this->buildKeyboardMarkup($keyboard),
            );

            // Закрепляем сообщение в групповом чате
            $groupChatId = config('services.telegram.group_chat_id');
            \Illuminate\Support\Facades\Log::info('Attempting to pin message', [
                'group_chat_id' => $groupChatId,
                'telegram_message_id' => $session->telegram_message_id,
                'session_id' => $session->id,
            ]);
            
            if ($groupChatId && $session->telegram_message_id) {
                try {
                    $bot->pinChatMessage(
                        chat_id: (int) $groupChatId,
                        message_id: (int) $session->telegram_message_id,
                        disable_notification: false,
                    );
                    \Illuminate\Support\Facades\Log::info('Message pinned successfully');
                } catch (\Throwable $e) {
                    // Логируем ошибку закрепления
                    \Illuminate\Support\Facades\Log::warning('Failed to pin message', [
                        'chat_id' => $groupChatId,
                        'message_id' => $session->telegram_message_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                \Illuminate\Support\Facades\Log::warning('Cannot pin message: missing data', [
                    'group_chat_id_set' => !empty($groupChatId),
                    'telegram_message_id_set' => !empty($session->telegram_message_id),
                ]);
            }

            $bot->answerCallbackQuery(text: '✅ Вы прикрепились к сессии');

        } catch (\Throwable $e) {
            $bot->answerCallbackQuery(
                text: '❌ ' . $e->getMessage(),
                show_alert: true,
            );
        }
    }

    /**
     * Открепиться от сессии
     * Callback: unassign:{session_id}
     */
    public function unassign(Nutgram $bot, string $sessionId): void
    {
        /** @var Admin $admin */
        $admin = $bot->get('admin');

        try {
            $session = $this->sessionService->findOrFail($sessionId);

            // Проверяем, что это наша сессия
            if ($session->admin_id !== $admin->id) {
                $bot->answerCallbackQuery(
                    text: '❌ Это не ваша сессия',
                    show_alert: true,
                );
                return;
            }

            // Выполняем открепление
            $this->unassignAction->execute($session, $admin);

            // Обновляем сообщение
            $session = $session->fresh();

            $text = $this->telegramService->formatSessionMessage($session);
            $keyboard = $this->telegramService->buildSessionKeyboard($session);

            $callbackQuery = $bot->callbackQuery();
            $messageId = $callbackQuery->message->message_id;
            $chatId = $callbackQuery->message->chat->id;

            $bot->editMessageText(
                text: $text,
                parse_mode: 'HTML',
                reply_markup: $this->buildKeyboardMarkup($keyboard),
            );

            // Открепляем сообщение в групповом чате
            $groupChatId = config('services.telegram.group_chat_id');
            if ($groupChatId && $session->telegram_message_id) {
                try {
                    $bot->unpinChatMessage(
                        chat_id: (int) $groupChatId,
                        message_id: (int) $session->telegram_message_id,
                    );
                } catch (\Throwable $e) {
                    // Логируем ошибку открепления
                    \Illuminate\Support\Facades\Log::warning('Failed to unpin message', [
                        'chat_id' => $groupChatId,
                        'message_id' => $session->telegram_message_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $bot->answerCallbackQuery(text: '🔓 Вы открепились от сессии');

        } catch (\Throwable $e) {
            $bot->answerCallbackQuery(
                text: '❌ ' . $e->getMessage(),
                show_alert: true,
            );
        }
    }

    /**
     * Завершить сессию
     * Callback: complete:{session_id}
     */
    public function complete(Nutgram $bot, string $sessionId): void
    {
        /** @var Admin $admin */
        $admin = $bot->get('admin');

        try {
            $session = $this->sessionService->findOrFail($sessionId);

            // Проверяем, что это наша сессия
            if ($session->admin_id !== $admin->id) {
                $bot->answerCallbackQuery(
                    text: '❌ Это не ваша сессия',
                    show_alert: true,
                );
                return;
            }

            // Выполняем завершение
            $this->completeAction->execute($session, $admin);

            // Обновляем сообщение
            $session = $session->fresh();

            $text = $this->telegramService->formatSessionMessage($session);
            $text .= "\n\n✅ <b>Сессия завершена</b>";

            // Убираем клавиатуру у завершенной сессии
            $bot->editMessageText(
                text: $text,
                parse_mode: 'HTML',
            );

            $bot->answerCallbackQuery(text: '✅ Сессия завершена');

        } catch (\Throwable $e) {
            $bot->answerCallbackQuery(
                text: '❌ ' . $e->getMessage(),
                show_alert: true,
            );
        }
    }

    /**
     * Показать список сессий текущего админа
     * Callback: sessions:my
     */
    public function mySessions(Nutgram $bot): void
    {
        /** @var Admin $admin */
        $admin = $bot->get('admin');

        $sessions = $this->sessionService->getAdminSessions($admin, 5);

        if ($sessions->isEmpty()) {
            $bot->answerCallbackQuery(
                text: 'У вас нет сессий',
                show_alert: true,
            );
            return;
        }

        $text = "📋 <b>Ваши сессии:</b>\n\n";

        foreach ($sessions as $session) {
            $statusEmoji = $session->status->emoji();
            $inputValue = $session->input_value;
            $date = $session->created_at->format('d.m H:i');

            $text .= "{$statusEmoji} <code>{$session->id}</code>\n";
            $text .= "   └ {$inputValue} ({$date})\n\n";
        }

        if ($sessions->hasMorePages()) {
            $text .= "<i>Показаны последние 5 сессий</i>";
        }

        $bot->sendMessage(
            text: $text,
            parse_mode: 'HTML',
        );

        $bot->answerCallbackQuery();
    }

    /**
     * Показать список новых (pending) сессий
     * Callback: menu:pending_sessions
     */
    public function pendingSessions(Nutgram $bot): void
    {
        $sessions = $this->sessionService->getPendingSessions(5);

        if ($sessions->isEmpty()) {
            $bot->answerCallbackQuery(
                text: 'Нет новых сессий',
                show_alert: true,
            );
            return;
        }

        $text = "🆕 <b>Новые сессии:</b>\n\n";

        foreach ($sessions as $session) {
            $inputValue = $session->input_value;
            $date = $session->created_at->format('d.m H:i');
            $ip = $session->ip;

            $text .= "📋 <code>{$session->id}</code>\n";
            $text .= "   ├ {$inputValue}\n";
            $text .= "   ├ 🌐 {$ip}\n";
            $text .= "   └ 📅 {$date}\n\n";
        }

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('🔙 Назад', callback_data: 'menu:back'),
            );

        $bot->sendMessage(
            text: $text,
            parse_mode: 'HTML',
            reply_markup: $keyboard,
        );

        $bot->answerCallbackQuery();
    }
    
    /**
     * Построение InlineKeyboardMarkup из массива
     */
    private function buildKeyboardMarkup(array $keyboard): ?InlineKeyboardMarkup
    {
        if (empty($keyboard)) {
            return null;
        }
        
        $markup = new InlineKeyboardMarkup();
        foreach ($keyboard as $row) {
            if (!empty($row)) {
                $markup->addRow(...$row);
            }
        }
        return $markup;
    }
}
