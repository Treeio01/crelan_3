<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SessionStatus;
use App\Events\SessionCreated;
use App\Models\PreSession;
use App\Models\Session;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PreSessionService
{
    public function __construct(
        private readonly LocationService $locationService,
        private readonly DeviceDetectionService $deviceService,
        private readonly ClientIpResolver $clientIpResolver,
    ) {}

    /**
     * Create a new pre-session
     */
    public function create(Request $request, string $pageName, ?string $pageUrl = null): PreSession
    {
        $ip = $this->clientIpResolver->resolve($request);
        $location = $this->locationService->getLocation($ip);
        $userAgent = (string) $request->header('User-Agent', '');
        $locale = app()->getLocale();

        return PreSession::create([
            'ip_address' => $ip,
            'country_code' => $location->countryCode,
            'country_name' => $location->countryName,
            'city' => $location->cityName,
            'user_agent' => $userAgent,
            'locale' => $locale,
            'page_url' => $pageUrl ?: $request->header('Referer'),
            'page_name' => $pageName,
            'device_type' => $this->deviceService->detectDeviceType($userAgent),
            'is_online' => true,
            'last_seen' => now(),
        ]);
    }

    /**
     * Update online status
     */
    public function updateOnlineStatus(PreSession $preSession, bool $isOnline): bool
    {
        return $isOnline ? $preSession->markAsOnline() : $preSession->markAsOffline();
    }

    /**
     * Convert pre-session to main session
     */
    public function convertToMainSession(PreSession $preSession, array $sessionData): Session
    {
        Log::info('PreSessionService: convertToMainSession start', [
            'pre_session_id' => $preSession->id,
            'input_type' => $sessionData['input_type'] ?? null,
            'input_value' => $sessionData['input_value'] ?? null,
            'ip' => $preSession->ip_address,
        ]);

        $session = Session::create(array_merge($sessionData, [
            'pre_session_id' => $preSession->id,
            'ip' => $preSession->ip_address,
            'ip_address' => $preSession->ip_address,
            'status' => SessionStatus::PENDING,
            'country_code' => $preSession->country_code,
            'country_name' => $preSession->country_name,
            'city' => $preSession->city,
            'user_agent' => $preSession->user_agent,
            'locale' => $preSession->locale,
            'device_type' => $preSession->device_type,
        ]));

        $session = $session->fresh();

        Log::info('PreSessionService: convertToMainSession session created', [
            'pre_session_id' => $preSession->id,
            'session_id' => $session->id,
        ]);

        $preSession->update([
            'converted_to_session_id' => $session->id,
            'converted_at' => now(),
        ]);

        Log::info('PreSessionService: convertToMainSession dispatch SessionCreated', [
            'pre_session_id' => $preSession->id,
            'session_id' => $session->id,
        ]);

        event(new SessionCreated($session));

        return $session;
    }

    /**
     * Get all pre-sessions with filters
     */
    public function getAll(array $filters = []): Collection
    {
        $query = PreSession::query();

        if (! empty($filters['country'])) {
            $query->where('country_code', $filters['country']);
        }

        if (! empty($filters['device_type'])) {
            $query->where('device_type', $filters['device_type']);
        }

        if (! empty($filters['status'])) {
            if ($filters['status'] === 'online') {
                $query->online();
            } elseif ($filters['status'] === 'offline') {
                $query->offline();
            }
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        $total = PreSession::count();
        $online = PreSession::online()->count();
        $converted = PreSession::converted()->count();
        $conversionRate = $total > 0 ? ($converted / $total) * 100 : 0;

        return [
            'total' => $total,
            'online' => $online,
            'converted' => $converted,
            'conversion_rate' => round($conversionRate, 1),
        ];
    }
}
