<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VentaController extends CrmController
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->query('buscar', ''));
        $estado = $request->query('estado', 'abierta');
        $ventas = $this->ventasVisibles($request)->with('cliente')
            ->when(in_array($estado, ['abierta', 'ganada', 'perdida'], true), fn ($q) => $q->where('estado', $estado))
            ->when($buscar !== '', fn ($q) => $q->where(function ($q) use ($buscar) {
                $q->where('producto', 'like', '%'.$buscar.'%')
                    ->orWhereHas('cliente', fn ($c) => $c->where('nombre', 'like', '%'.$buscar.'%'));
            }))
            ->orderBy('fecha_entrega')->get();

        return view('ventas.index', compact('ventas', 'buscar', 'estado'));
    }

    public function create(Request $request)
    {
        $this->exigirGestion($request);

        return $this->formulario($request, new Venta([
            'porciones' => 20, 'anticipo' => 0,
            'fecha_entrega' => today()->addDay(), 'hora_entrega' => '16:00',
        ]));
    }

    public function store(Request $request)
    {
        $this->exigirGestion($request);
        $datos = $this->validar($request);
        $venta = DB::transaction(function () use ($request, $datos) {
            $venta = Venta::create([
                ...$datos, 'negocio_id' => $request->user()->negocio_id,
                'responsable_id' => $request->user()->id,
                'etapa' => 'consulta', 'estado' => 'abierta', 'ultima_actividad' => now(),
            ]);
            $this->movimiento($venta, $request->user(), 'Registro de venta', 'Se registró la consulta.');

            return $venta;
        });

        return redirect()->route('ventas.show', $venta)->with('exito', 'Consulta guardada en el embudo.');
    }

    public function show(Request $request, int $venta)
    {
        $venta = $this->ventasVisibles($request)->with(['cliente', 'cocina'])->findOrFail($venta);
        $actividades = $venta->actividades()->with('autor')->latest()->get();

        return view('ventas.ficha', compact('venta', 'actividades'));
    }

    public function edit(Request $request, int $venta)
    {
        $this->exigirGestion($request);
        $venta = $this->ventasVisibles($request)->findOrFail($venta);
        abort_if($venta->estado !== 'abierta', 403, 'Las ventas cerradas conservan su historial.');

        return $this->formulario($request, $venta);
    }

    public function update(Request $request, int $venta)
    {
        $this->exigirGestion($request);
        $venta = $this->ventasVisibles($request)->findOrFail($venta);
        abort_if($venta->estado !== 'abierta', 403);
        $datos = $this->validar($request);
        // El cliente se conserva para que las actividades no cambien de dueño.
        unset($datos['cliente_id']);
        DB::transaction(function () use ($request, $venta, $datos) {
            $venta->update($datos);
            $this->movimiento($venta, $request->user(), 'Datos actualizados', 'Se actualizó la ficha del pedido.');
        });

        return redirect()->route('ventas.show', $venta)->with('exito', 'Venta actualizada.');
    }

    public function etapa(Request $request, int $venta)
    {
        $this->exigirGestion($request);
        $venta = $this->ventasVisibles($request)->findOrFail($venta);
        abort_if($venta->estado !== 'abierta', 403);
        $datos = $request->validate(['etapa' => ['required', Rule::in(array_keys(Venta::ETAPAS))]]);

        if ($datos['etapa'] !== $venta->etapa) {
            DB::transaction(function () use ($request, $venta, $datos) {
                $venta->update($datos);
                $this->movimiento($venta, $request->user(), 'Cambio de etapa', 'El pedido pasó a '.Venta::ETAPAS[$venta->etapa].'.');
            });
        }

        return back()->with('exito', 'Etapa guardada.');
    }

    public function cerrar(Request $request, int $venta)
    {
        $this->exigirGestion($request);
        $venta = $this->ventasVisibles($request)->findOrFail($venta);
        abort_if($venta->estado !== 'abierta', 403);
        $datos = $request->validate([
            'estado' => ['required', Rule::in(['ganada', 'perdida'])],
            'motivo_perdida' => 'required_if:estado,perdida|nullable|string|max:1000',
        ]);
        if ($datos['estado'] === 'ganada' && $venta->etapa !== 'entrega') {
            throw ValidationException::withMessages(['estado' => 'Mueve el pedido a Entrega antes de confirmarlo.']);
        }

        DB::transaction(function () use ($request, $venta, $datos) {
            $venta->update([...$datos, 'cerrada_en' => now()]);
            $this->movimiento($venta, $request->user(), 'Cierre de venta',
                $datos['estado'] === 'ganada' ? 'Entrega confirmada.' : 'Perdida: '.$datos['motivo_perdida']);
        });

        return back()->with('exito', 'Venta cerrada.');
    }

    private function formulario(Request $request, Venta $venta)
    {
        $clientes = $this->clientesVisibles($request)->where('activo', true)->orderBy('nombre')->get();
        $cocina = User::where('negocio_id', $request->user()->negocio_id)
            ->where('rol', 'colaborador')->where('activo', true)->get();

        return view('ventas.formulario', compact('venta', 'clientes', 'cocina'));
    }

    private function validar(Request $request): array
    {
        $negocio = $request->user()->negocio_id;

        return $request->validate([
            'cliente_id' => ['required', Rule::exists('clientes', 'id')->where('negocio_id', $negocio)->where('activo', true)],
            'cocina_id' => ['nullable', Rule::exists('users', 'id')->where('negocio_id', $negocio)->where('rol', 'colaborador')->where('activo', true)],
            'producto' => 'required|string|max:100', 'evento' => 'required|string|max:60',
            'porciones' => 'required|integer|min:1|max:10000',
            'monto' => 'required|numeric|min:0|max:9999999.99|decimal:0,2',
            'anticipo' => 'required|numeric|min:0|lte:monto|decimal:0,2',
            'fecha_entrega' => 'required|date_format:Y-m-d',
            'hora_entrega' => 'required|date_format:H:i',
            'notas' => 'nullable|string|max:2000',
        ]);
    }

    private function movimiento(Venta $venta, User $autor, string $asunto, string $descripcion): void
    {
        $venta->actividades()->create([
            'negocio_id' => $venta->negocio_id, 'cliente_id' => $venta->cliente_id,
            'autor_id' => $autor->id, 'tipo' => 'Sistema', 'asunto' => $asunto,
            'descripcion' => $descripcion, 'completada' => true,
        ]);
        $venta->update(['ultima_actividad' => now()]);
    }
}
