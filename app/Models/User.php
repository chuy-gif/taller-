<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    public const ROLES = [
        'admin' => 'Administradora',
        'editor' => 'Mostrador',
        'colaborador' => 'Cocina',
        'usuario_final' => 'Consulta',
    ];

    protected $fillable = ['name', 'email', 'password', 'negocio_id', 'rol', 'activo'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'activo' => 'boolean'];
    }

    public function negocio()
    {
        return $this->belongsTo(Negocio::class);
    }

    public function puedeGestionar(): bool
    {
        return in_array($this->rol, ['admin', 'editor'], true);
    }

    public function esCocina(): bool
    {
        return $this->rol === 'colaborador';
    }
}
