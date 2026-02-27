<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\BlockedIp;
use App\Services\ClientIpResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware для проверки блокированных IP адресов
 */
class CheckBlockedIp
{
    public function __construct(
        private readonly ClientIpResolver $clientIpResolver,
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $this->clientIpResolver->resolve($request);

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
}
