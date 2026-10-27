@extends('layouts.app')
@section('titulo', 'Actividades')
@section('subtitulo', 'Registra el contacto con tus clientes y organiza los siguientes pasos.')
@section('acciones')
@if(auth()->user()->puedeGestionar() || auth()->user()->esCocina())
<a class="boton" href="{{ route('actividades.create') }}">+ Nueva actividad</a>
@endif
@endsection
@section('contenido')
<form class="filtros" method="GET">
    <select name="filtro" aria-label="Filtrar actividades">
        <option value="todas" @selected($filtro === 'todas')>Todas las actividades</option>
        <option value="pendientes" @selected($filtro === 'pendientes')>Pendientes</option>
        <option value="completadas" @selected($filtro === 'completadas')>Completadas</option>
    </select>
    <button class="boton secundario" type="submit">Filtrar</button>
</form>
<section class="panel">
    @forelse($actividades as $actividad)
    <article class="elemento-lista">
        <div class="fila">
            <div>
                <strong>{{ $actividad->asunto }}</strong>
                <p>{{ $actividad->cliente->nombre }} · {{ $actividad->tipo }}</p>
            </div>
            <span class="insignia {{ $actividad->completada ? 'verde' : 'ambar' }}">{{ $actividad->completada ? 'Completada' : 'Pendiente' }}</span>
        </div>
        <p class="con-saltos">{{ $actividad->descripcion }}</p>
        <small>{{ $actividad->autor->name }} · Registrada {{ $actividad->created_at->format('d/m/Y H:i') }}</small>
        @if($actividad->programada_para)
        <small>Programada: {{ $actividad->programada_para->format('d/m/Y H:i') }}</small>
        @endif
        <div class="acciones-tabla espacio-arriba">
            @if($actividad->venta)
            <a class="enlace" href="{{ route('ventas.show', $actividad->venta) }}">Ver venta #NX-{{ $actividad->venta_id }} →</a>
            @endif
            @if(! $actividad->completada && (auth()->user()->puedeGestionar() || auth()->user()->esCocina()))
            <form action="{{ route('actividades.completar', $actividad) }}" method="POST">
                @csrf
                @method('PATCH')
                <button class="boton secundario pequeno" type="submit">Marcar completada</button>
            </form>
            @endif
        </div>
    </article>
    @empty
    <p class="vacio">No hay actividades con este filtro.</p>
    @endforelse
</section>
{{ $actividades->links('partials.paginacion') }}
@endsection
