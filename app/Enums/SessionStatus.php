<?php

declare(strict_types=1);

namespace App\Enums;

enum SessionStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Ожидание',
            self::PROCESSING => 'В обработке',
            self::COMPLETED => 'Завершена',
            self::CANCELLED => 'Отменена',
        };
    }

    public function emoji(): string
    {
        return match ($this) {
            self::PENDING => '🕐',
            self::PROCESSING => '⚙️',
            self::COMPLETED => '✅',
            self::CANCELLED => '❌',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
