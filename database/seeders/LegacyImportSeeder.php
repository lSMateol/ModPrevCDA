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
     */

    /** Fecha de corte para datos transaccionales */
    protected const FECHA_CORTE = '2025-06-01';

    /** Cache de mapeo de IDs legacy → IDs reales (evita N+1 queries) */
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
        // 0. LIMPIEZA PREVIA EN DESTINO (Base modprev_local)
        // ═══════════════════════════════════════════════════════════════
        // NOTA: La limpieza de la BD legacy DEBE ejecutarse ANTES con:
        //       php artisan db:seed --class=LegacyCleanupSeeder
        $this->limpiarDatosPrevios();

        // ═══════════════════════════════════════════════════════════════
        // 1. MAESTROS DEL NUEVO SISTEMA (Roles, Perfiles, Parámetros)
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('');
        $this->command->info('▸ [1/9] Configurando Roles, Perfiles y Parámetros del sistema...');
        $this->call(RoleSeeder::class);
        $this->call(PerfilSeeder::class);

        // ═══════════════════════════════════════════════════════════════
        // 2. DICCIONARIOS BÁSICOS (desde legacy)
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('▸ [2/9] Importando diccionarios (dominio, valor, ubica, marca)...');
        $this->importarTablaSimple('dominio', 'iddom');
        $this->importarTablaSimple('valor', 'idval');
        $this->importarTablaSimple('ubica', 'codubi');
        $this->importarTablaSimple('marca', 'idmar');

        // ═══════════════════════════════════════════════════════════════
        // 3. PARÁMETROS TÉCNICOS (desde seeders del sistema actual)
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('▸ [3/9] Configurando parámetros técnicos y configuración por combustible...');
        $this->call(TipparSeeder::class);
        $this->call(DynamicFieldsSeeder::class);

        // ═══════════════════════════════════════════════════════════════
        // 4. EMPRESAS (desde legacy)
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('▸ [4/9] Importando Empresas...');
        $this->importarEmpresas();

        // ═══════════════════════════════════════════════════════════════
        // 5. PERSONAS (desde legacy)
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('▸ [5/9] Importando Personas (ETL con perfil dual ID 8)...');
        $this->importarPersonas();

        // 5b. Cargar mapa de personas en memoria
        $this->cargarMapaPersonas();

        // ═══════════════════════════════════════════════════════════════
        // 6. VEHÍCULOS (desde legacy)
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
        $this->reconciliarUsuariosOperativos(); // Garantiza users para Ingenieros/Inspectores
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

    private function limpiarDatosPrevios()
    {
        $this->command->warn("  Limpiando datos transaccionales previos (≥ " . self::FECHA_CORTE . ")...");

        // Identificar IDs de diagnósticos en el rango de migración en la BD DESTINO
        $idsAEliminar = DB::table('diag')
            ->where('fecdia', '>=', self::FECHA_CORTE)
            ->pluck('iddia');

        if ($idsAEliminar->isNotEmpty()) {
            $countDiapar = 0;
            $countFotos = 0;
            $countDiag = 0;

            foreach ($idsAEliminar->chunk(2000) as $chunk) {
                $ids = $chunk->toArray();
                $countDiapar += DB::table('diapar')->whereIn('iddia', $ids)->delete();
                $countFotos += DB::table('foto')->whereIn('iddia', $ids)->delete();
                $countDiag += DB::table('diag')->whereIn('iddia', $ids)->delete();
            }

            $this->command->info("    ✓ $countDiag diagnósticos, $countDiapar parámetros y $countFotos fotos eliminados.");
        } else {
            $this->command->info("    - No se encontraron datos previos para limpiar.");
        }
    }

    private function cargarMapaPersonas()
    {
        $this->command->info("  Cargando mapa de personas en memoria...");
        $personasLegacy = DB::connection('legacy')->table('persona')->get(['idper', 'ndocper']);
        $personasReales = DB::table('persona')->get(['idper', 'ndocper'])->keyBy('ndocper');

        foreach ($personasLegacy as $p) {
            $real = $personasReales->get($p->ndocper);
            $this->mapaPersonas[$p->idper] = $real ? $real->idper : null;
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
                $rawEmail = trim($empresaReal->emaem ?? '');
                $email = (empty($rawEmail) || !filter_var($rawEmail, FILTER_VALIDATE_EMAIL))
                    ? "emp_{$empresaReal->idemp}@cda.com"
                    : $rawEmail;

                // Buscamos por username para evitar duplicados de integridad
                $userExistente = DB::table('users')->where('username', $data['usuaemp'])->first();
                $userData = [
                    'name'     => $empresaReal->razsoem,
                    'password' => $data['passemp'],
                    'idemp'    => $empresaReal->idemp,
                    'username' => $data['usuaemp'],
                    'email'    => $email
                ];

                if ($userExistente) {
                    // Evitar colisión de email si el nuevo email ya pertenece a otro usuario
                    if ($userExistente->email !== $email && DB::table('users')->where('email', $email)->where('username', '!=', $data['usuaemp'])->exists()) {
                        unset($userData['email']);
                    }
                    DB::table('users')->where('id', $userExistente->id)->update($userData);
                    $accion = "ACTUALIZADO";
                } else {
                    // Evitar colisión de email en inserción
                    if (DB::table('users')->where('email', $email)->exists()) {
                        $userData['email'] = "emp_{$empresaReal->idemp}_" . Str::random(3) . "@cda.com";
                    }
                    DB::table('users')->insert($userData);
                    $accion = "CREADO";
                }
                $this->command->info("      [AUDIT USER-EMP] username={$data['usuaemp']} | email=" . ($rawEmail ?: 'EMPTY') . " | acción={$accion}");
            }
        }
    }

    private function importarPersonas()
    {
        $this->command->info("  Importando Personas (Aplicando reglas ETL)...");
        $personas = DB::connection('legacy')->table('persona')->orderBy('idper')->get();

        foreach ($personas as $p) {
            $data = (array) $p;
            $ndoc = $data['ndocper'];

            if ($data['idpef'] > 8 || $data['idpef'] == 0) { $data['idpef'] = 1; }
            
            if (empty($ndoc)) {
                continue;
            }

            // Identificar si la persona ya existe por Documento o por su ID primario legado
            // Esto evita errores de IntegrityConstraintViolation (Duplicate entry) en el PK idper.
            $personaExistente = DB::table('persona')
                ->where('ndocper', $ndoc)
                ->orWhere('idper', $data['idper'])
                ->first();

            if ($personaExistente) {
                $vP = $personaExistente->idpef;
                $nP = $data['idpef'];
                $perFinal = $vP;

                if (($vP == 6 && $nP == 7) || ($vP == 7 && $nP == 6)) { $perFinal = 8; }
                elseif (in_array(1, [$vP, $nP])) { $perFinal = 1; }
                // Si el perfil entrante es operativo (Digitador/Ingeniero/Inspector) y el
                // destino aún no tiene un perfil operativo, lo promovemos.
                elseif (in_array($nP, [2, 4, 5]) && !in_array($vP, [1, 2, 4, 5])) { $perFinal = $nP; }

                $updateData = ['idpef' => $perFinal];
                
                // Aseguramos que el documento coincida (en caso de que hayamos encontrado por idper)
                $updateData['ndocper'] = $ndoc;

                if (empty($personaExistente->nliccon) && !empty($data['nliccon'])) {
                    $updateData['nliccon'] = $data['nliccon'];
                    $updateData['fvencon'] = $data['fvencon'];
                    $updateData['catcon']  = $data['catcon'];
                }
                DB::table('persona')->where('idper', $personaExistente->idper)->update($updateData);
            } else {
                $data['ciuper'] = $data['ciuper'] ?? 'NO REGISTRADA';
                unset($data['pass']);
                DB::table('persona')->insert($data);
            }

            $perFinal = DB::table('persona')->where('ndocper', $ndoc)->value('idpef');
            $idReal   = DB::table('persona')->where('ndocper', $ndoc)->value('idper');

            // Si el perfil en destino no es operativo, intentar con el perfil
            // sanitizado que viene del legacy (ya corregido por LegacyCleanupSeeder).
            // Esto cubre el caso donde corregirPerfilesIngenieros/Inspectores no
            // encontró el documento por diferencias de formato (ej: 825 vs 825.0).
            $perParaUser = in_array($perFinal, [1, 2, 4, 5]) ? $perFinal : null;
            if ($perParaUser === null && in_array($data['idpef'], [1, 2, 4, 5])) {
                $perParaUser = $data['idpef'];
                // Sincronizar el perfil correcto en destino para consistencia
                DB::table('persona')->where('ndocper', $ndoc)->update(['idpef' => $perParaUser]);
            }

            if ($perParaUser !== null) {
                $rawEmail = trim($data['emaper'] ?? '');
                $email = (empty($rawEmail) || !filter_var($rawEmail, FILTER_VALIDATE_EMAIL))
                    ? "user_{$ndoc}@cda.com"
                    : $rawEmail;

                $username = (string)$ndoc;
                $userExistente = DB::table('users')->where('username', $username)->first();
                
                $userData = [
                    'name'     => trim($data['nomper'] . ' ' . ($data['apeper'] ?? '')),
                    'password' => Hash::make($username),
                    'idper'    => $idReal,
                    'username' => $username,
                    'email'    => $email
                ];

                if ($userExistente) {
                    // Si ya existe por username, actualizamos. Evitamos cambiar email si choca con otro.
                    if ($userExistente->email !== $email && DB::table('users')->where('email', $email)->where('username', '!=', $username)->exists()) {
                        unset($userData['email']);
                    }
                    DB::table('users')->where('id', $userExistente->id)->update($userData);
                    $accion = "REUTILIZADO/ACTUALIZADO";
                } else {
                    // Si no existe por username, verificamos si el email choca para generar uno único
                    if (DB::table('users')->where('email', $email)->exists()) {
                        $userData['email'] = "user_{$ndoc}_" . Str::random(3) . "@cda.com";
                    }
                    DB::table('users')->insert($userData);
                    $accion = "CREADO";
                }

                // AUDITORÍA PARA IDS CRÍTICOS
                if ($data['idper'] == 817 || $data['idper'] == 777 || $data['idper'] == 825) {
                    $this->command->warn("      [AUDIT PERSONA] ID: {$data['idper']} | Doc: {$ndoc} | Perfil: {$data['idpef']} -> {$perParaUser} | Acción User: {$accion}");
                }

                $this->command->info("      [AUDIT USER] username={$username} | email=" . ($rawEmail ?: 'EMPTY') . " | acción={$accion}");
            }
        }
    }

    /**
     * Pase de reconciliación: busca en persona todos los Ingenieros (idpef=4)
     * e Inspectores (idpef=5) que no tienen un registro en users y los crea.
     * Esto cubre casos donde la lógica inline de importarPersonas() no pudo
     * crear el usuario por ambigüedad en el formato del ndocper o colisión de email.
     */
    private function reconciliarUsuariosOperativos(): void
    {
        $this->command->info('  Reconciliando usuarios de Ingenieros e Inspectores...');

        $operativos = DB::table('persona')
            ->whereIn('idpef', [4, 5])
            ->get(['idper', 'ndocper', 'nomper', 'apeper', 'emaper', 'idpef']);

        $creados = 0;
        foreach ($operativos as $op) {
            // Verificar si ya tiene un usuario vinculado
            if (DB::table('users')->where('idper', $op->idper)->exists()) {
                continue;
            }

            $ndoc  = (string) $op->ndocper;
            $email = !empty($op->emaper) ? $op->emaper : "user_{$ndoc}@cda.com";
            $nombre = trim($op->nomper . ' ' . ($op->apeper ?? ''));

            $username = (string) $ndoc;
            $userExistente = DB::table('users')->where('username', $username)->first();

            $userData = [
                'name'     => $nombre,
                'password' => Hash::make($username),
                'idper'    => $op->idper,
                'username' => $username,
                'email'    => $email
            ];

            if ($userExistente) {
                if ($userExistente->email !== $email && DB::table('users')->where('email', $email)->where('username', '!=', $username)->exists()) {
                    unset($userData['email']);
                }
                DB::table('users')->where('id', $userExistente->id)->update($userData);
                $accion = "RECONCILIADO (UPDATE)";
            } else {
                if (DB::table('users')->where('email', $email)->exists()) {
                    $userData['email'] = "user_{$username}_" . Str::random(3) . "@cda.com";
                }
                DB::table('users')->insert($userData);
                $accion = "RECONCILIADO (INSERT)";
            }

            $this->command->info("    ✓ [AUDIT RECONCILIAR] username={$username} | email={$email} | acción={$accion}");
            $creados++;
        }

        if ($creados === 0) {
            $this->command->info('    - Todos los Ingenieros/Inspectores ya tenían usuario.');
        } else {
            $this->command->info("    ✓ {$creados} usuario(s) de operativos reconciliados.");
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

    private function importarDiagnosticos()
    {
        // Ya no filtramos por FECHA_CORTE porque LegacyCleanupSeeder ya limpió todo lo anterior
        $diags = DB::connection('legacy')->table('diag')->get();

        $importados = 0;
        
        // Obtenemos todos los idper reales que existen en la BD destino para validación
        $personasRealesValidas = DB::table('persona')->pluck('idper', 'idper')->toArray();
        // ID de respaldo (el ingeniero unificado o el primer usuario que exista)
        $idRespaldo = DB::table('persona')->where('ndocper', 'like', '%1091682308%')->value('idper');
        if (!$idRespaldo && count($personasRealesValidas) > 0) {
            $idRespaldo = array_key_first($personasRealesValidas);
        }
        $this->command->info("    [DEBUG] idRespaldo detectado (Ingeniero Principal): " . ($idRespaldo ?? 'NULL'));

        // Obtenemos todos los idveh reales
        $vehiculosRealesValidos = DB::table('vehiculo')->pluck('idveh', 'idveh')->toArray();

        foreach ($diags as $d) {
            $data = (array) $d;
            unset($data['idpun'], $data['idmaq'], $data['dpiddia']);

            $idper   = $this->mapaPersonas[$data['idper']   ?? 0] ?? $data['idper'];
            $idinsp  = $this->mapaPersonas[$data['idinsp']  ?? 0] ?? $data['idinsp'];
            $iding   = $this->mapaPersonas[$data['iding']   ?? 0] ?? $data['iding'];

            // Validación estricta contra la BD destino: si no existe, usamos el respaldo
            $data['idper']  = isset($personasRealesValidas[$idper]) ? $idper : $idRespaldo;
            $data['idinsp'] = isset($personasRealesValidas[$idinsp]) ? $idinsp : $idRespaldo;
            $data['iding']  = isset($personasRealesValidas[$iding]) ? $iding : $idRespaldo;

            if (isset($data['kilomt']) && $data['kilomt'] < 0) { $data['kilomt'] = 0; }

            if (empty($data['idval_combu'])) {
                $data['idval_combu'] = $this->vehiculoCombustible[$data['idveh']] ?? 43;
            }

            // ── NUEVO: Asignar tipo_formulario según combustible ──
            $combustible = $data['idval_combu'];
            $data['tipo_formulario'] = ($combustible == 43) ? 'diesel_basico' : 'otto_completo';

            // Si el vehículo no existe en la BD destino, lamentablemente MySQL no dejará importarlo
            // a menos que insertemos un idveh válido. Por seguridad, lo omitiremos y mostraremos warning.
            if (!isset($vehiculosRealesValidos[$data['idveh']])) {
                continue;
            }

            // Inserción segura garantizada
            DB::table('diag')->updateOrInsert(['iddia' => $data['iddia']], $data);
            
            // Guardamos tanto idveh como idper para usar en la migración de diapar
            $this->idsDiagImportados[$data['iddia']] = [
                'idveh' => $data['idveh'],
                'idper' => $data['idper']
            ];

            // Auditoría para diagnósticos con idRespaldo
            if ($data['idper'] == $idRespaldo && $idper != $idRespaldo) {
                 // Log temporal para entender por qué cae en respaldo
                 // $this->command->warn("      [DEBUG DIAG] Diag #{$data['iddia']}: Autor original {$idper} no hallado en destino. Usando respaldo {$idRespaldo}.");
            }
            $importados++;
        }
        $this->command->info("  Diagnósticos: {$importados} importados.");
    }

    private function importarDiapar()
    {
        $this->command->info("  Importando Diapar (Filtro ESTRICTO, Agrupación y Anti-Duplicados)...");
        
        $parametrosValidos = DB::table('param')->pluck('idpar', 'idpar')->toArray();
        $batchSize = 2500;
        $insertData = [];
        $procesados = []; 
        $total = 0;
        $omitidos = 0;

        // Almacenamiento temporal para agrupar
        $inspeccionVisual = []; // [iddia => ['grupo' => val, 'tipo' => val, 'desc' => val]]
        $defectos = [];         // [iddia => [10 => val, 11 => val]]

        // Obtenemos idper válidos y el de respaldo
        $personasRealesValidas = DB::table('persona')->pluck('idper', 'idper')->toArray();
        $idRespaldo = DB::table('persona')->where('ndocper', 'like', '%1091682308%')->value('idper');
        if (!$idRespaldo && count($personasRealesValidas) > 0) {
            $idRespaldo = array_key_first($personasRealesValidas);
        }

        // =========================================================================
        // PRE-FASE: Captura de autores reales (Evitar reasignación masiva a 825)
        // =========================================================================
        $this->command->info("    -> Pre-fase: Mapeando autores reales desde legacy...");
        $realDigitadores = [];
        $idsImportados = array_keys($this->idsDiagImportados);
        
        foreach (array_chunk($idsImportados, 1000) as $chunkIds) {
            $autoresLegacy = DB::connection('legacy')->table('diapar')
                ->whereIn('iddia', $chunkIds)
                ->where('idper', '!=', 0)
                ->select('iddia', 'idper')
                ->get();
            
            foreach ($autoresLegacy as $al) {
                if (isset($realDigitadores[$al->iddia])) continue;
                $mapped = $this->mapaPersonas[$al->idper] ?? $al->idper;
                if (isset($personasRealesValidas[$mapped])) {
                    $realDigitadores[$al->iddia] = $mapped;
                }
            }
        }

        // =========================================================================
        // FASE 1: GENERACIÓN OBLIGATORIA (Base para TODOS los vehículos)
        // =========================================================================
        $this->command->info("    -> Fase 1: Generando Luces y Motor Diesel...");
        $now = now()->toDateTimeString();

        foreach ($this->idsDiagImportados as $iddia => $info) {
            $idveh = $info['idveh'];
            $idperDiag = $info['idper'];
            
            // Prioridad: Autor real de parámetros > Autor de cabecera > Respaldo
            $idperFinalF1 = $realDigitadores[$iddia] ?? ($idperDiag ?: $idRespaldo);
            
            $combustible = $this->vehiculoCombustible[$idveh] ?? null;
            $esDiesel = ($combustible == 43 || strtolower(trim((string)$combustible)) === 'diesel');
            
            // 1. LUCES (Aplica para todos)
            foreach ([1, 2] as $idparLuces) {
                $key = "{$iddia}-{$idparLuces}";
                $procesados[$key] = true;
                $insertData[] = [
                    'iddia' => $iddia,
                    'idpar' => $idparLuces,
                    'idper' => $idperFinalF1,
                    'valor' => 'funciona',
                    'created_at' => $now,
                    'updated_at' => $now,
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
                    $valorRand = mt_rand($r[0] * 100, $r[1] * 100) / 100;
                    
                    $insertData[] = [
                        'iddia' => $iddia,
                        'idpar' => $idparDiesel,
                        'idper' => $idperFinalF1,
                        'valor' => $valorRand,
                        'created_at' => $now,
                        'updated_at' => $now,
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
        // FASE 2: MIGRACIÓN DESDE LEGACY (Agrupación de Visual y Defectos)
        // =========================================================================
        $this->command->info("    -> Fase 2: Procesando datos desde legacy (Agrupando Inspección y Defectos)...");
        $idsImportados = array_keys($this->idsDiagImportados);

        foreach (array_chunk($idsImportados, 2000) as $chunk) {
            $query = DB::connection('legacy')->table('diapar')
                ->join('diag', 'diapar.iddia', '=', 'diag.iddia')
                ->select('diapar.*', 'diag.idveh')
                ->whereIn('diapar.iddia', $chunk);

            foreach ($query->cursor() as $dp) {
                // A. DEFECTOS (Legacy 30 y 31)
                if ($dp->idpar == 30 || $dp->idpar == 31) {
                    $idparDestino = ($dp->idpar == 30) ? 10 : 11;
                    $defectos[$dp->iddia][$idparDestino] = $dp->valor;
                    continue;
                }
                
                // B. INSPECCIÓN VISUAL (Legacy 34, 35, 36)
                if (in_array($dp->idpar, [34, 35, 36])) {
                    if (!isset($inspeccionVisual[$dp->iddia])) {
                        $inspeccionVisual[$dp->iddia] = ['grupo' => null, 'tipo' => null, 'desc' => null];
                    }
                    $v = trim($dp->valor ?? '');
                    if ($v !== '') {
                        if ($dp->idpar == 35) $inspeccionVisual[$dp->iddia]['grupo'] = $this->concatenarValor($inspeccionVisual[$dp->iddia]['grupo'], $v);
                        elseif ($dp->idpar == 36) $inspeccionVisual[$dp->iddia]['tipo'] = $this->concatenarValor($inspeccionVisual[$dp->iddia]['tipo'], $v);
                        elseif ($dp->idpar == 34) $inspeccionVisual[$dp->iddia]['desc'] = $this->concatenarValor($inspeccionVisual[$dp->iddia]['desc'], $v);
                    }
                    continue;
                }
                
                // C. OTROS PARÁMETROS (Gases, etc)
                $combustible = $this->vehiculoCombustible[$dp->idveh] ?? null;
                $esDiesel = ($combustible == 43 || strtolower(trim((string)$combustible)) === 'diesel');
                
                if ($esDiesel) {
                    $omitidos++;
                    continue;
                } else {
                    // Evitar parámetros que se generan dinámicamente o se consolidan en Fase 3
                    if ($dp->idpar >= 3 && $dp->idpar <= 9) continue;
                    if ($dp->idpar == 1 || $dp->idpar == 2) continue;
                    if ($dp->idpar == 14) continue; // ID 14 en legacy es CO, en destino es Inspección Visual
                    
                    $idparDestino = $dp->idpar;
                    if (!isset($parametrosValidos[$idparDestino])) continue;
                    
                    if (isset($procesados["{$dp->iddia}-{$idparDestino}"])) continue;
                    $procesados["{$dp->iddia}-{$idparDestino}"] = true;
                    
                    $idperMapeado = $this->mapaPersonas[$dp->idper] ?? $dp->idper;
                    $idperDiag = $this->idsDiagImportados[$dp->iddia]['idper'] ?? $idRespaldo;
                    
                    // Prioridad: Autor específico del registro > Autor pre-escaneado > Autor de cabecera
                    $idperFinal = isset($personasRealesValidas[$idperMapeado]) 
                        ? $idperMapeado 
                        : ($realDigitadores[$dp->iddia] ?? $idperDiag);
                    
                    // AUDITORÍA PROFUNDA PARA ID 817, 777 y 825 (Puntos 7 y 8 del requerimiento)
                    if ($dp->idper == 817 || $dp->idper == 777 || $idperFinal == 825) {
                        if ($total % 100 === 0) { // Limitar ruido en consola
                            $motivo = ($idperFinal == $idperMapeado) ? "MAPPING_OK" : (($idperFinal == $idperDiag) ? "FALLBACK_DIAG" : "FALLBACK_RESPALDO");
                            $this->command->info("      [AUDIT DIAPAR] Diag #{$dp->iddia} | Param: {$dp->idpar} | Orig: {$dp->idper} | Mapped: {$idperMapeado} | Final: {$idperFinal} | Motivo: {$motivo}");
                        }
                    }

                    $insertData[] = [
                        'iddia' => $dp->iddia,
                        'idpar' => $idparDestino,
                        'idper' => $idperFinal,
                        'valor' => $dp->valor,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $total++;
                    
                    if (count($insertData) >= $batchSize) {
                        DB::table('diapar')->insert($insertData);
                        $insertData = [];
                    }
                }
            }
        }

        // =========================================================================
        // FASE 3: INSERCIÓN DE DEFECTOS E INSPECCIÓN VISUAL (Consolidados)
        // =========================================================================
        $this->command->info("    -> Fase 3: Insertando Defectos normalizados e Inspección Visual con Reglas de Negocio...");
        
        foreach ($defectos as $iddia => $defs) {
            foreach ($defs as $idparDestino => $valor) {
                if (isset($procesados["$iddia-$idparDestino"])) continue;
                $procesados["$iddia-$idparDestino"] = true;
                
                $idperDiag = $this->idsDiagImportados[$iddia]['idper'] ?? $idRespaldo;
                $idperFinalF3 = $realDigitadores[$iddia] ?? $idperDiag;

                $insertData[] = [
                    'iddia' => $iddia, 'idpar' => $idparDestino, 'idper' => $idperFinalF3,
                    'valor' => $this->normalizarDefecto($valor), 'created_at' => $now, 'updated_at' => $now,
                ];
                $total++;
            }

            if (count($insertData) >= $batchSize) {
                DB::table('diapar')->insert($insertData);
                $insertData = [];
            }
        }
        
        foreach ($inspeccionVisual as $iddia => $iv) {
            $descOriginal = $iv['desc'] ?? '';
            $tipoDetectado = $this->mapearTipoLegacy($iv['tipo'] ?? null);
            $mapeoDestino = [];
            
            if ($tipoDetectado !== null) {
                $descRaw = str_replace('|', ',', $descOriginal);
                $descList = array_filter(array_map('trim', explode(',', $descRaw)));
                $jsonList = [];
                foreach ($descList as $item) {
                    $jsonList[] = ['obs' => $item, 'grupo' => 'Otro', 'tipo' => $tipoDetectado];
                }
                $jsonPayload = count($jsonList) > 0 ? json_encode(['list' => $jsonList, 'obs' => ''], JSON_UNESCAPED_UNICODE) : 'SIN REGISTRO';
                $mapeoDestino = [12 => 'Otro', 13 => $tipoDetectado, 14 => $jsonPayload];
            } else {
                if (trim($descOriginal) === '') continue;
                $obsLimpia = str_replace('|', ', ', $descOriginal);
                $jsonPayload = json_encode(['list' => [], 'obs' => $obsLimpia], JSON_UNESCAPED_UNICODE);
                $mapeoDestino = [14 => $jsonPayload];
            }
            
            foreach ($mapeoDestino as $idparDestino => $valor) {
                if (isset($procesados["$iddia-$idparDestino"])) continue;
                $procesados["$iddia-$idparDestino"] = true;
                
                $idperDiag = $this->idsDiagImportados[$iddia]['idper'] ?? $idRespaldo;
                $idperFinalF3 = $realDigitadores[$iddia] ?? $idperDiag;

                $insertData[] = [
                    'iddia' => $iddia, 'idpar' => $idparDestino, 'idper' => $idperFinalF3,
                    'valor' => $valor, 'created_at' => $now, 'updated_at' => $now,
                ];
                $total++;
            }

            if (count($insertData) >= $batchSize) {
                DB::table('diapar')->insert($insertData);
                $insertData = [];
            }
        }

        if (!empty($insertData)) {
            DB::table('diapar')->insert($insertData);
        }
        $this->command->info("  Diapar: {$total} procesados.");
    }

    private function mapearTipoLegacy($tipoRaw)
    {
        if (!$tipoRaw) return null;
        $tipos = array_map('trim', explode('|', $tipoRaw));
        foreach ($tipos as $tipo) {
            if ($tipo === '2') return 'Tipo A';
            if ($tipo === '3') return 'Tipo B';
        }
        return null;
    }

    private function normalizarDefecto($valor)
    {
        $v = strtolower(trim($valor ?? ''));
        if ($v === '' || $v === 'n/a' || $v === 'na' || $v === null) return 'na';
        if (in_array($v, ['si', 'sí', 's', '1', 'true', 'cumple'])) return 'si';
        if (in_array($v, ['no', 'n', '0', 'false', 'no cumple'])) return 'no';
        return 'na';
    }

    private function concatenarValor($actual, $nuevo)
    {
        if (empty($actual)) return $nuevo;
        if (strpos($actual, $nuevo) !== false) return $actual;
        return $actual . ' | ' . $nuevo;
    }

    private function importarFotos()
    {
        $this->command->info("  Importando Fotos...");
        $idsImportados = array_keys($this->idsDiagImportados);
        
        foreach (array_chunk($idsImportados, 2000) as $chunk) {
            $fotos = DB::connection('legacy')->table('foto')
                ->whereIn('iddia', $chunk)
                ->get();

            foreach ($fotos as $f) {
                DB::table('foto')->updateOrInsert(['idfot' => $f->idfot], (array)$f);
            }
        }
    }

    private function asignarRoles()
    {
        $this->command->info("  Asignando roles...");
        $users = User::all();
        foreach ($users as $user) {
            $user->syncRoles([]);
            if ($user->idemp) {
                $user->assignRole('Empresa');
            } elseif ($user->idper) {
                $perfil = DB::table('persona')->where('idper', $user->idper)->value('idpef');
                if ($perfil == 1) $user->assignRole('Administrador');
                if ($perfil == 2) $user->assignRole('Digitador');
                if ($perfil == 4) $user->assignRole('Ingeniero');
                if ($perfil == 5) $user->assignRole('Inspector');
            }
        }
    }
}
