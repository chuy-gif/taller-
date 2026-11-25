<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contacto extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'negocio_id', 'cliente_id', 'nombre', 'cargo', 'correo', 'telefono', 'principal',
    ];

    protected function casts(): array
    {
        return ['principal' => 'boolean'];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
