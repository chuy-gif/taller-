@extends('layouts.app')
@section('titulo', $cliente->exists ? 'Editar cliente' : 'Nuevo cliente')
@section('subtitulo', 'Guarda datos de contacto e indicaciones para el equipo.')
@section('contenido')
<form class="panel formulario amplio" action="{{ $cliente->exists ? route('clientes.update', $cliente) : route('clientes.store') }}" method="POST">
    @csrf
    @if($cliente->exists)
    @method('PUT')
    @endif
    <div class="campos">
        <label class="completo">
            Nombre del cliente
            <input name="nombre" value="{{ old('nombre', $cliente->nombre) }}" required maxlength="100" />
        </label>
        <label>
            Canal de contacto
            <select name="canal">
                @foreach(['WhatsApp', 'Instagram', 'Tienda', 'Correo', 'Teléfono'] as $canal)
                <option @selected(old('canal', $cliente->canal) === $canal)>{{ $canal }}</option>
                @endforeach
            </select>
        </label>
        <label>
            Teléfono
            <input name="telefono" value="{{ old('telefono', $cliente->telefono) }}" maxlength="25" autocomplete="tel" />
        </label>
        <label class="completo">
            Correo electrónico
            <input name="correo" type="email" value="{{ old('correo', $cliente->correo) }}" maxlength="150" />
        </label>
        <label class="completo">
            Alergias e indicaciones
            <textarea name="alergias" maxlength="1000" aria-describedby="nota-alergias">{{ old('alergias', $cliente->alergias) }}</textarea>
            <small class="nota" id="nota-alergias">En esta demostración, escribe únicamente ejemplos ficticios. <a class="enlace" href="{{ route('privacidad') }}#aviso-integral">Consulta el aviso integral</a>.</small>
        </label>
        <label class="completo">
            Notas
            <textarea name="notas" maxlength="2000">{{ old('notas', $cliente->notas) }}</textarea>
        </label>
    </div>
    <div class="acciones-formulario">
        <a class="boton secundario" href="{{ route('clientes.index') }}">Cancelar</a>
        <button class="boton" type="submit">Guardar cliente</button>
    </div>
</form>
@endsection
