<?php

namespace App\Http\Controllers;

use App\Models\Contacto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ContactoController extends CrmController
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->query('buscar', ''));
        $contactos = $this->visibles($request)->with('cliente')
            ->when($buscar !== '', fn ($query) => $query->where('nombre', 'like', '%'.$buscar.'%'))
            ->orderBy('nombre')->paginate(10)->withQueryString();

        return view('contactos.index', compact('contactos', 'buscar'));
    }

    public function create(Request $request)
    {
        $this->exigirGestion($request);

        return $this->formulario($request, new Contacto);
    }

    public function edit(Request $request, int $contacto)
    {
        $this->exigirGestion($request);

        return $this->formulario($request, $this->visibles($request)->findOrFail($contacto));
    }

    public function store(Request $request)
    {
        $this->exigirGestion($request);
        $this->guardar($request, new Contacto);

        return redirect()->route('contactos.index')->with('exito', 'Contacto guardado.');
    }

    public function update(Request $request, int $contacto)
    {
        $this->exigirGestion($request);
        $this->guardar($request, $this->visibles($request)->findOrFail($contacto));

        return redirect()->route('contactos.index')->with('exito', 'Contacto actualizado.');
    }

    public function destroy(Request $request, int $contacto)
    {
        $this->exigirGestion($request);
        $this->visibles($request)->findOrFail($contacto)->delete();

        return back()->with('exito', 'Contacto retirado de la agenda.');
    }

    private function visibles(Request $request)
    {
        $clientes = $this->clientesVisibles($request)->select('id');

        return Contacto::where('negocio_id', $request->user()->negocio_id)->whereIn('cliente_id', $clientes);
    }

    private function formulario(Request $request, Contacto $contacto)
    {
        $clientes = $this->clientesVisibles($request)->where('activo', true)->orderBy('nombre')->get();

        return view('contactos.formulario', compact('contacto', 'clientes'));
    }

    private function guardar(Request $request, Contacto $contacto): void
    {
        $datos = $request->validate([
            'cliente_id' => ['required', Rule::exists('clientes', 'id')->where('negocio_id', $request->user()->negocio_id)->where('activo', true)],
            'nombre' => 'required|string|max:100', 'cargo' => 'nullable|string|max:80',
            'correo' => 'nullable|email|max:150|required_without:telefono',
            'telefono' => ['nullable', 'string', 'max:25', 'required_without:correo', 'regex:/^[0-9+() .-]+$/'],
            'principal' => 'nullable|boolean',
        ]);
        $datos['principal'] = $request->boolean('principal');
        $datos['negocio_id'] = $request->user()->negocio_id;

        DB::transaction(function () use ($contacto, $datos) {
            if ($datos['principal']) {
                Contacto::where('negocio_id', $datos['negocio_id'])
                    ->where('cliente_id', $datos['cliente_id'])->update(['principal' => false]);
            }
            $contacto->fill($datos)->save();
        });
    }
}
