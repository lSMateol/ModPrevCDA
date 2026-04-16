<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Models\Perfil;
use App\Models\Valor;
use App\Models\Pagina;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
}
