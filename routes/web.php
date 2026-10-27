<?php

use App\Http\Controllers\AccesoController;
use App\Http\Controllers\ActividadController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

// Actividad 10: registro e inicio de sesión.
Route::middleware('guest')->group(function () {
    Route::get('/login', [AccesoController::class, 'login'])->name('login');
    Route::post('/login', [AccesoController::class, 'entrar'])->middleware('throttle:20,1');
    Route::get('/registro', [AccesoController::class, 'registro'])->name('registro');
    Route::post('/registro', [AccesoController::class, 'registrar'])->middleware('throttle:3,1');
});

// Estas rutas exigen una sesión real y una cuenta activa.
Route::middleware(['auth', 'usuario.activo'])->group(function () {
    Route::post('/logout', [AccesoController::class, 'salir'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('clientes', ClienteController::class)->except('destroy');
    Route::patch('/clientes/{cliente}/estado', [ClienteController::class, 'archivar'])->name('clientes.estado');
    Route::resource('usuarios', UsuarioController::class)->only(['index', 'create', 'store', 'update']);

    // Actividad 11: contactos, seguimiento de ventas y actividades.
    Route::resource('contactos', ContactoController::class)->except('show');
    Route::resource('ventas', VentaController::class)->except('destroy');
    Route::patch('/ventas/{venta}/etapa', [VentaController::class, 'etapa'])->name('ventas.etapa');
    Route::patch('/ventas/{venta}/cierre', [VentaController::class, 'cerrar'])->name('ventas.cierre');
    Route::resource('actividades', ActividadController::class)->only(['index', 'create', 'store']);
    Route::patch('/actividades/{actividad}/completar', [ActividadController::class, 'completar'])->name('actividades.completar');
});
