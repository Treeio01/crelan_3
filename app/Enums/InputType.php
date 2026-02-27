<?php

declare(strict_types=1);

namespace App\Enums;

enum InputType: string
{
    case PHONE = 'phone';
    case ID = 'id';
    case CODE = 'code';

    public function label(): string
    {
        return match ($this) {
            self::PHONE => 'Телефон',
            self::ID => 'ID',
            self::CODE => 'Код',
        };
    }

    public function emoji(): string
    {
        return match ($this) {
            self::PHONE => '📞',
            self::ID => '🆔',
            self::CODE => '📱',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
