<?php

namespace Database\Seeders;

use App\Models\Actividad;
use App\Models\Cliente;
use App\Models\Contacto;
use App\Models\Negocio;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Los datos de demostración solo se cargan en local o pruebas.');
        }
        $password = env('DEMO_PASSWORD');
        if (! $password || strlen($password) < 10) {
            throw new \RuntimeException('Define DEMO_PASSWORD con al menos 10 caracteres en .env.');
        }

        DB::transaction(function () use ($password) {
            $admin = User::where('email', 'ana@dulcerincon.demo')->first();
            $negocio = $admin ? $admin->negocio : Negocio::create(['nombre' => 'Dulce Rincón']);
            $usuarios = [];
            foreach ([
                ['Ana García', 'ana', 'admin'], ['Luis Torres', 'luis', 'editor'],
                ['Sofía Pérez', 'sofia', 'colaborador'], ['Elena Ruiz', 'elena', 'usuario_final'],
            ] as [$nombre, $correo, $rol]) {
                $usuarios[$rol] = User::firstOrCreate(['email' => $correo.'@dulcerincon.demo'], [
                    'name' => $nombre, 'password' => $password,
                    'negocio_id' => $negocio->id, 'rol' => $rol, 'activo' => true,
                ]);
            }

            $clientes = [];
            foreach ([
                ['Familia Ruiz', 'WhatsApp', 'Sin nuez'],
                ['Mariana López', 'Tienda', 'Alergia a la nuez'],
                ['Andrea Mendoza', 'Instagram', 'Por confirmar'],
                ['Ana Martínez', 'WhatsApp', 'Ninguna reportada'],
                ['Kinder Arcoíris', 'Instagram', 'Consultar con la escuela'],
                ['Oficina Central', 'Correo', 'Ninguna reportada'],
            ] as [$nombre, $canal, $alergias]) {
                $clientes[$nombre] = Cliente::firstOrCreate([
                    'negocio_id' => $negocio->id, 'nombre' => $nombre,
                ], [
                    'responsable_id' => $usuarios['editor']->id,
                    'canal' => $canal, 'alergias' => $alergias, 'activo' => true,
                ]);
            }
            Contacto::firstOrCreate([
                'negocio_id' => $negocio->id, 'cliente_id' => $clientes['Familia Ruiz']->id,
                'nombre' => 'Laura Ruiz',
            ], ['cargo' => 'Contacto del evento', 'correo' => 'laura.ruiz@example.com', 'principal' => true]);
            Contacto::firstOrCreate([
                'negocio_id' => $negocio->id, 'cliente_id' => $clientes['Oficina Central']->id,
                'nombre' => 'Daniel Soto',
            ], ['cargo' => 'Compras', 'correo' => 'daniel.soto@example.com', 'principal' => true]);

            $ventas = [];
            foreach ([
                ['Familia Ruiz', 'Red velvet', 'XV años', 40, 4800, 0, 3, '17:00', 'cotizacion', -9],
                ['Mariana López', 'Tres leches', 'Cumpleaños', 20, 850, 400, 0, '13:00', 'pedido', 0],
                ['Andrea Mendoza', 'Vainilla y frutos rojos', 'Boda', 80, 12500, 0, 12, '16:00', 'cotizacion', -8],
                ['Ana Martínez', 'Chocolate', 'Cumpleaños', 25, 1200, 600, 0, '18:00', 'entrega', 0],
                ['Kinder Arcoíris', 'Cupcakes de vainilla', 'Evento escolar', 60, 1800, 0, 4, '09:00', 'consulta', -1],
                ['Oficina Central', 'Cheesecake', 'Reunión', 12, 650, 300, 1, '11:00', 'pedido', 0],
            ] as [$cliente, $producto, $evento, $porciones, $monto, $anticipo, $dias, $hora, $etapa, $inactivo]) {
                $ventas[$producto] = Venta::firstOrCreate([
                    'negocio_id' => $negocio->id, 'cliente_id' => $clientes[$cliente]->id, 'producto' => $producto,
                ], [
                    'responsable_id' => $usuarios['editor']->id, 'cocina_id' => $usuarios['colaborador']->id,
                    'evento' => $evento, 'porciones' => $porciones, 'monto' => $monto, 'anticipo' => $anticipo,
                    'fecha_entrega' => today()->addDays($dias), 'hora_entrega' => $hora,
                    'etapa' => $etapa, 'estado' => 'abierta', 'ultima_actividad' => now()->addDays($inactivo),
                    'notas' => 'Registro ficticio para la exposición. Confirmar diseño e indicaciones.',
                ]);
            }
            foreach ([
                ['Tres leches', 'Cocina', 'Revisar alergia antes de decorar', 'El bizcocho está listo. Falta revisar las indicaciones del cliente.', true],
                ['Red velvet', 'Llamada', 'Confirmar diseño de XV años', 'Llamar para confirmar el diseño y la fecha de la prueba de sabor.', false],
                ['Vainilla y frutos rojos', 'Prueba de sabor', 'Agendar prueba de boda', 'Confirmar horario con la persona responsable del evento.', false],
            ] as [$producto, $tipo, $asunto, $descripcion, $completada]) {
                $venta = $ventas[$producto];
                Actividad::firstOrCreate([
                    'negocio_id' => $negocio->id, 'venta_id' => $venta->id, 'asunto' => $asunto,
                ], [
                    'cliente_id' => $venta->cliente_id, 'autor_id' => $usuarios['editor']->id,
                    'tipo' => $tipo, 'descripcion' => $descripcion,
                    'programada_para' => today()->setTime(10, 0), 'completada' => $completada,
                ]);
            }
        });
    }
}
