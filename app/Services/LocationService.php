<?php

declare(strict_types=1);

namespace App\Services;

use Stevebauman\Location\Facades\Location;
use Throwable;

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
                'cityName' => 'Localhost',
            ];
        }

        try {
            $location = Location::get($ip);

            if (is_object($location)) {
                return $location;
            }
        } catch (Throwable) {
        }

        return (object) [
            'countryCode' => null,
            'countryName' => 'Unknown',
            'cityName' => 'Unknown',
        ];
    }

    /**
     * Check if IP is local
     */
    public function isLocalIP(string $ip): bool
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
