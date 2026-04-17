<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehiculoSeeder extends Seeder
{
    public function run(): void
    {
        // AJUSTE DE IDS SEGÚN PDF:
        // Clase (clveh): AUTOMOVIL = 2, BUS = 3, CAMION = 7, CAMIONETA = 9, MICROBUS = 20
        // Combustible (combuveh): GASOLINA = 37, DIESEL = 43
        // Carga (crgveh): LIVIANO = 91, PESADO = 92

        // tipo_servicio: 1 = Particular, 2 = Público
        // Público → empresa obligatoria | Particular → empresa opcional

        $vehiculos = [
            ['idveh'=>1,  'nordveh'=>'V-001', 'tipoveh'=>1, 'tipo_servicio'=>2, 'placaveh'=>'XYZ123', 'linveh'=>1, 'modveh'=>2019, 'idemp'=>3, 'clveh'=>2,  'combuveh'=>37, 'prop'=>9,  'cond'=>7,  'soat'=>'SOAT-001', 'fecvens'=>'2024-12-31', 'tecmecveh'=>'TEC-001', 'fecvent'=>'2024-10-15', 'extcontveh'=>'EXT-001', 'fecvene'=>'2025-01-20', 'lictraveh'=>'LIC-001', 'polaveh'=>1, 'blinveh'=>2, 'paiveh'=>'COLOMBIA', 'crgveh'=>91, 'tmotveh'=>101],
            ['idveh'=>2,  'nordveh'=>'V-002', 'tipoveh'=>1, 'tipo_servicio'=>2, 'placaveh'=>'ABC987', 'linveh'=>1, 'modveh'=>2020, 'idemp'=>2, 'clveh'=>2,  'combuveh'=>37, 'prop'=>10, 'cond'=>10, 'soat'=>'SOAT-002', 'fecvens'=>'2024-03-15', 'tecmecveh'=>'TEC-002', 'fecvent'=>'2023-12-05', 'extcontveh'=>'EXT-002', 'fecvene'=>'2024-02-10', 'lictraveh'=>'LIC-002', 'polaveh'=>1, 'blinveh'=>2, 'paiveh'=>'COLOMBIA', 'crgveh'=>91, 'tmotveh'=>101],
            ['idveh'=>3,  'nordveh'=>'V-003', 'tipoveh'=>1, 'tipo_servicio'=>2, 'placaveh'=>'DEF456', 'linveh'=>2, 'modveh'=>2021, 'idemp'=>4, 'clveh'=>3,  'combuveh'=>43, 'prop'=>7,  'cond'=>8,  'soat'=>'SOAT-003', 'fecvens'=>'2025-07-20', 'tecmecveh'=>'TEC-003', 'fecvent'=>'2024-08-05', 'extcontveh'=>'EXT-003', 'fecvene'=>'2025-06-12', 'lictraveh'=>'LIC-003', 'polaveh'=>1, 'blinveh'=>1, 'paiveh'=>'COLOMBIA', 'crgveh'=>92, 'tmotveh'=>101],
            ['idveh'=>4,  'nordveh'=>'V-004', 'tipoveh'=>1, 'tipo_servicio'=>2, 'placaveh'=>'GHI312', 'linveh'=>7, 'modveh'=>2022, 'idemp'=>3, 'clveh'=>2,  'combuveh'=>37, 'prop'=>9,  'cond'=>8,  'soat'=>'SOAT-004', 'fecvens'=>'2025-09-01', 'tecmecveh'=>'TEC-004', 'fecvent'=>'2025-01-15', 'extcontveh'=>'EXT-004', 'fecvene'=>'2025-08-20', 'lictraveh'=>'LIC-004', 'polaveh'=>1, 'blinveh'=>2, 'paiveh'=>'COLOMBIA', 'crgveh'=>91, 'tmotveh'=>101],
            ['idveh'=>5,  'nordveh'=>'V-005', 'tipoveh'=>2, 'tipo_servicio'=>2, 'placaveh'=>'KVM091', 'linveh'=>3, 'modveh'=>2018, 'idemp'=>1, 'clveh'=>7,  'combuveh'=>43, 'prop'=>9,  'cond'=>7,  'soat'=>'SOAT-005', 'fecvens'=>'2023-10-12', 'tecmecveh'=>'TEC-005', 'fecvent'=>'2023-12-05', 'extcontveh'=>'EXT-005', 'fecvene'=>'2024-01-22', 'lictraveh'=>'LIC-005', 'polaveh'=>1, 'blinveh'=>2, 'paiveh'=>'COLOMBIA', 'crgveh'=>92, 'tmotveh'=>101],
            ['idveh'=>6,  'nordveh'=>'V-006', 'tipoveh'=>2, 'tipo_servicio'=>2, 'placaveh'=>'DTS442', 'linveh'=>4, 'modveh'=>2017, 'idemp'=>2, 'clveh'=>7,  'combuveh'=>43, 'prop'=>10, 'cond'=>8,  'soat'=>'SOAT-006', 'fecvens'=>'2024-03-15', 'tecmecveh'=>'TEC-006', 'fecvent'=>'2023-11-02', 'extcontveh'=>'EXT-006', 'fecvene'=>'2024-02-10', 'lictraveh'=>'LIC-006', 'polaveh'=>1, 'blinveh'=>2, 'paiveh'=>'COLOMBIA', 'crgveh'=>92, 'tmotveh'=>101],
            ['idveh'=>7,  'nordveh'=>'V-007', 'tipoveh'=>1, 'tipo_servicio'=>2, 'placaveh'=>'FGT102', 'linveh'=>1, 'modveh'=>2020, 'idemp'=>3, 'clveh'=>2,  'combuveh'=>37, 'prop'=>7,  'cond'=>7,  'soat'=>'SOAT-007', 'fecvens'=>'2024-07-20', 'tecmecveh'=>'TEC-007', 'fecvent'=>'2024-08-05', 'extcontveh'=>'EXT-007', 'fecvene'=>'2024-06-12', 'lictraveh'=>'LIC-007', 'polaveh'=>1, 'blinveh'=>2, 'paiveh'=>'COLOMBIA', 'crgveh'=>91, 'tmotveh'=>101],
            ['idveh'=>8,  'nordveh'=>'V-008', 'tipoveh'=>1, 'tipo_servicio'=>1, 'placaveh'=>'HTY092', 'linveh'=>2, 'modveh'=>2019, 'idemp'=>null, 'clveh'=>2,  'combuveh'=>43, 'prop'=>9,  'cond'=>8,  'soat'=>'SOAT-008', 'fecvens'=>'2024-10-30', 'tecmecveh'=>'TEC-008', 'fecvent'=>'2024-10-15', 'extcontveh'=>'EXT-008', 'fecvene'=>'2025-01-10', 'lictraveh'=>'LIC-008', 'polaveh'=>1, 'blinveh'=>2, 'paiveh'=>'COLOMBIA', 'crgveh'=>92, 'tmotveh'=>101],
            ['idveh'=>9,  'nordveh'=>'V-009', 'tipoveh'=>1, 'tipo_servicio'=>2, 'placaveh'=>'MKL445', 'linveh'=>5, 'modveh'=>2015, 'idemp'=>4, 'clveh'=>2,  'combuveh'=>37, 'prop'=>10, 'cond'=>10, 'soat'=>'SOAT-009', 'fecvens'=>'2025-05-10', 'tecmecveh'=>'TEC-009', 'fecvent'=>'2025-01-20', 'extcontveh'=>'EXT-009', 'fecvene'=>'2025-04-15', 'lictraveh'=>'LIC-009', 'polaveh'=>1, 'blinveh'=>2, 'paiveh'=>'COLOMBIA', 'crgveh'=>91, 'tmotveh'=>101],
            ['idveh'=>10, 'nordveh'=>'V-010', 'tipoveh'=>1, 'tipo_servicio'=>2, 'placaveh'=>'RTY990', 'linveh'=>5, 'modveh'=>2021, 'idemp'=>2, 'clveh'=>2,  'combuveh'=>37, 'prop'=>7,  'cond'=>8,  'soat'=>'SOAT-010', 'fecvens'=>'2025-03-25', 'tecmecveh'=>'TEC-010', 'fecvent'=>'2025-02-10', 'extcontveh'=>'EXT-010', 'fecvene'=>'2025-03-01', 'lictraveh'=>'LIC-010', 'polaveh'=>1, 'blinveh'=>2, 'paiveh'=>'COLOMBIA', 'crgveh'=>91, 'tmotveh'=>101],
            ['idveh'=>11, 'nordveh'=>'V-011', 'tipoveh'=>1, 'tipo_servicio'=>1, 'placaveh'=>'BCP112', 'linveh'=>6, 'modveh'=>2017, 'idemp'=>null, 'clveh'=>2,  'combuveh'=>37, 'prop'=>9,  'cond'=>9,  'soat'=>'SOAT-011', 'fecvens'=>'2025-06-15', 'tecmecveh'=>'TEC-011', 'fecvent'=>'2025-01-30', 'extcontveh'=>'EXT-011', 'fecvene'=>'2025-05-20', 'lictraveh'=>'LIC-011', 'polaveh'=>1, 'blinveh'=>2, 'paiveh'=>'COLOMBIA', 'crgveh'=>91, 'tmotveh'=>101],
        ];

        foreach ($vehiculos as $v) {
            DB::table('vehiculo')->updateOrInsert(['idveh' => $v['idveh']], $v);
        }

        // Vincular propietarios
        foreach ($vehiculos as $v) {
             DB::table('proveh')->updateOrInsert(
                ['idveh' => $v['idveh'], 'idper' => $v['prop']]
             );
        }
    }
}