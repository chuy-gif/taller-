@extends('layouts.app')
@section('titulo', 'Hola, '.auth()->user()->name)
@section('subtitulo', 'Esto es lo que pasa hoy en tu negocio.')
@section('acciones')
@if(auth()->user()->puedeGestionar())
<a class="boton" href="{{ route('ventas.create') }}">+ Nueva venta</a>
@endif
@endsection
@section('contenido')
<section class="bienvenida">
    <div>
        <h2>{{ $entregas->total() }} entregas para hoy</h2>
        <p>
            {{ $frias->total() }} cotizaciones necesitan seguimiento. Revisa también tus actividades
            pendientes.
        </p>
    </div>
    <span aria-hidden="true">✳</span>
</section>
<section class="indicadores" aria-label="Indicadores del negocio">
    <article>
        <p>Entregas de hoy</p>
        <strong>{{ $entregas->total() }}</strong>
        <small>Pedidos abiertos para hoy</small>
    </article>
    <article>
        <p>Cotizaciones frías</p>
        <strong>{{ $frias->total() }}</strong>
        <small>Más de 7 días sin actividad ni anticipo</small>
    </article>
    <article>
        <p>{{ auth()->user()->esCocina() ? 'Pedidos asignados' : 'Monto en pedidos' }}</p>
        <strong>{{ auth()->user()->esCocina() ? $resumen->sum() : '$'.number_format($monto, 2) }}</strong>
        <small>{{ auth()->user()->esCocina() ? 'Trabajo de tu equipo de cocina' : 'Confirmados y listos · MXN' }}</small>
    </article>
    <article>
        <p>Clientes activos</p>
        <strong>{{ $clientes }}</strong>
        <small>En tu espacio de trabajo</small>
    </article>
</section>
<section class="modulos" aria-label="Módulos de gestión">
    <a href="{{ route('clientes.index') }}">
        <strong>Clientes</strong>
        <small>Datos y ficha del comprador →</small>
    </a>
    <a href="{{ route('contactos.index') }}">
        <strong>Contactos</strong>
        <small>Personas y medios de contacto →</small>
    </a>
    <a href="{{ route('ventas.index') }}">
        <strong>Ventas y pedidos</strong>
        <small>Embudo y seguimiento comercial →</small>
    </a>
    <a href="{{ route('actividades.index') }}">
        <strong>Actividades</strong>
        <small>Notas, llamadas y pendientes →</small>
    </a>
</section>
<div class="dos-columnas">
    <section class="panel">
        <header class="panel-titulo">
            <h2>Entregas de hoy</h2>
            <small>{{ today()->format('d/m/Y') }}</small>
        </header>
        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th>Cliente / pedido</th>
                        <th>Hora</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entregas as $venta)
                    <tr>
                        <td>
                            <strong>{{ $venta->cliente->nombre }}</strong>
                            <small>{{ $venta->producto }} · {{ $venta->porciones }} p.</small>
                        </td>
                        <td>{{ $venta->hora_entrega }}</td>
                        <td><span class="insignia">{{ \App\Models\Venta::ETAPAS[$venta->etapa] }}</span></td>
                        <td><a class="enlace" href="{{ route('ventas.show', $venta) }}">Ver ficha →</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="vacio">No hay entregas abiertas para hoy.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $entregas->links('partials.paginacion', ['etiqueta' => 'Páginas de entregas de hoy']) }}
        <header class="panel-titulo separador"><h2>Embudo de ventas abiertas</h2></header>
        <div class="resumen-embudo">
            @foreach(\App\Models\Venta::ETAPAS as $clave => $nombre)
            <div>
                <small>{{ $nombre }}</small>
                <strong>{{ $resumen[$clave] ?? 0 }}</strong>
            </div>
            @endforeach
        </div>
    </section>
    <section class="panel">
        <header class="panel-titulo">
            <h2>Actividades pendientes</h2>
            <a class="enlace" href="{{ route('actividades.index', ['filtro' => 'pendientes']) }}">Ver todas</a>
        </header>
        @forelse($pendientes as $actividad)
        <article class="elemento-lista">
            <strong>{{ $actividad->asunto }}</strong>
            <p>{{ $actividad->cliente->nombre }}</p>
            <small>{{ $actividad->programada_para?->format('d/m/Y H:i') ?? 'Sin fecha programada' }}</small>
        </article>
        @empty
        <p class="vacio">No tienes actividades pendientes.</p>
        @endforelse
        <header class="panel-titulo separador"><h2>Cotizaciones por retomar</h2></header>
        @forelse($frias as $venta)
        <article class="elemento-lista">
            <strong>{{ $venta->cliente->nombre }}</strong>
            <p>{{ $venta->producto }}</p>
            <a class="enlace" href="{{ route('ventas.show', $venta) }}">Abrir seguimiento →</a>
        </article>
        @empty
        <p class="vacio">Tus cotizaciones están al día.</p>
        @endforelse
        {{ $frias->links('partials.paginacion', ['etiqueta' => 'Páginas de cotizaciones por retomar']) }}
    </section>
</div>
@endsection
