<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'negocio_id', 'responsable_id', 'nombre', 'canal', 'correo',
        'telefono', 'alergias', 'notas', 'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function contactos()
    {
        return $this->hasMany(Contacto::class);
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    public function actividades()
    {
        return $this->hasMany(Actividad::class);
    }
}
