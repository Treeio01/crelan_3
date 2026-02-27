<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ActionSelected;
use App\Events\FormSubmitted;
use App\Events\SessionAssigned;
use App\Events\SessionCreated;
use App\Events\SessionStatusChanged;
use App\Events\SessionUnassigned;
use App\Models\SessionHistory;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Listener для записи истории изменений сессий
 */
class UpdateSessionHistoryListener implements ShouldQueue
{
    /**
     * Обработка события создания сессии
     */
    public function handleSessionCreated(SessionCreated $event): void
    {
        SessionHistory::log(
            sessionId: $event->session->id,
            actionType: 'session_created',
            data: [
                'input_type' => $event->session->input_type->value,
                'input_value' => $event->session->input_value,
                'ip' => $event->session->ip,
            ]
        );
    }

    /**
     * Обработка события назначения админа
     */
    public function handleSessionAssigned(SessionAssigned $event): void
    {
        SessionHistory::log(
            sessionId: $event->session->id,
            actionType: 'session_assigned',
            adminId: $event->admin->id,
            data: [
                'admin_username' => $event->admin->username,
            ]
        );
    }

    /**
     * Обработка события открепления админа
     */
    public function handleSessionUnassigned(SessionUnassigned $event): void
    {
        SessionHistory::log(
            sessionId: $event->session->id,
            actionType: 'session_unassigned',
            adminId: $event->previousAdminId,
            data: [
                'admin_username' => $event->admin->username,
            ]
        );
    }

    /**
     * Обработка события отправки формы
     */
    public function handleFormSubmitted(FormSubmitted $event): void
    {
        SessionHistory::log(
            sessionId: $event->session->id,
            actionType: 'form_submitted',
            adminId: $event->session->admin_id,
            data: $event->formData->getHistoryData()
        );
    }

    /**
     * Обработка события изменения статуса
     */
    public function handleSessionStatusChanged(SessionStatusChanged $event): void
    {
        $actionType = match (true) {
            $event->isCompleted() => 'session_completed',
            $event->isCancelled() => 'session_cancelled',
            default => 'session_status_changed',
        };

        SessionHistory::log(
            sessionId: $event->session->id,
            actionType: $actionType,
            adminId: $event->admin?->id,
            data: [
                'old_status' => $event->oldStatus->value,
                'new_status' => $event->newStatus->value,
            ]
        );
    }

    /**
     * Обработка события выбора действия
     */
    public function handleActionSelected(ActionSelected $event): void
    {
        SessionHistory::log(
            sessionId: $event->session->id,
            actionType: 'action_selected',
            adminId: $event->admin->id,
            data: [
                'action_type' => $event->actionType->value,
                'action_label' => $event->actionType->label(),
            ]
        );
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
