<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Models\Empresa;
use App\Models\Persona;
use App\Models\Marca;
use App\Models\Valor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $combustibles = Valor::where('iddom', 2)->where('actval', 1)->orderBy('nomval')->get();
        $cargas       = Valor::where('iddom', 10)->where('actval', 1)->orderBy('nomval')->get();
        $marcas       = Marca::orderBy('idmar', 'asc')->get(['idmar', 'nommarlin', 'depmar']);

        $propietarios = Persona::where('actper', 1)
            ->where('idpef', 6)
            ->orderBy('nomper')
            ->get(['idper', 'nomper', 'apeper', 'ndocper']);

        $conductores = Persona::where('actper', 1)
            ->where('idpef', 7)
            ->orderBy('nomper')
            ->get(['idper', 'nomper', 'apeper', 'ndocper']);

        return view('vehiculos.dashboard_vehiculos', compact(
            'vehiculos', 'empresasFiltro', 'clasesFiltro', 'propietarios', 'conductores',
            'combustibles', 'cargas', 'marcas'
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

        if (empty($validated['nordveh']) || $validated['nordveh'] === 'Autogenerado') {
            $maxId = Vehiculo::max('idveh') ?? 0;
            $validated['nordveh'] = 'V-' . str_pad($maxId + 1, 4, '0', STR_PAD_LEFT);
        }

        // Asignar tipoveh automáticamente (1: Liviano/Pasajeros, 2: Carga/Pesado)
        // Según el seeder: Camión (clveh 7) es tipoveh 2. El resto suele ser 1.
        $validated['tipoveh'] = ($validated['clveh'] == 7) ? 2 : 1;

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
        $validated = $this->validateVehiculo($request, $id);
        
        // Mantener la integridad de tipoveh
        $validated['tipoveh'] = ($validated['clveh'] == 7) ? 2 : 1;

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
            'prop' => ['nullable', 'integer', Rule::exists('persona', 'idper')->where('idpef', 6)],
            'cond' => ['nullable', 'integer', Rule::exists('persona', 'idper')->where('idpef', 7)],
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

    /**
     * Eliminar vehículo validando relaciones
     */
    public function destroy($id)
    {
        $vehiculo = Vehiculo::withCount(['diagnosticos'])->findOrFail($id);

        // Validar integridad relacional (Diagnósticos y Documentos)
        if ($vehiculo->diagnosticos_count > 0 || $vehiculo->documentos_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el vehículo porque tiene diagnósticos asociados.'
            ], 422);
        }

        // Validar vínculos activos con MUP (Propietario, Conductor, Empresa)
        if ($vehiculo->prop || $vehiculo->cond || $vehiculo->idemp) {
            $vinculos = [];
            if ($vehiculo->prop) $vinculos[] = 'Propietario';
            if ($vehiculo->cond) $vinculos[] = 'Conductor';
            if ($vehiculo->idemp) $vinculos[] = 'Empresa';

            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el vehículo porque tiene vínculos activos con: ' . implode(', ', $vinculos) . '. Debe desvincular estas entidades antes de proceder.'
            ], 422);
        }

        // Limpiar la tabla pivote proveh para evitar errores de restricción de llave foránea
        $vehiculo->personas()->detach();

        $vehiculo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vehículo eliminado correctamente.'
        ]);
    }

    /**
     * Edición rápida inline (desde el dashboard, vía AJAX)
     * Actualiza campos de tipo select/relación sin recargar la página
     */
    public function quickUpdate(Request $request, $id)
    {
        $validated = $request->validate([
            'linveh'       => 'nullable|integer',
            'clveh'        => 'nullable|integer',
            'tipo_servicio'=> 'nullable|in:1,2',
            'idemp'        => 'nullable|integer',
            'combuveh'     => 'nullable|integer',
            'crgveh'       => 'nullable|integer',
            'blinveh'      => 'nullable|in:1,2',
            'polaveh'      => 'nullable|in:1,2',
            'prop'         => ['nullable', 'integer', Rule::exists('persona', 'idper')->where('idpef', 6)],
            'cond'         => ['nullable', 'integer', Rule::exists('persona', 'idper')->where('idpef', 7)],
        ]);

        try {
            $vehiculo = Vehiculo::findOrFail($id);

            // Convertir strings vacíos a null para las FK
            foreach (['linveh', 'clveh', 'idemp', 'combuveh', 'crgveh', 'prop', 'cond'] as $fk) {
                if (isset($validated[$fk]) && $validated[$fk] === '') {
                    $validated[$fk] = null;
                }
            }

            $vehiculo->update($validated);
            $vehiculo->load(['empresa', 'propietario', 'conductor', 'marca',
                             'clase', 'combustible', 'tipoMotor', 'categoriaCarga']);

            return response()->json(['success' => true, 'vehiculo' => $vehiculo]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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
            'propietarios' => Persona::where('actper', 1)->where('idpef', 6)->orderBy('nomper')->get(['idper', 'nomper', 'apeper', 'ndocper', 'nliccon', 'fvencon', 'catcon']),
            'conductores'  => Persona::where('actper', 1)->where('idpef', 7)->orderBy('nomper')->get(['idper', 'nomper', 'apeper', 'ndocper', 'nliccon', 'fvencon', 'catcon']),
            'empresas'     => ($user->hasRole('Empresa') && $user->empresa)
                                ? collect([$user->empresa])
                                : Empresa::orderBy('razsoem')->get(),
        ];
    }

    /**
     * Validación compartida para store y update
     */
    private function validateVehiculo(Request $request, $id = null): array
    {
        return $request->validate([
            'nordveh'      => 'nullable|string|max:30',
            'placaveh'     => 'required|string|max:6|unique:vehiculo,placaveh,' . ($id ? $id : 'NULL') . ',idveh',
            'tipo_servicio'=> 'required|in:1,2',
            'linveh'       => 'required|integer|exists:marca,idmar',
            'modveh'       => 'required|integer|min:1950|max:2035',
            'colveh'       => 'required|string|max:20',
            'clveh'        => 'required|integer|exists:valor,idval',
            'tmotveh'      => 'required|integer|exists:valor,idval',
            'combuveh'     => 'required|integer|exists:valor,idval',
            'capveh'       => 'required|integer|min:1',
            'cilveh'       => 'required|integer|min:0',
            'crgveh'       => 'required|integer|exists:valor,idval',
            'nmotveh'      => 'required|string|max:30',
            'nchaveh'      => 'required|string|max:30',
            'blinveh'      => 'required|in:1,2',
            'polaveh'      => 'nullable|in:1,2',
            'lictraveh'    => 'required|numeric|digits_between:1,15',
            'fmatv'        => 'required|date',
            'fecvenr'      => 'nullable|date',
            'soat'         => 'required|numeric|digits_between:1,15',
            'fecvens'      => 'required|date',
            'extcontveh'   => 'required|numeric|digits_between:1,15',
            'fecvene'      => 'required|date',
            'tecmecveh'    => 'required|numeric|digits_between:1,15',
            'fecvent'      => 'required|date',
            'prop'         => ['required', 'integer', Rule::exists('persona', 'idper')->where('idpef', 6)],
            'cond'         => ['required', 'integer', Rule::exists('persona', 'idper')->where('idpef', 7)],
            'idemp'        => 'nullable|integer|exists:empresa,idemp',
        ], [
            'placaveh.unique' => 'Ya existe un vehículo con esta placa.',
        ]);
    }
}
