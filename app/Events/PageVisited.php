<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Session;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие посещения страницы пользователем
 */
class PageVisited
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Session $session,
        public readonly string $pageName,
        public readonly string $pageUrl,
        public readonly ?string $actionType = null,
    ) {}
}
