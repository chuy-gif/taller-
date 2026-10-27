<?php

namespace App\Http\Controllers;

use App\Models\Negocio;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AccesoController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function entrar(Request $request)
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);
        $datos = $request->validate(['email' => 'required|email|max:150', 'password' => 'required|string|max:255']);
        $clave = 'login:'.$datos['email'].'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($clave, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Demasiados intentos. Espera '.RateLimiter::availableIn($clave).' segundos.',
            ]);
        }

        if (! Auth::attempt([...$datos, 'activo' => true])) {
            RateLimiter::hit($clave, 60);
            throw ValidationException::withMessages(['email' => 'El correo o la contraseña no son correctos.']);
        }

        RateLimiter::clear($clave);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function registro()
    {
        return view('auth.registro');
    }

    public function registrar(Request $request)
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);
        $datos = $request->validate([
            'name' => 'required|string|max:100',
            'negocio' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers(), 'max:255'],
        ]);

        $usuario = DB::transaction(function () use ($datos) {
            $negocio = Negocio::create(['nombre' => $datos['negocio']]);

            // El registro crea un espacio nuevo; no permite entrar a otro negocio.
            // El rol se decide en el servidor, nunca desde un campo del formulario.
            return User::create([
                'name' => $datos['name'], 'email' => $datos['email'],
                'password' => $datos['password'], 'negocio_id' => $negocio->id,
                'rol' => 'admin', 'activo' => true,
            ]);
        });

        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('exito', 'Cuenta creada. Tu espacio está listo.');
    }

    public function salir(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
