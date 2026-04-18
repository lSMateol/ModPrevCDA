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

        $creatorId = 2; // Carlos Ruiz (Digitador)
        $inspectorId = 11; // Andrés Vargas (Inspector)
        $engineerId = 12; // María Gómez (Ingeniero)
        
        // ==========================================
        // 1. SEED 5 APROBADOS
        // ==========================================
        for ($i = 1; $i <= 5; $i++) {
            $iddia = $i;
            DB::table('diag')->insert([
                'iddia'    => $iddia,
                'fecdia'   => Carbon::now()->subDays(20 + $i),
                'idveh'    => $i, // Vehículos 1 al 5
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
                'fecdia'   => Carbon::now()->subDays(15 + $i),
                'idveh'    => $i, // Vehículos 6 al 10
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
            
            // El diagnóstico original que fue rechazado y luego reasignado
            DB::table('diag')->insert([
                'iddia'    => $iddia,
                'fecdia'   => Carbon::now()->subDays(10 + $i),
                'idveh'    => ($i - 10), // Reutilizamos vehículos 1 al 5 para segundas revisiones
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
            // Creamos los nuevos diagnósticos que resultaron de la reasignación
            DB::table('diag')->insert([
                'iddia'    => $newPendingId,
                'fecdia'   => Carbon::now()->subMinutes(60 * $i),
                'idveh'    => ($i - 10),
                'aprobado' => null, // PENDIENTE
                'idper'    => $creatorId,
                'fecvig'   => Carbon::now()->addYear(),
                'kilomt'   => 20000 + ($i * 1000),
                'idinsp'   => $inspectorId,
                'iding'    => $engineerId,
                'iddiapar' => null,
                'dpiddia'  => $iddia, // Referencia al diagnóstico padre (el reasignado)
            ]);
        }

        // Historial básico
        DB::table('historial')->insert([
            ['tabla_ref'=>'diag','id_ref'=>1,'accion'=>'Aprobación','descripcion'=>'Diagnóstico aprobado automáticamente.','idper'=>2,'es_sistema'=>true,'created_at'=>now(), 'updated_at'=>now()],
            ['tabla_ref'=>'diag','id_ref'=>6,'accion'=>'Rechazo','descripcion'=>'Vehículo no cumple con estándares de seguridad.','idper'=>11,'es_sistema'=>false,'created_at'=>now(), 'updated_at'=>now()],
            ['tabla_ref'=>'diag','id_ref'=>11,'accion'=>'Reasignación','descripcion'=>'Se solicita revisión por parte de otro inspector.','idper'=>2,'es_sistema'=>false,'created_at'=>now(), 'updated_at'=>now()],
        ]);
    }

    /**
     * Helper para insertar parámetros de diagnóstico
     */
    private function seedParams($iddia, $isApproved)
    {
        $creatorId = 2;
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
