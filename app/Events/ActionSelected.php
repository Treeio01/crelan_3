<?php

declare(strict_types=1);

namespace App\Events;

use App\Enums\ActionType;
use App\Models\Admin;
use App\Models\Session;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие выбора действия админом
 */
class ActionSelected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Session $session,
        public readonly ActionType $actionType,
        public readonly Admin $admin,
    ) {}

    /**
     * Проверка, требует ли действие редирект
     */
    public function requiresRedirect(): bool
    {
        return $this->actionType->requiresRedirect();
    }

    /**
     * Получение URL для редиректа
     */
    public function getRedirectUrl(): ?string
    {
        return $this->actionType->getRedirectPath($this->session->id);
    }

    /**
     * Проверка, это действие "Онлайн"
     */
    public function isOnlineCheck(): bool
    {
        return $this->actionType === ActionType::ONLINE;
    }
}
