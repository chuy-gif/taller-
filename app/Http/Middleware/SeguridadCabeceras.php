<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SeguridadCabeceras
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'same-origin');
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('Content-Security-Policy',
            "default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; ".
            "form-action 'self'; frame-ancestors 'self'; base-uri 'self'; object-src 'none'"
        );

        return $response;
    }
}
