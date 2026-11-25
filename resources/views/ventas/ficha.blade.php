@extends('layouts.app')
@section('titulo', 'Venta #NX-'.$venta->id)
@section('subtitulo', $venta->cliente->nombre.' · '.$venta->producto)
@section('acciones')
@if(auth()->user()->puedeGestionar() && $venta->estado === 'abierta')
<a class="boton secundario" href="{{ route('ventas.edit', $venta) }}">Editar datos</a>
@endif
@if((auth()->user()->puedeGestionar() || auth()->user()->esCocina()) && $venta->estado === 'abierta')
<a class="boton" href="{{ route('actividades.create', ['cliente_id' => $venta->cliente_id, 'venta_id' => $venta->id]) }}">+ Seguimiento</a>
@endif
@endsection
@section('contenido')
<section class="panel ficha">
    <div class="fila">
        <h2>{{ $venta->producto }} · {{ $venta->porciones }} porciones</h2>
        <span class="insignia">{{ $venta->estado === 'abierta' ? \App\Models\Venta::ETAPAS[$venta->etapa] : ucfirst($venta->estado) }}</span>
    </div>
    <dl class="datos-ficha">
        <div>
            <dt>Cliente</dt>
            <dd><a class="enlace" href="{{ route('clientes.show', $venta->cliente) }}">{{ $venta->cliente->nombre }}</a></dd>
        </div>
        <div>
            <dt>Entrega</dt>
            <dd>{{ $venta->fecha_entrega->format('d/m/Y') }} · {{ $venta->hora_entrega }}</dd>
        </div>
        <div>
            <dt>Cocina</dt>
            <dd>{{ $venta->cocina?->name ?? 'Sin asignar' }}</dd>
        </div>
        @unless(auth()->user()->esCocina())
        <div>
            <dt>Monto MXN</dt>
            <dd>${{ number_format($venta->monto, 2) }}</dd>
        </div>
        <div>
            <dt>Anticipo MXN</dt>
            <dd>${{ number_format($venta->anticipo, 2) }}</dd>
        </div>
        <div>
            <dt>Saldo pendiente MXN</dt>
            <dd>${{ number_format($venta->monto - $venta->anticipo, 2) }}</dd>
        </div>
        @endunless
    </dl>
    <div class="aviso precaucion">
        <strong>Alergias e indicaciones</strong>
        <p>{{ $venta->cliente->alergias ?: 'No especificadas. Confirmar antes de preparar.' }}</p>
    </div>
    <p class="con-saltos texto-suave">{{ $venta->notas }}</p>
    @if($venta->motivo_perdida)
    <p>Motivo de pérdida: {{ $venta->motivo_perdida }}</p>
    @endif
    @if(auth()->user()->puedeGestionar() && $venta->estado === 'abierta')
    <form class="filtros separador" action="{{ route('ventas.etapa', $venta) }}" method="POST">
        @csrf
        @method('PATCH')
        <label>
            Etapa
            <select name="etapa">
                @foreach(\App\Models\Venta::ETAPAS as $clave => $nombre)
                <option value="{{ $clave }}" @selected($venta->etapa === $clave)>
                    {{ $nombre }}
                </option>
                @endforeach
            </select>
        </label>
        <button class="boton secundario" type="submit">Guardar etapa</button>
    </form>
    <details class="cierre-venta">
        <summary>Cerrar esta venta</summary>
        <form class="formulario" action="{{ route('ventas.cierre', $venta) }}" method="POST">
            @csrf
            @method('PATCH')
            <label>
                Resultado
                <select name="estado">
                    <option value="ganada">Ganada / entregada</option>
                    <option value="perdida">Perdida</option>
                </select>
            </label>
            <label>
                Motivo si se perdió
                <textarea name="motivo_perdida" maxlength="1000"></textarea>
            </label>
            <p class="nota">
                Para confirmar la entrega, el pedido debe estar en la etapa Entrega. Este cierre no
                registra un cobro del saldo.
            </p>
            <button class="boton" type="submit">Confirmar cierre</button>
        </form>
    </details>
    @endif
</section>
<section class="panel espacio-arriba">
    <header class="panel-titulo"><h2>Historial y actividades</h2></header>
    @include('partials.historial')
</section>
@endsection
