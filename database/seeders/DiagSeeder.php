<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DiagSeeder extends Seeder
{
    public function run(): void
    {
        // Ya no limpiamos tablas, sólo insertamos o actualizamos
        // para mantener idempotencia y no borrar historial real

        // Obtener IDs de personas usando llaves naturales si están (para demo, sabemos que son digitador, etc.)
        // La llave natural es ndocper, busquemos los que definimos en PersonaSeeder
        $creatorId   = DB::table('persona')->where('ndocper', 10000002)->value('idper') ?? 2; // Carlos Ruiz (Digitador)
        $inspectorId = DB::table('persona')->where('ndocper', 10000011)->value('idper') ?? 11; // Andrés Vargas (Inspector)
        $engineerId  = DB::table('persona')->where('ndocper', 10000012)->value('idper') ?? 12; // María Gómez (Ingeniero)
        
        // Placas de vehículos creadas en VehiculoSeeder
        $placasAprobados = ['XYZ123', 'ABC987', 'DEF456', 'GHI312', 'KVM091'];
        $placasNoAprobados = ['DTS442', 'FGT102', 'HTY092', 'MKL445', 'RTY990'];
        $placasReasignados = ['XYZ123', 'ABC987', 'DEF456', 'GHI312', 'KVM091'];
        
        // ==========================================
        // 1. SEED 5 APROBADOS
        // ==========================================
        foreach ($placasAprobados as $index => $placa) {
            $i = $index + 1;
            $iddia = $i; // Usamos un ID fijo para relacionar demo data consistentemente
            $idveh = DB::table('vehiculo')->where('placaveh', $placa)->value('idveh');
            
            if (!$idveh) continue;

            DB::table('diag')->updateOrInsert(
                ['iddia' => $iddia],
                [
                    // Los primeros 3 serán de HOY
                    'fecdia'   => $i <= 3 ? Carbon::now()->subHours($i + 1) : Carbon::now()->subDays($i + 5),
                    'idveh'    => $idveh,
                    'aprobado' => 1,
                    'idper'    => $creatorId,
                    'fecvig'   => Carbon::now()->addYear(),
                    'kilomt'   => 10000 + ($i * 1000),
                    'idinsp'   => $inspectorId,
                    'iding'    => $engineerId,
                    'iddiapar' => null,
                    'dpiddia'  => null,
                ]
            );
            $this->seedParams($iddia, true, $creatorId);
        }

        // ==========================================
        // 2. SEED 5 NO APROBADOS (RECHAZADOS - NO REASIGNADOS)
        // ==========================================
        foreach ($placasNoAprobados as $index => $placa) {
            $i = $index + 6;
            $iddia = $i;
            $idveh = DB::table('vehiculo')->where('placaveh', $placa)->value('idveh');
            
            if (!$idveh) continue;

            DB::table('diag')->updateOrInsert(
                ['iddia' => $iddia],
                [
                    'fecdia'   => Carbon::now()->subDays($i),
                    'idveh'    => $idveh,
                    'aprobado' => 0,
                    'idper'    => $creatorId,
                    'fecvig'   => Carbon::now()->addYear(),
                    'kilomt'   => 15000 + ($i * 1000),
                    'idinsp'   => $inspectorId,
                    'iding'    => $engineerId,
                    'iddiapar' => null,
                    'dpiddia'  => null,
                ]
            );
            $this->seedParams($iddia, false, $creatorId);
            
            DB::table('rechazo')->updateOrInsert(
                ['iddia' => $iddia],
                [
                    'motivo'     => 'Defectos críticos detectados en la inspección visual.',
                    'prioridad'  => 'Alta',
                    'estadorec'  => 'Rechazado',
                    'idper_ant'  => $inspectorId,
                    'updated_at' => now(),
                ]
            );
        }

        // ==========================================
        // 3. SEED 5 REASIGNADOS (BASADOS EN NO APROBADOS)
        // ==========================================
        foreach ($placasReasignados as $index => $placa) {
            $i = $index + 11;
            $iddia = $i;
            $newPendingId = $i + 5; // Generará IDs 16 al 20
            $idveh = DB::table('vehiculo')->where('placaveh', $placa)->value('idveh');
            
            if (!$idveh) continue;

            DB::table('diag')->updateOrInsert(
                ['iddia' => $iddia],
                [
                    'fecdia'   => Carbon::now()->subDays($i),
                    'idveh'    => $idveh,
                    'aprobado' => 0,
                    'idper'    => $creatorId,
                    'fecvig'   => Carbon::now()->addYear(),
                    'kilomt'   => 20000 + ($i * 1000),
                    'idinsp'   => $inspectorId,
                    'iding'    => $engineerId,
                    'iddiapar' => null,
                    'dpiddia'  => null,
                ]
            );
            $this->seedParams($iddia, false, $creatorId);
            
            DB::table('rechazo')->updateOrInsert(
                ['iddia' => $iddia],
                [
                    'motivo'     => 'Requiere segunda opinión técnica por discrepancia en resultados.',
                    'prioridad'  => 'Media',
                    'estadorec'  => 'Reasignado',
                    'idper_ant'  => $inspectorId,
                    'idper_nvo'  => 11,
                    'fecreasig'  => now(),
                    'updated_at' => now(),
                ]
            );

            // ==========================================
            // 4. SEED 5 PENDIENTES
            // ==========================================
            DB::table('diag')->updateOrInsert(
                ['iddia' => $newPendingId],
                [
                    'fecdia'   => Carbon::now()->subMinutes(30 * $i),
                    'idveh'    => $idveh,
                    'aprobado' => null, // PENDIENTE
                    'idper'    => $creatorId,
                    'fecvig'   => Carbon::now()->addYear(),
                    'kilomt'   => 20000 + ($i * 1000),
                    'idinsp'   => $inspectorId,
                    'iding'    => $engineerId,
                    'iddiapar' => null,
                    'dpiddia'  => $iddia,
                ]
            );
        }

        // Historial básico
        $historiales = [
            ['tabla_ref'=>'diag','id_ref'=>1,'accion'=>'Aprobación','descripcion'=>'Diagnóstico aprobado automáticamente.','idper'=>2,'es_sistema'=>true],
            ['tabla_ref'=>'diag','id_ref'=>6,'accion'=>'Rechazo','descripcion'=>'Vehículo no cumple con estándares de seguridad.','idper'=>11,'es_sistema'=>false],
            ['tabla_ref'=>'diag','id_ref'=>11,'accion'=>'Reasignación','descripcion'=>'Se solicita revisión por parte de otro inspector.','idper'=>2,'es_sistema'=>false],
        ];

        foreach ($historiales as $hist) {
            DB::table('historial')->updateOrInsert(
                ['tabla_ref' => $hist['tabla_ref'], 'id_ref' => $hist['id_ref'], 'accion' => $hist['accion']],
                array_merge($hist, ['updated_at' => now()])
            );
        }
    }

    private function seedParams($iddia, $isApproved, $creatorId)
    {
        $params = [
            ['idpar' => 1, 'v_ok' => 'funciona', 'v_fail' => 'no_funciona'],
            ['idpar' => 2, 'v_ok' => 'funciona', 'v_fail' => 'no_funciona'],
            ['idpar' => 3, 'v_ok' => '75.5',     'v_fail' => '60.0'],
            ['idpar' => 4, 'v_ok' => '3800',     'v_fail' => '2000'],
            ['idpar' => 5, 'v_ok' => '3.50',     'v_fail' => '5.00'],
            ['idpar' => 10,'v_ok' => 'no',       'v_fail' => 'si'],
        ];

        foreach ($params as $p) {
            DB::table('diapar')->updateOrInsert(
                ['iddia' => $iddia, 'idpar' => $p['idpar']],
                [
                    'idper' => $creatorId,
                    'valor' => $isApproved ? $p['v_ok'] : $p['v_fail'],
                ]
            );
        }
        
        if (!$isApproved) {
            $failParams = [
                ['idpar' => 12, 'valor' => 'Suspensión'],
                ['idpar' => 13, 'valor' => 'Fuga de aceite'],
                ['idpar' => 14, 'valor' => 'Se observa goteo constante en amortiguador trasero derecho.'],
            ];
            foreach ($failParams as $fp) {
                DB::table('diapar')->updateOrInsert(
                    ['iddia' => $iddia, 'idpar' => $fp['idpar']],
                    [
                        'idper' => $creatorId,
                        'valor' => $fp['valor'],
                    ]
                );
            }
        }
    }
}
