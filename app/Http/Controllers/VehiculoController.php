<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Models\Empresa;
use App\Models\Persona;
use App\Models\Marca;
use App\Models\Valor;
use Illuminate\Http\Request;

class VehiculoController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $query = Vehiculo::with([
            'empresa', 'propietario', 'conductor', 'marca',
            'clase', 'combustible', 'tipoMotor', 'categoriaCarga',
        ]);

        if ($user->hasRole('Empresa')) {
            $query->where('idemp', $user->idemp);
        }

        $vehiculos = $query->latest('idveh')->get();

        if ($user->hasRole('Empresa') && $user->empresa) {
            $empresasFiltro = collect([$user->empresa]);
        } else {
            $empresasFiltro = Empresa::orderBy('razsoem', 'ASC')->get();
        }

        $clasesFiltro = Valor::where('iddom', 1)->where('actval', 1)->orderBy('nomval')->get();

        $personas = Persona::where('actper', 1)
            ->orderBy('nomper')
            ->get(['idper', 'nomper', 'apeper', 'ndocper']);

        return view('vehiculos.dashboard_vehiculos', compact(
            'vehiculos', 'empresasFiltro', 'clasesFiltro', 'personas'
        ));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        $formData = $this->getFormData();
        return view('vehiculos.form_vehiculo', array_merge($formData, [
            'vehiculo' => null,
            'modo' => 'crear',
        ]));
    }

    /**
     * Guardar nuevo vehículo
     */
    public function store(Request $request)
    {
        $validated = $this->validateVehiculo($request);
        Vehiculo::create($validated);

        $user = auth()->user();
        $prefix = $user->hasRole('Administrador') ? 'admin' : 'digitador';

        return redirect("/{$prefix}/vehiculos")
            ->with('success', 'Vehículo creado exitosamente.');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $vehiculo = Vehiculo::with([
            'empresa', 'propietario', 'conductor', 'marca',
            'clase', 'combustible', 'tipoMotor', 'categoriaCarga',
        ])->findOrFail($id);

        $formData = $this->getFormData();
        return view('vehiculos.form_vehiculo', array_merge($formData, [
            'vehiculo' => $vehiculo,
            'modo' => 'editar',
        ]));
    }

    /**
     * Actualizar vehículo existente
     */
    public function update(Request $request, $id)
    {
        $vehiculo = Vehiculo::findOrFail($id);
        $validated = $this->validateVehiculo($request);
        $vehiculo->update($validated);

        $user = auth()->user();
        $prefix = $user->hasRole('Administrador') ? 'admin' : 'digitador';

        return redirect("/{$prefix}/vehiculos")
            ->with('success', 'Vehículo actualizado exitosamente.');
    }

    /**
     * Actualizar vínculos (propietario, conductor, empresa)
     */
    public function updateVinculos(Request $request, $id)
    {
        $request->validate([
            'prop' => 'nullable|integer|exists:persona,idper',
            'cond' => 'nullable|integer|exists:persona,idper',
            'idemp' => 'nullable|integer|exists:empresa,idemp',
        ]);

        $vehiculo = Vehiculo::findOrFail($id);
        $vehiculo->update([
            'prop' => $request->input('prop'),
            'cond' => $request->input('cond'),
            'idemp' => $request->input('idemp'),
        ]);

        $vehiculo->load(['empresa', 'propietario', 'conductor', 'marca',
                         'clase', 'combustible', 'tipoMotor', 'categoriaCarga']);

        return response()->json(['success' => true, 'vehiculo' => $vehiculo]);
    }

    // ─── Helpers privados ────────────────────────────────────

    /**
     * Datos comunes para los selects del formulario
     */
    private function getFormData(): array
    {
        $user = auth()->user();

        return [
            'marcas'       => Marca::orderBy('nommarlin')->get(),
            'clases'       => Valor::where('iddom', 1)->where('actval', 1)->orderBy('nomval')->get(),
            'combustibles' => Valor::where('iddom', 2)->where('actval', 1)->orderBy('nomval')->get(),
            'tiposMotor'   => Valor::where('iddom', 9)->where('actval', 1)->orderBy('nomval')->get(),
            'cargas'       => Valor::where('iddom', 10)->where('actval', 1)->orderBy('nomval')->get(),
            'personas'     => Persona::where('actper', 1)->orderBy('nomper')->get(['idper', 'nomper', 'apeper', 'ndocper']),
            'empresas'     => ($user->hasRole('Empresa') && $user->empresa)
                                ? collect([$user->empresa])
                                : Empresa::orderBy('razsoem')->get(),
        ];
    }

    /**
     * Validación compartida para store y update
     */
    private function validateVehiculo(Request $request): array
    {
        return $request->validate([
            'nordveh'      => 'nullable|string|max:30',
            'placaveh'     => 'required|string|max:6',
            'tipo_servicio'=> 'required|in:1,2',
            'linveh'       => 'nullable|integer|exists:marca,idmar',
            'modveh'       => 'nullable|integer|min:1950|max:2035',
            'colveh'       => 'nullable|string|max:20',
            'clveh'        => 'nullable|integer|exists:valor,idval',
            'tmotveh'      => 'nullable|integer|exists:valor,idval',
            'combuveh'     => 'nullable|integer|exists:valor,idval',
            'capveh'       => 'nullable|integer|min:1',
            'cilveh'       => 'nullable|integer|min:0',
            'crgveh'       => 'nullable|integer|exists:valor,idval',
            'nmotveh'      => 'nullable|string|max:30',
            'nchaveh'      => 'nullable|string|max:30',
            'blinveh'      => 'nullable|in:1,2',
            'polaveh'      => 'nullable|in:1,2',
            'lictraveh'    => 'nullable|string|max:15',
            'fmatv'        => 'nullable|date',
            'fecvenr'      => 'nullable|date',
            'soat'         => 'nullable|string|max:15',
            'fecvens'      => 'nullable|date',
            'extcontveh'   => 'nullable|string|max:15',
            'fecvene'      => 'nullable|date',
            'tecmecveh'    => 'nullable|string|max:15',
            'fecvent'      => 'nullable|date',
            'prop'         => 'nullable|integer|exists:persona,idper',
            'cond'         => 'nullable|integer|exists:persona,idper',
            'idemp'        => 'nullable|integer|exists:empresa,idemp',
        ]);
    }
}
