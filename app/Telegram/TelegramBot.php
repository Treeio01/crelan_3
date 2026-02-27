<?php

declare(strict_types=1);

namespace App\Telegram;

use App\Models\Admin;
use App\Telegram\Handlers\ActionHandler;
use App\Telegram\Handlers\AdminPanelHandler;
use App\Telegram\Handlers\BlockIpHandler;
use App\Telegram\Handlers\DomainHandler;
use App\Telegram\Handlers\MessageHandler;
use App\Telegram\Handlers\PreSessionHandler;
use App\Telegram\Handlers\ProfileHandler;
use App\Telegram\Handlers\SessionHandler;
use App\Telegram\Handlers\SmartSuppHandler;
use App\Telegram\Handlers\StartHandler;
use App\Telegram\Middleware\AdminAuthMiddleware;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;

/**
 * Telegram Bot для админ-панели
 * 
 * Регистрирует все handlers и middleware.
 * Все команды и callback'и проходят через AdminAuthMiddleware.
 */
class TelegramBot
{
    public function __construct(
        private readonly Nutgram $bot,
    ) {
        $this->registerMiddleware();
        $this->registerCommands();
        $this->registerCallbacks();
        $this->registerMessages();
        $this->registerErrorHandler();
    }

    /**
     * Регистрация глобального middleware
     */
    private function registerMiddleware(): void
    {
        $this->bot->middleware(AdminAuthMiddleware::class);
    }

    /**
     * Регистрация команд
     */
    private function registerCommands(): void
    {
        // /start — приветствие
        $this->bot->onCommand('start', StartHandler::class);

        // /profile — профиль админа
        $this->bot->onCommand('profile', ProfileHandler::class);

    }

