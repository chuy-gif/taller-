<?php

use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class DashboardBenchmarkTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_con_mil_ventas(): void
    {
        config(['session.encrypt' => false]);
        $this->seed();
        $this->actingAs(User::where('rol', 'admin')->firstOrFail());
        $base = Venta::firstOrFail()->getAttributes();
        unset($base['id']);

        $hoy = today()->toDateTimeString();
        $manana = today()->addDay()->toDateTimeString();
        $atras = now()->subDays(8)->toDateTimeString();
        for ($lote = 0; $lote < 10; $lote++) {
            $filas = [];
            for ($indice = 0; $indice < 100; $indice++) {
                $esEntrega = $lote < 5;
                $filas[] = [...$base, 'producto' => "Pastel de carga $lote-$indice",
                    'etapa' => $esEntrega ? 'pedido' : 'cotizacion', 'estado' => 'abierta',
                    'fecha_entrega' => $esEntrega ? $hoy : $manana,
                    'anticipo' => 0, 'ultima_actividad' => $atras];
            }
            foreach (array_chunk($filas, 40) as $grupo) {
                DB::table('ventas')->insert($grupo);
            }
        }

        for ($i = 0; $i < 3; $i++) {
            $this->get('/dashboard')->assertOk();
        }

        $tiempos = [];
        for ($i = 0; $i < 15; $i++) {
            $inicio = hrtime(true);
            $respuesta = $this->get('/dashboard');
            $tiempos[] = (hrtime(true) - $inicio) / 1e6;
            $respuesta->assertOk();
        }

        sort($tiempos);
        $vista = $respuesta->original->getData();
        fprintf(STDERR, "BENCHMARK mediana_ms=%.2f html_bytes=%d entregas_renderizadas=%d cotizaciones_renderizadas=%d entregas_totales=%d cotizaciones_totales=%d\n",
            $tiempos[7], strlen($respuesta->getContent()), $vista['entregas']->count(), $vista['frias']->count(),
            method_exists($vista['entregas'], 'total') ? $vista['entregas']->total() : $vista['entregas']->count(),
            method_exists($vista['frias'], 'total') ? $vista['frias']->total() : $vista['frias']->count());
    }
}
