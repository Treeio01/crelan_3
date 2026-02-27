<?php

declare(strict_types=1);

namespace App\Telegram\Handlers;

use App\Models\Admin;
use Illuminate\Support\Facades\Storage;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

/**
 * Handler для управления Smartsupp
 */
class SmartSuppHandler
{
    private const SETTINGS_FILE = 'smartsupp.json';

    /**
     * Показать меню Smartsupp
     * Callback: menu:smartsupp
     */
    public function showMenu(Nutgram $bot): void
    {
        /** @var Admin $admin */
        $admin = $bot->get('admin');


        $settings = $this->getSettings();
        $enabled = $settings['enabled'] ?? false;
        $key = $settings['key'] ?? '';

        $statusEmoji = $enabled ? '✅' : '❌';
        $statusText = $enabled ? 'Включен' : 'Выключен';
        $keyDisplay = $key ?: '<i>не установлен</i>';

        $text = <<<TEXT
💬 <b>Smartsupp Live Chat</b>

📊 <b>Статус:</b> {$statusEmoji} {$statusText}
🔑 <b>Ключ:</b> <code>{$keyDisplay}</code>

Smartsupp - виджет живого чата для посетителей.
TEXT;

        $toggleText = $enabled ? '❌ Выключить' : '✅ Включить';

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make($toggleText, callback_data: 'smartsupp:toggle'),
                InlineKeyboardButton::make('🔑 Изменить ключ', callback_data: 'smartsupp:set_key'),
            )
            ->addRow(
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
     * Переключить статус Smartsupp
     * Callback: smartsupp:toggle
     */
    public function toggle(Nutgram $bot): void
    {
        /** @var Admin $admin */
        $admin = $bot->get('admin');


        $settings = $this->getSettings();
        $settings['enabled'] = !($settings['enabled'] ?? false);
        $this->saveSettings($settings);

        $status = $settings['enabled'] ? 'включен ✅' : 'выключен ❌';
        $bot->answerCallbackQuery(text: "Smartsupp {$status}");

        // Обновляем меню
        $this->showMenu($bot);
    }

    /**
     * Начать ввод ключа
     * Callback: smartsupp:set_key
     */
    public function startSetKey(Nutgram $bot): void
    {
        /** @var Admin $admin */
        $admin = $bot->get('admin');


        $admin->setPendingAction('smartsupp', 'set_key');

        $text = <<<TEXT
🔑 <b>Установка ключа Smartsupp</b>

Отправьте ключ Smartsupp.

<b>Где найти ключ:</b>
1. Войдите в панель Smartsupp
2. Настройки → Код чата
3. Скопируйте значение key из кода

<b>Пример:</b>
<code>abc123xyz456</code>
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
     * Обработка ввода ключа
     */
    public function processSetKey(Nutgram $bot, Admin $admin, string $key): void
    {
        $key = trim($key);

        if (empty($key)) {
            $bot->sendMessage(
                text: '❌ Ключ не может быть пустым',
                parse_mode: 'HTML',
            );
            return;
        }

        // Сохраняем ключ
        $settings = $this->getSettings();
        $settings['key'] = $key;
        $settings['enabled'] = true; // Автоматически включаем
        $this->saveSettings($settings);

        $admin->clearPendingAction();

        $text = <<<TEXT
✅ <b>Ключ сохранён!</b>

🔑 Ключ: <code>{$key}</code>
📊 Статус: ✅ Включен

Smartsupp теперь активен на всех формах.
TEXT;

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('💬 Smartsupp', callback_data: 'menu:smartsupp'),
                InlineKeyboardButton::make('🔙 Меню', callback_data: 'menu:back'),
            );

        $bot->sendMessage(
            text: $text,
            parse_mode: 'HTML',
            reply_markup: $keyboard,
        );
    }

    /**
     * Получить настройки Smartsupp
     */
    public static function getSettings(): array
    {
        if (Storage::exists(self::SETTINGS_FILE)) {
            $content = Storage::get(self::SETTINGS_FILE);
            return json_decode($content, true) ?? [];
        }

        // Fallback на .env
        return [
            'enabled' => config('services.smartsupp.enabled', false),
            'key' => config('services.smartsupp.key', ''),
        ];
    }

    /**
     * Сохранить настройки Smartsupp
     */
    private function saveSettings(array $settings): void
    {
        Storage::put(self::SETTINGS_FILE, json_encode($settings, JSON_PRETTY_PRINT));
    }
}
