@extends('layouts.auth')
@section('titulo', 'Crea tu espacio de trabajo')
@section('subtitulo', 'Tu cuenta tendrá un negocio propio y comenzará con una base vacía.')
@section('contenido')
<form action="{{ route('registro') }}" method="POST" class="formulario">
    @csrf
    <label>
        Tu nombre
        <input
            name="name"
            value="{{ old('name') }}"
            maxlength="100"
            required
            autocomplete="name"
        />
    </label>
    <label>
        Nombre del negocio
        <input name="negocio" value="{{ old('negocio') }}" maxlength="100" required />
    </label>
    <label>
        Correo electrónico
        <input
            type="email"
            name="email"
            value="{{ old('email') }}"
            maxlength="150"
            required
            autocomplete="username"
        />
    </label>
    <label>
        Contraseña
        <input
            type="password"
            name="password"
            minlength="10"
            maxlength="255"
            required
            autocomplete="new-password"
        />
    </label>
    <p class="nota">Usa al menos 10 caracteres, con letras y números.</p>
    <label>
        Confirma tu contraseña
        <input
            type="password"
            name="password_confirmation"
            minlength="10"
            maxlength="255"
            required
            autocomplete="new-password"
        />
    </label>
    <button class="boton" type="submit">Crear mi cuenta</button>
</form>
<p class="ayuda-acceso">
    ¿Ya tienes cuenta?
    <a href="{{ route('login') }}">Iniciar sesión</a>
</p>
@endsection
