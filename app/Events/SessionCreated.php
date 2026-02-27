<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Session;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие создания сессии
 */
class SessionCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Session $session,
    ) {}
}
