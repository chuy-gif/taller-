<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComprobarUsuario
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()->activo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Esta cuenta está desactivada. Consulta a la administradora.',
            ]);
        }

        return $next($request);
    }
}
