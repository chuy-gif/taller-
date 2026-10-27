<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    // El pedido de la pastelería se sigue aquí como una oportunidad de venta.
    public const ETAPAS = [
        'consulta' => 'Consulta', 'cotizacion' => 'Cotización',
        'pedido' => 'Pedido', 'entrega' => 'Entrega',
    ];

    protected $fillable = [
        'negocio_id', 'cliente_id', 'responsable_id', 'cocina_id', 'producto',
        'evento', 'porciones', 'monto', 'anticipo', 'fecha_entrega', 'hora_entrega',
        'etapa', 'estado', 'notas', 'motivo_perdida', 'ultima_actividad', 'cerrada_en',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2', 'anticipo' => 'decimal:2',
            'fecha_entrega' => 'date', 'ultima_actividad' => 'datetime',
            'cerrada_en' => 'datetime',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function cocina()
    {
        return $this->belongsTo(User::class, 'cocina_id');
    }

    public function actividades()
    {
        return $this->hasMany(Actividad::class);
    }
}
