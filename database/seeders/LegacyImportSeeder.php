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
            $ndoc = $data['ndocper'];

            if ($data['idpef'] > 8 || $data['idpef'] == 0) { $data['idpef'] = 1; }
            
            if (empty($ndoc)) {
                continue;
            }

            $personaExistente = DB::table('persona')->where('ndocper', $ndoc)->first();

            if ($personaExistente) {
                $vP = $personaExistente->idpef;
                $nP = $data['idpef'];
                $perFinal = $vP;

                if (($vP == 6 && $nP == 7) || ($vP == 7 && $nP == 6)) { $perFinal = 8; }
                elseif (in_array(1, [$vP, $nP])) { $perFinal = 1; }

                $updateData = ['idpef' => $perFinal];
                if (empty($personaExistente->nliccon) && !empty($data['nliccon'])) {
                    $updateData['nliccon'] = $data['nliccon'];
                    $updateData['fvencon'] = $data['fvencon'];
                    $updateData['catcon']  = $data['catcon'];
                }
                DB::table('persona')->where('ndocper', $ndoc)->update($updateData);
            } else {
                $data['ciuper'] = $data['ciuper'] ?? 'NO REGISTRADA';
                unset($data['pass']);
                DB::table('persona')->insert($data);
            }

            $perFinal = DB::table('persona')->where('ndocper', $ndoc)->value('idpef');
            $idReal = DB::table('persona')->where('ndocper', $ndoc)->value('idper');

            if (in_array($perFinal, [1, 2])) {
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

            // Si el vehículo no existe en la BD destino, lamentablemente MySQL no dejará importarlo
            // a menos que insertemos un idveh válido. Por seguridad, lo omitiremos y mostraremos warning.
            if (!isset($vehiculosRealesValidos[$data['idveh']])) {
                continue;
            }

            // Inserción segura garantizada
            DB::table('diag')->updateOrInsert(['iddia' => $data['iddia']], $data);
            $this->idsDiagImportados[$data['iddia']] = $data['idveh'];
            $importados++;
        }
        $this->command->info("  Diagnósticos: {$importados} importados.");
    }

    private function importarDiapar()
    {
        $this->command->info("  Importando Diapar...");
        $parametrosValidos = DB::table('param')->pluck('idpar', 'idpar')->toArray();
        $batchSize = 2500;
        $insertData = [];
        $procesados = []; 
        $total = 0;

        // Obtenemos idper válidos y el de respaldo
        $personasRealesValidas = DB::table('persona')->pluck('idper', 'idper')->toArray();
        $idRespaldo = DB::table('persona')->where('ndocper', 'like', '%1091682308%')->value('idper');
        if (!$idRespaldo && count($personasRealesValidas) > 0) {
            $idRespaldo = array_key_first($personasRealesValidas);
        }

        // Fase 1: Generación de base (Luces, etc)
        $now = now()->toDateTimeString();
        foreach ($this->idsDiagImportados as $iddia => $idveh) {
            foreach ([1, 2] as $idpar) {
                $insertData[] = ['iddia' => $iddia, 'idpar' => $idpar, 'idper' => $idRespaldo, 'valor' => 'funciona', 'created_at' => $now, 'updated_at' => $now];
                $procesados["$iddia-$idpar"] = true;
                $total++;
            }
            if (count($insertData) >= $batchSize) {
                DB::table('diapar')->insert($insertData);
                $insertData = [];
            }
        }

        // Fase 2: Mapeo legacy
        $idsImportados = array_keys($this->idsDiagImportados);
        foreach (array_chunk($idsImportados, 2000) as $chunk) {
            $query = DB::connection('legacy')->table('diapar')
                ->whereIn('iddia', $chunk);

            foreach ($query->cursor() as $dp) {
                $idpar = $dp->idpar;
                if (in_array($idpar, [30, 31, 34, 35, 36])) continue; // Se procesan aparte o en el flujo dinámico
                if (!isset($parametrosValidos[$idpar])) continue;
                
                if (isset($procesados["$dp->iddia-$idpar"])) continue;
                $procesados["$dp->iddia-$idpar"] = true;

                $idperMapeado = $this->mapaPersonas[$dp->idper] ?? $dp->idper;
                $idperFinal = isset($personasRealesValidas[$idperMapeado]) ? $idperMapeado : $idRespaldo;

                $insertData[] = [
                    'iddia' => $dp->iddia,
                    'idpar' => $idpar,
                    'idper' => $idperFinal,
                    'valor' => $dp->valor,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
                $total++;

                if (count($insertData) >= $batchSize) {
                    DB::table('diapar')->insert($insertData);
                    $insertData = [];
                }
            }
        }

        if (!empty($insertData)) {
            DB::table('diapar')->insert($insertData);
        }
        $this->command->info("  Diapar: {$total} procesados.");
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
            }
        }
    }
}
