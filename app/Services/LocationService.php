<?php

namespace App\Services;

use Stevebauman\Location\Facades\Location;

class LocationService
{
    /**
     * Get location data for IP
     */
    public function getLocation(string $ip): object
    {
        if ($this->isLocalIP($ip)) {
            return (object) [
                'countryCode' => null,
                'countryName' => 'Local',
                'cityName' => 'Localhost'
            ];
        }
        
        try {
            return Location::get($ip);
        } catch (\Exception $e) {
            return (object) [
                'countryCode' => null,
                'countryName' => 'Unknown',
                'cityName' => 'Unknown'
            ];
        }
    }
    
    /**
     * Check if IP is local
     */
    public function isLocalIP(string $ip): bool
    {
        $localIPs = [
            '127.0.0.1',
            '::1',
            'localhost',
            '0.0.0.0'
        ];
        
        return in_array($ip, $localIPs) || 
               str_starts_with($ip, '192.168.') || 
               str_starts_with($ip, '10.') ||
               str_starts_with($ip, '172.');
    }
}
