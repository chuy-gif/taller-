@extends('layouts.app')
@section('titulo', 'Agregar integrante')
@section('subtitulo', 'Esta persona tendrá acceso a tu negocio con el rol que asignes.')
@section('contenido')
<form class="panel formulario amplio" method="POST" action="{{ route('usuarios.store') }}">
    @csrf
    <div class="campos">
        <label>
            Nombre
            <input name="name" value="{{ old('name') }}" required maxlength="100" />
        </label>
        <label>
            Correo
            <input
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                maxlength="150"
            />
        </label>
        <label class="completo">
            Rol
            <select name="rol">
                @foreach(\App\Models\User::ROLES as $clave => $nombre)
                <option value="{{ $clave }}" @selected(old('rol', 'usuario_final') === $clave)>
                    {{ $nombre }}
                </option>
                @endforeach
            </select>
        </label>
        <label>
            Contraseña inicial
            <input
                type="password"
                name="password"
                minlength="10"
                maxlength="255"
                required
                autocomplete="new-password"
            />
        </label>
        <label>
            Confirma la contraseña
            <input
                type="password"
                name="password_confirmation"
                minlength="10"
                maxlength="255"
                required
                autocomplete="new-password"
            />
        </label>
    </div>
    <p class="nota">
        La contraseña necesita al menos 10 caracteres con letras y números. Solo se guarda su hash.
    </p>
    <div class="acciones-formulario">
        <a class="boton secundario" href="{{ route('usuarios.index') }}">Cancelar</a>
        <button class="boton" type="submit">Crear integrante</button>
    </div>
</form>
@endsection
