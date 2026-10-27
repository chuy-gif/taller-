@extends('layouts.app')
@section('titulo', $cliente->nombre)
@section('subtitulo', 'Ficha del cliente e historial del CRM.')
@section('acciones')
@if(auth()->user()->puedeGestionar())
<a class="boton" href="{{ route('clientes.edit', $cliente) }}">Editar cliente</a>
@endif
@endsection
@section('contenido')
<section class="panel ficha">
    <dl class="datos-ficha">
        <div>
            <dt>Canal</dt>
            <dd>{{ $cliente->canal }}</dd>
        </div>
        <div>
            <dt>Correo</dt>
            <dd>{{ $cliente->correo ?? 'No registrado' }}</dd>
        </div>
        <div>
            <dt>Teléfono</dt>
            <dd>{{ $cliente->telefono ?? 'No registrado' }}</dd>
        </div>
    </dl>
    <div class="aviso precaucion">
        <strong>Alergias e indicaciones</strong>
        <p>{{ $cliente->alergias ?: 'No especificadas. Confirmar con el cliente.' }}</p>
    </div>
    <p class="texto-suave">{{ $cliente->notas }}</p>
    @if(auth()->user()->puedeGestionar())
    <form action="{{ route('clientes.estado', $cliente) }}" method="POST">
        @csrf
        @method('PATCH')
        <button class="enlace" type="submit">{{ $cliente->activo ? 'Archivar cliente' : 'Reactivar cliente' }}</button>
        <small class="nota">El historial se conserva.</small>
    </form>
    @endif
</section>
<div class="dos-columnas">
    <section class="panel">
        <header class="panel-titulo"><h2>Pedidos y ventas</h2></header>
        @forelse($ventas as $venta)
        <article class="elemento-lista">
            <strong>#NX-{{ $venta->id }} · {{ $venta->producto }}</strong>
            <p>{{ $venta->fecha_entrega->format('d/m/Y') }} · {{ \App\Models\Venta::ETAPAS[$venta->etapa] }} · {{ $venta->estado }}</p>
            <a class="enlace" href="{{ route('ventas.show', $venta) }}">Abrir ficha →</a>
        </article>
        @empty
        <p class="vacio">Este cliente todavía no tiene pedidos.</p>
        @endforelse
    </section>
    <section class="panel">
        <header class="panel-titulo"><h2>Personas de contacto</h2></header>
        @forelse($contactos as $contacto)
        <article class="elemento-lista">
            <strong>{{ $contacto->nombre }} {{ $contacto->principal ? '· Principal' : '' }}</strong>
            <p>{{ $contacto->cargo }}</p>
            <small>{{ $contacto->correo }} {{ $contacto->telefono }}</small>
        </article>
        @empty
        <p class="vacio">Puedes agregar personas desde Contactos.</p>
        @endforelse
    </section>
</div>
<section class="panel espacio-arriba">
    <header class="panel-titulo"><h2>Actividades recientes</h2></header>
    @include('partials.historial')
</section>
@endsection
