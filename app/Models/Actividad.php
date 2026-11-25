<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    protected $table = 'actividades';
    public const TIPOS = ['Nota', 'Llamada', 'Correo', 'Prueba de sabor', 'Cocina', 'Tarea'];

    protected $fillable = [
        'negocio_id', 'cliente_id', 'venta_id', 'autor_id', 'tipo',
        'asunto', 'descripcion', 'programada_para', 'completada',
    ];

    protected function casts(): array
    {
        return ['programada_para' => 'datetime', 'completada' => 'boolean'];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function autor()
    {
        return $this->belongsTo(User::class, 'autor_id');
    }
}
