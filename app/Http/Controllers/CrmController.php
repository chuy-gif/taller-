<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Cliente;
use App\Models\Venta;
use Illuminate\Http\Request;

abstract class CrmController extends Controller
{
    // Todas las consultas empiezan por el negocio de la sesión.
    // Así, cambiar un id en la URL no da acceso a datos de otro equipo.
    protected function ventasVisibles(Request $request)
    {
        return Venta::where('negocio_id', $request->user()->negocio_id)
            ->when($request->user()->esCocina(), fn ($query) =>
                $query->where('cocina_id', $request->user()->id)
            );
    }

    protected function clientesVisibles(Request $request)
    {
        return Cliente::where('negocio_id', $request->user()->negocio_id)
            ->when($request->user()->esCocina(), fn ($query) =>
                $query->whereHas('ventas', fn ($ventas) =>
                    $ventas->where('cocina_id', $request->user()->id)
                )
            );
    }

    protected function actividadesVisibles(Request $request)
    {
        return Actividad::where('negocio_id', $request->user()->negocio_id)
            ->when($request->user()->esCocina(), fn ($query) =>
                $query->whereHas('venta', fn ($ventas) =>
                    $ventas->where('cocina_id', $request->user()->id)
                )
            );
    }

    protected function exigirGestion(Request $request): void
    {
        abort_unless($request->user()->puedeGestionar(), 403);
    }
}
