@extends('layouts.app')
@section('titulo', 'Clientes')
@section('subtitulo', 'La información de las familias y empresas que compran contigo.')
@section('acciones')
@if(auth()->user()->puedeGestionar())
<a class="boton" href="{{ route('clientes.create') }}">+ Nuevo cliente</a>
@endif
@endsection
@section('contenido')
<form class="filtros" method="GET" action="{{ route('clientes.index') }}">
    <input
        name="buscar"
        value="{{ $buscar }}"
        placeholder="Buscar por nombre"
        aria-label="Buscar clientes"
        maxlength="100"
    />
    <button class="boton secundario" type="submit">Buscar</button>
</form>
<section class="panel tabla-contenedor">
    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Canal</th>
                <th>Teléfono</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($clientes as $cliente)
            <tr>
                <td>
                    <strong>{{ $cliente->nombre }}</strong>
                    <small>{{ $cliente->correo ?? 'Sin correo' }}</small>
                </td>
                <td>{{ $cliente->canal }}</td>
                <td>{{ $cliente->telefono ?? 'Sin teléfono' }}</td>
                <td><span class="insignia {{ $cliente->activo ? 'verde' : 'gris' }}">{{ $cliente->activo ? 'Activo' : 'Archivado' }}</span></td>
                <td><a class="enlace" href="{{ route('clientes.show', $cliente) }}">Ver ficha →</a></td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="vacio">
                    No hay clientes con este filtro. Puedes registrar uno nuevo.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</section>
{{ $clientes->links('partials.paginacion') }}
@endsection
