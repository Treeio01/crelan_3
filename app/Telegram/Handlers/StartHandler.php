<?php

declare(strict_types=1);

namespace App\Telegram\Handlers;

use App\Models\Admin;
use App\Services\SessionService;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

/**
 * Handler для команды /start
 */
class StartHandler
{
    public function __construct(
        private readonly SessionService $sessionService,
    ) {}

    /**
     * Обработка команды /start
     */
    public function __invoke(Nutgram $bot): void
    {
        /** @var Admin $admin */
        $admin = $bot->get('admin');

        $this->showMainMenu($bot, $admin);
    }

    /**
     * Показать главное меню
     */
    public function showMainMenu(Nutgram $bot, Admin $admin): void
    {
        $roleEmoji = $admin->role->emoji();
        $roleLabel = $admin->role->label();

        $username = $admin->username 
            ? "@{$admin->username}" 
            : "ID: {$admin->telegram_user_id}";

        // Получаем статистику
        $stats = $this->sessionService->getStats();
        $mySessions = $this->sessionService->getAdminSessions($admin, 1)->total();

        $text = <<<TEXT
👋 <b>Добро пожаловать!</b>

👤 {$username}
{$roleEmoji} Роль: {$roleLabel}

📊 <b>Статистика:</b>
├ 🆕 Новые: {$stats['pending']}
├ ⏳ В работе: {$stats['processing']}
├ ✅ Завершённые: {$stats['completed']}
└ 🔒 Мои: {$mySessions}
TEXT;

        // Строим inline клавиатуру
        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('📋 Мои сессии', callback_data: 'menu:my_sessions'),
                InlineKeyboardButton::make('🆕 Новые', callback_data: 'menu:pending_sessions'),
            )
            ->addRow(
                InlineKeyboardButton::make('👤 Профиль', callback_data: 'menu:profile'),
                InlineKeyboardButton::make('🔄 Обновить', callback_data: 'menu:refresh'),
            )->addRow(
                InlineKeyboardButton::make('💬 Smartsupp', callback_data: 'menu:smartsupp'),
            );

        // Кнопки для супер-админа
        if ($admin->canAddAdmins()) {
            $keyboard->addRow(
                InlineKeyboardButton::make('👥 Админы', callback_data: 'menu:admins'),
                InlineKeyboardButton::make('🌐 Домены', callback_data: 'menu:domains'),
            );
            
        }

        $bot->sendMessage(
            text: $text,
            parse_mode: 'HTML',
            reply_markup: $keyboard,
        );
    }

    /**
     * Обновить главное меню (callback)
     */
    public function refresh(Nutgram $bot): void
    {
        /** @var Admin $admin */
        $admin = $bot->get('admin');

        $roleEmoji = $admin->role->emoji();
        $roleLabel = $admin->role->label();

        $username = $admin->username 
            ? "@{$admin->username}" 
            : "ID: {$admin->telegram_user_id}";

        // Получаем статистику
        $stats = $this->sessionService->getStats();
        $mySessions = $this->sessionService->getAdminSessions($admin, 1)->total();

        $text = <<<TEXT
👋 <b>Добро пожаловать!</b>

👤 {$username}
{$roleEmoji} Роль: {$roleLabel}

📊 <b>Статистика:</b>
├ 🆕 Новые: {$stats['pending']}
├ ⏳ В работе: {$stats['processing']}
├ ✅ Завершённые: {$stats['completed']}
└ 🔒 Мои: {$mySessions}
TEXT;

        // Строим inline клавиатуру
        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('📋 Мои сессии', callback_data: 'menu:my_sessions'),
                InlineKeyboardButton::make('🆕 Новые', callback_data: 'menu:pending_sessions'),
            )
            ->addRow(
                InlineKeyboardButton::make('👤 Профиль', callback_data: 'menu:profile'),
                InlineKeyboardButton::make('🔄 Обновить', callback_data: 'menu:refresh'),
            );

        // Кнопки для супер-админа
        if ($admin->canAddAdmins()) {
            $keyboard->addRow(
                InlineKeyboardButton::make('👥 Админы', callback_data: 'menu:admins'),
                InlineKeyboardButton::make('🌐 Домены', callback_data: 'menu:domains'),
            );
            $keyboard->addRow(
                InlineKeyboardButton::make('💬 Smartsupp', callback_data: 'menu:smartsupp'),
            );
        }

        try {
            $bot->editMessageText(
                text: $text,
                parse_mode: 'HTML',
                reply_markup: $keyboard,
            );
            $bot->answerCallbackQuery(text: '✅ Обновлено');
        } catch (\Throwable $e) {
            $bot->answerCallbackQuery(text: '❌ Ошибка обновления');
        }
    }
}
