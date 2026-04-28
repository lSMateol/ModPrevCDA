<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Models\Empresa;
use App\Models\Diag;
use App\Models\Historial;
use App\Models\Persona;
use Illuminate\Http\Request;

class VehiculoEmpresaController extends Controller
{
    /**
     * Listado de vehículos vinculados a empresa.
     * Solo muestra vehículos con idemp NOT NULL.
     *
     * - Admin/Digitador: ven todos los vehículos vinculados.
     * - Empresa: solo los vinculados a su idemp.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Vehiculo::with([
            'empresa',
            'marca',
            'clase',
            'propietario',
            'conductor',
            'combustible',
        ])->whereNotNull('idemp'); // Solo vehículos con empresa

        // Filtrado por rol Empresa
        if ($user->hasRole('Empresa')) {
            $query->where('idemp', $user->idemp);
        }

        $vehiculos = $query->latest('idveh')->get();

        // Empresas para el select de filtro
        if ($user->hasRole('Empresa') && $user->empresa) {
            $empresas = collect([$user->empresa]);
        } else {
            $empresas = Empresa::orderBy('razsoem', 'ASC')->get();
        }

        return view('vehiculos.vehiculos_empresa', compact('vehiculos', 'empresas'));
    }

    /**
     * Detalle JSON de un vehículo con datos reales:
     * empresa, documentos, historial y último diagnóstico.
     *
     * Todas las estadísticas se calculan desde relaciones reales:
     * - personas_vinculadas = propietarios + conductores únicos de vehículos de la empresa
     * - total_diagnosticos = diagnósticos de vehículos de la empresa
     * - ultimo_diag_empresa = diagnóstico más reciente entre todos los vehículos de la empresa
     * - historial = movimientos de TODOS los vehículos de la empresa + diagnósticos relacionados
     */
    public function show($id)
    {
        $vehiculo = Vehiculo::with([
            'empresa',
            'marca',
            'clase',
            'propietario',
            'conductor',
            'combustible',
        ])->findOrFail($id);

        // ─── Último diagnóstico del vehículo seleccionado ───
        $ultimoDiagVehiculo = Diag::where('idveh', $vehiculo->idveh)
            ->with(['persona', 'inspector', 'ingeniero', 'vehiculo'])
            ->latest('fecdia')
            ->first();

        // ─── Estadísticas de empresa (calculadas desde relaciones reales) ───
        $empresaStats = null;
        $ultimoDiagEmpresa = null;
        $historial = collect();

        if ($vehiculo->empresa) {
            $emp = $vehiculo->empresa;

            // Todos los vehículos de esta empresa
            $vehiculosEmpresa = Vehiculo::where('idemp', $emp->idemp)->get();
            $idVehiculos = $vehiculosEmpresa->pluck('idveh')->toArray();

            // ── Personas vinculadas (sin duplicados) ──
            // Propietarios de los vehículos de la empresa
            $propIds = $vehiculosEmpresa->pluck('prop')->filter()->unique()->toArray();
            // Conductores de los vehículos de la empresa
            $condIds = $vehiculosEmpresa->pluck('cond')->filter()->unique()->toArray();
            // IDs únicos combinados (propietarios + conductores)
            $personaIds = collect(array_merge($propIds, $condIds))->unique()->values()->toArray();
            // Contar personas únicas (no contamos nomger porque es solo texto, no FK)
            $personasVinculadas = count($personaIds);

            // ── Total diagnósticos de los vehículos de la empresa ──
            $totalDiagnosticos = Diag::whereIn('idveh', $idVehiculos)->count();

            // ── Último diagnóstico de la empresa (el más reciente entre todos sus vehículos) ──
            $ultimoDiagEmpresa = Diag::whereIn('idveh', $idVehiculos)
                ->with(['persona', 'inspector', 'ingeniero', 'vehiculo'])
                ->latest('fecdia')
                ->first();

            $empresaStats = [
                'vehiculos_vinculados' => count($idVehiculos),
                'personas_vinculadas'  => $personasVinculadas,
                'total_diagnosticos'   => $totalDiagnosticos,
            ];

            // ── Reporte de Flota: Todos los diagnósticos de la empresa ──
            $reporteFlota = Diag::whereIn('idveh', $idVehiculos)
                ->with(['vehiculo.empresa', 'vehiculo.marca', 'persona', 'inspector', 'ingeniero', 'parametros.parametro.tippar', 'rechazo'])
                ->latest('fecdia')
                ->get();
        }

        // ─── Historial: movimientos DEL VEHÍCULO SELECCIONADO ───
        // Incluye: registros de tabla_ref='vehiculo' para ESTE vehículo
        //        + registros de tabla_ref='diag' para diagnósticos de ESTE vehículo
        $diagIdsDelVehiculo = Diag::where('idveh', $vehiculo->idveh)->pluck('iddia')->toArray();

        $historial = Historial::where(function ($q) use ($vehiculo, $diagIdsDelVehiculo) {
            $q->where(function ($sub) use ($vehiculo) {
                $sub->where('tabla_ref', 'vehiculo')
                    ->where('id_ref', $vehiculo->idveh);
            })->orWhere(function ($sub) use ($diagIdsDelVehiculo) {
                if (!empty($diagIdsDelVehiculo)) {
                    $sub->where('tabla_ref', 'diag')
                        ->whereIn('id_ref', $diagIdsDelVehiculo);
                }
            });
        })
            ->with('persona')
            ->latest('created_at')
            ->take(20)
            ->get();

        return response()->json([
            'vehiculo'            => $vehiculo,
            'ultimo_diag'         => $ultimoDiagVehiculo,
            'ultimo_diag_empresa' => $ultimoDiagEmpresa,
            'historial'           => $historial,
            'empresa_stats'       => $empresaStats,
            'reporte_flota'       => $reporteFlota ?? null,
        ]);
    }

