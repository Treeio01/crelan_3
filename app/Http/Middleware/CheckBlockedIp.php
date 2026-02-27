<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\BlockedIp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware для проверки блокированных IP адресов
 */
class CheckBlockedIp
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $this->getClientIp($request);
        
        // Telegram webhook должен работать независимо от клиентских блокировок.
        if ($request->is('api/telegram/webhook')) {
            return $next($request);
        }
        
        // Проверяем, заблокирован ли IP
        if (BlockedIp::isBlocked($ip)) {
            abort(403, 'Access denied');
        }
        
        return $next($request);
    }

    /**
     * Получение IP адреса клиента с учетом прокси
     */
    private function getClientIp(Request $request): string
    {
        $headers = [
            'CF-Connecting-IP', // Cloudflare
            'X-Real-IP',
            'X-Forwarded-For',
        ];
        
        foreach ($headers as $header) {
            if ($request->header($header)) {
                $forwardedIps = explode(',', (string) $request->header($header));
                $ip = trim($forwardedIps[0]);
                if (!empty($ip) && $ip !== '127.0.0.1') {
                    return $ip;
                }
            }
        }
        
        return $request->ip() ?: '127.0.0.1';
    }
}
