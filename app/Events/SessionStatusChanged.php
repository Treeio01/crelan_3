<?php

declare(strict_types=1);

namespace App\Events;

use App\Enums\SessionStatus;
use App\Models\Admin;
use App\Models\Session;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие изменения статуса сессии
 */
class SessionStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Session $session,
        public readonly SessionStatus $oldStatus,
        public readonly SessionStatus $newStatus,
        public readonly ?Admin $admin = null,
    ) {}

    /**
     * Проверка, завершена ли сессия
     */
    public function isCompleted(): bool
    {
        return $this->newStatus === SessionStatus::COMPLETED;
    }

    /**
     * Проверка, отменена ли сессия
     */
    public function isCancelled(): bool
    {
        return $this->newStatus === SessionStatus::CANCELLED;
    }
}
