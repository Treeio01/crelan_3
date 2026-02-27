<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\FormDataDTO;
use App\DTOs\SessionDTO;
use App\Enums\ActionType;
use App\Enums\InputType;
use App\Enums\SessionStatus;
use App\Events\ActionSelected;
use App\Events\FormSubmitted;
use App\Events\SessionAssigned;
use App\Events\SessionCreated;
use App\Events\SessionStatusChanged;
use App\Events\SessionUnassigned;
use App\Models\Admin;
use App\Models\Session;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для работы с сессиями
 * 
 * Диспатчит события после успешных операций.
 * Listeners обрабатывают: Telegram, WebSocket, History.
 */
class SessionService
{
    /**
     * Создание новой сессии
     */
    public function create(InputType $inputType, string $inputValue, string $ip): Session
    {
        Log::info('SessionService: create start', [
            'input_type' => $inputType->value,
            'input_value' => $inputValue,
            'ip' => $ip,
        ]);

        $session = Session::create([
            'input_type' => $inputType,
            'input_value' => $inputValue,
            'ip' => $ip,
            'ip_address' => $ip,
            'status' => SessionStatus::PENDING,
        ]);

        Log::info('SessionService: create session created', [
            'session_id' => $session->id,
        ]);

        // Диспатч события
        Log::info('SessionService: dispatch SessionCreated', [
            'session_id' => $session->id,
        ]);
        event(new SessionCreated($session));

        return $session;
    }

    /**
     * Получение сессии по ID
     */
    public function find(string $sessionId): ?Session
    {
        return Session::find($sessionId);
    }

    /**
     * Получение сессии или выброс исключения
     */
    public function findOrFail(string $sessionId): Session
    {
        return Session::findOrFail($sessionId);
    }

    /**
     * Назначение админа на сессию
     */
    public function assign(Session $session, Admin $admin): Session
    {
        if (!$session->canBeAssignedTo($admin)) {
            throw new \RuntimeException('Сессия уже назначена на другого админа');
        }

        $session->update([
            'admin_id' => $admin->id,
            'status' => SessionStatus::PROCESSING,
        ]);

        $session = $session->fresh();

        // Диспатч события
        event(new SessionAssigned($session, $admin));

        return $session;
    }

    /**
     * Открепление админа от сессии
     */
    public function unassign(Session $session, Admin $admin): Session
    {
        if ($session->admin_id !== $admin->id) {
            throw new \RuntimeException('Вы не можете открепиться от чужой сессии');
        }

        $previousAdminId = $session->admin_id;

        $session->update([
            'admin_id' => null,
            'status' => SessionStatus::PENDING,
        ]);

        $session = $session->fresh();

        // Диспатч события
        event(new SessionUnassigned($session, $admin, $previousAdminId));

        return $session;
    }

    /**
     * Выбор действия админом
     */
    public function selectAction(Session $session, ActionType $actionType, Admin $admin): Session
    {
        if ($session->admin_id !== $admin->id) {
            throw new \RuntimeException('Вы не можете выбирать действие для чужой сессии');
        }

        $session->update([
            'action_type' => $actionType,
        ]);

        $session = $session->fresh();

        // Диспатч события
        event(new ActionSelected($session, $actionType, $admin));

        return $session;
    }

    /**
     * Сохранение данных формы
     */
    public function submitForm(Session $session, FormDataDTO $formData): Session
    {
        $updateData = $formData->getSessionUpdateData();
        $updateData['last_activity_at'] = now();
        $updateData['action_type'] = null; // Сбрасываем action_type после отправки

        $session->update($updateData);

        $session = $session->fresh();

        // Диспатч события
        event(new FormSubmitted($session, $formData));

        return $session;
    }

    /**
     * Завершение сессии
     */
    public function complete(Session $session, Admin $admin): Session
    {
        if ($session->admin_id !== $admin->id) {
            throw new \RuntimeException('Вы не можете завершить чужую сессию');
        }

        $oldStatus = $session->status;

        $session->update([
            'status' => SessionStatus::COMPLETED,
        ]);

        $session = $session->fresh();

        // Диспатч события
        event(new SessionStatusChanged($session, $oldStatus, SessionStatus::COMPLETED, $admin));

        return $session;
    }

    /**
     * Отмена сессии
     */
    public function cancel(Session $session, ?Admin $admin = null, ?string $reason = null): Session
    {
        $oldStatus = $session->status;

        $session->update([
            'status' => SessionStatus::CANCELLED,
        ]);

        $session = $session->fresh();

        // Диспатч события
        event(new SessionStatusChanged($session, $oldStatus, SessionStatus::CANCELLED, $admin));

        return $session;
    }

    /**
     * Обновление telegram_message_id
     */
    public function updateTelegramMessageId(Session $session, int $messageId): Session
    {
        $session->update([
            'telegram_message_id' => $messageId,
        ]);

        return $session->fresh();
    }

    /**
     * Обновление telegram message_id и chat_id
     */
    public function updateTelegramMessage(Session $session, int $messageId, ?int $chatId = null): Session
    {
        $data = ['telegram_message_id' => $messageId];
        
        if ($chatId !== null) {
            $data['telegram_chat_id'] = $chatId;
        }
        
        $session->update($data);

        return $session->fresh();
    }

    /**
     * Обновление времени последней активности
     */
    public function updateLastActivity(Session $session): Session
    {
        $session->update([
            'last_activity_at' => now(),
        ]);

        return $session;
    }

    /**
     * Получение активных сессий (pending + processing)
     */
    public function getActiveSessions(): Collection
    {
        return Session::active()
            ->with('admin')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Получение сессий админа с пагинацией
     */
    public function getAdminSessions(Admin $admin, int $perPage = 10): LengthAwarePaginator
    {
        return Session::forAdmin($admin->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Получение pending сессий
     */
    public function getPendingSessions(int $limit = 10): Collection
    {
        return Session::pending()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Получение сессий по статусу с пагинацией
     */
    public function getSessionsByStatus(SessionStatus $status, int $perPage = 10): LengthAwarePaginator
    {
        return Session::where('status', $status)
            ->with('admin')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Получение статуса сессии для API
     */
    public function getSessionStatus(string $sessionId): ?SessionDTO
    {
        $session = $this->find($sessionId);

        if ($session === null) {
            return null;
        }

        return SessionDTO::fromModel($session);
    }

    /**
     * Получение статистики сессий
     */
    public function getStats(): array
    {
        return [
            'pending' => Session::where('status', SessionStatus::PENDING->value)->count(),
            'processing' => Session::where('status', SessionStatus::PROCESSING->value)->count(),
            'completed' => Session::where('status', SessionStatus::COMPLETED->value)->count(),
            'cancelled' => Session::where('status', SessionStatus::CANCELLED->value)->count(),
            'total' => Session::count(),
        ];
    }

    /**
     * Проверка онлайн статуса (разница между last_activity_at и now)
     */
    public function isOnline(Session $session, int $thresholdSeconds = 30): bool
    {
        if ($session->last_activity_at === null) {
            return false;
        }

        return $session->last_activity_at->diffInSeconds(now()) < $thresholdSeconds;
    }
}
