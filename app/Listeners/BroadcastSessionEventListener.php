<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ActionSelected;
use App\Events\FormSubmitted;
use App\Events\SessionAssigned;
use App\Events\SessionCreated;
use App\Events\SessionStatusChanged;
use App\Events\SessionUnassigned;
use App\Services\WebSocketService;

/**
 * Listener для broadcasting событий через WebSocket
 *
 * Выполняется синхронно — WebSocket-событие отправляется мгновенно.
 * Сам broadcast через GenericBroadcastEvent (ShouldBroadcast) попадает в очередь.
 */
class BroadcastSessionEventListener
{
    public function __construct(
        private readonly WebSocketService $webSocketService,
    ) {}

    /**
     * Обработка события создания сессии
     */
    public function handleSessionCreated(SessionCreated $event): void
    {
        $this->webSocketService->broadcastSessionCreated($event->session);
        $this->webSocketService->broadcastNewSessionToAdmins($event->session);
    }

    /**
     * Обработка события назначения админа
     */
    public function handleSessionAssigned(SessionAssigned $event): void
    {
        $this->webSocketService->broadcastSessionAssigned($event->session);
    }

    /**
     * Обработка события открепления админа
     */
    public function handleSessionUnassigned(SessionUnassigned $event): void
    {
        $this->webSocketService->broadcastSessionUnassigned(
            $event->session,
            $event->previousAdminId
        );
    }

    /**
     * Обработка события отправки формы
     */
    public function handleFormSubmitted(FormSubmitted $event): void
    {
        $this->webSocketService->broadcastFormSubmitted(
            $event->session,
            $event->formData->actionType->value,
            $event->formData->getHistoryData()
        );
    }

    /**
     * Обработка события изменения статуса
     */
    public function handleSessionStatusChanged(SessionStatusChanged $event): void
    {
        if ($event->isCompleted()) {
            $this->webSocketService->broadcastSessionCompleted($event->session);
        } elseif ($event->isCancelled()) {
            $this->webSocketService->broadcastSessionCancelled($event->session);
        }
    }

    /**
     * Обработка события выбора действия
     */
    public function handleActionSelected(ActionSelected $event): void
    {
        $this->webSocketService->broadcastActionSelected(
            $event->session,
            $event->actionType
        );

        // Специальная обработка для действия "Онлайн"
        if ($event->isOnlineCheck()) {
            $this->webSocketService->broadcastOnlineCheck($event->session);
        }
    }

    /**
     * Подписка на события
     */
    public function subscribe($events): array
    {
        return [
            SessionCreated::class => 'handleSessionCreated',
            SessionAssigned::class => 'handleSessionAssigned',
            SessionUnassigned::class => 'handleSessionUnassigned',
            FormSubmitted::class => 'handleFormSubmitted',
            SessionStatusChanged::class => 'handleSessionStatusChanged',
            ActionSelected::class => 'handleActionSelected',
        ];
    }
}
