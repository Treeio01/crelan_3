<?php

declare(strict_types=1);

namespace App\Telegram\Handlers;

use App\Actions\Admin\GetAdminProfileAction;
use App\Models\Admin;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

/**
 * Handler для команды /profile
 */
class ProfileHandler
{
    public function __construct(
        private readonly GetAdminProfileAction $getProfileAction,
    ) {}

    /**
     * Обработка команды /profile
     */
    public function __invoke(Nutgram $bot): void
    {
        /** @var Admin $admin */
        $admin = $bot->get('admin');

        $text = $this->getProfileAction->getFormattedForTelegram($admin);

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(
                    text: '📋 Мои сессии',
                    callback_data: 'sessions:my'
                ),
                InlineKeyboardButton::make(
                    text: '🔄 Обновить',
                    callback_data: 'profile:refresh'
                ),
            );

        $bot->sendMessage(
            text: $text,
            parse_mode: 'HTML',
            reply_markup: $keyboard,
        );
    }

    /**
     * Показать профиль (callback)
     */
    public function showProfile(Nutgram $bot): void
    {
        /** @var Admin $admin */
        $admin = $bot->get('admin');

        $text = $this->getProfileAction->getFormattedForTelegram($admin);

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(
                    text: '📋 Мои сессии',
                    callback_data: 'sessions:my'
                ),
                InlineKeyboardButton::make(
                    text: '🔄 Обновить',
                    callback_data: 'profile:refresh'
                ),
            )
            ->addRow(
                InlineKeyboardButton::make(
                    text: '🔙 Назад',
                    callback_data: 'menu:back'
                ),
            );

        $bot->sendMessage(
            text: $text,
            parse_mode: 'HTML',
            reply_markup: $keyboard,
        );

        $bot->answerCallbackQuery();
    }

    /**
     * Обработка callback для обновления профиля
     */
    public function refresh(Nutgram $bot): void
    {
        /** @var Admin $admin */
        $admin = $bot->get('admin');

        $text = $this->getProfileAction->getFormattedForTelegram($admin);

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(
                    text: '📋 Мои сессии',
                    callback_data: 'sessions:my'
                ),
                InlineKeyboardButton::make(
                    text: '🔄 Обновить',
                    callback_data: 'profile:refresh'
                ),
            );

        try {
            $bot->editMessageText(
                text: $text,
                parse_mode: 'HTML',
                reply_markup: $keyboard,
            );
        } catch (\Throwable) {
            // Сообщение не изменилось — игнорируем
        }

        $bot->answerCallbackQuery(text: '✅ Обновлено');
    }
}
