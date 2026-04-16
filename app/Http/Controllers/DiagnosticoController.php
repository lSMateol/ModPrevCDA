<?php

namespace App\Http\Controllers;

use App\Models\Diag;
use App\Models\Diapar;
use App\Models\Param;
use App\Models\Vehiculo;
use App\Models\Persona;
use App\Models\Empresa;
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
            return redirect()->back()->withErrors(['error' => 'Usuario no tiene una persona asociada.']);
        }

        $diagnostico = Diag::create([
            'fecdia' => now(),
            'idveh' => $request->idveh,
            'aprobado' => 0,
            'idper' => $persona->idper,
            'fecvig' => now()->addYear(),
            'kilomt' => $request->kilometraje,
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
        });

        $prefix = $this->getPrefix();
        return redirect()->route($prefix . '.diagnosticos.show', $diagnostico->iddia)->with('success', 'Parámetros guardados correctamente.');
    }

    public function show($id)
    {
        $diagnostico = Diag::with(['vehiculo.empresa', 'persona', 'inspector', 'ingeniero', 'parametros.parametro'])->findOrFail($id);
        return view('diagnosticos.show', compact('diagnostico'));
    }

    public function destroy($id)
    {
        $diagnostico = Diag::findOrFail($id);
        $diagnostico->parametros()->delete();
        $diagnostico->delete();
        
        $prefix = $this->getPrefix();
        return redirect()->route($prefix . '.diagnosticos.index')->with('success', 'Diagnóstico eliminado.');
    }
    public function alertas()
    {
        $vehiculos = Vehiculo::with('empresa')->get();
        $hoy = now();
        
        $alertas = $vehiculos->map(function($v) use ($hoy) {
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

            return [
                'vehiculo' => $v,
                'documentos' => $docs,
                'prioridad' => collect($docs)->pluck('estado')->contains('vencido') ? 'alta' : (collect($docs)->pluck('estado')->contains('por_vencer') ? 'media' : 'baja')
            ];
        })->filter(function($item) {
            return $item['prioridad'] !== 'baja'; // Solo mostrar los que tienen algo vencido o por vencer
        })->sortByDesc(function($item) {
            return $item['prioridad'] === 'alta' ? 2 : ($item['prioridad'] === 'media' ? 1 : 0);
        });

        $metricas = [
            'criticos' => $alertas->where('prioridad', 'alta')->count(),
            'advertencias' => $alertas->where('prioridad', 'media')->count(),
            'al_dia' => $vehiculos->count() - $alertas->count()
        ];

        return view('diagnosticos.alertas', compact('alertas', 'metricas'));
    }
}