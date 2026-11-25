@extends('layouts.app')
@section('titulo', 'Mi equipo')
@section('subtitulo', 'Administra los usuarios y permisos de tu espacio de trabajo.')
@section('acciones')
<a class="boton" href="{{ route('usuarios.create') }}">+ Agregar integrante</a>
@endsection
@section('contenido')
<section class="panel">
    @foreach($usuarios as $usuario)
    <article class="elemento-lista">
        <div class="fila">
            <div>
                <strong>{{ $usuario->name }}</strong>
                <p>{{ $usuario->email }}</p>
            </div>
            <span class="insignia {{ $usuario->activo ? 'verde' : 'gris' }}">{{ $usuario->activo ? 'Activo' : 'Desactivado' }}</span>
        </div>
        <form class="filtros" method="POST" action="{{ route('usuarios.update', $usuario) }}">
            @csrf
            @method('PUT')
            <select name="rol" aria-label="Rol de {{ $usuario->name }}">
                @foreach(\App\Models\User::ROLES as $clave => $nombre)
                <option value="{{ $clave }}" @selected($usuario->rol === $clave)>
                    {{ $nombre }}
                </option>
                @endforeach
            </select>
            <select name="activo" aria-label="Acceso de {{ $usuario->name }}">
                <option value="1" @selected($usuario->activo)>Activo</option>
                <option value="0" @selected(! $usuario->activo)>Desactivado</option>
            </select>
            <button class="boton secundario pequeno" type="submit">Guardar permisos</button>
        </form>
    </article>
    @endforeach
</section>
@endsection
