<?php

declare(strict_types=1);

namespace App\Providers;

use App\Listeners\BroadcastSessionEventListener;
use App\Listeners\SendTelegramNotificationListener;
use App\Listeners\UpdateSessionHistoryListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Подписчики событий (Event Subscribers)
     * Каждый подписчик обрабатывает несколько типов событий
     */
    protected $subscribe = [
        // Запись в историю сессий
        UpdateSessionHistoryListener::class,
        
        // Broadcasting через WebSocket
        BroadcastSessionEventListener::class,
        
        // Уведомления в Telegram
        SendTelegramNotificationListener::class,
    ];

    /**
     * Регистрация сервисов
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Определение, должны ли события автоматически обнаруживаться
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
