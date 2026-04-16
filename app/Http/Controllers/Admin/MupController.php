<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Models\Perfil;
use App\Models\Valor;
use App\Models\Pagina;
use App\Models\User;
use App\Models\Empresa;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class MupController extends Controller
{
    /**
     * Display the Conductores view.
     */
    public function conductores()
    {
        // 1. Asegurar que el perfil 'Conductor' existe (ID 6 o similar si no está)
        // En este sistema, según PerfilSeeder, llegan hasta el 5. Usaremos el 6 para Conductor si no existe.
        $perfilConductor = Perfil::firstOrCreate(
            ['nompef' => 'Conductor'],
            ['idpef' => 6, 'pagpri' => null]
        );

        // 2. Obtener listado de conductores
        $conductores = Persona::where('idpef', $perfilConductor->idpef)
            ->orderBy('idper', 'desc')
            ->get();

        // 3. Obtener datos para los combos (Tipos de documento y Categorías)
        $tiposDoc = Valor::where('iddom', 4)->where('actval', 1)->get();
        $categorias = Valor::where('iddom', 5)->where('actval', 1)->get();

        return view('admin.mup.conductores', compact('conductores', 'tiposDoc', 'categorias'));
    }

    /**
     * Store a new conductor.
     */
    public function storeConductor(Request $request)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:100',
            'tdocper' => 'required|exists:valor,idval',
            'ndocper' => 'required|numeric|unique:persona,ndocper',
            'emaper' => 'required|email|max:60',
            'telper' => 'nullable|string|max:10',
            'catcon' => 'required|exists:valor,idval',
            'nliccon' => 'required|string|max:20',
            'fvencon' => 'required|date',
            'actper' => 'required|in:0,1',
        ]);

        try {
            DB::beginTransaction();

            // Split name into nomper and apeper (Best practice as requested)
            $parts = explode(' ', $request->nombre_completo, 2);
            $nomper = $parts[0];
            $apeper = $parts[1] ?? '';

            $perfilConductor = Perfil::where('nompef', 'Conductor')->first();

            $persona = Persona::create([
                'nomper' => $nomper,
                'apeper' => $apeper,
                'tdocper' => $request->tdocper,
                'ndocper' => $request->ndocper,
                'emaper' => $request->emaper,
                'telper' => $request->telper ?? '',
                'catcon' => $request->catcon,
                'nliccon' => $request->nliccon,
                'fvencon' => $request->fvencon,
                'actper' => $request->actper,
                'idpef' => $perfilConductor->idpef,
                'codubi' => 1, // Default location for now as it's required in DB
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Conductor registrado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error registrando conductor: " . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al registrar el conductor.')->withInput();
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

            // 3. Procesar Permisos
            if ($request->has('permisos')) {
                foreach ($request->permisos as $modulo => $actions) {
                    // $actions es un array como ['ver' => 'on', 'crear' => 'on']
                    foreach ($actions as $action => $status) {
                        if ($status === 'on') {
                            $permissionName = strtolower(str_replace(' ', '_', $modulo)) . "." . $action;
                            $permission = Permission::firstOrCreate(['name' => $permissionName]);
                            $role->givePermissionTo($permission);
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
     * Display the Users (MUP) main dashbaord.
     */
    public function usuarios()
    {
        // 1. Listado de usuarios con su perfil y persona vinculada
        $usuarios = User::with('persona.perfil', 'empresa')
            ->orderBy('id', 'desc')
            ->get();

        // 2. Perfiles con conteo de personas (para las tarjetas de insight)
        $perfiles = Perfil::withCount('personas')->get();

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
            'telper' => 'nullable|string',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
            'idpef' => 'required|exists:perfil,idpef',
            'idemp' => 'nullable|exists:empresa,idemp',
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
            $user->assignRole($perfil->nompef);

            DB::commit();

            return redirect()->back()->with('success', 'Usuario creado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error registrando usuario: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error al registrar usuario: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the Propietarios view.
     */
    public function propietarios()
    {
        // 1. Asegurar que el perfil 'Propietario' existe
        $perfilPropietario = Perfil::firstOrCreate(
            ['nompef' => 'Propietario'],
            ['idpef' => 7, 'pagpri' => null] // ID 7 siguiendo el orden (5 del seeder, 6 conductores)
        );

        // 2. Obtener listado de propietarios
        $propietarios = Persona::where('idpef', $perfilPropietario->idpef)
            ->orderBy('idper', 'desc')
            ->get();

        // 3. Obtener datos para combos
        $tiposDoc = Valor::where('iddom', 4)->where('actval', 1)->get();
        $categorias = Valor::where('iddom', 5)->where('actval', 1)->get();

        return view('admin.mup.propietarios', compact('propietarios', 'tiposDoc', 'categorias'));
    }

    /**
     * Store a new propietario.
     */
    public function storePropietario(Request $request)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:100',
            'tdocper' => 'required|exists:valor,idval',
            'ndocper' => 'required|numeric|unique:persona,ndocper',
            'emaper' => 'required|email|max:60',
            'telper' => 'nullable|string|max:10',
            'catcon' => 'required|exists:valor,idval',
            'nliccon' => 'required|string|max:20',
            'fvencon' => 'required|date',
            'actper' => 'required|in:0,1',
            'dirper' => 'nullable|string|max:100',
            'ciuper' => 'nullable|string|max:50',
        ]);

        try {
            DB::beginTransaction();

            $parts = explode(' ', $request->nombre_completo, 2);
            $nomper = $parts[0];
            $apeper = $parts[1] ?? '';

            $perfilPropietario = Perfil::where('nompef', 'Propietario')->first();

            $persona = Persona::create([
                'nomper' => $nomper,
                'apeper' => $apeper,
                'tdocper' => $request->tdocper,
                'ndocper' => $request->ndocper,
                'emaper' => $request->emaper,
                'telper' => $request->telper ?? '',
                'catcon' => $request->catcon,
                'nliccon' => $request->nliccon,
                'fvencon' => $request->fvencon,
                'actper' => $request->actper,
                'idpef' => $perfilPropietario->idpef,
                'codubi' => 1,
                // Si tienes columnas dirper o ciuper en tu tabla persona, descomenta abajo:
                // 'dirper' => $request->dirper,
                // 'ciuper' => $request->ciuper,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Propietario registrado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error registrando propietario: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error al registrar propietario: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the Empresas view.
     */
    public function empresas()
    {
        // 1. Asegurar perfil 'Empresa' (ID 8)
        Perfil::firstOrCreate(
            ['nompef' => 'Empresa'],
            ['idpef' => 8, 'pagpri' => null]
        );

        // 2. Obtener listado
        $empresas = Empresa::with('perfil')->orderBy('idemp', 'desc')->get();

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
            'ciudeem' => 'nullable|string|max:50', // mapeado a direm/telem según tabla real si aplica
            'nomger' => 'required|string|max:100',
            'telem' => 'required|string|max:15',
            'emaem' => 'required|email|max:60',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
        ]);

        try {
            DB::beginTransaction();

            $perfilEmpresa = Perfil::where('nompef', 'Empresa')->first();

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
                'passemp' => $request->password, // guardamos texto plano según el modelo Empresa o hash? User usa Hash.
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
            $user->assignRole($perfilEmpresa->nompef);

            DB::commit();

            return redirect()->back()->with('success', 'Empresa y usuario de acceso creados exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error registrando empresa: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error al registrar empresa: ' . $e->getMessage())->withInput();
        }
    }
}
