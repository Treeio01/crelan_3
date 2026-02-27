<?php

declare(strict_types=1);

namespace App\Actions\Session;

use App\DTOs\SessionDTO;
use App\Enums\InputType;
use App\Services\SessionService;

/**
 * Action для создания новой сессии
 * 
 * События (Telegram, WebSocket, History) обрабатываются через Listeners.
 */
class CreateSessionAction
{
    public function __construct(
        private readonly SessionService $sessionService,
    ) {}

    /**
     * Создание новой сессии
     */
    public function execute(InputType $inputType, string $inputValue, string $ip): SessionDTO
    {
        $session = $this->sessionService->create($inputType, $inputValue, $ip);

        return SessionDTO::fromModel($session);
    }

    /**
     * Создание сессии из массива данных
     */
    public function fromArray(array $data): SessionDTO
    {
        $inputType = $data['input_type'] instanceof InputType
            ? $data['input_type']
            : InputType::from($data['input_type']);

        return $this->execute(
            inputType: $inputType,
            inputValue: $data['input_value'],
            ip: $data['ip'],
        );
    }
}
