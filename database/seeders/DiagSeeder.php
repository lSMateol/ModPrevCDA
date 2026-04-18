<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DiagSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar tablas para evitar duplicados e inconsistencias y asegurar los números solicitados
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('historial')->truncate();
        DB::table('rechazo')->truncate();
        DB::table('diapar')->truncate();
        DB::table('diag')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Obtener IDs dinámicamente de la tabla persona según su perfil
        $creatorId   = DB::table('persona')->where('idpef', 2)->value('idper') ?? 2;  // Digitador
        $inspectorId = DB::table('persona')->where('idpef', 4)->value('idper') ?? 11; // Inspector
        $engineerId  = DB::table('persona')->where('idpef', 5)->value('idper') ?? 12; // Ingeniero
        
        // ==========================================
        // 1. SEED 5 APROBADOS
        // ==========================================
        for ($i = 1; $i <= 5; $i++) {
            $iddia = $i;
            DB::table('diag')->insert([
                'iddia'    => $iddia,
                // Los primeros 3 serán de HOY para que aparezcan en métricas y primera página
                'fecdia'   => $i <= 3 ? Carbon::now()->subHours($i + 1) : Carbon::now()->subDays($i + 5),
                'idveh'    => $i,
                'aprobado' => 1,
                'idper'    => $creatorId,
                'fecvig'   => Carbon::now()->addYear(),
                'kilomt'   => 10000 + ($i * 1000),
                'idinsp'   => $inspectorId,
                'iding'    => $engineerId,
                'iddiapar' => null,
                'dpiddia'  => null,
            ]);
            $this->seedParams($iddia, true);
        }

        // ==========================================
        // 2. SEED 5 NO APROBADOS (RECHAZADOS - NO REASIGNADOS)
        // ==========================================
        for ($i = 6; $i <= 10; $i++) {
            $iddia = $i;
            DB::table('diag')->insert([
                'iddia'    => $iddia,
                'fecdia'   => Carbon::now()->subDays($i),
                'idveh'    => $i,
                'aprobado' => 0,
                'idper'    => $creatorId,
                'fecvig'   => Carbon::now()->addYear(),
                'kilomt'   => 15000 + ($i * 1000),
                'idinsp'   => $inspectorId,
                'iding'    => $engineerId,
                'iddiapar' => null,
                'dpiddia'  => null,
            ]);
            $this->seedParams($iddia, false);
            
            DB::table('rechazo')->insert([
                'iddia'      => $iddia,
                'motivo'     => 'Defectos críticos detectados en la inspección visual.',
                'prioridad'  => 'Alta',
                'estadorec'  => 'Rechazado',
                'idper_ant'  => $inspectorId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ==========================================
        // 3. SEED 5 REASIGNADOS (BASADOS EN NO APROBADOS)
        // ==========================================
        for ($i = 11; $i <= 15; $i++) {
            $iddia = $i;
            $newPendingId = $i + 5; // Generará IDs 16 al 20
            
            DB::table('diag')->insert([
                'iddia'    => $iddia,
                'fecdia'   => Carbon::now()->subDays($i),
                'idveh'    => ($i - 10), 
                'aprobado' => 0,
                'idper'    => $creatorId,
                'fecvig'   => Carbon::now()->addYear(),
                'kilomt'   => 20000 + ($i * 1000),
                'idinsp'   => $inspectorId,
                'iding'    => $engineerId,
                'iddiapar' => null,
                'dpiddia'  => null,
            ]);
            $this->seedParams($iddia, false);
            
            DB::table('rechazo')->insert([
                'iddia'      => $iddia,
                'motivo'     => 'Requiere segunda opinión técnica por discrepancia en resultados.',
                'prioridad'  => 'Media',
                'estadorec'  => 'Reasignado',
                'idper_ant'  => $inspectorId,
                'idper_nvo'  => 11,
                'fecreasig'  => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // ==========================================
            // 4. SEED 5 PENDIENTES
            // ==========================================
            DB::table('diag')->insert([
                'iddia'    => $newPendingId,
                // Los ponemos como muy recientes (hace minutos/horas) de HOY
                'fecdia'   => Carbon::now()->subMinutes(30 * $i),
                'idveh'    => ($i - 10),
                'aprobado' => null, // PENDIENTE (null)
                'idper'    => $creatorId,
                'fecvig'   => Carbon::now()->addYear(),
                'kilomt'   => 20000 + ($i * 1000),
                'idinsp'   => $inspectorId,
                'iding'    => $engineerId,
                'iddiapar' => null,
                'dpiddia'  => $iddia,
            ]);
        }

        // Historial básico
        DB::table('historial')->insert([
            ['tabla_ref'=>'diag','id_ref'=>1,'accion'=>'Aprobación','descripcion'=>'Diagnóstico aprobado automáticamente.','idper'=>2,'es_sistema'=>true,'created_at'=>now(), 'updated_at'=>now()],
            ['tabla_ref'=>'diag','id_ref'=>6,'accion'=>'Rechazo','descripcion'=>'Vehículo no cumple con estándares de seguridad.','idper'=>11,'es_sistema'=>false,'created_at'=>now(), 'updated_at'=>now()],
            ['tabla_ref'=>'diag','id_ref'=>11,'accion'=>'Reasignación','descripcion'=>'Se solicita revisión por parte de otro inspector.','idper'=>2,'es_sistema'=>false,'created_at'=>now(), 'updated_at'=>now()],
        ]);
    }

    private function seedParams($iddia, $isApproved)
    {
        $creatorId = DB::table('persona')->where('idpef', 2)->value('idper') ?? 2;
        $params = [
            ['idpar' => 1, 'v_ok' => 'funciona', 'v_fail' => 'no_funciona'],
            ['idpar' => 2, 'v_ok' => 'funciona', 'v_fail' => 'no_funciona'],
            ['idpar' => 3, 'v_ok' => '75.5',     'v_fail' => '60.0'],
            ['idpar' => 4, 'v_ok' => '3800',     'v_fail' => '2000'],
            ['idpar' => 5, 'v_ok' => '3.50',     'v_fail' => '5.00'],
            ['idpar' => 10,'v_ok' => 'no',       'v_fail' => 'si'],
        ];

        foreach ($params as $p) {
            DB::table('diapar')->insert([
                'iddia' => $iddia,
                'idpar' => $p['idpar'],
                'idper' => $creatorId,
                'valor' => $isApproved ? $p['v_ok'] : $p['v_fail'],
            ]);
        }
        
        if (!$isApproved) {
            DB::table('diapar')->insert([
                ['iddia' => $iddia, 'idpar' => 12, 'idper' => $creatorId, 'valor' => 'Suspensión'],
                ['iddia' => $iddia, 'idpar' => 13, 'idper' => $creatorId, 'valor' => 'Fuga de aceite'],
                ['iddia' => $iddia, 'idpar' => 14, 'idper' => $creatorId, 'valor' => 'Se observa goteo constante en amortiguador trasero derecho.'],
            ]);
        }
    }
}
