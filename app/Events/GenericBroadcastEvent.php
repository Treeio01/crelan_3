<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Универсальное событие для WebSocket broadcasting
 */
class GenericBroadcastEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $channelName,
        public readonly string $eventName,
        public readonly array $data,
    ) {}

    /**
     * Канал для broadcasting
     */
    public function broadcastOn(): array
    {
        // Определяем тип канала по префиксу
        if (str_starts_with($this->channelName, 'admin.')) {
            // Приватный канал для конкретного админа
            return [new PrivateChannel($this->channelName)];
        }

        if ($this->channelName === 'admin') {
            // Приватный канал для всех админов
            return [new PrivateChannel('admin')];
        }

        if (str_starts_with($this->channelName, 'session.')) {
            // Публичный канал сессии
            return [new Channel($this->channelName)];
        }

        // По умолчанию публичный канал
        return [new Channel($this->channelName)];
    }

    /**
     * Имя события
     */
    public function broadcastAs(): string
    {
        return $this->eventName;
    }

    /**
     * Данные для передачи
     */
    public function broadcastWith(): array
    {
        return $this->data;
    }
}
