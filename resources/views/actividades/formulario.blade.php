@extends('layouts.app')
@section('titulo', 'Nueva actividad')
@section('subtitulo', 'Guarda lo que pasó o programa una tarea para después.')
@section('contenido')
<form class="panel formulario amplio" method="POST" action="{{ route('actividades.store') }}">
    @csrf
    <div class="campos">
        <label>
            Cliente
            <select name="cliente_id" required id="actividad-cliente">
                <option value="">Selecciona un cliente</option>
                @foreach($clientes as $cliente)
                <option value="{{ $cliente->id }}" @selected(old('cliente_id', request('cliente_id')) == $cliente->id)>
                    {{ $cliente->nombre }}
                </option>
                @endforeach
            </select>
        </label>
        <label>
            Venta relacionada
            <select name="venta_id" id="actividad-venta">
                <option value="">Sin venta relacionada</option>
                @foreach($ventas as $venta)
                <option
                    value="{{ $venta->id }}"
                    data-cliente="{{ $venta->cliente_id }}"
                    @selected(old('venta_id', request('venta_id')) == $venta->id)
                >
                    #NX-{{ $venta->id }} · {{ $venta->cliente->nombre }} · {{ $venta->producto }}
                </option>
                @endforeach
            </select>
        </label>
        <label>
            Tipo
            <select name="tipo">
                @foreach(\App\Models\Actividad::TIPOS as $tipo)
                <option @selected(old('tipo') === $tipo)>{{ $tipo }}</option>
                @endforeach
            </select>
        </label>
        <label>
            Fecha y hora programada (opcional)
            <input type="datetime-local" name="programada_para" value="{{ old('programada_para') }}" />
        </label>
        <label class="completo">
            Asunto
            <input
                name="asunto"
                value="{{ old('asunto') }}"
                maxlength="120"
                required
                placeholder="Ej. Confirmar diseño del pastel"
            />
        </label>
        <label class="completo">
            Descripción
            <textarea name="descripcion" maxlength="2000" required>{{ old('descripcion') }}</textarea>
        </label>
        <label class="casilla completo">
            <input type="checkbox" name="completada" value="1" @checked(old('completada')) />
            Ya se realizó esta actividad
        </label>
    </div>
    <p class="nota">
        Las tareas futuras quedan pendientes. Cocina solo puede registrar actividades sobre sus
        pedidos asignados.
    </p>
    <div class="acciones-formulario">
        <a class="boton secundario" href="{{ route('actividades.index') }}">Cancelar</a>
        <button class="boton" type="submit">Guardar actividad</button>
    </div>
</form>
@endsection
