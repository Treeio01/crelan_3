<?php

declare(strict_types=1);

namespace App\Telegram\Handlers;

use App\Actions\Session\SelectActionAction;
use App\Enums\ActionType;
use App\Models\Admin;
use App\Services\SessionService;
use App\Services\TelegramService;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

/**
 * Handler для обработки действий админа
 * 
 * Обрабатывает callback'и:
 * - action:{session_id}:{action_type} — выбор действия для сессии
 */
class ActionHandler
{
    public function __construct(
        private readonly SessionService $sessionService,
        private readonly TelegramService $telegramService,
        private readonly SelectActionAction $selectActionAction,
    ) {}

    /**
     * Обработка выбора действия
     * Callback: action:{session_id}:{action_type}
     */
    public function handle(Nutgram $bot, string $sessionId, string $actionTypeValue): void
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

            // Проверяем, что сессия в обработке
            if (!$session->isProcessing()) {
                $bot->answerCallbackQuery(
                    text: '❌ Сессия не в обработке',
                    show_alert: true,
                );
                return;
            }

            // Парсим тип действия
            $actionType = ActionType::tryFrom($actionTypeValue);

            if ($actionType === null) {
                $bot->answerCallbackQuery(
                    text: '❌ Неизвестное действие',
                    show_alert: true,
                );
                return;
            }

            // Обработка действия "Онлайн" — отдельная логика
            if ($actionType === ActionType::ONLINE) {
                $this->handleOnlineCheck($bot, $session);
                return;
            }

            // Пуш с иконкой требует выбор иконки номером (+ быстрые кнопки)
            if ($actionType === ActionType::PUSH_ICON) {
                $admin->setPendingAction($sessionId, $actionTypeValue);

                $iconsPath = base_path('scripts/icons.json');
                $iconsCount = 0;
                if (file_exists($iconsPath)) {
                    $iconsData = json_decode(file_get_contents($iconsPath), true) ?? [];
                    $iconsCount = count($iconsData);
                }

                // Быстрые кнопки для популярных иконок
                $quickKeyboard = InlineKeyboardMarkup::make()
                    ->addRow(
                        InlineKeyboardButton::make(
                            text: '❌ Отмена',
                            callback_data: 'cancel_conversation',
                        ),
                    );

                $bot->sendMessage(
                    text: "🔔 <b>Пуш с иконкой</b>\n\nВведите номер иконки" . ($iconsCount ? " (1-{$iconsCount})" : '') . "\nили выберите быструю кнопку:",
                    parse_mode: 'HTML',
                    reply_markup: $quickKeyboard,
                );
                $bot->answerCallbackQuery(text: '🔢 Выберите иконку');
                return;
            }

            // Кастомные действия требуют ввода текста от админа
            if ($actionType->requiresAdminInput()) {
                // Сохраняем ожидающее действие
                $admin->setPendingAction($sessionId, $actionTypeValue);
                
                $prompt = match ($actionType) {
                    ActionType::CUSTOM_ERROR => "❌ <b>Кастомная ошибка</b>\n\nВведите текст ошибки:",
                    ActionType::CUSTOM_QUESTION => "❓ <b>Кастомный вопрос</b>\n\nВведите текст вопроса:",
                    ActionType::CUSTOM_IMAGE => "🖼 <b>Картинка</b>\n\nОтправьте URL или фото:",
                    ActionType::IMAGE_QUESTION => "🖼❓ <b>Картинка с вопросом</b>\n\nОтправьте фото с подписью (подпись будет вопросом) или сначала фото, затем вопрос:",
                    ActionType::REDIRECT => "🔗 <b>Редирект</b>\n\nВведите URL для редиректа:",
                    ActionType::QR_CODE => "📷 <b>QR код</b>\n\nОтправьте фото QR-кода для отправки пользователю:",
                    default => "Введите текст:",
                };
                
                $bot->sendMessage(
                    text: $prompt,
                    parse_mode: 'HTML',
                );
                
                $bot->answerCallbackQuery(text: '✏️ Введите текст');
                return;
            }

            // Выполняем выбор действия
            $this->selectActionAction->execute($session, $actionType, $admin);

            // Обновляем сообщение
            $session = $session->fresh();

            $text = $this->telegramService->formatSessionMessage($session);
            $keyboard = $this->telegramService->buildSessionKeyboard($session);

            $bot->editMessageText(
                text: $text,
                parse_mode: 'HTML',
                reply_markup: $this->buildKeyboardMarkup($keyboard),
            );

            $actionEmoji = $actionType->emoji();
            $actionLabel = $actionType->label();

            $bot->answerCallbackQuery(
                text: "{$actionEmoji} Выбрано: {$actionLabel}",
            );

        } catch (\Throwable $e) {
            $bot->answerCallbackQuery(
                text: '❌ ' . $e->getMessage(),
                show_alert: true,
            );
        }
    }

    /**
     * Быстрый выбор иконки по кнопке
     * Callback: push_icon_quick:{session_id}:{icon_id}
     */
    public function handleQuickIcon(Nutgram $bot, string $sessionId, string $iconId): void
    {
        /** @var Admin $admin */
        $admin = $bot->get('admin');

        try {
            $session = $this->sessionService->findOrFail($sessionId);

            if ($session->admin_id !== $admin->id) {
                $bot->answerCallbackQuery(text: '❌ Это не ваша сессия', show_alert: true);
                return;
            }

            if (!$session->isProcessing()) {
                $bot->answerCallbackQuery(text: '❌ Сессия не в обработке', show_alert: true);
                return;
            }

            // Устанавливаем иконку и выполняем действие
            $session->update([
                'push_icon_id' => $iconId,
                'action_type' => ActionType::PUSH_ICON->value,
            ]);

            $this->selectActionAction->execute($session, ActionType::PUSH_ICON, $admin);

            // Очищаем pending action
            $admin->clearPendingAction();

            // Обновляем сообщение сессии
            $this->telegramService->updateSessionMessage($session->fresh());

            // Удаляем сообщение с кнопками
            try {
                $bot->deleteMessage(
                    chat_id: $bot->chatId(),
                    message_id: $bot->callbackQuery()->message->message_id,
                );
            } catch (\Throwable) {}

            $bot->sendMessage(
                text: "✅ 🔔 Пуш с иконкой ({$iconId}) установлено!\n\nПользователь перенаправлен.",
                parse_mode: 'HTML',
            );

            $bot->answerCallbackQuery(text: '🔔 Иконка выбрана');

        } catch (\Throwable $e) {
            $bot->answerCallbackQuery(text: '❌ ' . $e->getMessage(), show_alert: true);
        }
    }

    /**
     * Обработка проверки онлайн статуса
     */
    private function handleOnlineCheck(Nutgram $bot, $session): void
    {
        $isOnline = $this->sessionService->isOnline($session);

        $this->telegramService->notifyOnlineStatus($session, $isOnline);

        $status = $isOnline ? '🟢 Пользователь онлайн' : '🔴 Пользователь оффлайн';

        $bot->answerCallbackQuery(
            text: $status,
            show_alert: true,
        );
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
