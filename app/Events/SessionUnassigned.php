<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Admin;
use App\Models\Session;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие открепления админа от сессии
 */
class SessionUnassigned
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Session $session,
        public readonly Admin $admin,
        public readonly int $previousAdminId,
    ) {}
}
