<?php

declare(strict_types=1);

namespace App\Telegram\Handlers;

use App\Models\Admin;
use App\Models\BlockedIp;
use App\Models\Session;
use App\Services\SessionService;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

/**
 * Handler для блокировки IP адресов
 */
class BlockIpHandler
{
    public function __construct(
        private readonly SessionService $sessionService,
        private readonly TelegramService $telegramService,
    ) {}

    /**
     * Запрос подтверждения блокировки IP
     * Callback: block_ip:{session_id}
     */
    public function blockIp(Nutgram $bot, string $sessionId): void
    {
        /** @var Admin $admin */
        $admin = $bot->get('admin');

        try {
            $session = $this->sessionService->findOrFail($sessionId);
            $ipAddress = $session->ip_address;

            if (empty($ipAddress)) {
                $bot->answerCallbackQuery(
                    text: '❌ IP адрес не найден',
                    show_alert: true,
                );
                return;
            }

            // Проверяем, не заблокирован ли уже
            if (BlockedIp::isBlocked($ipAddress)) {
                $bot->answerCallbackQuery(
                    text: '⚠️ IP уже заблокирован',
                    show_alert: true,
                );
                return;
            }

            // Сохраняем pending action для подтверждения
            $admin->setPendingAction($sessionId, 'block_ip');

            // Отправляем запрос на подтверждение
            $keyboard = InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make(
                        text: '❌ Отмена',
                        callback_data: 'cancel_conversation'
                    )
                );

            $bot->sendMessage(
                text: "⚠️ <b>Подтверждение блокировки IP</b>\n\n" .
                      "IP: <code>{$ipAddress}</code>\n" .
                      "Сессия: <code>{$sessionId}</code>\n\n" .
                      "Отправьте <b>*</b> (звездочку) для подтверждения блокировки.",
                parse_mode: 'HTML',
                reply_markup: $keyboard,
            );

            $bot->answerCallbackQuery(
                text: '⏳ Отправьте * для подтверждения'
            );

        } catch (\Throwable $e) {
            Log::error('Failed to start block IP confirmation', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            $bot->answerCallbackQuery(
                text: '❌ Ошибка: ' . $e->getMessage(),
                show_alert: true,
            );
        }
    }

    /**
     * Подтверждение блокировки (вызывается из MessageHandler)
     */
    public function confirmBlock(Nutgram $bot, Admin $admin, string $sessionId): void
    {
        try {
            $session = $this->sessionService->findOrFail($sessionId);
            $ipAddress = $session->ip_address;

            if (empty($ipAddress)) {
                $bot->sendMessage('❌ IP адрес не найден');
                return;
            }

            // Проверяем, не заблокирован ли уже
            if (BlockedIp::isBlocked($ipAddress)) {
                $bot->sendMessage('⚠️ IP уже заблокирован');
                return;
            }

            // Блокируем IP
            BlockedIp::block(
                ipAddress: $ipAddress,
                adminId: $admin->id,
                reason: "Заблокировано из сессии {$sessionId}"
            );

            Log::info('IP blocked from Telegram', [
                'ip_address' => $ipAddress,
                'session_id' => $sessionId,
                'admin_id' => $admin->id,
            ]);

            // Обновляем сообщение в Telegram
            $session = $session->fresh();
            $this->telegramService->updateSessionMessage($session);

            $bot->sendMessage(
                text: "✅ <b>IP заблокирован</b>\n\n" .
                      "IP: <code>{$ipAddress}</code>\n" .
                      "Сессия: <code>{$sessionId}</code>",
                parse_mode: 'HTML'
            );

        } catch (\Throwable $e) {
            Log::error('Failed to block IP', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            $bot->sendMessage('❌ Ошибка при блокировке IP: ' . $e->getMessage());
        }
    }

    /**
     * Разблокировка IP
     * Callback: unblock_ip:{ip_address}
     */
    public function unblockIp(Nutgram $bot, string $ipAddress): void
    {
        /** @var Admin $admin */
        $admin = $bot->get('admin');

        try {
            if (!BlockedIp::isBlocked($ipAddress)) {
                $bot->answerCallbackQuery(
                    text: '⚠️ IP не заблокирован',
                    show_alert: true,
                );
                return;
            }

            BlockedIp::unblock($ipAddress);

            Log::info('IP unblocked from Telegram', [
                'ip_address' => $ipAddress,
                'admin_id' => $admin->id,
            ]);

            $bot->answerCallbackQuery(
                text: "✅ IP {$ipAddress} разблокирован"
            );

        } catch (\Throwable $e) {
            Log::error('Failed to unblock IP', [
                'ip_address' => $ipAddress,
                'error' => $e->getMessage(),
            ]);

            $bot->answerCallbackQuery(
                text: '❌ Ошибка при разблокировке IP',
                show_alert: true,
            );
        }
    }
}
