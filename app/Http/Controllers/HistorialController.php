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
                $query->where('aprobado', 0);
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
                        'rechazo'
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

        // REGLA ESTRICTA: SOLO Aprobados y No Aprobados (sin Pendientes)
        $query->whereNotNull('aprobado');

        // Filtro adicional por estado
        if ($request->filled('estado')) {
            $estado = $request->estado;
            if ($estado === 'aprobado') {
                $query->where('aprobado', 1);
            } elseif ($estado === 'no_aprobado') {
                $query->where('aprobado', 0);
            } elseif ($estado === 'pendiente') {
                $query->whereRaw('1 = 0');
            }
        }

        $diagnosticos = $query->limit(300)->get();

        // Cargar parámetros fallidos de forma eficiente solo para los rechazados
        $rechazados = $diagnosticos->where('aprobado', 0);
        if ($rechazados->count() > 0) {
            $rechazados->load(['parametros.parametro.tippar']);
            foreach ($rechazados as $diag) {
                $fallas = [];
                if ($diag->parametros) {
                    foreach ($diag->parametros as $p) {
                        $pMeta = $p->parametro;
                        if (!$pMeta) continue;

                        $v = $p->valor;
                        $nomTip = strtoupper(optional($pMeta->tippar)->nomtip ?? '');
                        $failed = false;

                        if ($pMeta->nompar == 'desc_inspeccion') {
                            $data = @json_decode($v, true);
                            $lista = is_array($data) ? ($data['list'] ?? $data) : [];
                            foreach ($lista as $def) {
                                if (($def['tipo'] ?? '') == 'Tipo A' || ($def['tipo'] ?? '') == 'Tipo B') {
                                    $isTipoA = ($def['tipo'] ?? '') == 'Tipo A';
                                    
                                    $grupo = strtoupper($def['grupo'] ?? 'DEFECTO');
                                    $obs = strtoupper($def['obs'] ?? ($def['desc'] ?? ($def['defecto'] ?? '')));
                                    
                                    $desc = $obs ? $grupo . ' - ' . $obs : $grupo . ' VISUAL (' . ($def['tipo'] ?? '') . ')';
                                    
                                    // Check if this desc is already added to avoid duplicates
                                    $exists = false;
                                    foreach ($fallas as $f) {
                                        if ($f['desc'] === $desc) { $exists = true; break; }
                                    }
                                    if (!$exists) {
                                        $fallas[] = ['desc' => $desc, 'is_tipo_a' => $isTipoA];
                                    }
                                }
                            }
                        } else {
                            if ($pMeta->control == 'number' && ($pMeta->rini !== null && $pMeta->rfin !== null)) {
                                if (is_numeric($v) && ($v < $pMeta->rini || $v > $pMeta->rfin)) $failed = true;
                            } elseif ($pMeta->control == 'radio') {
                                if (str_contains($nomTip, 'DEFECTOS') && !str_contains($nomTip, 'VISUAL')) {
                                    if (str_contains(strtolower($pMeta->nompar), 'criterios')) {
                                        if (!in_array(strtolower($v), ['si', 'na'])) $failed = true;
                                    } else {
                                        if (strtolower($v) == 'si') $failed = true;
                                    }
                                } else {
                                    if (in_array(strtolower($v), ['no', 'no_funciona'])) $failed = true;
                                }
                            }

                            if ($failed) {
                                $desc = strtoupper(str_replace('_', ' ', $pMeta->nompar));
                                $exists = false;
                                foreach ($fallas as $f) {
                                    if ($f['desc'] === $desc) { $exists = true; break; }
                                }
                                if (!$exists) {
                                    $fallas[] = ['desc' => $desc, 'is_tipo_a' => false];
                                }
                            }
                        }
                    }
                }
                $diag->fallas_calculadas = $fallas;
                $diag->unsetRelation('parametros'); // Liberar memoria
            }
        }

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
