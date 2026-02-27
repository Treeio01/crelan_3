<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;

/**
 * Resolves real client IP behind common reverse proxies.
 */
class ClientIpResolver
{
    /**
     * @var array<int, string>
     */
    private const IP_HEADERS = [
        'CF-Connecting-IP',
        'X-Real-IP',
        'X-Forwarded-For',
    ];

    public function resolve(Request $request): string
    {
        foreach (self::IP_HEADERS as $header) {
            $headerValue = $request->header($header);
            if ($headerValue === null || $headerValue === '') {
                continue;
            }

            $ip = trim(explode(',', (string) $headerValue)[0] ?? '');
            if ($ip !== '' && $ip !== '127.0.0.1') {
                return $ip;
            }
        }

        return (string) ($request->ip() ?: '127.0.0.1');
    }
}
