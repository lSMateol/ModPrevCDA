<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * LegacyCleanupSeeder — FASE 1: Limpieza directa sobre la base de datos LEGACY.
 *
 * ┌─────────────────────────────────────────────────────────────────────────┐
 * │ EJECUTAR ANTES de LegacyImportSeeder.                                   │
 * │ Este seeder MODIFICA DIRECTAMENTE la BD legacy (cdarastr_cdarev).       │
 * │ NO modifica la base de datos destino (modprev_local).                   │
 * └─────────────────────────────────────────────────────────────────────────┘
 */
class LegacyCleanupSeeder extends Seeder
{
    /** Solo se conservan diagnósticos a partir de esta fecha */
    protected const FECHA_CORTE = '2025-06-01';

    /** Tamaño de los chunks para operaciones en lote */
    protected const CHUNK_SIZE = 2000;

    public function run(): void
    {
        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════════╗');
        $this->command->info('║   LEGACY CLEANUP — Limpieza previa sobre BD legacy       ║');
        $this->command->info('╚══════════════════════════════════════════════════════════╝');

        $this->limpiarLegacyCompleto();

        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════════╗');
        $this->command->info('║   ✓  Limpieza legacy completada.                        ║');
        $this->command->info('╚══════════════════════════════════════════════════════════╝');
    }

    private function limpiarLegacyCompleto(): void
    {
        // ─── FASE 0: Unificación de Usuarios Duplicados ─────────────────────────────
        $this->command->info('▸ [FASE 0] Unificando registros de usuarios duplicados...');
        $this->unificarUsuariosDuplicados();
        
        // ─── FASE 0.5: Corrección de Perfiles (Inspectores, Ingenieros y Digitadores) ───────────
        $this->command->info('▸ [FASE 0.5] Corrigiendo perfiles operativos específicos...');
        $this->corregirPerfilesInspectores();
        $this->corregirPerfilesIngenieros();
        $this->corregirPerfilesDigitadores();

        // ─── FASE 1: Purga por fecha ───────────────────────────────────────
        $this->command->info('');
        $this->command->info('▸ [FASE 1] Purgando diagnósticos anteriores a ' . self::FECHA_CORTE . '...');
        $this->purgarDiagnosticosPorFecha();

        // ─── FASE 2: Eliminación de usuarios por Excel ─────────────────────
        $this->command->info('');
        $this->command->info('▸ [FASE 2] Eliminando usuarios/personas del Excel...');
        $this->eliminarPersonasPorExcel();

        // ─── FASE 3: Eliminación de empresas y dependencias ────────────────
        $this->command->info('');
        $this->command->info('▸ [FASE 3] Eliminando empresas y sus dependencias...');
        $this->eliminarEmpresasYDependencias();
    }

