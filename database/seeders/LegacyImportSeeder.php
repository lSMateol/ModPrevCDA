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
     */
    /**
     * Cache de mapeo de IDs legacy a IDs reales para evitar N+1 queries
     */
    protected $mapaPersonas = [];
    protected $idsVehiculos = [];

    public function run(): void
    {
        $this->command->info('Iniciando importación desde Legacy...');

        // 1. Asegurar que los maestros del nuevo sistema existan (Evita errores de FK)
        $this->command->info('Configurando Roles y Perfiles maestros...');
        $this->call(RoleSeeder::class);
        $this->call(PerfilSeeder::class);

        // 2. Diccionarios Básicos
        $this->importarTablaSimple('dominio', 'iddom');
        $this->importarTablaSimple('valor', 'idval');
        $this->importarTablaSimple('ubica', 'codubi');
        $this->importarTablaSimple('tippar', 'idtip');
        $this->importarTablaSimple('param', 'idpar');
        $this->importarTablaSimple('marca', 'idmar');
        
        // 3. Empresas
        $this->importarEmpresas();

        // 4. Personas (Corazón del ETL)
        $this->importarPersonas();

        // 5. Cargar mapa de personas en memoria para rapidez
        $this->cargarMapaPersonas();

        // 6. Vehículos
        $this->importarVehiculos();

        // 7. Cargar IDs de vehículos existentes en un mapa (O(1) lookup)
        $this->idsVehiculos = DB::table('vehiculo')->pluck('idveh', 'idveh')->toArray();

        // 8. Historial y Transacciones
        $this->importarTablaSimple('proveh', ['idveh', 'idper']);
        
        $this->importarDiagnosticos();
        $this->importarDiapar();
        $this->importarTablaSimple('foto', 'idfot');
        $this->importarMantenimientos();

        // 9. Asignar roles de Spatie a todos los usuarios
        $this->asignarRoles();

        $this->command->info('Importación finalizada con éxito.');
    }

    private function cargarMapaPersonas()
    {
        $this->command->info("Cargando mapa de personas en memoria...");
        $personasLegacy = DB::connection('legacy')->table('persona')->get(['idper', 'ndocper']);
        $personasReales = DB::table('persona')->get(['idper', 'ndocper'])->keyBy('ndocper');

        foreach ($personasLegacy as $p) {
            $real = $personasReales->get($p->ndocper);
            $this->mapaPersonas[$p->idper] = $real ? $real->idper : 1;
        }
    }

    private function importarTablaSimple($tabla, $pk)
    {
        $this->command->info("Importando tabla: {$tabla}");
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
        $this->command->info("Importando Empresas y creando usuarios...");
        $empresas = DB::connection('legacy')->table('empresa')->get();

        foreach ($empresas as $e) {
            $data = (array) $e;
            $data['idpef'] = 3;
            $data['usuaemp'] = $data['nonitem'] ?? 'emp_' . $data['idemp'];
            $data['passemp'] = Hash::make($data['usuaemp']);
            $data['ciudeem'] = 'NO REGISTRADA';

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
        $this->command->info("Importando Personas (Aplicando reglas ETL)...");
        $personas = DB::connection('legacy')->table('persona')->orderBy('idper')->get();

        foreach ($personas as $p) {
            $data = (array) $p;
            if ($data['idpef'] > 8 || $data['idpef'] == 0) { $data['idpef'] = 1; }
            $ndoc = $data['ndocper'];

            if (empty($ndoc)) {
                $this->command->warn("Saltando persona ID {$data['idper']} ({$data['nomper']}) porque no tiene documento.");
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
                $idReal = $personaExistente->idper;
            } else {
                $data['ciuper'] = 'NO REGISTRADA';
                unset($data['pass']);
                DB::table('persona')->insert($data);
                $idReal = DB::getPdo()->lastInsertId();
            }

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
        $this->command->info("Importando Vehículos...");
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
        $this->command->info("Importando Diagnósticos...");
        $diags = DB::connection('legacy')->table('diag')->get();

        foreach ($diags as $d) {
            $data = (array) $d;
            unset($data['idpun'], $data['idmaq']);

            $data['idper'] = $this->mapaPersonas[$data['idper'] ?? 0] ?? 1;
            $data['idinsp'] = $this->mapaPersonas[$data['idinsp'] ?? 0] ?? 1;
            $data['iding'] = $this->mapaPersonas[$data['iding'] ?? 0] ?? 1;

            if (isset($data['kilomt']) && $data['kilomt'] < 0) { $data['kilomt'] = 0; }

            if (isset($this->idsVehiculos[$data['idveh']])) {
                DB::table('diag')->updateOrInsert(['iddia' => $data['iddia']], $data);
            }
        }
    }

    private function importarDiapar()
    {
        $this->command->info("Importando Diapar (560k+ registros, usando cursor para velocidad)...");
        $idsDiag = DB::table('diag')->pluck('iddia', 'iddia')->toArray();
        
        $insertData = [];
        $batchSize = 2000;
        $total = 0;

        foreach (DB::connection('legacy')->table('diapar')->cursor() as $dp) {
            if (isset($idsDiag[$dp->iddia])) {
                $insertData[] = [
                    'iddia' => $dp->iddia,
                    'idpar' => $dp->idpar,
                    'idper' => $this->mapaPersonas[$dp->idper ?? 0] ?? 1,
                    'valor' => $dp->valor,
                    'created_at' => now(),
                    'updated_at' => now(),
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
        $this->command->info("Diapar finalizado. Procesados: {$total}");
    }

    private function importarMantenimientos()
    {
        $this->command->info("Importando Mantenimientos...");
        $mants = DB::connection('legacy')->table('mantenimiento')->get();

        foreach ($mants as $m) {
            $data = (array) $m;
            $data['created_at'] = $data['updated_at'] = $data['fechareg'] ?? now();
            unset($data['fechareg']);
            DB::table('mantenimientos')->updateOrInsert(['idmant' => $data['idmant']], $data);
        }
    }

    private function asignarRoles()
    {
        $this->command->info("Asignando roles de Spatie a los usuarios...");
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

        $this->command->info("Roles asignados correctamente: {$asignados} usuarios.");
    }
}
