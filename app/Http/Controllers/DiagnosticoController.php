<?php

namespace App\Http\Controllers;

use App\Models\Diag;
use App\Models\Diapar;
use App\Models\Param;
use App\Models\Vehiculo;
use App\Models\Persona;
use App\Models\Empresa;
use App\Models\Rechazo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DiagnosticoController extends Controller
{
    // Método auxiliar para obtener el prefijo según el rol
    private function getPrefix()
    {
        return Auth::user()->hasRole('Administrador') ? 'admin' : 'digitador';
    }

    public function index(Request $request)
    {
        $query = Diag::with(['vehiculo.empresa', 'persona']);

        // Filtros
        if ($request->filled('fecha')) {
            $query->whereDate('fecdia', $request->fecha);
        }
        if ($request->filled('empresa_id')) {
            $query->whereHas('vehiculo', function ($q) use ($request) {
                $q->where('idemp', $request->empresa_id);
            });
        }
        if ($request->filled('placa')) {
            $query->whereHas('vehiculo', function ($q) use ($request) {
                $q->where('placaveh', 'like', '%' . $request->placa . '%');
            });
        }
        if ($request->filled('aprobado') && in_array($request->aprobado, [0,1])) {
            $query->where('aprobado', $request->aprobado);
        }

        $diagnosticos = $query->orderBy('fecdia', 'desc')->paginate(15);

        // Métricas del día
        $hoy = now()->toDateString();
        $completados = Diag::whereDate('fecdia', $hoy)->where('aprobado', 1)->count();
        $pendientes = Diag::whereDate('fecdia', $hoy)->where('aprobado', 0)->count();
        $totalHoy = $completados + $pendientes;
        $efectividad = $totalHoy > 0 ? round(($completados / $totalHoy) * 100) : 0;

        $empresas = Empresa::all();

        return view('diagnosticos.index', compact('diagnosticos', 'completados', 'pendientes', 'efectividad', 'empresas'));
    }

    public function dataForModal()
    {
        // Eliminar el dd() que está deteniendo la ejecución
        $vehiculos = Vehiculo::with('empresa')->get();
        $inspectores = Persona::where('idpef', 4)->get(); // idpef=4 = Inspector
        $ingenieros = Persona::where('idpef', 5)->get();  // idpef=5 = Ingeniero
        return response()->json(compact('vehiculos', 'inspectores', 'ingenieros'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'idveh' => 'required|exists:vehiculo,idveh',
            'kilometraje' => 'required|integer|min:0',
            'idinsp' => 'required|exists:persona,idper',
            'iding' => 'required|exists:persona,idper',
        ]);

        $persona = Auth::user()->persona;
        if (!$persona) {
            return response()->json(['message' => 'Usuario no tiene una persona asociada.'], 403);
        }

        // Restricción: No duplicar diagnósticos pendientes para el mismo vehículo
        $existente = Diag::where('idveh', $request->idveh)->whereNull('aprobado')->first();
        if ($existente) {
            return response()->json([
                'duplicate' => true,
                'message' => "Ya existe un diagnóstico PENDIENTE para este vehículo (ID-#{$existente->iddia}).",
                'iddia' => $existente->iddia
            ], 422);
        }

        $diagnostico = Diag::create([
            'fecdia' => now(),
            'idveh' => $request->idveh,
            'aprobado' => null, // Inicia como pendiente
            'idper' => $persona->idper,
            'fecvig' => now()->addYear(),
            'kilomt' => $request->idveh ? Vehiculo::find($request->idveh)->kilomt : 0, // Fallback if needed
            'idinsp' => $request->idinsp,
            'iding' => $request->iding,
        ]);

        $prefix = $this->getPrefix();
        return redirect()->route($prefix . '.diagnosticos.edit', $diagnostico->iddia)
            ->with('success', 'Servicio preventivo agendado. Complete los parámetros del diagnóstico.');
    }

    public function edit($id)
    {
        $diagnostico = Diag::with(['vehiculo', 'parametros.parametro'])->findOrFail($id);
        $paramValues = [];
        foreach ($diagnostico->parametros as $p) {
            $paramValues[$p->parametro->nompar] = $p->valor;
        }
        $parametrosPorTipo = Param::with('tippar')->where('actpar', 1)->get()->groupBy('tippar.nomtip');

        return view('diagnosticos.form', compact('diagnostico', 'paramValues', 'parametrosPorTipo'));
    }

    public function update(Request $request, $id)
    {
        $diagnostico = Diag::findOrFail($id);
        $parametros = Param::where('actpar', 1)->get();
        $rules = [];
        foreach ($parametros as $param) {
            $regla = 'nullable';
            if ($param->control === 'number' && !is_null($param->rini) && !is_null($param->rfin)) {
                $regla .= "|numeric|between:{$param->rini},{$param->rfin}";
            } elseif ($param->control === 'radio') {
                $regla .= "|in:si,no,na,funciona,no_funciona";
            }
            $rules[$param->nompar] = $regla;
        }
        $request->validate($rules);

        $persona = Auth::user()->persona;
        DB::transaction(function () use ($request, $diagnostico, $persona, $parametros) {
            foreach ($parametros as $param) {
                $valor = $request->input($param->nompar);
                if ($valor !== null) {
                    Diapar::updateOrCreate(
                        ['iddia' => $diagnostico->iddia, 'idpar' => $param->idpar],
                        ['idper' => $persona->idper, 'valor' => $valor]
                    );
                }
            }

            // Calcular aprobación automática al guardar
            $allCumple = true;
            $diagnosticoFresh = $diagnostico->fresh('parametros.parametro');
            
            foreach($diagnosticoFresh->parametros as $p) {
                $pMeta = $p->parametro;
                $v = $p->valor;
                
                if ($pMeta->control == 'number' && ($pMeta->rini !== null && $pMeta->rfin !== null)) {
                    if ($v < $pMeta->rini || $v > $pMeta->rfin) $allCumple = false;
                } elseif ($pMeta->control == 'radio') {
                    if (in_array($v, ['no', 'no_funciona'])) $allCumple = false;
                } elseif (in_array($pMeta->nompar, ['grupo_inspeccion', 'tipo_defecto'])) {
                    if (!empty($v)) $allCumple = false;
                }
            }

            $diagnostico->update(['aprobado' => $allCumple ? 1 : 0]);
        });

        $prefix = $this->getPrefix();
        return redirect()->route($prefix . '.diagnosticos.show', $diagnostico->iddia)->with('success', 'Diagnóstico procesado correctamente.');
    }

    public function show($id)
    {
        $diagnostico = Diag::with(['vehiculo.empresa', 'persona', 'inspector', 'ingeniero', 'parametros.parametro', 'fotos'])->findOrFail($id);
        return view('diagnosticos.show', compact('diagnostico'));
    }

    public function updateAsignacion(Request $request, $id)
    {
        $diagnostico = Diag::findOrFail($id);
        
        $request->validate([
            'kilomt' => 'required|numeric',
            'idinsp' => 'required|exists:persona,idper',
            'iding' => 'required|exists:persona,idper',
        ]);

        $diagnostico->update([
            'kilomt' => $request->kilomt,
            'idinsp' => $request->idinsp,
            'iding' => $request->iding,
        ]);

        return response()->json(['success' => true, 'message' => 'Asignación de servicio actualizada correctamente.']);
    }

    public function destroy($id)
    {
        $diagnostico = Diag::findOrFail($id);
        
        // Eliminar registros relacionados para evitar errores de integridad
        $diagnostico->parametros()->delete();
        $diagnostico->rechazo()->delete();
        $diagnostico->fotos()->delete();
        $diagnostico->documentos()->delete();
        
        $diagnostico->delete();
        
        $prefix = $this->getPrefix();
        return redirect()->route($prefix . '.diagnosticos.index')->with('success', 'Diagnóstico eliminado.');
    }
    public function alertas(Request $request)
    {
        $query = Vehiculo::with(['empresa', 'marca']);
        
        // Filtro por Placa
        if ($request->filled('placa')) {
            $query->where('placaveh', 'like', '%' . $request->placa . '%');
        }

        // Filtro por Empresa
        if ($request->filled('empresa_id')) {
            $query->where('idemp', $request->empresa_id);
        }

        $vehiculos = $query->get();
        $hoy = now();
        
        // Filtros de fecha de vencimiento
        $fechaInicio = $request->filled('fecha_inicio') ? \Carbon\Carbon::parse($request->fecha_inicio)->startOfDay() : null;
        $fechaFin = $request->filled('fecha_fin') ? \Carbon\Carbon::parse($request->fecha_fin)->endOfDay() : null;

        $alertas = $vehiculos->map(function($v) use ($hoy, $fechaInicio, $fechaFin) {
            $docs = [];
            
            // SOAT
            if ($v->fecvens) {
                $fec = \Carbon\Carbon::parse($v->fecvens);
                $docs['soat'] = [
                    'fecha' => $fec,
                    'dias' => $hoy->diffInDays($fec, false),
                    'estado' => $hoy->gt($fec) ? 'vencido' : ($hoy->diffInDays($fec) < 15 ? 'por_vencer' : 'activo')
                ];
            }
            
            // Tecnomecánica
            if ($v->fecvent) {
                $fec = \Carbon\Carbon::parse($v->fecvent);
                $docs['tecno'] = [
                    'fecha' => $fec,
                    'dias' => $hoy->diffInDays($fec, false),
                    'estado' => $hoy->gt($fec) ? 'vencido' : ($hoy->diffInDays($fec) < 15 ? 'por_vencer' : 'activo')
                ];
            }

            // Filtrar por Rango de Vencimiento
            if ($fechaInicio || $fechaFin) {
                $tieneDocEnRango = collect($docs)->some(function($d) use ($fechaInicio, $fechaFin) {
                    if ($fechaInicio && $fechaFin) return $d['fecha']->between($fechaInicio, $fechaFin);
                    if ($fechaInicio) return $d['fecha']->gte($fechaInicio);
                    if ($fechaFin) return $d['fecha']->lte($fechaFin);
                    return true;
                });
                if (!$tieneDocEnRango) return null;
            }

            // Si no tiene documentos registrados, no genera alerta
            if (empty($docs)) return null;

            $alertaItem = [
                'vehiculo' => $v,
                'documentos' => $docs,
                'prioridad' => collect($docs)->pluck('estado')->contains('vencido') ? 'alta' : (collect($docs)->pluck('estado')->contains('por_vencer') ? 'media' : 'baja')
            ];

            // Solo mostrar los que tienen algo vencido o por vencer (o si está filtrado por fecha)
            if ($alertaItem['prioridad'] === 'baja' && !($fechaInicio || $fechaFin)) return null;

            return $alertaItem;
        })->filter()->sortByDesc(function($item) {
            return $item['prioridad'] === 'alta' ? 2 : ($item['prioridad'] === 'media' ? 1 : 0);
        });

        $metricas = [
            'criticos' => $alertas->where('prioridad', 'alta')->count(),
            'advertencias' => $alertas->where('prioridad', 'media')->count(),
            'al_dia' => $vehiculos->count() - $alertas->count()
        ];

        $empresas = Empresa::all();

        return view('diagnosticos.alertas', compact('alertas', 'metricas', 'empresas'));
    }

    public function rechazados(Request $request)
    {
        $query = Diag::where('aprobado', 0)->with(['vehiculo.empresa', 'inspector', 'rechazo.inspectorAnterior']);

        if ($request->filled('placa')) {
            $query->whereHas('vehiculo', function ($q) use ($request) {
                $q->where('placaveh', 'like', '%' . $request->placa . '%');
            });
        }

        if ($request->filled('inspector')) {
            $query->where('idinsp', $request->inspector);
        }

        if ($request->filled('fecha')) {
            $query->whereDate('fecdia', $request->fecha);
        }

        $rechazados = $query->orderBy('fecdia', 'desc')->paginate(15);
        $inspectores = Persona::where('idpef', 4)->get();

        return view('diagnosticos.rechazados.index', compact('rechazados', 'inspectores'));
    }

    public function editRechazo($id)
    {
        $diagnostico = Diag::with(['vehiculo.empresa', 'inspector', 'rechazo'])->findOrFail($id);
        $inspectores = Persona::where('idpef', 4)->get();
        return view('diagnosticos.rechazados.edit', compact('diagnostico', 'inspectores'));
    }

    public function updateRechazo(Request $request, $id)
    {
        $diagnostico = Diag::findOrFail($id);
        
        $request->validate([
            'motivo' => 'required|string',
            'idinsp' => 'required|exists:persona,idper',
        ]);

        DB::transaction(function () use ($request, $diagnostico) {
            $diagnostico->update([
                'idinsp' => $request->idinsp,
            ]);

            Rechazo::updateOrCreate(
                ['iddia' => $diagnostico->iddia],
                [
                    'motivo' => $request->motivo,
                    'notas' => $request->observaciones,
                    'estadorec' => 'Rechazado'
                ]
            );
        });

        $prefix = $this->getPrefix();
        return redirect()->route($prefix . '.rechazados')->with('success', 'Registro de rechazo actualizado.');
    }

    public function reasignar($id)
    {
        $diagnostico = Diag::with(['vehiculo.empresa', 'inspector', 'rechazo'])->findOrFail($id);
        $inspectores = Persona::where('idpef', 4)->get();
        $ingenieros = Persona::where('idpef', 5)->get();
        return view('diagnosticos.rechazados.reasignar', compact('diagnostico', 'inspectores', 'ingenieros'));
    }

    public function storeReasignacion(Request $request, $id)
    {
        $diagnosticoAnterior = Diag::findOrFail($id);

        $request->validate([
            'idinsp_nuevo' => 'required|exists:persona,idper',
            'iding_nuevo' => 'required|exists:persona,idper',
            'kilomt' => 'required|numeric',
        ]);

        // Restricción: No duplicar si ya hay uno pendiente
        $existente = Diag::where('idveh', $diagnosticoAnterior->idveh)->whereNull('aprobado')->first();
        if ($existente) {
            return redirect()->back()->with('error', "El vehículo ya tiene una inspección PENDIENTE (ID-#{$existente->iddia}). Debe completarla o eliminarla antes de crear una nueva.");
        }

        $nuevoDiagnostico = DB::transaction(function () use ($request, $diagnosticoAnterior) {
            // 1. Marcar el rechazo anterior como procesado/reasignado
            if ($diagnosticoAnterior->rechazo) {
                $diagnosticoAnterior->rechazo->update([
                    'idper_nvo' => $request->idinsp_nuevo,
                    'fecreasig' => now(),
                    'estadorec' => 'Reasignado'
                ]);
            }

            // 2. Crear el NUEVO diagnóstico para el mismo vehículo
            $nuevo = Diag::create([
                'fecdia' => now(), // Tomado por defecto el día en que se hace
                'idveh'  => $diagnosticoAnterior->idveh,
                'aprobado' => null, // Inicia como pendiente
                'idinsp' => $request->idinsp_nuevo,
                'iding' => $request->iding_nuevo,
                'idper' => Auth::id(), // Quien realiza la reasignación
                'kilomt' => $request->kilomt,
                'dpiddia' => $diagnosticoAnterior->iddia, // Referencia al original
            ]);

            return $nuevo;
        });

        $prefix = $this->getPrefix();
        return redirect()->route($prefix . '.diagnosticos.edit', $nuevoDiagnostico->iddia)
            ->with('success', 'Nueva inspección programada y reasignada correctamente.');
    }

    public function getFotos($id)
    {
        $diagnostico = Diag::with('fotos')->findOrFail($id);
        $fotos = $diagnostico->fotos->map(function($f) {
            return [
                'id' => $f->idfot,
                // Usar ruta de fallback para garantizar acceso si no hay symlink
                'url' => route('storage.fallback', ['path' => $f->rutafoto])
            ];
        });
        return response()->json($fotos);
    }

    public function serveFile($path)
    {
        $fullPath = storage_path('app/public/' . $path);
        if (!file_exists($fullPath)) abort(404);
        return response()->file($fullPath);
    }

    public function uploadFotos(Request $request, $id)
    {
        $diagnostico = Diag::findOrFail($id);
        
        // Organizar por Año, Mes y Día
        $fecha = \Carbon\Carbon::parse($diagnostico->fecdia);
        $year = $fecha->format('Y');
        $month = $fecha->format('m');
        $day = $fecha->format('d');
        $basePath = "fotos_diagnosticos/{$year}/{$month}/{$day}";

        // 1. Eliminar fotos que ya no están en la lista (si se enviara una lista de IDs a mantener)
        if ($request->has('ids_a_eliminar')) {
            $ids = json_decode($request->ids_a_eliminar);
            if (!empty($ids)) {
                $fotosAEliminar = $diagnostico->fotos()->whereIn('idfot', $ids)->get();
                foreach ($fotosAEliminar as $f) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($f->rutafoto);
                    $f->delete();
                }
            }
        }

        // 2. Guardar nuevas fotos
        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $file) {
                // El archivo ya viene como .webp desde el cliente
                $path = $file->store($basePath, 'public');
                $diagnostico->fotos()->create([
                    'rutafoto' => $path
                ]);
            }
        }

        return response()->json(['message' => 'Fotos actualizadas correctamente'], 201);
    }

    public function approve($id)
    {
        $diagnostico = Diag::findOrFail($id);
        $diagnostico->update(['aprobado' => 1]);
        
        $prefix = $this->getPrefix();
        return redirect()->route($prefix . '.diagnosticos.index')->with('success', 'Diagnóstico aprobado y completado correctamente.');
    }

    public function reject($id)
    {
        $diagnostico = Diag::findOrFail($id);
        $diagnostico->update(['aprobado' => 0]);
        
        $prefix = $this->getPrefix();
        return redirect()->route($prefix . '.diagnosticos.index')->with('success', 'Diagnóstico marcado como rechazado.');
    }

    public function export($id)
    {
        $diagnostico = Diag::with([
            'vehiculo.empresa', 
            'vehiculo.marca', 
            'persona', 
            'inspector', 
            'ingeniero', 
            'parametros.parametro.tippar', 
            'fotos',
            'rechazo'
        ])->findOrFail($id);

        // Restricción: Solo estados finales (Aprobado/No Aprobado)
        if (is_null($diagnostico->aprobado)) {
            $prefix = $this->getPrefix();
            return redirect()->route($prefix . '.diagnosticos.index')->with('error', 'Debe terminar el proceso (guardar parámetros) para poder exportar este diagnóstico.');
        }

        if ($diagnostico->rechazo && $diagnostico->rechazo->estadorec == 'Reasignado') {
            $prefix = $this->getPrefix();
            return redirect()->route($prefix . '.diagnosticos.index')->with('error', 'Este diagnóstico ha sido reasignado. Debe completar el nuevo proceso para exportar el formato actualizado.');
        }

        $params = $diagnostico->parametros->groupBy(function($p) {
            return $p->parametro->tippar->nomtip;
        });

        return view('diagnosticos.export', compact('diagnostico', 'params'));
    }
}