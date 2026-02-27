<?php

declare(strict_types=1);

namespace App\Enums;

enum AdminRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Супер-админ',
            self::ADMIN => 'Админ',
        };
    }

    public function emoji(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => '👑',
            self::ADMIN => '👤',
        };
    }

    public function canAddAdmins(): bool
    {
        return $this === self::SUPER_ADMIN;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
