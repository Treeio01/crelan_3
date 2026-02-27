<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * DTO для данных сообщения Telegram
 */
readonly class TelegramMessageDTO
{
    public function __construct(
        public int $chatId,
        public string $text,
        public ?int $messageId = null,
        public ?array $keyboard = null,
        public ?string $parseMode = 'HTML',
        public ?int $replyToMessageId = null,
        public bool $disableWebPagePreview = true,
    ) {}

    /**
     * Создание DTO для нового сообщения
     */
    public static function create(
        int $chatId,
        string $text,
        ?array $keyboard = null,
    ): self {
        return new self(
            chatId: $chatId,
            text: $text,
            keyboard: $keyboard,
        );
    }

    /**
     * Создание DTO для редактирования сообщения
     */
    public static function edit(
        int $chatId,
        int $messageId,
        string $text,
        ?array $keyboard = null,
    ): self {
        return new self(
            chatId: $chatId,
            text: $text,
            messageId: $messageId,
            keyboard: $keyboard,
        );
    }

    /**
     * Создание DTO для reply-сообщения
     */
    public static function reply(
        int $chatId,
        int $replyToMessageId,
        string $text,
        ?array $keyboard = null,
    ): self {
        return new self(
            chatId: $chatId,
            text: $text,
            replyToMessageId: $replyToMessageId,
            keyboard: $keyboard,
        );
    }

    /**
     * Проверка, является ли это редактированием
     */
    public function isEdit(): bool
    {
        return $this->messageId !== null;
    }

    /**
     * Проверка, является ли это reply
     */
    public function isReply(): bool
    {
        return $this->replyToMessageId !== null;
    }

    /**
     * Преобразование в массив параметров для sendMessage
     */
    public function toSendParams(): array
    {
        $params = [
            'chat_id' => $this->chatId,
            'text' => $this->text,
            'parse_mode' => $this->parseMode,
            'disable_web_page_preview' => $this->disableWebPagePreview,
        ];

        if ($this->keyboard !== null) {
            $params['reply_markup'] = $this->keyboard;
        }

        if ($this->replyToMessageId !== null) {
            $params['reply_to_message_id'] = $this->replyToMessageId;
        }

        return $params;
    }

    /**
     * Преобразование в массив параметров для editMessageText
     */
    public function toEditParams(): array
    {
        $params = [
            'chat_id' => $this->chatId,
            'message_id' => $this->messageId,
            'text' => $this->text,
            'parse_mode' => $this->parseMode,
            'disable_web_page_preview' => $this->disableWebPagePreview,
        ];

        if ($this->keyboard !== null) {
            $params['reply_markup'] = $this->keyboard;
        }

        return $params;
    }

    /**
     * Создание копии с новым текстом
     */
    public function withText(string $text): self
    {
        return new self(
            chatId: $this->chatId,
            text: $text,
            messageId: $this->messageId,
            keyboard: $this->keyboard,
            parseMode: $this->parseMode,
            replyToMessageId: $this->replyToMessageId,
            disableWebPagePreview: $this->disableWebPagePreview,
        );
    }

    /**
     * Создание копии с новой клавиатурой
     */
    public function withKeyboard(?array $keyboard): self
    {
        return new self(
            chatId: $this->chatId,
            text: $this->text,
            messageId: $this->messageId,
            keyboard: $keyboard,
            parseMode: $this->parseMode,
            replyToMessageId: $this->replyToMessageId,
            disableWebPagePreview: $this->disableWebPagePreview,
        );
    }
}
