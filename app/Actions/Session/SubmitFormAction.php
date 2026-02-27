<?php

declare(strict_types=1);

namespace App\Actions\Session;

use App\DTOs\FormDataDTO;
use App\DTOs\SessionDTO;
use App\Models\Session;
use App\Services\SessionService;

/**
 * Action для обработки отправки формы пользователем
 * 
 * События (Telegram, WebSocket, History) обрабатываются через Listeners.
 */
class SubmitFormAction
{
    public function __construct(
        private readonly SessionService $sessionService,
    ) {}

    /**
     * Обработка отправки формы
     */
    public function execute(Session $session, FormDataDTO $formData): SessionDTO
    {
        $session = $this->sessionService->submitForm($session, $formData);

        return SessionDTO::fromModel($session);
    }

    /**
     * Обработка отправки формы из массива данных
     */
    public function fromArray(string $sessionId, array $data): SessionDTO
    {
        $session = $this->sessionService->findOrFail($sessionId);
        $formData = FormDataDTO::fromArray(array_merge($data, ['session_id' => $sessionId]));

        return $this->execute($session, $formData);
    }
}
