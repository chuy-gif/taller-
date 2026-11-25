@extends('layouts.auth')
@section('titulo', 'Qué gusto verte de nuevo')
@section('subtitulo', 'Entra con tu cuenta para continuar con los pedidos del día.')
@section('contenido')
<form action="{{ route('login') }}" method="POST" class="formulario">
    @csrf
    <label>
        Correo electrónico
        <input
            type="email"
            name="email"
            value="{{ old('email') }}"
            required
            maxlength="150"
            autocomplete="username"
            autofocus
        />
    </label>
    <label>
        Contraseña
        <span class="campo-contrasena">
            <input
                type="password"
                name="password"
                id="password"
                required
                maxlength="255"
                autocomplete="current-password"
            />
            <button
                type="button"
                class="ver-contrasena"
                aria-controls="password"
                aria-pressed="false"
            >
                Mostrar
            </button>
        </span>
    </label>
    <button class="boton" type="submit">Entrar a Nexo →</button>
</form>
<p class="ayuda-acceso">
    ¿Es tu primer acceso?
    <a href="{{ route('registro') }}">Crear cuenta y espacio de trabajo</a>
</p>
<p class="nota">Para entrar a un equipo existente, la administradora debe registrar tu usuario.</p>
@endsection