    /**
     * Регистрация callback-обработчиков
     */
    private function registerCallbacks(): void
    {
        // === ГЛАВНОЕ МЕНЮ ===
        $this->bot->onCallbackQueryData('menu:refresh', [StartHandler::class, 'refresh']);
        $this->bot->onCallbackQueryData('menu:my_sessions', [SessionHandler::class, 'mySessions']);
        $this->bot->onCallbackQueryData('menu:pending_sessions', [SessionHandler::class, 'pendingSessions']);
        $this->bot->onCallbackQueryData('menu:profile', [ProfileHandler::class, 'showProfile']);
        $this->bot->onCallbackQueryData('menu:admins', [AdminPanelHandler::class, 'admins']);
        $this->bot->onCallbackQueryData('menu:add_admin', [AdminPanelHandler::class, 'startAddAdmin']);
        $this->bot->onCallbackQueryData('menu:domains', fn(Nutgram $bot) => app(DomainHandler::class)->showMenu($bot));
        $this->bot->onCallbackQueryData('menu:back', [StartHandler::class, 'refresh']);

        // === ДОМЕНЫ ===
        $this->bot->onCallbackQueryData('domain:add', fn(Nutgram $bot) => app(DomainHandler::class)->startAdd($bot));
        $this->bot->onCallbackQueryData('domain:list', fn(Nutgram $bot) => app(DomainHandler::class)->listDomains($bot));
        $this->bot->onCallbackQueryData('domain:info:{domain}', fn(Nutgram $bot, string $domain) => app(DomainHandler::class)->infoDomain($bot, $domain));
        $this->bot->onCallbackQueryData('domain:edit:{domain}', fn(Nutgram $bot, string $domain) => app(DomainHandler::class)->startEdit($bot, $domain));
        $this->bot->onCallbackQueryData('domain:purge_cache', fn(Nutgram $bot) => app(DomainHandler::class)->purgeCache($bot));

        // === SMARTSUPP ===
        $this->bot->onCallbackQueryData('menu:smartsupp', fn(Nutgram $bot) => app(SmartSuppHandler::class)->showMenu($bot));
        $this->bot->onCallbackQueryData('smartsupp:toggle', fn(Nutgram $bot) => app(SmartSuppHandler::class)->toggle($bot));
        $this->bot->onCallbackQueryData('smartsupp:set_key', fn(Nutgram $bot) => app(SmartSuppHandler::class)->startSetKey($bot));

        // === ПРОФИЛЬ ===
        $this->bot->onCallbackQueryData('profile:refresh', [ProfileHandler::class, 'refresh']);

        // === СЕССИИ ===
        $this->bot->onCallbackQueryData('assign:{sessionId}', [SessionHandler::class, 'assign']);
        $this->bot->onCallbackQueryData('unassign:{sessionId}', [SessionHandler::class, 'unassign']);
        $this->bot->onCallbackQueryData('complete:{sessionId}', [SessionHandler::class, 'complete']);
        $this->bot->onCallbackQueryData('sessions:my', [SessionHandler::class, 'mySessions']);
        $this->bot->onCallbackQueryData('sessions:filter:{status}', [AdminPanelHandler::class, 'filterSessions']);

        // === БЛОКИРОВКА IP ===
        $this->bot->onCallbackQueryData('block_ip:{sessionId}', [BlockIpHandler::class, 'blockIp']);
        $this->bot->onCallbackQueryData('unblock_ip:{ipAddress}', [BlockIpHandler::class, 'unblockIp']);

        // === ДЕЙСТВИЯ ===
        $this->bot->onCallbackQueryData('action:{sessionId}:{actionType}', [ActionHandler::class, 'handle']);
        $this->bot->onCallbackQueryData('push_icon_quick:{sessionId}:{iconId}', [ActionHandler::class, 'handleQuickIcon']);

        // === PRE-SESSION ===
        $this->bot->onCallbackQueryData('presession:online:{preSessionId}', [PreSessionHandler::class, 'online']);

        // === ОТМЕНА CONVERSATION ===
        $this->bot->onCallbackQueryData('cancel_conversation', function (Nutgram $bot) {
            /** @var Admin|null $admin */
            $admin = $bot->get('admin');
            if ($admin && $admin->hasPendingAction()) {
                $admin->clearPendingAction();
            }

            try {
                $bot->deleteMessage(
                    chat_id: $bot->chatId(),
                    message_id: $bot->callbackQuery()->message->message_id
                );
            } catch (\Throwable $e) {
                // Игнорируем ошибки удаления
            }
            
            $bot->answerCallbackQuery(text: '❌ Отменено');
        });
    }

    /**
     * Регистрация обработчиков сообщений (для pending actions)
     */
    private function registerMessages(): void
    {
        // Текстовые сообщения (для кастомных действий)
        $this->bot->onText('{text}', [MessageHandler::class, 'handle']);
        
        // Фото (для кастомной картинки)
        $this->bot->onPhoto([MessageHandler::class, 'handle']);
    }

    /**
     * Регистрация обработчика ошибок
     */
    private function registerErrorHandler(): void
    {
        $this->bot->onException(function (Nutgram $bot, \Throwable $exception) {
            Log::error('Telegram bot error', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'user_id' => $bot->userId(),
            ]);

            try {
                $bot->sendMessage('❌ Произошла ошибка. Попробуйте позже.');
            } catch (\Throwable) {
                // Игнорируем ошибку отправки
            }
        });

        $this->bot->onApiError(function (Nutgram $bot, \Throwable $exception) {
            Log::error('Telegram API error', [
                'message' => $exception->getMessage(),
                'user_id' => $bot->userId(),
            ]);
        });
    }

    /**
     * Запуск бота в режиме long polling
     */
    public function run(): void
    {
        $this->bot->run();
    }

    /**
     * Обработка webhook запроса
     */
    public function handleWebhook(): void
    {
        $this->bot->run();
    }

    /**
     * Получение экземпляра бота
     */
    public function getBot(): Nutgram
    {
        return $this->bot;
    }
}