    /**
     * Actualizar la empresa vinculada a un vehículo.
     * Solo Admin y Digitador (protegido por middleware de ruta).
     */
    public function updateVinculoEmpresa(Request $request, $id)
    {
        $request->validate([
            'idemp' => 'nullable|integer|exists:empresa,idemp',
        ], [
            'integer' => 'Debe ser un número entero.',
            'exists' => 'La empresa seleccionada no es válida.',
        ]);

        $vehiculo = Vehiculo::findOrFail($id);
        $oldEmpresa = $vehiculo->idemp;
        $newEmpresa = $request->input('idemp');

        $vehiculo->update(['idemp' => $newEmpresa]);

        // Registrar en historial
        $user = auth()->user();
        $oldName = $oldEmpresa ? Empresa::find($oldEmpresa)?->razsoem ?? 'ID ' . $oldEmpresa : 'Ninguna';
        $newName = $newEmpresa ? Empresa::find($newEmpresa)?->razsoem ?? 'ID ' . $newEmpresa : 'Ninguna';

        Historial::create([
            'tabla_ref'   => 'vehiculo',
            'id_ref'      => $vehiculo->idveh,
            'accion'      => 'Reasignación de Empresa',
            'descripcion' => 'Empresa cambiada de "' . $oldName . '" a "' . $newName . '" para vehículo ' . $vehiculo->placaveh,
            'idper'       => $user->idper,
            'es_sistema'  => false,
        ]);

        $vehiculo->load(['empresa', 'marca', 'clase', 'propietario', 'conductor', 'combustible']);

        return response()->json(['success' => true, 'vehiculo' => $vehiculo]);
    }

