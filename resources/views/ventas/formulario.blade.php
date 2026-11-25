@extends('layouts.app')
@section('titulo', $venta->exists ? 'Editar venta #NX-'.$venta->id : 'Nueva venta')
@section('subtitulo', 'Captura el pedido y la información que necesita cocina.')
@section('contenido')
<form class="panel formulario amplio" action="{{ $venta->exists ? route('ventas.update', $venta) : route('ventas.store') }}" method="POST">
    @csrf
    @if($venta->exists)
    @method('PUT')
    @endif
    <div class="campos">
        <label class="completo">
            Cliente
            @if($venta->exists)
            <input value="{{ $venta->cliente->nombre }}" readonly />
            @else
            <select name="cliente_id" required>
                <option value="">Selecciona un cliente activo</option>
                @foreach($clientes as $cliente)
                <option value="{{ $cliente->id }}" @selected(old('cliente_id') == $cliente->id)>
                    {{ $cliente->nombre }}
                </option>
                @endforeach
            </select>
            @endif
        </label>
        <label>
            Producto o sabor
            <input name="producto" value="{{ old('producto', $venta->producto) }}" maxlength="100" required />
        </label>
        <label>
            Evento
            <input
                name="evento"
                value="{{ old('evento', $venta->evento) }}"
                maxlength="60"
                placeholder="Ej. Cumpleaños, boda"
                required
            />
        </label>
        <label>
            Porciones
            <input
                type="number"
                name="porciones"
                min="1"
                max="10000"
                value="{{ old('porciones', $venta->porciones) }}"
                required
            />
        </label>
        <label>
            Responsable de cocina
            <select name="cocina_id">
                <option value="">Sin asignar</option>
                @foreach($cocina as $persona)
                <option value="{{ $persona->id }}" @selected(old('cocina_id', $venta->cocina_id) == $persona->id)>
                    {{ $persona->name }}
                </option>
                @endforeach
            </select>
        </label>
        <label>
            Monto estimado (MXN)
            <input
                type="number"
                name="monto"
                min="0"
                max="9999999.99"
                step="0.01"
                value="{{ old('monto', $venta->monto) }}"
                required
            />
        </label>
        <label>
            Anticipo (MXN)
            <input
                type="number"
                name="anticipo"
                min="0"
                step="0.01"
                value="{{ old('anticipo', $venta->anticipo) }}"
                required
            />
        </label>
        <label>
            Fecha de entrega
            <input type="date" name="fecha_entrega" value="{{ old('fecha_entrega', $venta->fecha_entrega?->format('Y-m-d')) }}" required />
        </label>
        <label>
            Hora de entrega
            <input type="time" name="hora_entrega" value="{{ old('hora_entrega', $venta->hora_entrega) }}" required />
        </label>
        <label class="completo">
            Notas para mostrador y cocina
            <textarea name="notas" maxlength="2000">{{ old('notas', $venta->notas) }}</textarea>
        </label>
    </div>
    <p class="nota">
        El anticipo no puede ser mayor al monto. Las alergias se consultan desde la ficha del
        cliente.
    </p>
    <div class="acciones-formulario">
        <a class="boton secundario" href="{{ route('ventas.index') }}">Cancelar</a>
        <button class="boton" type="submit">Guardar venta</button>
    </div>
</form>
@endsection
