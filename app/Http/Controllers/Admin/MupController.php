<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Models\Perfil;
use App\Models\Valor;
use App\Models\Pagina;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Vehiculo;
use App\Support\LicenciaConduccion;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class MupController extends Controller
{
    /**
     * Validación: licencia (catcon, nliccon, fvencon) todo null o los tres informados.
     */
    protected function rulesLicenciaTriada(Request $request): array
    {
        $licenciaPresente = $request->filled('catcon')
            || $request->filled('nliccon')
            || $request->filled('fvencon');

        return [
            'catcon' => [
                'nullable',
                'string',
                'max:5',
                Rule::requiredIf($licenciaPresente),
                Rule::in(LicenciaConduccion::CATEGORIAS),
            ],
            'nliccon' => [
                'nullable',
                'string',
                'max:20',
                Rule::requiredIf($licenciaPresente),
            ],
            'fvencon' => [
                'nullable',
                'date',
                Rule::requiredIf($licenciaPresente),
            ],
        ];
    }

    protected function normalizedLicencia(Request $request): array
    {
        if (!$request->filled('catcon') && !$request->filled('nliccon') && !$request->filled('fvencon')) {
            return ['catcon' => null, 'nliccon' => null, 'fvencon' => null];
        }

        return [
            'catcon' => $request->catcon,
            'nliccon' => $request->nliccon,
            'fvencon' => $request->fvencon,
        ];
    }

    /**
     * Personas conductor en todo el sistema: perfil Conductor O asignadas en vehículo (cond).
     */
    protected function personaIdsConductorGlobal(Perfil $perfilConductor): array
    {
        $porPerfil = Persona::query()->where('idpef', $perfilConductor->idpef)->pluck('idper');
        $porVehiculo = Vehiculo::query()->whereNotNull('cond')->distinct()->pluck('cond');

        return $porPerfil->merge($porVehiculo)->unique()->filter()->values()->all();
    }

    /**
     * Personas propietario en todo el sistema: perfil Propietario O asignadas en vehículo (prop).
     */
    protected function personaIdsPropietarioGlobal(Perfil $perfilPropietario): array
    {
        $porPerfil = Persona::query()->where('idpef', $perfilPropietario->idpef)->pluck('idper');
        $porVehiculo = Vehiculo::query()->whereNotNull('prop')->distinct()->pluck('prop');

        return $porPerfil->merge($porVehiculo)->unique()->filter()->values()->all();
    }

    /**
     * Display the Conductores view.
     */
    public function conductores()
    {
        // PerfilSeeder: Conductor = idpef 7
        $perfilConductor = Perfil::firstOrCreate(
            ['nompef' => 'Conductor'],
            ['idpef' => 7, 'pagpri' => null]
        );

        $ids = $this->personaIdsConductorGlobal($perfilConductor);
        $conductores = $ids === []
            ? collect()
            : Persona::with(['vehiculosConducidos', 'vehiculosPropios'])
                ->whereIn('idper', $ids)
                ->orderBy('idper', 'desc')
                ->get();

        // 3. Tipos de documento y categorías fijas de licencia (texto)
        $tiposDoc = Valor::where('iddom', 4)->where('actval', 1)->get();
        $licenciaCategorias = LicenciaConduccion::CATEGORIAS;

        return view('admin.mup.conductores', compact('conductores', 'tiposDoc', 'licenciaCategorias'));
    }

    /**
     * Store a new conductor.
     */
    public function storeConductor(Request $request)
    {
        $request->validate(array_merge([
            'nombre_completo' => 'required|string|max:100',
            'tdocper' => 'required|exists:valor,idval',
            'ndocper' => 'required|numeric|unique:persona,ndocper',
            'emaper' => 'required|email|max:60',
            'telper' => 'nullable|string|max:20|regex:/^[0-9]+$/',
            'actper' => 'required|in:0,1',
        ], $this->rulesLicenciaTriada($request)), [
            'ndocper.unique' => 'Ya existe una persona registrada con este número de documento.',
            'emaper.email' => 'El formato del correo electrónico no es válido.',
            'catcon.in' => 'Seleccione una categoría de licencia válida (A1–C3).',
        ]);

        try {
            DB::beginTransaction();

            // Split name into nomper and apeper
            $parts = explode(' ', $request->nombre_completo, 2);
            $nomper = $parts[0];
            $apeper = $parts[1] ?? '';

            $perfilConductor = Perfil::firstOrCreate(['nompef' => 'Conductor'], ['idpef' => 7, 'pagpri' => null]);

            $lic = $this->normalizedLicencia($request);

            Persona::create(array_merge([
                'nomper' => $nomper,
                'apeper' => $apeper,
                'tdocper' => $request->tdocper,
                'ndocper' => $request->ndocper,
                'emaper' => $request->emaper,
                'telper' => $request->telper ?? '',
                'actper' => $request->actper,
                'idpef' => $perfilConductor->idpef,
                'codubi' => 1, // Default
            ], $lic));

            DB::commit();
            return redirect()->back()->with('success', 'Conductor registrado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error registrando conductor: " . $e->getMessage());
            return redirect()->back()->with('error', $this->friendlyError($e, 'registrar el conductor'))->withInput();
        }
    }

    /**
     * Update an existing conductor record.
     */
    public function updateConductor(Request $request, $id)
    {
        $persona = Persona::findOrFail($id);

        $perfilConductor = Perfil::firstOrCreate(['nompef' => 'Conductor'], ['idpef' => 7, 'pagpri' => null]);
        if (! in_array((int) $id, array_map('intval', $this->personaIdsConductorGlobal($perfilConductor)), true)) {
            abort(404);
        }

        $request->validate(array_merge([
            'nombre_completo' => 'required|string|max:100',
            'tdocper' => 'required|exists:valor,idval',
            'ndocper' => 'required|numeric|unique:persona,ndocper,' . $id . ',idper',
            'emaper' => 'required|email|max:60',
            'telper' => 'nullable|string|max:20|regex:/^[0-9]+$/',
            'actper' => 'required|in:0,1',
        ], $this->rulesLicenciaTriada($request)), [
            'catcon.in' => 'Seleccione una categoría de licencia válida (A1–C3).',
        ]);

        try {
            DB::beginTransaction();

            $parts = explode(' ', $request->nombre_completo, 2);
            $nomper = $parts[0];
            $apeper = $parts[1] ?? '';

            $lic = $this->normalizedLicencia($request);

            $persona->update(array_merge([
                'nomper' => $nomper,
                'apeper' => $apeper,
                'tdocper' => $request->tdocper,
                'ndocper' => $request->ndocper,
                'emaper' => $request->emaper,
                'telper' => $request->telper ?? '',
                'actper' => $request->actper,
                'idpef' => $perfilConductor->idpef,
            ], $lic));

            DB::commit();
            return redirect()->back()->with('success', 'Conductor actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error actualizando conductor: " . $e->getMessage());
            return redirect()->back()->with('error', $this->friendlyError($e, 'actualizar el conductor'));
        }
    }

    /**
     * Remove a conductor from the system.
     */
    public function destroyConductor($id)
    {
        try {
            DB::beginTransaction();
            $persona = Persona::findOrFail($id);

            $perfilConductor = Perfil::firstOrCreate(['nompef' => 'Conductor'], ['idpef' => 7, 'pagpri' => null]);
            if (! in_array((int) $id, array_map('intval', $this->personaIdsConductorGlobal($perfilConductor)), true)) {
                abort(404);
            }

            Vehiculo::where('cond', $id)->update(['cond' => null]);
            DB::table('proveh')->where('idper', $id)->delete();
            
            // Si el conductor tiene un usuario vinculado, lo borramos también para mantener integridad
            User::where('idper', $id)->delete();
            
            $persona->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Conductor eliminado permanentemente del sistema.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error eliminando conductor: " . $e->getMessage());
            return redirect()->back()->with('error', $this->friendlyError($e, 'eliminar el conductor'));
        }
    }

    /**
     * Display the view for creating a new system profile.
     */
    public function nuevoPerfil()
    {
        $modulos = Pagina::orderBy('ordpag')->get();
        return view('admin.mup.nuevo-perfil', compact('modulos'));
    }

    /**
     * Store a new system profile and its associated permissions.
     */
    public function storePerfil(Request $request)
    {
        $request->validate([
            'nompef' => 'required|string|max:255|unique:perfil,nompef',
            'tipo_pef' => 'required|string|max:50',
            'des_pef' => 'nullable|string',
            'permisos' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            // 1. Crear Perfil en tabla interna
            $perfil = Perfil::create([
                'nompef' => $request->nompef,
                'tipo_pef' => $request->tipo_pef,
                'des_pef' => $request->des_pef,
                'pagpri' => null, // Opcional: página de inicio por defecto
            ]);

            // 2. Crear Role de Spatie
            $role = Role::firstOrCreate(['name' => $request->nompef]);

            // 3. Procesar Permisos con Mapeo de Rutas
            if ($request->has('permisos')) {
                foreach ($request->permisos as $nompag => $actions) {
                    $pagina = Pagina::where('nompag', $nompag)->first();
                    if (!$pagina) continue;

                    // Obtener el prefijo de la ruta base (ej: admin.mup.usuarios)
                    // Usaremos un mapeo manual para mayor precisión por ahora
                    $baseRoute = $this->mapPaginaToRoute($nompag);
                    if (!$baseRoute) continue;

                    foreach ($actions as $action => $status) {
                        if ($status === 'on') {
                            $routeNames = $this->getRouteNamesForAction($baseRoute, $action);
                            
                            foreach ($routeNames as $rn) {
                                $permission = Permission::firstOrCreate(['name' => $rn]);
                                $role->givePermissionTo($permission);
                            }
                        }
                    }
                }
            }

            // 4. Vincular todas las páginas permitidas en pagper (para visibilidad de menú)
            if ($request->has('permisos')) {
                $paginasIds = Pagina::whereIn('nompag', array_keys($request->permisos))->pluck('idpag');
                $perfil->paginas()->sync($paginasIds);
            }

            DB::commit();

            return redirect()->route('admin.mup.conductores')->with('success', 'Perfil creado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error creando perfil: " . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al crear el perfil: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Update an existing profile and its permissions.
     */
    public function updatePerfil(Request $request, $id)
    {
        $perfil = Perfil::findOrFail($id);
        
        $request->validate([
            'nompef' => 'required|string|max:255|unique:perfil,nompef,' . $id . ',idpef',
            'des_pef' => 'nullable|string',
            'permisos' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            // 1. Actualizar Perfil interno
            $perfil->update([
                'nompef' => $request->nompef,
                'des_pef' => $request->des_pef,
            ]);

            // 2. Sincronizar con Role de Spatie
            $role = Role::firstOrCreate(['name' => $request->nompef]);
            
            // Si el nombre del perfil cambió, el rol viejo debe ser manejado, 
            // pero Spatie usualmente identifica roles por nombre único.
            // Para simplicidad en este MVP, asumiremos que si cambia el nombre, se actualiza el rol.
            if ($role->name !== $request->nompef) {
                $role->update(['name' => $request->nompef]);
            }

            // 3. Limpiar y Reasignar Permisos Basados en Rutas
            $role->syncPermissions([]); // Limpiar todo para reasignar fresco

            if ($request->has('permisos')) {
                foreach ($request->permisos as $nompag => $actions) {
                    $baseRoute = $this->mapPaginaToRoute($nompag);
                    if (!$baseRoute) continue;

                    foreach ($actions as $action => $status) {
                        if ($status === 'on') {
                            $routeNames = $this->getRouteNamesForAction($baseRoute, $action);
                            foreach ($routeNames as $rn) {
                                $permission = Permission::firstOrCreate(['name' => $rn]);
                                $role->givePermissionTo($permission);
                            }
                        }
                    }
                }

                // 4. Sincronizar páginas para menú
                $paginasIds = Pagina::whereIn('nompag', array_keys($request->permisos))->pluck('idpag');
                $perfil->paginas()->sync($paginasIds);
            } else {
                $perfil->paginas()->detach();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Perfil "' . $perfil->nompef . '" actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error actualizando perfil: " . $e->getMessage());
            return redirect()->back()->with('error', 'No se pudo actualizar el perfil: ' . $e->getMessage());
        }
    }

    /**
     * Display the Users (MUP) main dashbaord.
     */
    public function usuarios()
    {
        // 1. Listado de usuarios con su perfil y persona vinculada
        $usuarios = User::with('persona.perfil', 'empresa')
            ->orderBy('id', 'desc')
            ->get();

        // 2. Perfiles con conteo de personas y sus permisos (Spatie) cargados manualmente
        $perfilesRaw = Perfil::withCount('personas')
            ->with(['paginas'])
            ->orderBy('idpef')
            ->get();

        // Mapeamos los permisos de Spatie a cada perfil para que el editor los reconozca
        $perfiles = $perfilesRaw->map(function($p) {
            $role = \Spatie\Permission\Models\Role::where('name', $p->nompef)->first();
            // Inyectamos solo los nombres en un array plano para el frontend
            $p->permission_names = $role ? $role->permissions->pluck('name')->toArray() : [];
            return $p;
        });

        // 3. Combos para el formulario
        $tiposDoc = Valor::where('iddom', 4)->where('actval', 1)->get();
        $empresas = Empresa::orderBy('razsoem')->get();

        return view('admin.mup.usuarios', compact('usuarios', 'perfiles', 'tiposDoc', 'empresas'));
    }

    /**
     * Store a new User + Persona synchronized record.
     */
    public function storeUsuario(Request $request)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:100',
            'tdocper' => 'required',
            'ndocper' => 'required|numeric|unique:persona,ndocper',
            'emaper' => 'required|email|unique:persona,emaper',
            'telper' => 'nullable|string|max:20|regex:/^[0-9]+$/',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
            'idpef' => [
                'required',
                'exists:perfil,idpef',
                function ($attribute, $value, $fail) {
                    $perfil = Perfil::find($value);
                    if ($perfil && !in_array($perfil->nompef, ['Administrador', 'Digitador', 'Inspector', 'Ingeniero'])) {
                        $fail('El perfil seleccionado no es válido para este módulo.');
                    }
                }
            ],
        ], [
            'ndocper.unique' => 'Ya existe un usuario con este número de documento.',
            'emaper.unique' => 'Ya existe un usuario con este correo electrónico.',
            'emaper.email' => 'El formato del correo electrónico no es válido.',
            'username.unique' => 'Este nombre de usuario ya está en uso.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        try {
            DB::beginTransaction();

            // 1. Split name
            $parts = explode(' ', $request->nombre_completo, 2);
            $nomper = $parts[0];
            $apeper = $parts[1] ?? '';

            // 2. Create Persona
            $persona = Persona::create([
                'nomper' => $nomper,
                'apeper' => $apeper,
                'tdocper' => $request->tdocper,
                'ndocper' => $request->ndocper,
                'emaper' => $request->emaper,
                'telper' => $request->telper ?? '',
                'idpef' => $request->idpef,
                'idemp' => $request->idemp,
                'codubi' => 1, // Default
                'actper' => 1, // Default active
            ]);

            // 3. Create User
            $user = User::create([
                'name' => $request->nombre_completo,
                'username' => $request->username,
                'email' => $request->emaper,
                'password' => Hash::make($request->password),
                'idper' => $persona->idper,
                'idemp' => $request->idemp,
            ]);

            // 4. Assign Spatie Role
            $perfil = Perfil::find($request->idpef);
            if ($perfil) {
                Role::firstOrCreate(['name' => $perfil->nompef]);
                $user->assignRole($perfil->nompef);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Usuario creado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error registrando usuario: " . $e->getMessage());
            return redirect()->back()->with('error', $this->friendlyError($e, 'registrar el usuario'))->withInput();
        }
    }

    /**
     * Display the Propietarios view.
     */
    public function propietarios()
    {
        // 1. Asegurar que el perfil 'Propietario' existe respetando IDs reales de BD
        // PerfilSeeder: Propietario = idpef 6
        $perfil = Perfil::firstOrCreate(
            ['nompef' => 'Propietario'],
            ['idpef' => 6, 'pagpri' => null]
        );

        $ids = $this->personaIdsPropietarioGlobal($perfil);
        $propietarios = $ids === []
            ? collect()
            : Persona::with(['vehiculosConducidos', 'vehiculosPropios'])
                ->whereIn('idper', $ids)
                ->orderBy('idper', 'desc')
                ->get();

        // 3. Obtener datos para combos respetando estructura real
        // Tipos de documento activos para nuevos registros
        $tiposDoc = Valor::where('iddom', 4)->where('actval', 1)->get();
        $licenciaCategorias = LicenciaConduccion::CATEGORIAS;

        return view('admin.mup.propietarios', compact('propietarios', 'tiposDoc', 'licenciaCategorias'));
    }

    /**
     * Store a new propietario.
     */
    public function storePropietario(Request $request)
    {
        $request->validate(array_merge([
            'nombre_completo' => 'required|string|max:100',
            'tdocper' => 'required|exists:valor,idval',
            'ndocper' => 'required|numeric|unique:persona,ndocper',
            'emaper' => 'required|email|max:60',
            'telper' => 'nullable|string|max:20|regex:/^[0-9]+$/',
            'actper' => 'required|in:0,1',
            'dirper' => 'nullable|string|max:150',
            'ciuper' => 'nullable|string|max:50',
        ], $this->rulesLicenciaTriada($request)), [
            'ndocper.unique' => 'Ya existe una persona registrada con este número de documento.',
            'emaper.email' => 'El formato del correo electrónico no es válido.',
            'catcon.in' => 'Seleccione una categoría de licencia válida (A1–C3).',
        ]);

        try {
            DB::beginTransaction();

            $parts = explode(' ', $request->nombre_completo, 2);
            $nomper = $parts[0];
            $apeper = $parts[1] ?? '';

            $perfilPropietario = Perfil::firstOrCreate(['nompef' => 'Propietario'], ['idpef' => 6, 'pagpri' => null]);

            $lic = $this->normalizedLicencia($request);

            Persona::create(array_merge([
                'nomper' => $nomper,
                'apeper' => $apeper,
                'tdocper' => $request->tdocper,
                'ndocper' => $request->ndocper,
                'emaper' => $request->emaper,
                'telper' => $request->telper ?? '',
                'actper' => $request->actper,
                'dirper' => $request->dirper,
                'ciuper' => $request->ciuper,
                'idpef' => $perfilPropietario->idpef,
                'codubi' => 1,
            ], $lic));

            DB::commit();
            return redirect()->back()->with('success', 'Propietario registrado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error registrando propietario: " . $e->getMessage());
            return redirect()->back()->with('error', $this->friendlyError($e, 'registrar el propietario'))->withInput();
        }
    }

    /**
     * Update an existing propietario.
     */
    public function updatePropietario(Request $request, $id)
    {
        $persona = Persona::findOrFail($id);

        $request->validate(array_merge([
            'nombre_completo' => 'required|string|max:100',
            'tdocper' => 'required|exists:valor,idval',
            'ndocper' => 'required|numeric|unique:persona,ndocper,' . $id . ',idper',
            'emaper' => 'required|email|max:60',
            'telper' => 'nullable|string|max:20|regex:/^[0-9]+$/',
            'actper' => 'required|in:0,1',
            'dirper' => 'nullable|string|max:150',
            'ciuper' => 'nullable|string|max:50',
        ], $this->rulesLicenciaTriada($request)), [
            'catcon.in' => 'Seleccione una categoría de licencia válida (A1–C3).',
        ]);

        try {
            DB::beginTransaction();

            $perfilPropietario = Perfil::firstOrCreate(['nompef' => 'Propietario'], ['idpef' => 6, 'pagpri' => null]);
            if (! in_array((int) $id, array_map('intval', $this->personaIdsPropietarioGlobal($perfilPropietario)), true)) {
                abort(404);
            }

            $parts = explode(' ', $request->nombre_completo, 2);
            $nomper = $parts[0];
            $apeper = $parts[1] ?? '';

            $lic = $this->normalizedLicencia($request);

            $persona->update(array_merge([
                'nomper' => $nomper,
                'apeper' => $apeper,
                'tdocper' => $request->tdocper,
                'ndocper' => $request->ndocper,
                'emaper' => $request->emaper,
                'telper' => $request->telper ?? '',
                'actper' => $request->actper,
                'dirper' => $request->dirper,
                'ciuper' => $request->ciuper,
                'idpef' => $perfilPropietario->idpef,
            ], $lic));

            DB::commit();
            return redirect()->back()->with('success', 'Propietario actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error actualizando propietario: " . $e->getMessage());
            return redirect()->back()->with('error', $this->friendlyError($e, 'actualizar el propietario'))->withInput();
        }
    }

    /**
     * Delete a propietario.
     */
    public function destroyPropietario($id)
    {
        try {
            DB::beginTransaction();
            $persona = Persona::findOrFail($id);

            $perfilPropietario = Perfil::firstOrCreate(['nompef' => 'Propietario'], ['idpef' => 6, 'pagpri' => null]);
            if (! in_array((int) $id, array_map('intval', $this->personaIdsPropietarioGlobal($perfilPropietario)), true)) {
                abort(404);
            }

            Vehiculo::where('prop', $id)->update(['prop' => null]);
            DB::table('proveh')->where('idper', $id)->delete();
            
            // Clean linked records if any
            User::where('idper', $id)->delete();
            $persona->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Propietario eliminado del sistema.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error eliminando propietario: " . $e->getMessage());
            return redirect()->back()->with('error', $this->friendlyError($e, 'eliminar el propietario'));
        }
    }

    /**
     * Display the Empresas view.
     */
    public function empresas()
    {
        // 1. Asegurar perfil 'Empresa' respetando estructura actual
        $perfil = Perfil::firstOrCreate(
            ['nompef' => 'Empresa'],
            ['pagpri' => null]
        );
        
        // 2. Obtener listado real desde BD
        $empresas = Empresa::with(['perfil', 'vehiculos'])->orderBy('idemp', 'desc')->get();

        return view('admin.mup.empresas', compact('empresas'));
    }

    /**
     * Store a new Empresa + Linked User.
     */
    public function storeEmpresa(Request $request)
    {
        $request->validate([
            'razsoem' => 'required|string|max:100',
            'nonitem' => 'required|string|unique:empresa,nonitem',
            'abremp' => 'nullable|string|max:10',
            'direm' => 'nullable|string|max:100',
            'ciudeem' => 'nullable|string|max:50',
            'nomger' => 'required|string|max:100',
            'telem' => 'required|string|max:20|regex:/^[0-9]+$/',
            'emaem' => 'required|email|max:60',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'nonitem.unique' => 'El NIT de esta empresa ya se encuentra registrado.',
            'username.unique' => 'El nombre de usuario ya está asignado a otra entidad o usuario.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'telem.max' => 'El teléfono no puede superar los 20 caracteres.',
        ]);

        try {
            DB::beginTransaction();

            $perfilEmpresa = Perfil::firstOrCreate(['nompef' => 'Empresa'], ['pagpri' => null]);

            // 1. Crear Empresa
            $empresa = Empresa::create([
                'razsoem' => $request->razsoem,
                'nonitem' => $request->nonitem,
                'abremp' => $request->abremp,
                'direm' => $request->direm,
                'telem' => $request->telem,
                'emaem' => $request->emaem,
                'nomger' => $request->nomger,
                'idpef' => $perfilEmpresa->idpef,
                'codubi' => 1,
                'usuaemp' => $request->username,
                'passemp' => Hash::make($request->password), 
            ]);

            // 2. Crear User vinculado
            $user = User::create([
                'name' => $request->razsoem,
                'username' => $request->username,
                'email' => $request->emaem,
                'password' => Hash::make($request->password),
                'idemp' => $empresa->idemp,
            ]);

            // 3. Asignar rol
            Role::firstOrCreate(['name' => $perfilEmpresa->nompef]);
            $user->assignRole($perfilEmpresa->nompef);

            DB::commit();
            return redirect()->back()->with('success', 'Empresa y usuario de acceso creados exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error registrando empresa: " . $e->getMessage());
            return redirect()->back()->with('error', $this->friendlyError($e, 'registrar la empresa'))->withInput();
        }
    }

    /**
     * Update an existing Empresa.
     */
    public function updateEmpresa(Request $request, $id)
    {
        $empresa = Empresa::findOrFail($id);

        $request->validate([
            'razsoem' => 'required|string|max:100',
            'nonitem' => 'required|string|unique:empresa,nonitem,' . $id . ',idemp',
            'abremp' => 'nullable|string|max:10',
            'direm' => 'nullable|string|max:100',
            'nomger' => 'required|string|max:100',
            'telem' => 'required|string|max:20|regex:/^[0-9]+$/',
            'emaem' => 'required|email|max:60',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        try {
            DB::beginTransaction();

            $empresa->update([
                'razsoem' => $request->razsoem,
                'nonitem' => $request->nonitem,
                'abremp' => $request->abremp,
                'direm' => $request->direm,
                'telem' => $request->telem,
                'emaem' => $request->emaem,
                'nomger' => $request->nomger,
            ]);

            // Sync empresa + usuario de acceso asociado
            $linkedUser = User::where('idemp', $id)->first();
            if ($linkedUser) {
                if ($request->filled('username')) {
                    $request->validate([
                        'username' => 'unique:users,username,' . $linkedUser->id,
                    ]);
                }

                $userData = [
                    'name' => $request->razsoem,
                    'email' => $request->emaem,
                ];

                if ($request->filled('username')) {
                    $userData['username'] = $request->username;
                    $empresa->usuaemp = $request->username;
                }

                if ($request->filled('password')) {
                    $userData['password'] = Hash::make($request->password);
                    $empresa->passemp = Hash::make($request->password);
                }

                $linkedUser->update($userData);
                $empresa->save();
            } elseif ($request->filled('username') && $request->filled('password')) {
                $request->validate([
                    'username' => 'unique:users,username',
                ]);

                $newUser = User::create([
                    'name' => $request->razsoem,
                    'username' => $request->username,
                    'email' => $request->emaem,
                    'password' => Hash::make($request->password),
                    'idemp' => $empresa->idemp,
                ]);

                $perfilNombre = optional($empresa->perfil)->nompef ?? 'Empresa';
                Role::firstOrCreate(['name' => $perfilNombre]);
                $newUser->assignRole($perfilNombre);

                $empresa->usuaemp = $request->username;
                $empresa->passemp = Hash::make($request->password);
                $empresa->save();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Empresa actualizada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error actualizando empresa: " . $e->getMessage());
            return redirect()->back()->with('error', $this->friendlyError($e, 'actualizar la empresa'));
        }
    }

    /**
     * Delete an Empresa.
     */
    public function destroyEmpresa($id)
    {
        try {
            DB::beginTransaction();
            $empresa = Empresa::findOrFail($id);
            
            // Critical cleanup: Companies often have many linked records. 
            // We clean User access first.
            User::where('idemp', $id)->delete();
            $empresa->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Empresa eliminada del sistema.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error eliminando empresa: " . $e->getMessage());
            return redirect()->back()->with('error', $this->friendlyError($e, 'eliminar la empresa'));
        }
    }

    /**
     * Update an existing User + Persona.
     */
    public function updateUsuario(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
            'nombre_completo' => 'required|string|max:100',
            'tdocper' => 'required',
            'ndocper' => 'required|numeric|unique:persona,ndocper,' . $user->idper . ',idper',
            'emaper' => 'required|email|unique:persona,emaper,' . $user->idper . ',idper',
            'telper' => 'nullable|string|max:20|regex:/^[0-9]+$/',
            'username' => 'required|string|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'idpef' => [
                'required',
                'exists:perfil,idpef',
                function ($attribute, $value, $fail) {
                    $perfil = Perfil::find($value);
                    if ($perfil && !in_array($perfil->nompef, ['Administrador', 'Digitador', 'Inspector', 'Ingeniero'])) {
                        $fail('El perfil seleccionado no es válido para este módulo.');
                    }
                }
            ],
            'actper' => 'required|in:0,1',
        ]);

        try {
            DB::beginTransaction();

            $parts = explode(' ', $request->nombre_completo, 2);
            $nomper = $parts[0];
            $apeper = $parts[1] ?? '';

            // Update Persona
            if (!$user->persona) {
                throw new \RuntimeException('El usuario no tiene un registro de persona vinculado.');
            }

            $user->persona->update([
                'nomper' => $nomper,
                'apeper' => $apeper,
                'tdocper' => $request->tdocper,
                'ndocper' => $request->ndocper,
                'emaper' => $request->emaper,
                'telper' => $request->telper ?? '',
                'idpef' => $request->idpef,
                'idemp' => $request->idemp,
                'actper' => $request->actper,
            ]);

            // Update User
            $userData = [
                'name' => $request->nombre_completo,
                'username' => $request->username,
                'email' => $request->emaper,
                'idemp' => $request->idemp,
            ];
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }
            $user->update($userData);

            // Sync Role
            $perfil = Perfil::find($request->idpef);
            if ($perfil) {
                Role::firstOrCreate(['name' => $perfil->nompef]);
                $user->syncRoles([$perfil->nompef]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Usuario actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error actualizando usuario: " . $e->getMessage());
            return redirect()->back()->with('error', $this->friendlyError($e, 'actualizar el usuario'));
        }
    }

    /**
     * Physically delete a User + Persona.
     */
    public function destroyUsuario($id)
    {
        try {
            DB::beginTransaction();
            $user = User::findOrFail($id);
            $idper = $user->idper;

            // Delete User first
            $user->delete();

            // Delete Persona
            if ($idper) {
                Persona::where('idper', $idper)->delete();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Usuario eliminado permanentemente del sistema.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error eliminando usuario: " . $e->getMessage());
            return redirect()->back()->with('error', $this->friendlyError($e, 'eliminar el usuario'));
        }
    }

    /**
     * Mapea el nombre de la página (Módulo) al nombre base de la ruta en web.php
     */
    private function mapPaginaToRoute($nompag)
    {
        $map = [
            'Dashboard'      => 'admin.dashboard',
            'Diagnóstico'    => 'admin.diagnosticos',
            'Vehículos'      => 'admin.vehiculos',
            'Alertas'        => 'admin.alertas',
            'Mantenimiento'  => 'admin.dashboard', // Fallback si no hay ruta dedicada
            'Empresas'       => 'admin.mup.empresas',
            'Usuarios'       => 'admin.mup.usuarios',
            'Conductores'    => 'admin.mup.conductores',
            'Propietarios'   => 'admin.mup.propietarios',
            'Rechazados'     => 'admin.rechazados',
        ];

        return $map[$nompag] ?? null;
    }

    /**
     * Retorna los nombres de rutas específicos para una acción CRUD
     */
    private function getRouteNamesForAction($baseRoute, $action)
    {
        switch ($action) {
            case 'ver':
                if (in_array($baseRoute, ['admin.dashboard', 'admin.alertas', 'admin.rechazados'])) {
                    return [$baseRoute];
                }
                return [$baseRoute . '.index', $baseRoute . '.show'];
            
            case 'crear':
                return [$baseRoute . '.create', $baseRoute . '.store'];
            
            case 'editar':
                return [$baseRoute . '.edit', $baseRoute . '.update'];
            
            case 'eliminar':
                return [$baseRoute . '.destroy'];
            
            default:
                return [];
        }
    }

    /**
     * Traduce excepciones de base de datos a mensajes amigables para el usuario.
     */
    private function friendlyError(\Exception $e, string $accion): string
    {
        $msg = $e->getMessage();

        // Duplicación de registro (email, NIT, documento, username)
        if (str_contains($msg, 'Duplicate entry')) {
            if (str_contains($msg, 'email_unique') || str_contains($msg, 'emaper')) {
                return 'No se pudo ' . $accion . ': el correo electrónico ya está registrado en el sistema.';
            }
            if (str_contains($msg, 'username')) {
                return 'No se pudo ' . $accion . ': el nombre de usuario ya está en uso.';
            }
            if (str_contains($msg, 'ndocper') || str_contains($msg, 'documento')) {
                return 'No se pudo ' . $accion . ': el número de documento ya está registrado.';
            }
            if (str_contains($msg, 'nonitem') || str_contains($msg, 'nit')) {
                return 'No se pudo ' . $accion . ': el NIT ya se encuentra registrado.';
            }
            return 'No se pudo ' . $accion . ': ya existe un registro con datos duplicados. Verifica correo, documento o usuario.';
        }

        // Dato demasiado largo para la columna
        if (str_contains($msg, 'Data too long')) {
            if (str_contains($msg, 'telem')) {
                return 'No se pudo ' . $accion . ': el número de teléfono es demasiado largo. Máximo 20 caracteres.';
            }
            return 'No se pudo ' . $accion . ': uno de los campos ingresados excede la longitud máxima permitida. Revisa los datos e intenta nuevamente.';
        }

        // Columna ausente en BD (p. ej. ciuper no migrada)
        if (str_contains($msg, 'Unknown column') && str_contains($msg, 'ciuper')) {
            return 'No se pudo ' . $accion . ': la base de datos no tiene la columna de ciudad (ciuper). Ejecute en el servidor: php artisan migrate';
        }

        if (str_contains($msg, 'Unknown column')) {
            return 'No se pudo ' . $accion . ': la estructura de la base de datos no coincide con la aplicación. Ejecute php artisan migrate y vuelva a intentar.';
        }

        // Restricción de clave foránea
        if (str_contains($msg, 'foreign key constraint') || str_contains($msg, 'Cannot add or update a child row')) {
            return 'No se pudo ' . $accion . ': verifique que el tipo de documento exista y que el código de ubicación (ciudad) sea válido en el sistema.';
        }

        // Error de conexión
        if (str_contains($msg, 'Connection refused') || str_contains($msg, 'SQLSTATE[HY000]')) {
            return 'Error de conexión con la base de datos. Por favor, intenta nuevamente en unos minutos.';
        }

        // Error genérico
        return 'Ocurrió un error inesperado al ' . $accion . '. Por favor, contacta al administrador del sistema.';
    }
}
