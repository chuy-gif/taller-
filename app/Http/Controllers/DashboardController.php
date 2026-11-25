<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends CrmController
{
    public function index(Request $request)
    {
        $abiertas = $this->ventasVisibles($request)->where('estado', 'abierta');
        $hoy = today();
        $entregas = (clone $abiertas)->where('fecha_entrega', '>=', $hoy->toDateString())
            ->where('fecha_entrega', '<', $hoy->copy()->addDay()->toDateString())
            ->with('cliente')->orderBy('hora_entrega')->orderBy('id')
            ->paginate(10, ['*'], 'pagina_entregas')->withQueryString();
        $frias = (clone $abiertas)->where('etapa', 'cotizacion')->where('anticipo', 0)
            ->where('ultima_actividad', '<', now()->subDays(7))->with('cliente')
            ->orderBy('ultima_actividad')->orderBy('id')
            ->paginate(10, ['*'], 'pagina_cotizaciones')->withQueryString();
        $monto = (clone $abiertas)->whereIn('etapa', ['pedido', 'entrega'])->sum('monto');
        $clientes = $this->clientesVisibles($request)->where('activo', true)->count();
        $pendientes = $this->actividadesVisibles($request)->where('completada', false)
            ->with('cliente')->orderByRaw('programada_para IS NULL')->orderBy('programada_para')->limit(5)->get();
        $resumen = (clone $abiertas)->selectRaw('etapa, count(*) as total')->groupBy('etapa')->pluck('total', 'etapa');

        return view('dashboard', compact('entregas', 'frias', 'monto', 'clientes', 'pendientes', 'resumen'));
    }
}
