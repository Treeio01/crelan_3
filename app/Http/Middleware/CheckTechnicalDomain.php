<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware для проверки технических доменов
 * 
 * Технические домены (только для webhook) редиректят на crelan.be
 */
class CheckTechnicalDomain
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        
        // Пропускаем API запросы (webhook и т.д.) и публичный botodel endpoint
        if ($request->is('api/*') || $request->is('botodel-icon')) {
            return $next($request);
        }
        
        // Получаем технический домен из конфига
        $technicalDomain = config('services.domains.technical');
        
        if ($technicalDomain) {
            $technicalDomains = [
                $technicalDomain,
                'www.' . $technicalDomain,
            ];
            
            // Технический домен — редирект
            if (in_array($host, $technicalDomains)) {
                return redirect('https://www.crelan.be');
            }
        }
        
        return $next($request);
    }
}
