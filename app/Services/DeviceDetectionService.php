<?php

declare(strict_types=1);

namespace App\Services;

class DeviceDetectionService
{
    /**
     * Detect device type from user agent
     */
    public function detectDeviceType(string $userAgent): string
    {
        if ((bool) preg_match('/Mobile|Android|iPhone|iPad|iPod/', $userAgent)) {
            return (bool) preg_match('/iPad/', $userAgent) ? 'tablet' : 'mobile';
        }

        return 'desktop';
    }

    /**
     * Get device icon
     */
    public function getDeviceIcon(string $deviceType): string
    {
        return match ($deviceType) {
            'desktop' => '🖥️',
            'mobile' => '📱',
            'tablet' => '📱',
            default => '💻',
        };
    }

    /**
     * Check if mobile device
     */
    public function isMobile(string $userAgent): bool
    {
        return (bool) preg_match('/Mobile|Android|iPhone|iPod/', $userAgent);
    }

    /**
     * Check if tablet device
     */
    public function isTablet(string $userAgent): bool
    {
        return (bool) preg_match('/iPad/', $userAgent);
    }

    /**
     * Check if desktop device
     */
    public function isDesktop(string $userAgent): bool
    {
        return ! $this->isMobile($userAgent);
    }
}
