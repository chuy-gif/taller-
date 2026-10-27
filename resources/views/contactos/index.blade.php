@extends('layouts.app')
@section('titulo', 'Contactos')
@section('subtitulo', 'Las personas con quienes hablas dentro de cada familia o empresa.')
@section('acciones')
@if(auth()->user()->puedeGestionar())
<a class="boton" href="{{ route('contactos.create') }}">+ Nuevo contacto</a>
@endif
@endsection
@section('contenido')
<form class="filtros" method="GET">
    <input
        name="buscar"
        value="{{ $buscar }}"
        placeholder="Buscar persona"
        aria-label="Buscar contactos"
        maxlength="100"
    />
    <button class="boton secundario" type="submit">Buscar</button>
</form>
<section class="panel tabla-contenedor">
    <table>
        <thead>
            <tr>
                <th>Persona</th>
                <th>Cliente</th>
                <th>Contacto</th>
                <th>Principal</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contactos as $contacto)
            <tr>
                <td>
                    <strong>{{ $contacto->nombre }}</strong>
                    <small>{{ $contacto->cargo }}</small>
                </td>
                <td><a class="enlace" href="{{ route('clientes.show', $contacto->cliente) }}">{{ $contacto->cliente->nombre }}</a></td>
                <td>
                    {{ $contacto->correo }}
                    <small>{{ $contacto->telefono }}</small>
                </td>
                <td>{{ $contacto->principal ? 'Sí' : 'No' }}</td>
                <td>
                    @if(auth()->user()->puedeGestionar())
                    <div class="acciones-tabla">
                        <a class="enlace" href="{{ route('contactos.edit', $contacto) }}">Editar</a>
                        <form
                            action="{{ route('contactos.destroy', $contacto) }}"
                            method="POST"
                            data-confirmar="¿Retirar a esta persona de la agenda?"
                        >
                            <!-- El borrado es lógico: el registro permanece en la base. -->
                            @csrf
                            @method('DELETE')
                            <button class="enlace peligro" type="submit">Retirar</button>
                        </form>
                    </div>
                    @else
                    <span class="texto-suave">Solo consulta</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="vacio">No hay contactos con este filtro.</td></tr>
            @endforelse
        </tbody>
    </table>
</section>
{{ $contactos->links('partials.paginacion') }}
@endsection
