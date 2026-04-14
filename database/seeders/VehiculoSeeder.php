<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehiculoSeeder extends Seeder
{
    public function run(): void
    {
        $vehiculos = [
            ['idveh'=>1,  'nordveh'=>'V-001', 'tipoveh'=>1, 'placaveh'=>'XYZ123', 'linveh'=>1, 'modveh'=>2019, 'idemp'=>3, 'clveh'=>2,  'combuveh'=>91, 'prop'=>9,  'cond'=>7,  'soat'=>'SOAT-001', 'fecvens'=>'2024-12-31', 'tecmecveh'=>'TEC-001', 'fecvent'=>'2024-10-15', 'extcontveh'=>'EXT-001', 'fecvene'=>'2025-01-20', 'lictraveh'=>'LIC-001', 'polaveh'=>1, 'blinveh'=>2, 'paiveh'=>'COLOMBIA', 'crgveh'=>111, 'tmotveh'=>101],
            ['idveh'=>2,  'nordveh'=>'V-002', 'tipoveh'=>1, 'placaveh'=>'ABC987', 'linveh'=>1, 'modveh'=>2020, 'idemp'=>2, 'clveh'=>1,  'combuveh'=>91, 'prop'=>10, 'cond'=>10, 'soat'=>'SOAT-002', 'fecvens'=>'2024-03-15', 'tecmecveh'=>'TEC-002', 'fecvent'=>'2023-12-05', 'extcontveh'=>'EXT-002', 'fecvene'=>'2024-02-10', 'lictraveh'=>'LIC-002', 'polaveh'=>1, 'blinveh'=>2, 'paiveh'=>'COLOMBIA', 'crgveh'=>111, 'tmotveh'=>101],
            ['idveh'=>3,  'nordveh'=>'V-003', 'tipoveh'=>1, 'placaveh'=>'DEF456', 'linveh'=>2, 'modveh'=>2021, 'idemp'=>4, 'clveh'=>2,  'combuveh'=>92, 'prop'=>7,  'cond'=>8,  'soat'=>'SOAT-003', 'fecvens'=>'2025-07-20', 'tecmecveh'=>'TEC-003', 'fecvent'=>'2024-08-05', 'extcontveh'=>'EXT-003', 'fecvene'=>'2025-06-12', 'lictraveh'=>'LIC-003', 'polaveh'=>1, 'blinveh'=>1, 'paiveh'=>'COLOMBIA', 'crgveh'=>112, 'tmotveh'=>101],
            ['idveh'=>4,  'nordveh'=>'V-004', 'tipoveh'=>1, 'placaveh'=>'GHI312', 'linveh'=>7, 'modveh'=>2022, 'idemp'=>3, 'clveh'=>2,  'combuveh'=>91, 'prop'=>9,  'cond'=>8,  'soat'=>'SOAT-004', 'fecvens'=>'2025-09-01', 'tecmecveh'=>'TEC-004', 'fecvent'=>'2025-01-15', 'extcontveh'=>'EXT-004', 'fecvene'=>'2025-08-20', 'lictraveh'=>'LIC-004', 'polaveh'=>1, 'blinveh'=>2, 'paiveh'=>'COLOMBIA', 'crgveh'=>111, 'tmotveh'=>101],
            ['idveh'=>5,  'nordveh'=>'V-005', 'tipoveh'=>2, 'placaveh'=>'KVM091', 'linveh'=>3, 'modveh'=>2018, 'idemp'=>1, 'clveh'=>4,  'combuveh'=>92, 'prop'=>9,  'cond'=>7,  'soat'=>'SOAT-005', 'fecvens'=>'2023-10-12', 'tecmecveh'=>'TEC-005', 'fecvent'=>'2023-12-05', 'extcontveh'=>'EXT-005', 'fecvene'=>'2024-01-22', 'lictraveh'=>'LIC-005', 'polaveh'=>1, 'blinveh'=>2, 'paiveh'=>'COLOMBIA', 'crgveh'=>112, 'tmotveh'=>101],
            ['idveh'=>6,  'nordveh'=>'V-006', 'tipoveh'=>2, 'placaveh'=>'DTS442', 'linveh'=>4, 'modveh'=>2017, 'idemp'=>2, 'clveh'=>4,  'combuveh'=>92, 'prop'=>10, 'cond'=>8,  'soat'=>'SOAT-006', 'fecvens'=>'2024-03-15', 'tecmecveh'=>'TEC-006', 'fecvent'=>'2023-11-02', 'extcontveh'=>'EXT-006', 'fecvene'=>'2024-02-10', 'lictraveh'=>'LIC-006', 'polaveh'=>1, 'blinveh'=>2, 'paiveh'=>'COLOMBIA', 'crgveh'=>112, 'tmotveh'=>101],
            ['idveh'=>7,  'nordveh'=>'V-007', 'tipoveh'=>1, 'placaveh'=>'FGT102', 'linveh'=>1, 'modveh'=>2020, 'idemp'=>3, 'clveh'=>1,  'combuveh'=>91, 'prop'=>7,  'cond'=>7,  'soat'=>'SOAT-007', 'fecvens'=>'2024-07-20', 'tecmecveh'=>'TEC-007', 'fecvent'=>'2024-08-05', 'extcontveh'=>'EXT-007', 'fecvene'=>'2024-06-12', 'lictraveh'=>'LIC-007', 'polaveh'=>1, 'blinveh'=>2, 'paiveh'=>'COLOMBIA', 'crgveh'=>111, 'tmotveh'=>101],
            ['idveh'=>8,  'nordveh'=>'V-008', 'tipoveh'=>1, 'placaveh'=>'HTY092', 'linveh'=>2, 'modveh'=>2019, 'idemp'=>1, 'clveh'=>2,  'combuveh'=>92, 'prop'=>9,  'cond'=>8,  'soat'=>'SOAT-008', 'fecvens'=>'2024-10-30', 'tecmecveh'=>'TEC-008', 'fecvent'=>'2024-10-15', 'extcontveh'=>'EXT-008', 'fecvene'=>'2025-01-10', 'lictraveh'=>'LIC-008', 'polaveh'=>1, 'blinveh'=>2, 'paiveh'=>'COLOMBIA', 'crgveh'=>112, 'tmotveh'=>101],
            ['idveh'=>9,  'nordveh'=>'V-009', 'tipoveh'=>1, 'placaveh'=>'MKL445', 'linveh'=>5, 'modveh'=>2015, 'idemp'=>4, 'clveh'=>1,  'combuveh'=>91, 'prop'=>10, 'cond'=>10, 'soat'=>'SOAT-009', 'fecvens'=>'2025-05-10', 'tecmecveh'=>'TEC-009', 'fecvent'=>'2025-01-20', 'extcontveh'=>'EXT-009', 'fecvene'=>'2025-04-15', 'lictraveh'=>'LIC-009', 'polaveh'=>1, 'blinveh'=>2, 'paiveh'=>'COLOMBIA', 'crgveh'=>111, 'tmotveh'=>101],
            ['idveh'=>10, 'nordveh'=>'V-010', 'tipoveh'=>1, 'placaveh'=>'RTY990', 'linveh'=>5, 'modveh'=>2021, 'idemp'=>2, 'clveh'=>1,  'combuveh'=>91, 'prop'=>7,  'cond'=>8,  'soat'=>'SOAT-010', 'fecvens'=>'2025-03-25', 'tecmecveh'=>'TEC-010', 'fecvent'=>'2025-02-10', 'extcontveh'=>'EXT-010', 'fecvene'=>'2025-03-01', 'lictraveh'=>'LIC-010', 'polaveh'=>1, 'blinveh'=>2, 'paiveh'=>'COLOMBIA', 'crgveh'=>111, 'tmotveh'=>101],
            ['idveh'=>11, 'nordveh'=>'V-011', 'tipoveh'=>1, 'placaveh'=>'BCP112', 'linveh'=>6, 'modveh'=>2017, 'idemp'=>1, 'clveh'=>1,  'combuveh'=>91, 'prop'=>9,  'cond'=>9,  'soat'=>'SOAT-011', 'fecvens'=>'2025-06-15', 'tecmecveh'=>'TEC-011', 'fecvent'=>'2025-01-30', 'extcontveh'=>'EXT-011', 'fecvene'=>'2025-05-20', 'lictraveh'=>'LIC-011', 'polaveh'=>1, 'blinveh'=>2, 'paiveh'=>'COLOMBIA', 'crgveh'=>111, 'tmotveh'=>101],
        ];

        foreach ($vehiculos as $v) {
            DB::table('vehiculo')->insert($v);
        }

        // Vincular propietarios
        DB::table('proveh')->insert([
            ['idveh' => 1,  'idper' => 9],
            ['idveh' => 2,  'idper' => 10],
            ['idveh' => 3,  'idper' => 7],
            ['idveh' => 4,  'idper' => 9],
            ['idveh' => 5,  'idper' => 9],
            ['idveh' => 6,  'idper' => 10],
            ['idveh' => 7,  'idper' => 7],
            ['idveh' => 8,  'idper' => 9],
            ['idveh' => 9,  'idper' => 10],
            ['idveh' => 10, 'idper' => 7],
            ['idveh' => 11, 'idper' => 9],
        ]);
    }
}
