<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Diag;

class HistorialController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = Diag::with(['vehiculo', 'vehiculo.marca', 'rechazo'])
                    ->orderBy('fecdia', 'desc');

        // RBAC: Si es Empresa, solo ve historiales de sus vehículos
        if ($user->hasRole('Empresa')) {
            $idemp = $user->idemp;
            $query->whereHas('vehiculo', function($q) use ($idemp) {
                $q->where('idemp', $idemp);
            });
        }

        // Filtros (si vienen por request, opcional backend)
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->whereHas('vehiculo', function($q) use ($searchTerm) {
                $q->where('placaveh', 'like', "%{$searchTerm}%")
                  ->orWhere('linveh', 'like', "%{$searchTerm}%"); // Ojo con linveh (es idmar)
            });
        }

        if ($request->filled('estado')) {
            $estado = $request->estado;
            if ($estado === 'aprobado') {
                $query->where('aprobado', 1);
            } elseif ($estado === 'no_aprobado') {
                $query->where('aprobado', 0)->whereDoesntHave('rechazo', function($q){
                    $q->where('estadorec', 'Reasignado');
                });
            } elseif ($estado === 'reasignado') {
                $query->where('aprobado', 0)->whereHas('rechazo', function($q) {
                    $q->where('estadorec', 'Reasignado');
                });
            } elseif ($estado === 'pendiente') {
                $query->whereNull('aprobado');
            }
        }

        if ($request->filled('fecha')) {
            $query->whereDate('fecdia', $request->fecha);
        }

        $diagnosticos = $query->get();

        $empresasFiltro = \App\Models\Empresa::orderBy('razsoem', 'ASC')->get();
        if ($user->hasRole('Empresa') && $user->empresa) {
            $empresasFiltro = collect([$user->empresa]);
        }

        return view('vehiculos.historial', compact('diagnosticos', 'empresasFiltro'));
    }

    public function exportarReporte(Request $request)
    {
        $user = auth()->user();
        
        $query = Diag::with([
                        'vehiculo.empresa',
                        'vehiculo.marca',
                        'rechazo',
                        // Solo cargamos parámetros con valor fallido para evitar memory crash
                        'parametros' => function($q) {
                            $q->whereIn('valor', ['no', 'no_funciona'])
                              ->with(['parametro' => function($pq) {
                                  $pq->select(['idpar', 'nompar']);
                              }])
                              ->select(['iddiapar', 'iddia', 'idpar', 'valor']);
                        }
                    ])
                    ->orderBy('fecdia', 'desc');

        // RBAC: Si es Empresa, solo ve historiales de sus vehículos
        if ($user->hasRole('Empresa')) {
            $idemp = $user->idemp;
            $query->whereHas('vehiculo', function($q) use ($idemp) {
                $q->where('idemp', $idemp);
            });
        } elseif ($request->filled('empresa')) {
            $idemp = $request->empresa;
            $query->whereHas('vehiculo', function($q) use ($idemp) {
                $q->where('idemp', $idemp);
            });
        }

        // Filtro por fecha
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecdia', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecdia', '<=', $request->fecha_fin);
        }

        // REGLA ESTRICTA: SOLO Aprobados y No Aprobados (sin Pendientes ni Reasignados)
        $query->where(function($q) {
            $q->where('aprobado', 1)
              ->orWhere(function($sub) {
                  $sub->where('aprobado', 0)->whereDoesntHave('rechazo', function($r) {
                      $r->where('estadorec', 'Reasignado');
                  });
              });
        });

        // Filtro adicional por estado
        if ($request->filled('estado')) {
            $estado = $request->estado;
            if ($estado === 'aprobado') {
                $query->where('aprobado', 1);
            } elseif ($estado === 'no_aprobado') {
                $query->where('aprobado', 0);
            } elseif ($estado === 'reasignado' || $estado === 'pendiente') {
                $query->whereRaw('1 = 0');
            }
        }

        $diagnosticos = $query->limit(300)->get();

        // Vehículos únicos dentro del resultado filtrado (siempre refleja el filtro activo)
        $totalVehiculos = $diagnosticos->pluck('idveh')->unique()->count();

        // Determinar empresa para mostrar datos del destinatario
        $empresaId = null;
        if ($user->hasRole('Empresa')) {
            $empresaId = $user->idemp;
        } elseif ($request->filled('empresa')) {
            $empresaId = $request->empresa;
        }
        
        $empresa = $empresaId ? \App\Models\Empresa::find($empresaId) : null;

        // Periodo evaluado
        $periodoInicio = $request->filled('fecha_inicio')
            ? \Carbon\Carbon::parse($request->fecha_inicio)->format('d/m/Y')
            : null;
        $periodoFin = $request->filled('fecha_fin')
            ? \Carbon\Carbon::parse($request->fecha_fin)->format('d/m/Y')
            : null;

        return view('vehiculos.export_historial', compact(
            'diagnosticos', 'empresa', 'totalVehiculos', 'request', 'periodoInicio', 'periodoFin'
        ));
    }
}