    /**
     * Permite a la Empresa (o Admin) editar su perfil corporativo sin tocar al gerente
     */
    public function updatePerfil(Request $request, $id)
    {
        $request->validate([
            'razsoem' => 'required|string|max:100',
            'nonitem' => 'required|string|max:20',
            'direm' => 'nullable|string|max:100',
            'telem' => 'nullable|string|max:15',
            'nomger' => 'required|string|max:100', // Agregado porque no tocar al gerente no significa no mostrarlo
        ], [
            'required' => 'Este campo es obligatorio.',
            'string' => 'El formato debe ser texto.',
            'max' => 'El valor no debe exceder los :max caracteres.',
        ]);

        try {
            $empresa = Empresa::findOrFail($id);
            
            // Validar permisos (Solo la misma empresa o Admin)
            $user = auth()->user();
            if ($user->hasRole('Empresa') && $user->idemp !== $empresa->idemp) {
                return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
            }

            // Actualizamos excepto el NIT si es que no quieren cambiarlo o si quisieras bloquearlo. 
            // Como pidieron editar, lo dejamos libre.
            $empresa->update([
                'razsoem' => $request->razsoem,
                'nonitem' => $request->nonitem,
                'direm' => $request->direm,
                'telem' => $request->telem,
                'nomger' => $request->nomger,
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Perfil actualizado exitosamente',
                'empresa' => $empresa
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar perfil: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Exportar reporte de flota unificado (múltiples diagnósticos en un solo documento imprimible).
     * Filtra por empresa_id y rango de fechas.
     * Empresa solo puede exportar sus propios diagnósticos.
     */
    public function exportFlota(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'empresa_id'   => 'required|integer|exists:empresa,idemp',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin'    => 'nullable|date',
            'placa'        => 'nullable|string|max:10',
        ], [
            'required' => 'Este campo es obligatorio.',
            'integer'  => 'El formato debe ser un número entero.',
            'exists'   => 'El registro seleccionado no es válido.',
            'date'     => 'Debe ingresar una fecha válida.',
        ]);

        $user = auth()->user();
        $empresaId = $request->empresa_id;

        // Seguridad: Empresa solo puede ver los suyos
        if ($user->hasRole('Empresa') && $user->idemp !== (int) $empresaId) {
            abort(403, 'No autorizado para ver esta empresa.');
        }

        $empresa = \App\Models\Empresa::findOrFail($empresaId);

        // IDs de vehículos de la empresa
        $idVehiculos = \App\Models\Vehiculo::where('idemp', $empresaId)->pluck('idveh')->toArray();

        // Query base: diagnósticos finalizados (no pendientes)
        $query = \App\Models\Diag::whereIn('idveh', $idVehiculos)
            ->whereNotNull('aprobado')
            ->with([
                'vehiculo.empresa',
                'vehiculo.marca',
                'vehiculo.clase',
                'vehiculo.combustible',
                'vehiculo.tipoMotor',
                'persona',
                'inspector',
                'ingeniero',
                'parametros.parametro.tippar',
                'fotos',
                'rechazo',
            ]);

        // Filtros de fecha
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecdia', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecdia', '<=', $request->fecha_fin);
        }

        // Filtro por placa (búsqueda parcial, case-insensitive)
        if ($request->filled('placa')) {
            $placa = strtoupper(trim($request->placa));
            $query->whereHas('vehiculo', function ($q) use ($placa) {
                $q->where('placaveh', 'LIKE', '%' . $placa . '%');
            });
        }

        $diagnosticosRaw = $query->latest('fecdia')->get();

        $diagnosticos = $diagnosticosRaw->filter(function ($d) use (&$excluidosPorFotos) {
            if ($d->fotos->count() < 2) {
                $excluidosPorFotos++;
                return false;
            }
            return true;
        });

        if ($diagnosticos->isEmpty()) {
            $totalEncontrados = $diagnosticosRaw->count();
            
            $mensaje = "No se encontraron diagnósticos exportables para <strong>{$empresa->razsoem}</strong> en el rango seleccionado.";
            $detalle = "Se requiere que cada diagnóstico finalizado tenga al menos <strong>2 fotos</strong> de evidencia fotográfica.";

            if ($totalEncontrados > 0) {
                $detalle = "Se encontraron {$totalEncontrados} registros finalizados, pero:<br>";
                if ($excluidosPorFotos > 0) $detalle .= "&bull; {$excluidosPorFotos} de ellos no cumplen con el mínimo de 2 fotos exigido.";
            } else {
                $mensaje = "No existen diagnósticos finalizados para esta empresa en las fechas seleccionadas.";
                $detalle = "Verifique el rango de fechas o asegúrese de que los diagnósticos hayan sido guardados correctamente.";
            }

            return response()->view('diagnosticos.export_error', [
                'titulo' => 'Reporte Consolidado No Disponible',
                'placa' => 'EMPRESA: ' . $empresa->razsoem,
                'iddia' => 'FLOTA',
                'mensaje' => $mensaje,
                'detalle' => $detalle,
            ], 422);
        }

        return view('diagnosticos.export_consolidado', compact('diagnosticos', 'empresa'));
    }
}