    private function unificarUsuariosDuplicados(): void
    {
        $legacy = DB::connection('legacy');
        
        $personas = $legacy->table('persona')->get(['idper', 'ndocper']);
        $mapaDocs = [];
        foreach ($personas as $p) {
            $ndoc = preg_replace('/[^0-9]/', '', (string)$p->ndocper);
            if ($ndoc !== '') {
                $mapaDocs[$ndoc] = $p->idper;
            }
        }

        $unificaciones = [
            ['origen' => '9999',  'destino' => '1091682308',  'rol' => 'Ingeniero'], // código genérico → Ingeniero principal
            ['origen' => '825',   'destino' => '1091682308',  'rol' => 'Ingeniero'], // Angela María (shortcode) → Ingeniero principal
            ['origen' => '3333',  'destino' => '10122437764', 'rol' => 'Inspector'], // Inspector placeholder → Inspector principal
        ];

        foreach ($unificaciones as $u) {
            $idOrigen = $mapaDocs[$u['origen']] ?? null;
            $idDestino = $mapaDocs[$u['destino']] ?? null;

            if (!$idOrigen) {
                $this->command->info("    - No se encontró {$u['rol']} origen ({$u['origen']}). No se requiere unificación.");
                continue;
            }
            if (!$idDestino) {
                $this->command->warn("    - [!] No se encontró {$u['rol']} destino ({$u['destino']}). No se puede unificar.");
                continue;
            }

            // Realizamos la unificación de referencias
            $afectadosDiagPer  = $legacy->table('diag')->where('idper', $idOrigen)->update(['idper' => $idDestino]);
            $afectadosDiagInsp = $legacy->table('diag')->where('idinsp', $idOrigen)->update(['idinsp' => $idDestino]);
            $afectadosDiagIng  = $legacy->table('diag')->where('iding', $idOrigen)->update(['iding' => $idDestino]);
            $afectadosDiapar   = $legacy->table('diapar')->where('idper', $idOrigen)->update(['idper' => $idDestino]);
            
            $afectadosProveh = $legacy->table('proveh')->where('idper', $idOrigen)->update(['idper' => $idDestino]);
            $afectadosVehProp = $legacy->table('vehiculo')->where('prop', $idOrigen)->update(['prop' => $idDestino]);
            $afectadosVehCond = $legacy->table('vehiculo')->where('cond', $idOrigen)->update(['cond' => $idDestino]);

            $totalDiag = $afectadosDiagPer + $afectadosDiagInsp + $afectadosDiagIng;

            // Asegurarnos de que el destino tenga el perfil correcto
            $idpefDestino = ($u['rol'] === 'Ingeniero') ? 4 : 5;
            $legacy->table('persona')->where('idper', $idDestino)->update(['idpef' => $idpefDestino]);

            $this->command->info("    ✓ {$u['rol']} unificado correctamente (de {$idOrigen} a {$idDestino}):");
            $this->command->info("      - Registros Diag actualizados: {$totalDiag}");
            $this->command->info("      - Registros Diapar actualizados: {$afectadosDiapar}");
            $this->command->info("      - Perfil forzado a: " . ($idpefDestino === 4 ? 'Ingeniero (4)' : 'Inspector (5)'));
        }
    }

    private function actualizarPerfilPorDocumentoNormalizado(array $docsArray, int $idpefObjetivo, string $nombrePerfil): void
    {
        $legacy = DB::connection('legacy');
        $todasPersonas = $legacy->table('persona')->get(['idper', 'ndocper']);
        $mapaDocs = array_flip($docsArray); // Para búsqueda rápida O(1)
        
        $idsAfectados = [];
        foreach ($todasPersonas as $p) {
            $ndocNorm = preg_replace('/[^0-9]/', '', (string)$p->ndocper);
            if (isset($mapaDocs[$ndocNorm])) {
                $idsAfectados[] = $p->idper;
            }
        }

        if (empty($idsAfectados)) {
            $this->command->info("    - No se encontraron registros para actualizar a {$nombrePerfil} (ID {$idpefObjetivo}).");
            return;
        }

        $afectados = $legacy->table('persona')
            ->whereIn('idper', $idsAfectados)
            ->update(['idpef' => $idpefObjetivo]);
            
        $this->command->info("    ✓ Perfiles de {$nombrePerfil} corregidos (ID {$idpefObjetivo}): {$afectados} registros.");
    }

    private function corregirPerfilesInspectores(): void
    {
        // Documentos de Inspectores (según LegacyCleanupData y manuales)
        $docs = ["1073712304", "999", "3333", "3334", "3337", "87090252", "10122437764"];
        $this->actualizarPerfilPorDocumentoNormalizado($docs, 5, 'Inspectores');
    }

    private function corregirPerfilesIngenieros(): void
    {
        // Documentos de Ingenieros (según LegacyCleanupData y principal)
        $docs = ["9999", "1091682308", "825"];
        $this->actualizarPerfilPorDocumentoNormalizado($docs, 4, 'Ingenieros');
    }

    private function corregirPerfilesDigitadores(): void
    {
        $docs = ["39670811", "1026268702"];
        $this->actualizarPerfilPorDocumentoNormalizado($docs, 2, 'Digitadores');
    }

