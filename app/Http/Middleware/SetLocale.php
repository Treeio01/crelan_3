<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Stevebauman\Location\Facades\Location;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
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
            $ip = $this->getClientIP($request);
            
            // Skip for localhost/development
            if ($this->isLocalIP($ip)) {
                return null;
            }
            
            $location = Location::get($ip);
            
            if ($location && $location->countryCode) {
                $countryCode = $location->countryCode;
                
                // Check if we have a mapping for this country
                if (isset(self::COUNTRY_LANGUAGE_MAPPING[$countryCode])) {
                    return self::COUNTRY_LANGUAGE_MAPPING[$countryCode];
                }
            }
        } catch (\Exception $e) {
            // Log error if needed, but don't break the application
            \Log::warning('IP location detection failed: ' . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Get client IP address
     */
    private function getClientIP(Request $request): string
    {
        // Check X-Forwarded-For header first (for proxies/Cloudflare)
        if ($request->header('X-Forwarded-For')) {
            $forwardedIps = explode(',', $request->header('X-Forwarded-For'));
            $ip = trim($forwardedIps[0]);
            if (!empty($ip) && $ip !== '127.0.0.1') {
                return $ip;
            }
        }
        
        // Check X-Real-IP header
        if ($request->header('X-Real-IP')) {
            $ip = $request->header('X-Real-IP');
            if (!empty($ip) && $ip !== '127.0.0.1') {
                return $ip;
            }
        }
        
        // Check CF-Connecting-IP header (Cloudflare)
        if ($request->header('CF-Connecting-IP')) {
            $ip = $request->header('CF-Connecting-IP');
            if (!empty($ip) && $ip !== '127.0.0.1') {
                return $ip;
            }
        }
        
        // Fall back to default Laravel method
        $ip = $request->ip();
        if (!empty($ip) && $ip !== '127.0.0.1') {
            return $ip;
        }
        
        return '127.0.0.1';
    }
    
    /**
     * Check if IP is local/development
     */
    private function isLocalIP(string $ip): bool
    {
        return in_array($ip, [
            '127.0.0.1',
            '::1',
            'localhost',
            '0.0.0.0'
        ]) || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.');
    }
}
