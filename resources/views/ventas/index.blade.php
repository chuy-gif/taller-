@extends('layouts.app')
@section('titulo', 'Ventas y pedidos')
@section('subtitulo', 'Sigue cada oportunidad desde la consulta hasta la entrega.')
@section('acciones')
@if(auth()->user()->puedeGestionar())
<a class="boton" href="{{ route('ventas.create') }}">+ Nueva venta</a>
@endif
@endsection
@section('contenido')
<form class="filtros" method="GET">
    <select name="estado" aria-label="Filtrar estado">
        <option value="abierta" @selected($estado === 'abierta')>Abiertas</option>
        <option value="ganada" @selected($estado === 'ganada')>Entregadas / ganadas</option>
        <option value="perdida" @selected($estado === 'perdida')>Perdidas</option>
        <option value="todas" @selected($estado === 'todas')>Todas</option>
    </select>
    <input
        name="buscar"
        value="{{ $buscar }}"
        placeholder="Buscar producto o cliente"
        aria-label="Buscar ventas"
        maxlength="100"
    />
    <button class="boton secundario" type="submit">Filtrar</button>
</form>
@if($estado === 'abierta')
<div class="embudo">
    @foreach(\App\Models\Venta::ETAPAS as $clave => $nombre)
    <section class="columna-embudo">
        <h2>
            {{ $nombre }}
            <span>{{ $ventas->where('etapa', $clave)->count() }}</span>
        </h2>
        @forelse($ventas->where('etapa', $clave) as $venta)
        <article class="tarjeta-venta">
            <small>#NX-{{ $venta->id }}</small>
            <h3><a href="{{ route('ventas.show', $venta) }}">{{ $venta->cliente->nombre }}</a></h3>
            <p>{{ $venta->producto }} · {{ $venta->porciones }} p.</p>
            <p>{{ $venta->evento }}</p>
            @unless(auth()->user()->esCocina())
            <strong class="monto">${{ number_format($venta->monto, 2) }} MXN</strong>
            @endunless
            <small>{{ $venta->fecha_entrega->format('d/m/Y') }} · {{ $venta->hora_entrega }}</small>
            <a class="enlace" href="{{ route('ventas.show', $venta) }}">Abrir seguimiento →</a>
        </article>
        @empty
        <p class="vacio">Sin pedidos en esta etapa.</p>
        @endforelse
    </section>
    @endforeach
</div>
@else
<section class="panel tabla-contenedor">
    <table>
        <thead>
            <tr>
                <th>Venta</th>
                <th>Cliente</th>
                <th>Entrega</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($ventas as $venta)
            <tr>
                <td><strong>#NX-{{ $venta->id }} · {{ $venta->producto }}</strong></td>
                <td>{{ $venta->cliente->nombre }}</td>
                <td>{{ $venta->fecha_entrega->format('d/m/Y') }}</td>
                <td>{{ $venta->estado }}</td>
                <td><a class="enlace" href="{{ route('ventas.show', $venta) }}">Ver ficha →</a></td>
            </tr>
            @empty
            <tr><td colspan="5" class="vacio">No hay ventas con este filtro.</td></tr>
            @endforelse
        </tbody>
    </table>
</section>
@endif
@endsection
