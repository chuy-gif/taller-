<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class UsuarioController extends CrmController
{
    public function index(Request $request)
    {
        $this->exigirAdmin($request);
        $usuarios = User::where('negocio_id', $request->user()->negocio_id)->orderBy('name')->get();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create(Request $request)
    {
        $this->exigirAdmin($request);

        return view('usuarios.formulario');
    }

    public function store(Request $request)
    {
        $this->exigirAdmin($request);
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);
        $datos = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email',
            'rol' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers(), 'max:255'],
        ]);
        User::create([...$datos, 'negocio_id' => $request->user()->negocio_id, 'activo' => true]);

        return redirect()->route('usuarios.index')->with('exito', 'Integrante registrado.');
    }

    public function update(Request $request, int $usuario)
    {
        $this->exigirAdmin($request);
        $usuario = User::where('negocio_id', $request->user()->negocio_id)->findOrFail($usuario);
        $datos = $request->validate([
            'rol' => ['required', Rule::in(array_keys(User::ROLES))],
            'activo' => 'required|boolean',
        ]);
        if ($usuario->id === $request->user()->id && ($datos['rol'] !== 'admin' || ! $datos['activo'])) {
            throw ValidationException::withMessages(['rol' => 'Conserva tu propio acceso de administración.']);
        }
        $usuario->update($datos);

        return back()->with('exito', 'Permisos actualizados.');
    }

    private function exigirAdmin(Request $request): void
    {
        abort_unless($request->user()->rol === 'admin', 403);
    }
}
