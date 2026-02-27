<?php

declare(strict_types=1);

namespace App\Telegram\Handlers;

use App\Actions\Admin\AddAdminAction;
use App\Enums\SessionStatus;
use App\Models\Admin;
use App\Services\AdminService;
use App\Services\SessionService;
use App\Services\TelegramService;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

/**
 * Handler для админ-панели
 * 
 * Команды:
 * - /addadmin {telegram_id} — добавить нового админа (только супер-админ)
 * - /sessions — показать список сессий
 * - /admins — показать список админов (только супер-админ)
 */
class AdminPanelHandler
{
    public function __construct(
        private readonly AddAdminAction $addAdminAction,
        private readonly AdminService $adminService,
        private readonly SessionService $sessionService,
        private readonly TelegramService $telegramService,
    ) {}


    /**
     * Показать список сессий
     * Команда: /sessions
     */
    public function sessions(Nutgram $bot): void
    {
        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(
                    text: '🆕 Новые',
                    callback_data: 'sessions:filter:pending'
                ),
                InlineKeyboardButton::make(
                    text: '⚙️ В работе',
                    callback_data: 'sessions:filter:processing'
                ),
            )
            ->addRow(
                InlineKeyboardButton::make(
                    text: '✅ Завершенные',
                    callback_data: 'sessions:filter:completed'
                ),
                InlineKeyboardButton::make(
                    text: '📋 Мои',
                    callback_data: 'sessions:my'
                ),
            );

        // Получаем статистику
        $pendingCount = $this->sessionService->getPendingSessions()->count();
        $activeSessions = $this->sessionService->getActiveSessions();
        $processingCount = $activeSessions->where('status', SessionStatus::PROCESSING)->count();

        $text = <<<TEXT
📊 <b>Панель сессий</b>

🆕 Новых сессий: <b>{$pendingCount}</b>
⚙️ В работе: <b>{$processingCount}</b>

Выберите фильтр:
TEXT;

        $bot->sendMessage(
            text: $text,
            parse_mode: 'HTML',
            reply_markup: $keyboard,
        );
    }

    /**
     * Фильтр сессий по статусу
     * Callback: sessions:filter:{status}
     */
    public function filterSessions(Nutgram $bot, string $statusValue): void
    {
        $status = SessionStatus::tryFrom($statusValue);

        if ($status === null) {
            $bot->answerCallbackQuery(
                text: '❌ Неизвестный статус',
                show_alert: true,
            );
            return;
        }

        $sessions = $this->sessionService->getSessionsByStatus($status, 5);

        if ($sessions->isEmpty()) {
            $bot->answerCallbackQuery(
                text: "Нет сессий со статусом: {$status->label()}",
                show_alert: true,
            );
            return;
        }

        $statusEmoji = $status->emoji();
        $statusLabel = $status->label();

        $text = "{$statusEmoji} <b>Сессии: {$statusLabel}</b>\n\n";

        foreach ($sessions as $session) {
            $inputValue = $session->input_value;
            $date = $session->created_at->format('d.m H:i');

            $adminInfo = '';
            if ($session->admin) {
                $adminName = $session->admin->username
                    ? "@{$session->admin->username}"
                    : "ID:{$session->admin->telegram_user_id}";
                $adminInfo = " 👤 {$adminName}";
            }

            $text .= "• <code>{$session->id}</code>\n";
            $text .= "  └ {$inputValue} ({$date}){$adminInfo}\n\n";
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
     * Показать список админов
     * Callback: menu:admins (только для супер-админа)
     */
    public function admins(Nutgram $bot): void
    {
        /** @var Admin $admin */
        $admin = $bot->get('admin');

        // Проверяем права
        if (!$admin->isSuperAdmin()) {
            $bot->answerCallbackQuery(
                text: '🚫 Только для супер-админа',
                show_alert: true,
            );
            return;
        }

        $admins = $this->adminService->getAllAdmins();

        $text = "👥 <b>Список администраторов:</b>\n\n";

        foreach ($admins as $adm) {
            $roleEmoji = $adm->role->emoji();
            $username = $adm->username ? "@{$adm->username}" : "ID: {$adm->telegram_user_id}";
            $status = $adm->is_active ? '✅' : '❌';

            $text .= "{$roleEmoji} {$username} {$status}\n";
            $text .= "   └ Сессий: {$adm->completed_sessions_count}\n\n";
        }

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('➕ Добавить', callback_data: 'menu:add_admin'),
                InlineKeyboardButton::make('🔙 Назад', callback_data: 'menu:back'),
            );

        if ($bot->callbackQuery()) {
            $bot->editMessageText(
                text: $text,
                parse_mode: 'HTML',
                reply_markup: $keyboard,
            );
            $bot->answerCallbackQuery();
        } else {
            $bot->sendMessage(
                text: $text,
                parse_mode: 'HTML',
                reply_markup: $keyboard,
            );
        }
    }

    /**
     * Начать добавление админа
     * Callback: menu:add_admin
     */
    public function startAddAdmin(Nutgram $bot): void
    {
        /** @var Admin $admin */
        $admin = $bot->get('admin');

        // Проверяем права
        if (!$admin->canAddAdmins()) {
            $bot->answerCallbackQuery(
                text: '🚫 У вас нет прав',
                show_alert: true,
            );
            return;
        }

        // Сохраняем pending_action для добавления админа
        $admin->setPendingAction('admin', 'add');

        $text = <<<TEXT
➕ <b>Добавление админа</b>

Отправьте Telegram ID нового админа:

<b>Пример:</b>
<code>123456789</code>

💡 <i>Telegram ID можно узнать у бота @userinfobot</i>
TEXT;

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('❌ Отмена', callback_data: 'cancel_conversation'),
            );

        if ($bot->callbackQuery()) {
            $bot->editMessageText(
                text: $text,
                parse_mode: 'HTML',
                reply_markup: $keyboard,
            );
            $bot->answerCallbackQuery();
        } else {
            $bot->sendMessage(
                text: $text,
                parse_mode: 'HTML',
                reply_markup: $keyboard,
            );
        }
    }

    /**
     * Обработка добавления админа (из MessageHandler)
     */
    public function processAddAdmin(Nutgram $bot, Admin $admin, string $telegramIdInput): void
    {
        $telegramId = trim($telegramIdInput);

        if (!is_numeric($telegramId)) {
            $bot->sendMessage(
                text: "❌ <b>Неверный формат!</b>\n\nОтправьте числовой Telegram ID.\n\nПример: <code>123456789</code>",
                parse_mode: 'HTML',
            );
            return;
        }

        $newAdminTelegramId = (int) $telegramId;

        try {
            $newAdmin = $this->addAdminAction->execute(
                newAdminTelegramId: $newAdminTelegramId,
                requestingAdmin: $admin,
            );

            $text = <<<TEXT
✅ <b>Админ добавлен!</b>

🆔 <b>Telegram ID:</b> <code>{$newAdmin->telegram_user_id}</code>
👤 <b>Роль:</b> {$newAdmin->role->label()}
TEXT;

            $keyboard = InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('👥 Список админов', callback_data: 'menu:admins'),
                    InlineKeyboardButton::make('🔙 Назад', callback_data: 'menu:back'),
                );

            $bot->sendMessage(
                text: $text,
                parse_mode: 'HTML',
                reply_markup: $keyboard,
            );

            $admin->clearPendingAction();

        } catch (\Throwable $e) {
            $bot->sendMessage(
                text: "❌ <b>Ошибка:</b> {$e->getMessage()}",
                parse_mode: 'HTML',
            );
            $admin->clearPendingAction();
        }
    }
}
