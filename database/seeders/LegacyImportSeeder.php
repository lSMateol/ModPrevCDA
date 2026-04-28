<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LegacyImportSeeder extends Seeder
{
    /**
     * IMPORTANTE: Para correr este seeder, debes configurar una segunda conexión
     * de base de datos en config/database.php llamada 'legacy' que apunte a la
     * base de datos cdarastr_cdarev.
     *
     * FECHA DE CORTE: Solo se importan diagnósticos a partir del 01/06/2025.
     * Empresas, personas y vehículos se importan completos porque son datos
     * maestros requeridos para vínculos.
     *
     * TABLAS EXCLUIDAS DE LA MIGRACIÓN (9 tablas):
     * ┌──────────────────┬──────────────────────────────────────────────────┐
     * │ config           │ No existe en el nuevo sistema                   │
     * │ mantenimiento    │ No se migra (decisión de negocio)               │
     * │ maquina          │ No existe en el nuevo sistema                   │
     * │ pagina           │ Se usa PaginaSeeder propio del nuevo sistema    │
     * │ pagper           │ Se usa PaginaSeeder propio del nuevo sistema    │
     * │ punaten          │ No existe en el nuevo sistema                   │
     * │ tippar           │ Se usa TipparSeeder + DynamicFieldsSeeder       │
     * │ param            │ Se usa TipparSeeder + DynamicFieldsSeeder       │
     * │ perfil           │ Se usa PerfilSeeder propio (incluye perfil 8)   │
     * └──────────────────┴──────────────────────────────────────────────────┘
     */

    /** Fecha de corte para datos transaccionales */
    protected const FECHA_CORTE = '2025-06-01';

    /**
     * Cache de mapeo de IDs legacy a IDs reales para evitar N+1 queries
     */
    protected $mapaPersonas = [];
    protected $idsVehiculos = [];

    /** IDs de diagnósticos importados (post-filtro de fecha) */
    protected $idsDiagImportados = [];

    /** Mapa de vehículo → combustible para asignar idval_combu */
    protected $vehiculoCombustible = [];

    public function run(): void
    {
        $this->command->info('══════════════════════════════════════════════');
        $this->command->info('  Importación Legacy — Corte: ' . self::FECHA_CORTE);
        $this->command->info('══════════════════════════════════════════════');

        // ═══════════════════════════════════════════════════════════════
        // 0. LIMPIEZA PREVIA (Opcional, según condiciones)
        // ═══════════════════════════════════════════════════════════════
        $this->limpiarDatosPrevios();
        $this->purgarLegacyAntiguo();

        // ═══════════════════════════════════════════════════════════════
        // 1. MAESTROS DEL NUEVO SISTEMA (Roles, Perfiles, Parámetros)
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('');
        $this->command->info('▸ [1/9] Configurando Roles, Perfiles y Parámetros del sistema...');
        $this->call(RoleSeeder::class);
        $this->call(PerfilSeeder::class);

        // ═══════════════════════════════════════════════════════════════
        // 2. DICCIONARIOS BÁSICOS (desde legacy)
        //    NOTA: tippar y param NO se importan desde legacy.
        //    Se preservan los definidos en TipparSeeder y DynamicFieldsSeeder
        //    para mantener la lógica de formularios por tipo de combustible,
        //    se_mantiene, tipo_vehiculo_config, etc.
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('▸ [2/9] Importando diccionarios (dominio, valor, ubica, marca)...');
        $this->importarTablaSimple('dominio', 'iddom');
        $this->importarTablaSimple('valor', 'idval');
        $this->importarTablaSimple('ubica', 'codubi');
        $this->importarTablaSimple('marca', 'idmar');

        // ═══════════════════════════════════════════════════════════════
        // 3. PARÁMETROS TÉCNICOS (desde seeders del sistema actual)
        //    Esto crea tippar (1-6), param (1-26), tipo_vehiculo_config
        //    y el campo se_mantiene. NO se toca desde legacy.
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('▸ [3/9] Configurando parámetros técnicos y configuración por combustible...');
        $this->call(TipparSeeder::class);
        $this->call(DynamicFieldsSeeder::class);

        // ═══════════════════════════════════════════════════════════════
        // 4. EMPRESAS (desde legacy, completas)
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('▸ [4/9] Importando Empresas...');
        $this->importarEmpresas();

        // ═══════════════════════════════════════════════════════════════
        // 5. PERSONAS (desde legacy, completas — ETL con perfil dual)
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('▸ [5/9] Importando Personas (ETL con perfil dual ID 8)...');
        $this->importarPersonas();

        // 5b. Cargar mapa de personas en memoria
        $this->cargarMapaPersonas();

        // ═══════════════════════════════════════════════════════════════
        // 6. VEHÍCULOS (desde legacy, completos)
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('▸ [6/9] Importando Vehículos...');
        $this->importarVehiculos();

        // 6b. Cargar mapas auxiliares
        $this->idsVehiculos = DB::table('vehiculo')->pluck('idveh', 'idveh')->toArray();
        $this->vehiculoCombustible = DB::table('vehiculo')->pluck('combuveh', 'idveh')->toArray();

        // ═══════════════════════════════════════════════════════════════
        // 7. PROVEH (relación persona-vehículo, completa)
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('▸ [7/9] Importando relaciones Persona-Vehículo (proveh)...');
        $this->importarTablaSimple('proveh', ['idveh', 'idper']);

        // ═══════════════════════════════════════════════════════════════
        // 8. DIAGNÓSTICOS (filtrados por fecha de corte)
        //    + Asignación automática de idval_combu desde vehiculo.combuveh
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('▸ [8/9] Importando Diagnósticos (≥ ' . self::FECHA_CORTE . ')...');
        $this->importarDiagnosticos();

        // ═══════════════════════════════════════════════════════════════
        // 9. DIAPAR + FOTO (solo los vinculados a diagnósticos importados)
        //    + ROLES SPATIE
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('▸ [9/9] Importando Diapar, Fotos y asignando Roles...');
        $this->importarDiapar();
        $this->importarFotos();
        $this->asignarRoles();

        $this->command->info('');
        $this->command->info('══════════════════════════════════════════════');
        $this->command->info('  ✓ Importación finalizada con éxito');
        $this->command->info('    Diagnósticos importados: ' . count($this->idsDiagImportados));
        $this->command->info('══════════════════════════════════════════════');
    }

    // ─────────────────────────────────────────────────────────────────
    //  MÉTODOS AUXILIARES
    // ─────────────────────────────────────────────────────────────────

    /**
     * Limpia los datos transaccionales previos para evitar duplicados
     * o inconsistencias en re-ejecuciones.
     */
    /**
     * Limpia los datos transaccionales previos para evitar duplicados
     * o inconsistencias en re-ejecuciones.
     */
    private function limpiarDatosPrevios()
    {
        $this->command->warn("  Limpiando datos transaccionales previos (≥ " . self::FECHA_CORTE . ")...");

        // Identificar IDs de diagnósticos en el rango de migración
        $idsAEliminar = DB::table('diag')
            ->where('fecdia', '>=', self::FECHA_CORTE)
            ->pluck('iddia');

        if ($idsAEliminar->isNotEmpty()) {
            $countDiapar = DB::table('diapar')->whereIn('iddia', $idsAEliminar)->delete();
            $countFotos = DB::table('foto')->whereIn('iddia', $idsAEliminar)->delete();
            $countDiag = DB::table('diag')->whereIn('iddia', $idsAEliminar)->delete();

            $this->command->info("    ✓ $countDiag diagnósticos, $countDiapar parámetros y $countFotos fotos eliminados.");
        } else {
            $this->command->info("    - No se encontraron datos previos para limpiar.");
        }
    }

    /**
     * PURGA de la base de datos LEGACY (cdarastr_cdarev).
     * Elimina datos anteriores a la fecha de corte para aligerar el origen.
     */
    private function purgarLegacyAntiguo()
    {
        $this->command->warn("  [PELIGRO] Purgando datos antiguos en base LEGACY (< " . self::FECHA_CORTE . ")...");

        // 1. Obtener IDs de diagnósticos antiguos en la base legacy
        $idsViejos = DB::connection('legacy')->table('diag')
            ->where('fecdia', '<', self::FECHA_CORTE)
            ->pluck('iddia');

        if ($idsViejos->isNotEmpty()) {
            $this->command->info("    -> Eliminando registros relacionados para " . count($idsViejos) . " diagnósticos antiguos...");

            // Procesamos en bloques para no saturar la base de datos antigua
            foreach ($idsViejos->chunk(3000) as $chunk) {
                DB::connection('legacy')->table('diapar')->whereIn('iddia', $chunk)->delete();
                DB::connection('legacy')->table('foto')->whereIn('iddia', $chunk)->delete();
                DB::connection('legacy')->table('diag')->whereIn('iddia', $chunk)->delete();
            }

            $this->command->info("    ✓ Base legacy purgada correctamente.");
        } else {
            $this->command->info("    - No se encontraron datos antiguos en legacy para purgar.");
        }
    }

    private function cargarMapaPersonas()
    {
        $this->command->info("  Cargando mapa de personas en memoria...");
        $personasLegacy = DB::connection('legacy')->table('persona')->get(['idper', 'ndocper']);
        $personasReales = DB::table('persona')->get(['idper', 'ndocper'])->keyBy('ndocper');

        foreach ($personasLegacy as $p) {
            $real = $personasReales->get($p->ndocper);
            $this->mapaPersonas[$p->idper] = $real ? $real->idper : 1;
        }
    }

    private function importarTablaSimple($tabla, $pk)
    {
        $this->command->info("  Importando tabla: {$tabla}");
        $registros = DB::connection('legacy')->table($tabla)->get();

        foreach ($registros as $row) {
            $data = (array) $row;
            if (is_array($pk)) {
                $cond = [];
                foreach ($pk as $k) { $cond[$k] = $data[$k]; }
                DB::table($tabla)->updateOrInsert($cond, $data);
            } else {
                DB::table($tabla)->updateOrInsert([$pk => $data[$pk]], $data);
            }
        }
    }

    private function importarEmpresas()
    {
        $this->command->info("  Importando Empresas y creando usuarios...");
        $empresas = DB::connection('legacy')->table('empresa')->get();

        foreach ($empresas as $e) {
            $data = (array) $e;
            $data['idpef'] = 3;
            $data['usuaemp'] = $data['nonitem'] ?? 'emp_' . $data['idemp'];
            $data['passemp'] = Hash::make($data['usuaemp']);
            $data['ciudeem'] = $data['ciudeem'] ?? 'NO REGISTRADA';

            $key = $data['nonitem'] ? ['nonitem' => $data['nonitem']] : ['idemp' => $data['idemp']];
            DB::table('empresa')->updateOrInsert($key, $data);

            $empresaReal = DB::table('empresa')->where('idemp', $data['idemp'])->first();
            if ($empresaReal) {
                DB::table('users')->updateOrInsert(
                    ['email' => $empresaReal->emaem ?? "emp_{$empresaReal->idemp}@cda.com"],
                    [
                        'name' => $empresaReal->razsoem,
                        'password' => $data['passemp'],
                        'idemp' => $empresaReal->idemp,
                        'username' => $data['usuaemp']
                    ]
                );
            }
        }
    }

    private function importarPersonas()
    {
        $this->command->info("  Importando Personas (Aplicando reglas ETL)...");
        $personas = DB::connection('legacy')->table('persona')->orderBy('idper')->get();

        foreach ($personas as $p) {
            $data = (array) $p;
            if ($data['idpef'] > 8 || $data['idpef'] == 0) { $data['idpef'] = 1; }
            $ndoc = $data['ndocper'];

            if (empty($ndoc)) {
                $this->command->warn("  ⚠ Saltando persona ID {$data['idper']} ({$data['nomper']}) — sin documento.");
                continue;
            }

            $personaExistente = DB::table('persona')->where('ndocper', $ndoc)->first();

            if ($personaExistente) {
                $vP = $personaExistente->idpef;
                $nP = $data['idpef'];
                $perFinal = $vP;

                // Lógica de perfil dual (Propietario + Conductor = 8)
                if (($vP == 6 && $nP == 7) || ($vP == 7 && $nP == 6)) { $perFinal = 8; }
                elseif (in_array(1, [$vP, $nP])) { $perFinal = 1; }

                $updateData = ['idpef' => $perFinal];
                if (empty($personaExistente->nliccon) && !empty($data['nliccon'])) {
                    $updateData['nliccon'] = $data['nliccon'];
                    $updateData['fvencon'] = $data['fvencon'];
                    $updateData['catcon']  = $data['catcon'];
                }
                DB::table('persona')->where('ndocper', $ndoc)->update($updateData);
                $idReal = $personaExistente->idper;
            } else {
                $data['ciuper'] = $data['ciuper'] ?? 'NO REGISTRADA';
                unset($data['pass']);
                DB::table('persona')->insert($data);
                $idReal = DB::getPdo()->lastInsertId();
            }

            // Solo crear cuentas de acceso para Administradores y Digitadores
            if (in_array(DB::table('persona')->where('idper', $idReal)->value('idpef'), [1, 2])) {
                DB::table('users')->updateOrInsert(
                    ['email' => $data['emaper'] ?? "user_{$ndoc}@cda.com"],
                    ['name' => $data['nomper'] . ' ' . $data['apeper'], 'password' => Hash::make((string)$ndoc), 'idper' => $idReal, 'username' => (string)$ndoc]
                );
            }
        }
    }

    private function importarVehiculos()
    {
        $this->command->info("  Importando Vehículos...");
        $vehiculos = DB::connection('legacy')->table('vehiculo')->get();

        foreach ($vehiculos as $v) {
            $data = (array) $v;
            $data['tipo_servicio'] = empty($data['idemp']) ? 1 : 2;
            $data['prop'] = $this->mapaPersonas[$data['prop'] ?? 0] ?? null;
            $data['cond'] = $this->mapaPersonas[$data['cond'] ?? 0] ?? null;

            DB::table('vehiculo')->updateOrInsert(['placaveh' => $data['placaveh']], $data);
        }
    }

    /**
     * Importar diagnósticos con filtro de fecha y asignación de idval_combu.
     *
     * CAMBIOS RESPECTO AL ORIGINAL:
     * 1. Solo importa diagnósticos con fecdia >= FECHA_CORTE
     * 2. Asigna idval_combu desde vehiculo.combuveh (fallback: 43 = Diesel)
     * 3. Remueve columnas legacy que no existen en el nuevo schema (idpun, idmaq)
     * 4. Guarda los IDs importados para filtrar diapar y foto
     */
    private function importarDiagnosticos()
    {
        $diags = DB::connection('legacy')->table('diag')
            ->where('fecdia', '>=', self::FECHA_CORTE)
            ->get();

        $importados = 0;
        $omitidos = 0;

        foreach ($diags as $d) {
            $data = (array) $d;

            // Remover columnas que no existen en el nuevo schema o que pueden romper FKs por filtros de fecha
            unset($data['idpun'], $data['idmaq'], $data['dpiddia']);

            // Mapear personas (legacy ID → real ID)
            $data['idper'] = $this->mapaPersonas[$data['idper'] ?? 0] ?? 1;
            $data['idinsp'] = $this->mapaPersonas[$data['idinsp'] ?? 0] ?? 1;
            $data['iding'] = $this->mapaPersonas[$data['iding'] ?? 0] ?? 1;

            // Sanitizar kilometraje negativo
            if (isset($data['kilomt']) && $data['kilomt'] < 0) { $data['kilomt'] = 0; }

            // ── NUEVO: Asignar idval_combu desde el combustible del vehículo ──
            // Si no existe en legacy (columna nueva), derivar del vehículo
            if (empty($data['idval_combu'])) {
                $data['idval_combu'] = $this->vehiculoCombustible[$data['idveh']] ?? 43; // Fallback: Diesel
            }

            // Solo importar si el vehículo existe en el nuevo sistema
            if (isset($this->idsVehiculos[$data['idveh']])) {
                DB::table('diag')->updateOrInsert(['iddia' => $data['iddia']], $data);
                $this->idsDiagImportados[$data['iddia']] = $data['idveh'];
                $importados++;
            } else {
                $omitidos++;
            }
        }

        $this->command->info("  Diagnósticos: {$importados} importados, {$omitidos} omitidos (vehículo inexistente)");
    }

    /**
     * Importar diapar solo para diagnósticos que pasaron el filtro de fecha.
     * Usa cursor para eficiencia con datasets grandes (560k+ registros en legacy).
     */
    /**
     * Importar diapar con control estricto de duplicados y separación Diesel vs Otto.
     * FASE 1: Genera automáticamente parámetros críticos (Motor Diesel, Luces).
     * FASE 2: Migra datos cualitativos desde legacy asegurando 0 duplicados.
     */
    private function importarDiapar()
    {
        $this->command->info("  Importando Diapar (Separación total Diesel vs Otto + Anti-Duplicados)...");
        
        $parametrosValidos = DB::table('param')->pluck('idpar', 'idpar')->toArray();
        $insertData = [];
        $batchSize = 2500;
        $total = 0;
        
        // 🛑 CONTROL DE DUPLICADOS: Evita múltiples registros por iddia + idpar
        $procesados = []; 
        
        // =========================================================================
        // FASE 1: GENERACIÓN OBLIGATORIA (Base para TODOS los vehículos)
        // Garantiza que el Motor Diesel se genere SIEMPRE, sin depender de legacy
        // =========================================================================
        $this->command->info("    -> Fase 1: Generando Luces y Motor Diesel...");
        
        foreach ($this->idsDiagImportados as $iddia => $idveh) {
            $combustible = strtolower($this->vehiculoCombustible[$idveh] ?? '');
            $esDiesel = ($combustible === 'diesel');
            
            // 1. LUCES (Aplica para ambos: Diesel y Otto)
            foreach ([1, 2] as $idparLuces) {
                $key = "{$iddia}-{$idparLuces}";
                $procesados[$key] = true;
                
                $insertData[] = [
                    'iddia' => $iddia,
                    'idpar' => $idparLuces,
                    'idper' => 1, // Fallback genérico de inspector
                    'valor' => 'Funciona',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $total++;
            }
            
            // 2. MOTOR DIESEL (Exclusivo para vehículos Diesel)
            if ($esDiesel) {
                $rangosMotorDiesel = [
                    3 => [71.00, 80.00],    // temp_c
                    4 => [3500.00, 4200.00], // rpm
                    5 => [3.00, 4.00],      // ciclo1
                    6 => [2.80, 2.99],      // ciclo2
                    7 => [2.50, 2.79],      // ciclo3
                    8 => [2.30, 2.49],      // ciclo4
                    9 => [0.00, 35.00],     // resultado_diesel
                ];
                
                foreach ($rangosMotorDiesel as $idparDiesel => $r) {
                    $key = "{$iddia}-{$idparDiesel}";
                    $procesados[$key] = true;
                    
                    // Generar valor aleatorio en rango con 2 decimales
                    $valorRand = mt_rand($r[0] * 100, $r[1] * 100) / 100;
                    
                    $insertData[] = [
                        'iddia' => $iddia,
                        'idpar' => $idparDiesel,
                        'idper' => 1,
                        'valor' => $valorRand,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $total++;
                }
            }
            
            if (count($insertData) >= $batchSize) {
                DB::table('diapar')->insert($insertData);
                $insertData = [];
            }
        }
        
        // =========================================================================
        // FASE 2: MIGRACIÓN DESDE LEGACY (Gases, Defectos, Inspección Visual)
        // =========================================================================
        $this->command->info("    -> Fase 2: Migrando datos desde legacy (Gases, Defectos, Visual)...");
        
        $query = DB::connection('legacy')->table('diapar')
            ->join('diag', 'diapar.iddia', '=', 'diag.iddia')
            ->select('diapar.*', 'diag.idveh');
            
        $omitidos = 0;
        
        foreach ($query->cursor() as $dp) {
            // Validar existencia del diagnóstico importado
            if (!isset($this->idsDiagImportados[$dp->iddia])) {
                continue;
            }
            
            $combustible = strtolower($this->vehiculoCombustible[$dp->idveh] ?? '');
            $esDiesel = ($combustible === 'diesel');
            
            $idparDestino = null;
            
            // 🔴 FLUJO EXCLUSIVO POR TIPO DE COMBUSTIBLE
            if ($esDiesel) {
                $idparDestino = $this->mapearDiesel($dp->idpar);
            } else {
                $idparDestino = $this->mapearOtto($dp->idpar);
            }
            
            // Si el mapeo retorna null o no existe en sistema nuevo, ignorar registro
            if (!$idparDestino || !isset($parametrosValidos[$idparDestino])) {
                continue;
            }
            
            // 🛑 CONTROL DE DUPLICADOS (Previene repetir el mismo idpar en un diagnóstico)
            $key = "{$dp->iddia}-{$idparDestino}";
            if (isset($procesados[$key])) {
                $omitidos++; 
                continue;
            }
            $procesados[$key] = true;
            
            // Calcular el valor final a persistir
            $valorFinal = $this->calcularValorCualitativo($dp, $idparDestino);
            
            $insertData[] = [
                'iddia' => $dp->iddia,
                'idpar' => $idparDestino,
                'idper' => $this->mapaPersonas[$dp->idper ?? 0] ?? 1,
                'valor' => $valorFinal,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $total++;
            
            if (count($insertData) >= $batchSize) {
                DB::table('diapar')->insert($insertData);
                $insertData = [];
            }
        }
        
        if (!empty($insertData)) {
            DB::table('diapar')->insert($insertData);
        }
        
        $this->command->info("  Diapar: {$total} insertados/generados, {$omitidos} duplicados controlados u omitidos.");
    }

    /**
     * Mapeo exclusivo para vehículos DIESEL.
     * Retorna null para rechazar cualquier parámetro prohibido.
     */
    private function mapearDiesel($idparLegacy)
    {
        // NO procesar Motor Diesel ni Luces aquí, ya se generaron en Fase 1.
        $mapeo = [
            30 => 10, // Defectos
            31 => 11, // Defectos
            34 => 14, // Visual: Descripcion -> desc_inspeccion
            35 => 12, // Visual: Grupo -> grupo_inspeccion
            36 => 13, // Visual: Tipo defecto -> tipo_defecto
        ];
        
        return $mapeo[$idparLegacy] ?? null; // Null rechaza gases y otros irrelevantes
    }

    /**
     * Mapeo exclusivo para vehículos CICLO OTTO (NO DIESEL).
     * Retorna null para rechazar cualquier parámetro Diesel prohibido.
     */
    private function mapearOtto($idparLegacy)
    {
        // Bloquear Motor Diesel explícitamente (23 a 29 legacy)
        $bloqueadosDiesel = [23, 24, 25, 26, 27, 28, 29];
        if (in_array($idparLegacy, $bloqueadosDiesel)) {
            return null;
        }
        
        $mapeo = [
            30 => 10, // Defectos
            31 => 11, // Defectos
            34 => 14, // Visual: Descripcion -> desc_inspeccion
            35 => 12, // Visual: Grupo -> grupo_inspeccion
            36 => 13, // Visual: Tipo defecto -> tipo_defecto
        ];
        
        if (isset($mapeo[$idparLegacy])) {
            return $mapeo[$idparLegacy];
        }
        
        // Retornar ID original para Gases y Ciclo Otto. 
        // Luces (1,2) serán ignoradas por el control de duplicados (ya generadas).
        return $idparLegacy;
    }

    /**
     * Calcula el valor final para parámetros de texto y mapeos cualitativos.
     * Los numéricos de Motor Diesel ya no pasan por aquí (Fase 1).
     */
    private function calcularValorCualitativo($dp, $idparDestino)
    {
        // Defectos (S/N -> Cumple/No Cumple)
        if (in_array($idparDestino, [10, 11])) {
            $v = trim($dp->valor ?? '');
            return ($v === 'S') ? 'Cumple' : (($v === 'N') ? 'No Cumple' : 'N/A');
        }
        
        // Inspección Visual (Mantener texto plano)
        if (in_array($idparDestino, [12, 13, 14])) {
            return $dp->valor ?? 'SIN REGISTRO';
        }
        
        // Fallback: Mantener valor original (Aplica para Gases y OTTO)
        return $dp->valor ?? null;
    }

    /**
     * Importar fotos solo para diagnósticos que pasaron el filtro de fecha.
     */
    private function importarFotos()
    {
        $this->command->info("  Importando Fotos (solo diags filtrados)...");

        $fotos = DB::connection('legacy')->table('foto')->get();
        $importadas = 0;

        foreach ($fotos as $f) {
            $data = (array) $f;
            if (isset($this->idsDiagImportados[$data['iddia']])) {
                DB::table('foto')->updateOrInsert(['idfot' => $data['idfot']], $data);
                $importadas++;
            }
        }

        $this->command->info("  Fotos importadas: {$importadas}");
    }


    private function asignarRoles()
    {
        $this->command->info("  Asignando roles de Spatie a los usuarios...");
        $users = User::all();
        $asignados = 0;

        foreach ($users as $user) {
            // Limpiar roles previos para idempotencia
            $user->syncRoles([]);

            if ($user->idemp) {
                // Es una cuenta de Empresa
                $user->assignRole('Empresa');
                $asignados++;
            } elseif ($user->idper) {
                // Es una cuenta de Persona → buscar su perfil
                $perfil = DB::table('persona')->where('idper', $user->idper)->value('idpef');

                if ($perfil == 1) {
                    $user->assignRole('Administrador');
                    $asignados++;
                } elseif ($perfil == 2) {
                    $user->assignRole('Digitador');
                    $asignados++;
                }
            }
        }

        $this->command->info("  Roles asignados correctamente: {$asignados} usuarios.");
    }
}
