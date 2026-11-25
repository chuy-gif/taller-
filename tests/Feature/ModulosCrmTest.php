<?php

namespace Tests\Feature;

use App\Models\Actividad;
use App\Models\Cliente;
use App\Models\Contacto;
use App\Models\Negocio;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ModulosCrmTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['session.encrypt' => false]);
        $this->seed();
    }

    private function usuario(string $rol = 'admin'): User
    {
        return User::where('rol', $rol)->firstOrFail();
    }

    private function datosVenta(): array
    {
        return [
            'cliente_id' => Cliente::firstOrFail()->id,
            'producto' => 'Pastel de prueba', 'evento' => 'CumpleaÃ±os',
            'porciones' => 20, 'monto' => '900.50', 'anticipo' => '200.25',
            'fecha_entrega' => today()->addDay()->format('Y-m-d'), 'hora_entrega' => '16:00',
        ];
    }

    public function test_las_pantallas_exigen_sesion(): void
    {
        foreach (['dashboard', 'clientes', 'contactos', 'ventas', 'actividades', 'usuarios'] as $ruta) {
            $this->get('/'.$ruta)->assertRedirect('/login');
        }
    }

    public function test_el_registro_guarda_hash_y_crea_espacio_propio(): void
    {
        $this->post('/registro', [
            'name' => 'Nuevo usuario', 'negocio' => 'PastelerÃ­a de prueba',
            'email' => 'nuevo@example.com', 'password' => 'ClaveSegura2026',
            'password_confirmation' => 'ClaveSegura2026', 'rol' => 'editor',
            'negocio_id' => $this->usuario()->negocio_id,
        ])->assertRedirect('/dashboard');
        $nuevo = User::where('email', 'nuevo@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('ClaveSegura2026', $nuevo->password));
        $this->assertNotSame('ClaveSegura2026', $nuevo->password);
        $this->assertNotEquals($this->usuario()->negocio_id, $nuevo->negocio_id);
        $this->assertSame('admin', $nuevo->rol);
        $this->assertAuthenticatedAs($nuevo);
        $this->get('/clientes')->assertOk()->assertDontSee('Familia Ruiz');
    }

    public function test_rechaza_registro_con_password_debil_o_correo_repetido(): void
    {
        $antes = User::count();
        $this->post('/registro', [
            'name' => 'Prueba', 'negocio' => 'Negocio', 'email' => 'ana@dulcerincon.demo',
            'password' => 'corta', 'password_confirmation' => 'otra',
        ])->assertSessionHasErrors(['email', 'password']);
        $this->assertSame($antes, User::count());
    }

    public function test_login_real_y_cierre_de_sesion(): void
    {
        $this->post('/login', ['email' => 'ana@dulcerincon.demo', 'password' => 'incorrecta'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/login', ['email' => 'ana@dulcerincon.demo', 'password' => 'NexoDemo2026!'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_limita_intentos_de_login(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'intentos@example.com', 'password' => 'incorrecta']);
        }
        $this->post('/login', ['email' => 'intentos@example.com', 'password' => 'incorrecta'])
            ->assertSessionHasErrors('email')
            ->assertSessionHas('errors', fn ($errores) => str_contains($errores->first('email'), 'Demasiados intentos'));
        $this->assertGuest();
    }

    public function test_editor_puede_guardar_editar_y_archivar_clientes(): void
    {
        $this->actingAs($this->usuario('editor'));
        $this->post('/clientes', [
            'nombre' => 'Cliente CRUD', 'canal' => 'Tienda', 'correo' => 'cliente@example.com',
            'alergias' => 'Sin almendra', 'negocio_id' => 99999,
        ])->assertSessionHasNoErrors();
        $cliente = Cliente::where('nombre', 'Cliente CRUD')->firstOrFail();
        $this->assertEquals($this->usuario()->negocio_id, $cliente->negocio_id);
        $this->put('/clientes/'.$cliente->id, ['nombre' => 'Cliente editado', 'canal' => 'WhatsApp'])
            ->assertSessionHasNoErrors();
        $this->patch('/clientes/'.$cliente->id.'/estado')->assertSessionHasNoErrors();
        $this->assertFalse($cliente->fresh()->activo);
        $this->assertSame('Cliente editado', $cliente->fresh()->nombre);
    }

    public function test_usuario_de_consulta_no_puede_capturar_datos(): void
    {
        $this->actingAs($this->usuario('usuario_final'));
        $this->get('/dashboard')->assertOk()->assertDontSee('+ Nueva venta');
        $this->post('/clientes', ['nombre' => 'No permitido'])->assertForbidden();
        $this->post('/contactos', [])->assertForbidden();
        $this->post('/ventas', [])->assertForbidden();
        $this->post('/actividades', [])->assertForbidden();
        $this->get('/usuarios')->assertForbidden();
    }

    public function test_no_hay_acceso_a_registros_de_otro_negocio(): void
    {
        $otro = Negocio::create(['nombre' => 'Otro negocio']);
        $cliente = Cliente::create([
            'negocio_id' => $otro->id, 'responsable_id' => $this->usuario()->id,
            'nombre' => 'Cliente reservado', 'canal' => 'Tienda',
        ]);
        $this->actingAs($this->usuario());
        $this->get('/clientes/'.$cliente->id)->assertNotFound();
        $this->put('/clientes/'.$cliente->id, ['nombre' => 'Cambio', 'canal' => 'Tienda'])->assertNotFound();
        $this->post('/contactos', ['cliente_id' => $cliente->id, 'nombre' => 'Intruso', 'correo' => 'x@example.com'])
            ->assertSessionHasErrors('cliente_id');
        $this->post('/ventas', [...$this->datosVenta(), 'cliente_id' => $cliente->id])
            ->assertSessionHasErrors('cliente_id');
        $this->post('/actividades', [
            'cliente_id' => $cliente->id, 'tipo' => 'Nota', 'asunto' => 'Intruso', 'descripcion' => 'No permitido',
        ])->assertSessionHasErrors('cliente_id');
    }

    public function test_contactos_tienen_un_principal_y_borrado_logico(): void
    {
        $this->actingAs($this->usuario());
        $cliente = Cliente::firstOrFail();
        $datos = ['cliente_id' => $cliente->id, 'nombre' => 'Contacto nuevo', 'correo' => 'contacto@example.com', 'principal' => 1];
        $this->post('/contactos', $datos)->assertSessionHasNoErrors();
        $contacto = Contacto::where('nombre', 'Contacto nuevo')->firstOrFail();
        $this->assertSame(1, Contacto::where('cliente_id', $cliente->id)->where('principal', true)->count());
        $this->put('/contactos/'.$contacto->id, [...$datos, 'nombre' => 'Contacto actualizado'])->assertSessionHasNoErrors();
        $this->delete('/contactos/'.$contacto->id)->assertSessionHasNoErrors();
        $this->assertSoftDeleted('contactos', ['id' => $contacto->id]);
    }

    public function test_anticipo_invalido_no_guarda_venta_y_los_centavos_se_conservan(): void
    {
        $this->actingAs($this->usuario('editor'));
        $antes = Venta::count();
        $this->post('/ventas', [...$this->datosVenta(), 'anticipo' => '1000'])
            ->assertSessionHasErrors('anticipo');
        $this->assertSame($antes, Venta::count());
        $this->post('/ventas', $this->datosVenta())->assertSessionHasNoErrors();
        $venta = Venta::where('producto', 'Pastel de prueba')->firstOrFail();
        $this->assertSame('900.50', $venta->monto);
        $this->assertSame('200.25', $venta->anticipo);
    }

    public function test_venta_cambia_etapa_y_conserva_historial_al_cerrar(): void
    {
        $this->actingAs($this->usuario());
        $venta = Venta::where('etapa', 'consulta')->firstOrFail();
        $this->patch('/ventas/'.$venta->id.'/cierre', ['estado' => 'ganada'])->assertSessionHasErrors('estado');
        $this->patch('/ventas/'.$venta->id.'/etapa', ['etapa' => 'entrega'])->assertSessionHasNoErrors();
        $this->patch('/ventas/'.$venta->id.'/cierre', ['estado' => 'ganada'])->assertSessionHasNoErrors();
        $this->assertSame('ganada', $venta->fresh()->estado);
        $this->assertNotNull($venta->fresh()->cerrada_en);
        $this->assertGreaterThanOrEqual(2, $venta->actividades()->count());
        $this->patch('/ventas/'.$venta->id.'/etapa', ['etapa' => 'consulta'])->assertForbidden();
    }

    public function test_venta_abierta_se_edita_sin_cambiar_su_cliente_archivado(): void
    {
        $this->actingAs($this->usuario('editor'));
        $venta = Venta::firstOrFail();
        $clienteOriginal = $venta->cliente;
        $clienteOriginal->update(['activo' => false]);
        $datos = [...$this->datosVenta(), 'cliente_id' => $clienteOriginal->id];

        $this->put('/ventas/'.$venta->id, $datos)->assertSessionHasNoErrors();
        $this->assertSame('Pastel de prueba', $venta->fresh()->producto);
        $this->assertSame($clienteOriginal->id, $venta->fresh()->cliente_id);
        $this->post('/ventas', $datos)->assertSessionHasErrors('cliente_id');

        $otroCliente = Cliente::where('id', '!=', $clienteOriginal->id)->firstOrFail();
        $this->put('/ventas/'.$venta->id, [...$datos, 'cliente_id' => $otroCliente->id])
            ->assertSessionHasNoErrors();
        $this->assertSame($clienteOriginal->id, $venta->fresh()->cliente_id);
    }

    public function test_cocina_solo_consulta_asignados_y_registra_seguimientos(): void
    {
        $cocina = $this->usuario('colaborador');
        $venta = Venta::firstOrFail();
        $this->actingAs($cocina);
        $this->get('/ventas/'.$venta->id)->assertOk()->assertDontSee('Saldo pendiente MXN');
        $this->patch('/ventas/'.$venta->id.'/etapa', ['etapa' => 'entrega'])->assertForbidden();
        $this->post('/actividades', [
            'cliente_id' => $venta->cliente_id, 'venta_id' => $venta->id,
            'tipo' => 'Cocina', 'asunto' => 'Avance en horno', 'descripcion' => 'Listo para decorar.', 'completada' => 1,
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('actividades', ['asunto' => 'Avance en horno', 'autor_id' => $cocina->id]);
        $venta->update(['cocina_id' => null]);
        $this->get('/ventas/'.$venta->id)->assertNotFound();
    }

    public function test_tarea_futura_no_reinicia_cotizacion_fria_hasta_completarse(): void
    {
        $this->actingAs($this->usuario());
        $venta = Venta::where('producto', 'Red velvet')->firstOrFail();
        $antes = $venta->ultima_actividad->toDateTimeString();
        $this->post('/actividades', [
            'cliente_id' => $venta->cliente_id, 'venta_id' => $venta->id, 'tipo' => 'Tarea',
            'asunto' => 'Llamada futura de prueba', 'descripcion' => 'Confirmar diseÃ±o.',
            'programada_para' => now()->addDay()->format('Y-m-d\TH:i'),
        ])->assertSessionHasNoErrors();
        $actividad = Actividad::where('asunto', 'Llamada futura de prueba')->firstOrFail();
        $this->assertSame($antes, $venta->fresh()->ultima_actividad->toDateTimeString());
        $this->patch('/actividades/'.$actividad->id.'/completar')->assertSessionHasNoErrors();
        $this->assertTrue($actividad->fresh()->completada);
        $this->assertTrue($venta->fresh()->ultima_actividad->isToday());
    }

    public function test_repetir_el_cierre_de_una_actividad_no_actualiza_la_venta(): void
    {
        $this->actingAs($this->usuario());
        $venta = Venta::firstOrFail();
        $actividad = $venta->actividades()->create([
            'negocio_id' => $venta->negocio_id, 'cliente_id' => $venta->cliente_id,
            'autor_id' => $this->usuario()->id, 'tipo' => 'Tarea', 'asunto' => 'Confirmar pedido',
            'descripcion' => 'Verificar hora de entrega.', 'completada' => false,
        ]);
        $url = '/actividades/'.$actividad->id.'/completar';

        $this->patch($url)->assertRedirect();
        $this->assertTrue($actividad->fresh()->completada);
        $fechaReal = $venta->fresh()->ultima_actividad->toDateTimeString();

        $this->travel(8)->days();
        $this->patch($url)->assertRedirect();
        $this->assertSame($fechaReal, $venta->fresh()->ultima_actividad->toDateTimeString());
    }

    public function test_cuenta_inactiva_y_autodesactivacion_estan_bloqueadas(): void
    {
        $admin = $this->usuario();
        $this->actingAs($admin)->put('/usuarios/'.$admin->id, ['rol' => 'admin', 'activo' => 0])
            ->assertSessionHasErrors('rol');
        $this->assertTrue($admin->fresh()->activo);
        $editor = $this->usuario('editor');
        $editor->update(['activo' => false]);
        $this->actingAs($editor)->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_las_pantallas_compilan_y_el_texto_ingresado_se_escapa(): void
    {
        $this->actingAs($this->usuario());
        $cliente = Cliente::firstOrFail();
        $cliente->update(['notas' => '<script>alert(1)</script>']);
        foreach (['/dashboard', '/clientes', '/clientes/create', '/contactos', '/contactos/create', '/ventas', '/ventas/create', '/actividades', '/actividades/create', '/usuarios', '/usuarios/create'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/clientes/'.$cliente->id)->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_una_peticion_sin_csrf_se_rechaza(): void
    {
        // Reactiva la comprobaciÃ³n que Laravel omite normalmente en pruebas.
        $middleware = new class($this->app, $this->app['encrypter']) extends \Illuminate\Foundation\Http\Middleware\PreventRequestForgery {
            protected function runningUnitTests()
            {
                return false;
            }
        };
        $this->app->instance(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class, $middleware);
        $this->post('/login', ['email' => 'ana@dulcerincon.demo', 'password' => 'NexoDemo2026!'])
            ->assertStatus(419);
        $this->assertGuest();
    }

    public function test_actividad_no_puede_mezclar_cliente_y_venta_distintos(): void
    {
        $this->actingAs($this->usuario());
        $venta = Venta::firstOrFail();
        $otro = Cliente::where('id', '!=', $venta->cliente_id)->firstOrFail();
        $antes = Actividad::count();
        $this->post('/actividades', [
            'cliente_id' => $otro->id, 'venta_id' => $venta->id,
            'tipo' => 'Nota', 'asunto' => 'RelaciÃ³n invÃ¡lida', 'descripcion' => 'No debe guardarse.',
        ])->assertSessionHasErrors('venta_id');
        $this->assertSame($antes, Actividad::count());
    }

    public function test_dashboard_calcula_los_indicadores_desde_la_base(): void
    {
        $this->actingAs($this->usuario())->get('/dashboard')
            ->assertOk()
            ->assertViewHas('entregas', fn ($lista) => $lista->count() === 2)
            ->assertViewHas('frias', fn ($lista) => $lista->count() === 2)
            ->assertViewHas('clientes', 6)
            ->assertViewHas('monto', fn ($monto) => (float) $monto === 2700.0);
    }

    public function test_dashboard_pagina_las_dos_listas_y_conserva_los_totales(): void
    {
        $referencia = Venta::firstOrFail();
        $datos = [
            ...$this->datosVenta(), 'cliente_id' => $referencia->cliente_id,
            'negocio_id' => $referencia->negocio_id, 'responsable_id' => $this->usuario()->id,
            'estado' => 'abierta', 'anticipo' => 0, 'ultima_actividad' => now()->subDays(8),
        ];
        for ($i = 1; $i <= 10; $i++) {
            Venta::create([...$datos, 'etapa' => 'pedido', 'fecha_entrega' => today()]);
            Venta::create([...$datos, 'etapa' => 'cotizacion']);
        }

        $this->actingAs($this->usuario())->get('/dashboard')
            ->assertOk()->assertSee('12 entregas para hoy')->assertSee('12 cotizaciones necesitan seguimiento')
            ->assertViewHas('entregas', fn ($lista) => $lista->count() === 10 && $lista->total() === 12)
            ->assertViewHas('frias', fn ($lista) => $lista->count() === 10 && $lista->total() === 12);

        $this->get('/dashboard?pagina_entregas=2')
            ->assertOk()->assertSee('12 entregas para hoy')
            ->assertViewHas('entregas', fn ($lista) => $lista->count() === 2 && $lista->currentPage() === 2)
            ->assertViewHas('frias', fn ($lista) => $lista->count() === 10 && $lista->currentPage() === 1)
            ->assertSee('pagina_entregas=2&amp;pagina_cotizaciones=2', false);
    }

    public function test_avisos_de_privacidad_se_consultan_antes_del_registro(): void
    {
        $this->get('/registro')->assertOk()->assertSee('Consulta el aviso de privacidad');
        $this->get('/privacidad')->assertOk()->assertSee('Aviso simplificado')->assertSee('Aviso integral');
        $this->assertGuest();
    }
}
