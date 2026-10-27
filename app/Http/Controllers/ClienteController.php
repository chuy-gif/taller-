<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClienteController extends CrmController
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->query('buscar', ''));
        $clientes = $this->clientesVisibles($request)
            ->when($buscar !== '', fn ($query) => $query->where('nombre', 'like', '%'.$buscar.'%'))
            ->orderByDesc('activo')->orderBy('nombre')->paginate(10)->withQueryString();

        return view('clientes.index', compact('clientes', 'buscar'));
    }

    public function create(Request $request)
    {
        $this->exigirGestion($request);

        return view('clientes.formulario', ['cliente' => new Cliente]);
    }

    public function store(Request $request)
    {
        $this->exigirGestion($request);
        $cliente = Cliente::create([
            ...$this->validar($request),
            'negocio_id' => $request->user()->negocio_id,
            'responsable_id' => $request->user()->id,
        ]);

        return redirect()->route('clientes.show', $cliente)->with('exito', 'Cliente guardado.');
    }

    public function show(Request $request, int $cliente)
    {
        $cliente = $this->clientesVisibles($request)->findOrFail($cliente);
        $contactos = $cliente->contactos()->orderByDesc('principal')->get();
        $ventas = $this->ventasVisibles($request)->where('cliente_id', $cliente->id)->latest()->get();
        $actividades = $this->actividadesVisibles($request)->where('cliente_id', $cliente->id)
            ->with('autor')->latest()->limit(15)->get();

        return view('clientes.ficha', compact('cliente', 'contactos', 'ventas', 'actividades'));
    }

    public function edit(Request $request, int $cliente)
    {
        $this->exigirGestion($request);
        $cliente = $this->clientesVisibles($request)->findOrFail($cliente);

        return view('clientes.formulario', compact('cliente'));
    }

    public function update(Request $request, int $cliente)
    {
        $this->exigirGestion($request);
        $cliente = $this->clientesVisibles($request)->findOrFail($cliente);
        $cliente->update($this->validar($request));

        return redirect()->route('clientes.show', $cliente)->with('exito', 'Cliente actualizado.');
    }

    public function archivar(Request $request, int $cliente)
    {
        $this->exigirGestion($request);
        $cliente = $this->clientesVisibles($request)->findOrFail($cliente);
        $cliente->update(['activo' => ! $cliente->activo]);

        return back()->with('exito', 'Estado del cliente actualizado. Su historial se conserva.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:100',
            'canal' => ['required', Rule::in(['WhatsApp', 'Instagram', 'Tienda', 'Correo', 'Teléfono'])],
            'correo' => 'nullable|email|max:150',
            'telefono' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+() .-]+$/'],
            'alergias' => 'nullable|string|max:1000',
            'notas' => 'nullable|string|max:2000',
        ]);
    }
}
