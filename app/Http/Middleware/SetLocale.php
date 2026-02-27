<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\ClientIpResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Stevebauman\Location\Facades\Location;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SetLocale
{
    public function __construct(
        private readonly ClientIpResolver $clientIpResolver,
    ) {}

    /**
     * Supported locales
     */
    private const SUPPORTED_LOCALES = ['nl', 'fr'];
    
    /**
     * Default locale
     */
    private const DEFAULT_LOCALE = 'nl';
    
    /**
     * Country to language mapping for Belgium regions
     */
    private const COUNTRY_LANGUAGE_MAPPING = [
        'BE' => 'nl', // Belgium - default to Dutch
        'NL' => 'nl', // Netherlands - Dutch
        'FR' => 'fr', // France - French
        'LU' => 'fr', // Luxembourg - French
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->determineLocale($request);
        
        App::setLocale($locale);
        
        // Store in session for persistence
        session(['locale' => $locale]);
        
        return $next($request);
    }

    /**
     * Determine which locale to use
     */
    private function determineLocale(Request $request): string
    {
        // 1. Check URL parameter (for switching)
        if ($request->has('lang') && $this->isSupported($request->get('lang'))) {
            return $request->get('lang');
        }
        
        // 2. Check domain (FR domain = FR locale, higher priority than session)
        $domainLocale = $this->getLocaleFromDomain($request);
        if ($domainLocale && $this->isSupported($domainLocale)) {
            return $domainLocale;
        }
        
        // 3. Check session
        if (session()->has('locale') && $this->isSupported(session('locale'))) {
            return session('locale');
        }
        
        // 4. Check browser preference (Accept-Language)
        $browserLocale = $request->getPreferredLanguage();
        if (is_string($browserLocale) && $browserLocale !== '') {
            $browserLocale = strtolower(substr($browserLocale, 0, 2));
            if ($this->isSupported($browserLocale)) {
                return $browserLocale;
            }
        }
        
        // 5. Check IP-based location
        $ipBasedLocale = $this->getLocaleFromIP($request);
        if ($ipBasedLocale && $this->isSupported($ipBasedLocale)) {
            return $ipBasedLocale;
        }
        
        // 6. Default
        return self::DEFAULT_LOCALE;
    }

    /**
     * Check if locale is supported
     */
    private function isSupported(?string $locale): bool
    {
        return $locale && in_array($locale, self::SUPPORTED_LOCALES, true);
    }
    
    /**
     * Get locale from domain
     */
    private function getLocaleFromDomain(Request $request): ?string
    {
        $host = $request->getHost();
        
        // Check if domain contains FR indicators
        if (str_contains($host, '.fr') || str_contains($host, '-fr.') || str_contains($host, 'fr-') || str_contains($host, 'french')) {
            return 'fr';
        }
        
        // Check if domain contains NL indicators
        if (str_contains($host, '.nl') || str_contains($host, '-nl.') || str_contains($host, 'nl-') || str_contains($host, 'dutch')) {
            return 'nl';
        }
        
        return null;
    }
    
    /**
     * Get locale from IP-based location
     */
    private function getLocaleFromIP(Request $request): ?string
    {
        try {
            $ip = $this->clientIpResolver->resolve($request);
            
            // Skip for localhost/development
            if ($this->isLocalIP($ip)) {
                return null;
            }

            $countryCode = Cache::remember(
                'locale:country_code:'.$ip,
                now()->addHours(6),
                static function () use ($ip): string {
                    $location = Location::get($ip);

                    return is_object($location) && isset($location->countryCode)
                        ? (string) $location->countryCode
                        : '';
                },
            );

            if ($countryCode !== '' && isset(self::COUNTRY_LANGUAGE_MAPPING[$countryCode])) {
                return self::COUNTRY_LANGUAGE_MAPPING[$countryCode];
            }
        } catch (Throwable $e) {
            // Log error if needed, but don't break the application
            Log::warning('IP location detection failed: '.$e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Check if IP is local/development
     */
    private function isLocalIP(string $ip): bool
    {
        if ($ip === 'localhost') {
            return true;
        }

        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}
