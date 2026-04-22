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
        
        $query = Diag::with(['vehiculo.empresa', 'vehiculo.marca', 'rechazo', 'parametros.parametro'])
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

        // Filtro por fecha (puede ser rango en el request)
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecdia', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecdia', '<=', $request->fecha_fin);
        }

        // REGLA ESTRICTA PARA EL REPORTE: SOLO Aprobados y No Aprobados.
        // Se excluyen completamente Pendientes y Reasignados.
        $query->where(function($q) {
            $q->where('aprobado', 1) // Aprobados
              ->orWhere(function($sub) { // No Aprobados (Rechazados definitivos)
                  $sub->where('aprobado', 0)->whereDoesntHave('rechazo', function($r) {
                      $r->where('estadorec', 'Reasignado');
                  });
              });
        });

        // Filtro adicional por estado si se especificó en la vista
        if ($request->filled('estado')) {
            $estado = $request->estado;
            if ($estado === 'aprobado') {
                $query->where('aprobado', 1);
            } elseif ($estado === 'no_aprobado') {
                $query->where('aprobado', 0);
            } elseif ($estado === 'reasignado' || $estado === 'pendiente') {
                // Si el usuario filtró por algo que no se permite en el reporte,
                // forzamos a que no devuelva nada
                $query->whereRaw('1 = 0');
            }
        }

        $diagnosticos = $query->get();

        // Determinar empresa
        $empresaId = null;
        if ($user->hasRole('Empresa')) {
            $empresaId = $user->idemp;
        } elseif ($request->filled('empresa')) {
            $empresaId = $request->empresa;
        }
        
        $empresa = null;
        $totalVehiculos = 0;
        if ($empresaId) {
            $empresa = \App\Models\Empresa::find($empresaId);
            $totalVehiculos = \App\Models\Vehiculo::where('idemp', $empresaId)->count();
        } else {
            // Si no hay empresa específica filtrada, contamos los vehículos únicos de estos diagnósticos
            $totalVehiculos = $diagnosticos->pluck('idveh')->unique()->count();
        }

        return view('vehiculos.export_historial', compact('diagnosticos', 'empresa', 'totalVehiculos', 'request'));
    }
}
