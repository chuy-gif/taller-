<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends CrmController
{
    public function index(Request $request)
    {
        $abiertas = $this->ventasVisibles($request)->where('estado', 'abierta');
        $entregas = (clone $abiertas)->whereDate('fecha_entrega', today())
            ->with('cliente')->orderBy('hora_entrega')->get();
        $frias = (clone $abiertas)->where('etapa', 'cotizacion')->where('anticipo', 0)
            ->where('ultima_actividad', '<', now()->subDays(7))->with('cliente')->get();
        $monto = (clone $abiertas)->whereIn('etapa', ['pedido', 'entrega'])->sum('monto');
        $clientes = $this->clientesVisibles($request)->where('activo', true)->count();
        $pendientes = $this->actividadesVisibles($request)->where('completada', false)
            ->with('cliente')->orderByRaw('programada_para IS NULL')->orderBy('programada_para')->limit(5)->get();
        $resumen = (clone $abiertas)->selectRaw('etapa, count(*) as total')->groupBy('etapa')->pluck('total', 'etapa');

        return view('dashboard', compact('entregas', 'frias', 'monto', 'clientes', 'pendientes', 'resumen'));
    }
}