    private function purgarDiagnosticosPorFecha(): void
    {
        $legacy = DB::connection('legacy');

        $idsViejos = $legacy->table('diag')
            ->where('fecdia', '<', self::FECHA_CORTE)
            ->pluck('iddia');

        $total = $idsViejos->count();
        if ($total === 0) {
            $this->command->info('    - No hay diagnósticos anteriores a la fecha de corte.');
            return;
        }

        $this->command->info("    - Encontrados {$total} diagnósticos a purgar...");

        $diaparEliminados = 0;
        $fotosEliminadas  = 0;
        $diagEliminados   = 0;

        foreach ($idsViejos->chunk(self::CHUNK_SIZE) as $chunk) {
            $ids = $chunk->toArray();
            $diaparEliminados += $legacy->table('diapar')->whereIn('iddia', $ids)->delete();
            $fotosEliminadas += $legacy->table('foto')->whereIn('iddia', $ids)->delete();
            $diagEliminados += $legacy->table('diag')->whereIn('iddia', $ids)->delete();
        }

        $this->command->info("    ✓ Diagnósticos eliminados : {$diagEliminados}");
    }

    private function eliminarPersonasPorExcel(): void
    {
        $legacy = DB::connection('legacy');
        $idIngenieroPrincipal = $legacy->table('persona')
            ->where('ndocper', 'like', '%1091682308%')
            ->value('idper') ?? 825;

        //1. Procesar DocsPorPerfil
        $this->command->info('  -- Evaluando lista: getDocsPorPerfil --');
        $docsPorPerfil = LegacyCleanupData::getDocsPorPerfil();
        $docsAEliminarPerfil = [];
        foreach ($docsPorPerfil as $docs) {
            foreach ($docs as $doc) {
                $docClean = preg_replace('/[^0-9]/', '', (string)$doc);
                if ($docClean !== '') $docsAEliminarPerfil[$docClean] = true;
            }
        }
        $this->ejecutarBorradoDePersonas($docsAEliminarPerfil, 'getDocsPorPerfil', $idIngenieroPrincipal);

        // 2. Procesar DocsTodos
        $this->command->info('  -- Evaluando lista: getDocsTodos --');
        $docsTodos = LegacyCleanupData::getDocsTodos();
        $docsAEliminarTodos = [];
        foreach ($docsTodos as $doc) {
            $docClean = preg_replace('/[^0-9]/', '', (string)$doc);
            if ($docClean !== '') $docsAEliminarTodos[$docClean] = true;
        }
        $this->ejecutarBorradoDePersonas($docsAEliminarTodos, 'getDocsTodos', $idIngenieroPrincipal);
    }

    private function ejecutarBorradoDePersonas(array $docsAEliminar, string $origen, $idIngenieroPrincipal): void
    {
        $legacy = DB::connection('legacy');
        
        $todasPersonas = $legacy->table('persona')->get(['idper', 'ndocper']);
        $idsPersonasAEliminar = [];
        foreach ($todasPersonas as $p) {
            $ndocNorm = preg_replace('/[^0-9]/', '', (string)$p->ndocper);
            if (isset($docsAEliminar[$ndocNorm])) {
                $idsPersonasAEliminar[] = $p->idper;
            }
        }

        // Filtrar personas para NO eliminar aquellas que tienen historial (Evitar borrar o reasignar digitadores)
        $idsSegurosParaEliminar = [];
        foreach ($idsPersonasAEliminar as $idper) {
            $hasDiag = $legacy->table('diag')->where('idper', $idper)->orWhere('idinsp', $idper)->orWhere('iding', $idper)->exists();
            $hasDiapar = $legacy->table('diapar')->where('idper', $idper)->exists();
            $hasVehicles = $legacy->table('vehiculo')->where('prop', $idper)->orWhere('cond', $idper)->exists();

            if (!$hasDiag && !$hasDiapar && !$hasVehicles) {
                $idsSegurosParaEliminar[] = $idper;
            }
        }

        if (empty($idsSegurosParaEliminar)) {
            $this->command->info("    - Las personas de la lista en {$origen} tienen registros históricos (Diagnósticos/Vehículos) y no pueden ser eliminadas para preservar la integridad.");
            $this->command->info('');
            return;
        }

        $personasEliminadas = 0;

        foreach (array_chunk($idsSegurosParaEliminar, self::CHUNK_SIZE) as $chunk) {
            // Solo eliminamos relaciones secundarias sin impacto legal
            $legacy->table('proveh')->whereIn('idper', $chunk)->delete();
            $personasEliminadas += $legacy->table('persona')->whereIn('idper', $chunk)->delete();
        }

        $this->command->info("    ✓ Personas eliminadas (Origen: {$origen}): {$personasEliminadas} (Se omitieron las que tenían historial de digitación o diagnósticos)");
        $this->command->info('');
    }

