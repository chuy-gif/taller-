<?php

use App\Http\Middleware\ComprobarUsuario;
use App\Http\Middleware\SeguridadCabeceras;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SeguridadCabeceras::class);
        $middleware->alias(['usuario.activo' => ComprobarUsuario::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Laravel conserva el manejo estándar de errores y validaciones.
    })
    ->create();
