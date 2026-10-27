<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ActividadController extends CrmController
{
    public function index(Request $request)
    {
        $filtro = $request->query('filtro', 'todas');
        $actividades = $this->actividadesVisibles($request)->with(['cliente', 'venta', 'autor'])
            ->when($filtro === 'pendientes', fn ($q) => $q->where('completada', false))
            ->when($filtro === 'completadas', fn ($q) => $q->where('completada', true))
            ->latest()->paginate(12)->withQueryString();

        return view('actividades.index', compact('actividades', 'filtro'));
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->puedeGestionar() || $request->user()->esCocina(), 403);
        $clientes = $this->clientesVisibles($request)->orderBy('nombre')->get();
        $ventas = $this->ventasVisibles($request)->where('estado', 'abierta')->with('cliente')->get();

        return view('actividades.formulario', compact('clientes', 'ventas'));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->puedeGestionar() || $request->user()->esCocina(), 403);
        $datos = $request->validate([
            'cliente_id' => ['required', Rule::exists('clientes', 'id')->where('negocio_id', $request->user()->negocio_id)],
            'venta_id' => 'nullable|integer',
            'tipo' => ['required', Rule::in(Actividad::TIPOS)],
            'asunto' => 'required|string|max:120',
            'descripcion' => 'required|string|max:2000',
            'programada_para' => 'nullable|date_format:Y-m-d\TH:i',
            'completada' => 'nullable|boolean',
        ]);
        $this->clientesVisibles($request)->findOrFail($datos['cliente_id']);
        $venta = empty($datos['venta_id']) ? null : $this->ventasVisibles($request)->findOrFail($datos['venta_id']);

        if ($request->user()->esCocina() && ! $venta) {
            throw ValidationException::withMessages(['venta_id' => 'Cocina debe seleccionar un pedido asignado.']);
        }
        if ($venta && ($venta->cliente_id != $datos['cliente_id'] || $venta->estado !== 'abierta')) {
            throw ValidationException::withMessages(['venta_id' => 'Selecciona un pedido abierto del mismo cliente.']);
        }
        $datos['completada'] = $request->boolean('completada');

        DB::transaction(function () use ($request, $datos, $venta) {
            Actividad::create([
                ...$datos, 'negocio_id' => $request->user()->negocio_id,
                'autor_id' => $request->user()->id,
            ]);
            // Programar una tarea futura no cuenta como contactar al cliente.
            if ($venta && $datos['completada']) {
                $venta->update(['ultima_actividad' => now()]);
            }
        });

        return redirect()->route('actividades.index')->with('exito', 'Actividad guardada.');
    }

    public function completar(Request $request, int $actividad)
    {
        abort_unless($request->user()->puedeGestionar() || $request->user()->esCocina(), 403);
        $actividad = $this->actividadesVisibles($request)->findOrFail($actividad);
        abort_if($actividad->tipo === 'Sistema', 403);
        DB::transaction(function () use ($actividad) {
            $actividad->update(['completada' => true]);
            if ($actividad->venta && $actividad->venta->estado === 'abierta') {
                $actividad->venta->update(['ultima_actividad' => now()]);
            }
        });

        return back()->with('exito', 'Actividad marcada como completada.');
    }
}