    private function eliminarEmpresasYDependencias(): void
    {
        $legacy = DB::connection('legacy');
        $empresasFiltro = LegacyCleanupData::getEmpresasFiltro();

        $empresasLegacy = $legacy->table('empresa')->get(['idemp', 'nonitem']);
        $empIdsAEliminar = [];

        foreach ($empresasLegacy as $e) {
            $idemp  = (int) $e->idemp;
            $norma  = preg_replace('/[^0-9]/', '', (string)($e->nonitem ?? ''));

            if (array_key_exists($idemp, $empresasFiltro)) {
                $patron = preg_replace('/[^0-9]/', '', (string)$empresasFiltro[$idemp]);
                if ($patron === '' || $patron === $norma) {
                    $empIdsAEliminar[] = $idemp;
                }
            }
        }

        if (empty($empIdsAEliminar)) {
            $this->command->info('    - No se encontraron empresas para eliminar.');
            return;
        }

        $idsVehiculos = [];
        foreach (array_chunk($empIdsAEliminar, self::CHUNK_SIZE) as $chunkEmp) {
            $chunkVeh = $legacy->table('vehiculo')->whereIn('idemp', $chunkEmp)->pluck('idveh')->toArray();
            $idsVehiculos = array_merge($idsVehiculos, $chunkVeh);
        }

        if (!empty($idsVehiculos)) {
            $idsDiag = [];
            foreach (array_chunk($idsVehiculos, self::CHUNK_SIZE) as $chunkVeh) {
                $chunkDiag = $legacy->table('diag')->whereIn('idveh', $chunkVeh)->pluck('iddia')->toArray();
                $idsDiag = array_merge($idsDiag, $chunkDiag);
            }

            $diagEliminados = count($idsDiag);
            $diaparEliminados = 0;
            $fotosEliminadas = 0;

            if (!empty($idsDiag)) {
                foreach (array_chunk($idsDiag, self::CHUNK_SIZE) as $chunk) {
                    $diaparEliminados += $legacy->table('diapar')->whereIn('iddia', $chunk)->delete();
                    $fotosEliminadas += $legacy->table('foto')->whereIn('iddia', $chunk)->delete();
                    $legacy->table('diag')->whereIn('iddia', $chunk)->delete();
                }
            }
            foreach (array_chunk($idsVehiculos, self::CHUNK_SIZE) as $chunk) {
                $legacy->table('proveh')->whereIn('idveh', $chunk)->delete();
                $legacy->table('mantenimiento')->whereIn('idveh', $chunk)->delete();
                $legacy->table('vehiculo')->whereIn('idveh', $chunk)->delete();
            }
        }

        $empresasEliminadas = 0;
        foreach (array_chunk($empIdsAEliminar, self::CHUNK_SIZE) as $chunkEmp) {
            $empresasEliminadas += $legacy->table('empresa')->whereIn('idemp', $chunkEmp)->delete();
        }
        
        $this->command->info("    ✓ Empresas eliminadas (Origen: getEmpresasFiltro): {$empresasEliminadas}");
        $this->command->warn("      - Vehículos eliminados: " . count($idsVehiculos ?? []));
        $this->command->warn("      - [!] Diagnósticos eliminados en cascada: " . ($diagEliminados ?? 0));
        $this->command->warn("      - [!] Parámetros eliminados (diapar): " . ($diaparEliminados ?? 0));
    }
}
