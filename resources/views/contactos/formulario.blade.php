@extends('layouts.app')
@section('titulo', $contacto->exists ? 'Editar contacto' : 'Nuevo contacto')
@section('subtitulo', 'Relaciona a una persona con un cliente y agrega cómo localizarla.')
@section('contenido')
<form class="panel formulario amplio" method="POST" action="{{ $contacto->exists ? route('contactos.update', $contacto) : route('contactos.store') }}">
    @csrf
    @if($contacto->exists)
    @method('PUT')
    @endif
    <div class="campos">
        <label class="completo">
            Cliente
            <select name="cliente_id" required>
                <option value="">Selecciona un cliente</option>
                @foreach($clientes as $cliente)
                <option value="{{ $cliente->id }}" @selected(old('cliente_id', $contacto->cliente_id) == $cliente->id)>
                    {{ $cliente->nombre }}
                </option>
                @endforeach
            </select>
        </label>
        <label>
            Nombre de la persona
            <input name="nombre" value="{{ old('nombre', $contacto->nombre) }}" maxlength="100" required />
        </label>
        <label>
            Cargo o relación
            <input
                name="cargo"
                value="{{ old('cargo', $contacto->cargo) }}"
                maxlength="80"
                placeholder="Ej. Mamá, coordinación de eventos"
            />
        </label>
        <label>
            Correo
            <input name="correo" type="email" value="{{ old('correo', $contacto->correo) }}" maxlength="150" />
        </label>
        <label>
            Teléfono
            <input name="telefono" value="{{ old('telefono', $contacto->telefono) }}" maxlength="25" />
        </label>
        <label class="casilla completo">
            <input type="checkbox" name="principal" value="1" @checked(old('principal', $contacto->principal)) />
            Es el contacto principal del cliente
        </label>
    </div>
    <p class="nota">
        Registra al menos un correo o teléfono. Solo puede haber un contacto principal por cliente.
    </p>
    <div class="acciones-formulario">
        <a class="boton secundario" href="{{ route('contactos.index') }}">Cancelar</a>
        <button class="boton" type="submit">Guardar contacto</button>
    </div>
</form>
@endsection
