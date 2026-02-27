<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class SkipCsrfForApi extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        '/api/pre-session',
        '/api/pre-session/*',
        '/api/pre-sessions',
        '/api/session',
        '/api/session/*',
        '/api/visit',
    ];
}
