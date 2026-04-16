<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DominioSeeder extends Seeder
{
    public function run(): void
    {
        // 1. SEEDER DE DOMINIOS (Basado exactamente en tu PDF)
        $dominios = [
            ['iddom' => 1,  'nomdom' => 'Clase de Vehiculo'],
            ['iddom' => 2,  'nomdom' => 'Tipo de Combustible'],
            ['iddom' => 3,  'nomdom' => 'Tipo de Vehiculo'],
            ['iddom' => 4,  'nomdom' => 'Tipo de Documento'],
            ['iddom' => 5,  'nomdom' => 'Categoría de Conductor'],
            ['iddom' => 6,  'nomdom' => 'Tipo de archivo'],
            ['iddom' => 7,  'nomdom' => 'Controles'],
            ['iddom' => 8,  'nomdom' => 'Radioacción'],
            ['iddom' => 9,  'nomdom' => 'Tipo de Motor'],
            ['iddom' => 10, 'nomdom' => 'Carga Vehículo'],
        ];

        foreach ($dominios as $dominio) {
            DB::table('dominio')->updateOrInsert(
                ['iddom' => $dominio['iddom']], 
                ['nomdom' => $dominio['nomdom']]
            );
        }

        // 2. SEEDER DE VALORES (Sincronizado 100% con PDF de exportación)
        $valores = [
            // Clase de Vehículo (iddom: 1)
            ['idval' => 2,  'iddom' => 1, 'nomval' => 'AUTOMOVIL',     'parval' => 'AUTO',  'actval' => 1],
            ['idval' => 3,  'iddom' => 1, 'nomval' => 'BUS',           'parval' => 'BUS',   'actval' => 1],
            ['idval' => 6,  'iddom' => 1, 'nomval' => 'BUSETA',        'parval' => 'BUSE',  'actval' => 1],
            ['idval' => 7,  'iddom' => 1, 'nomval' => 'CAMION',        'parval' => 'CMN',   'actval' => 1],
            ['idval' => 9,  'iddom' => 1, 'nomval' => 'CAMIONETA',     'parval' => 'CAM',   'actval' => 1],
            ['idval' => 20, 'iddom' => 1, 'nomval' => 'MICROBUS',      'parval' => 'MIC',   'actval' => 1],

            // Tipo de Combustible (iddom: 2)
            ['idval' => 37, 'iddom' => 2, 'nomval' => 'GASOLINA',      'parval' => 'GAS',   'actval' => 1],
            ['idval' => 43, 'iddom' => 2, 'nomval' => 'DIESEL',        'parval' => 'DIE',   'actval' => 1],
            ['idval' => 40, 'iddom' => 2, 'nomval' => 'GAS-NATURAL',   'parval' => 'GNV',   'actval' => 1],

            // Tipo de Motor (iddom: 9)
            ['idval' => 101, 'iddom' => 9, 'nomval' => '4 T',          'parval' => '4T',    'actval' => 1],
            ['idval' => 102, 'iddom' => 9, 'nomval' => '2 T',          'parval' => '2T',    'actval' => 1],

            // Carga Vehículo (iddom: 10)
            ['idval' => 91, 'iddom' => 10, 'nomval' => 'Liviano',      'parval' => 'LIV',   'actval' => 1],
            ['idval' => 92, 'iddom' => 10, 'nomval' => 'Pesado',       'parval' => 'PES',   'actval' => 1],

            // Tipo de Documento (iddom: 4)
            ['idval' => 57, 'iddom' => 4, 'nomval' => 'C.C.',          'parval' => 'CC',    'actval' => 1],
            ['idval' => 58, 'iddom' => 4, 'nomval' => 'NIT',           'parval' => 'NIT',   'actval' => 1],
        ];

        foreach ($valores as $valor) {
            DB::table('valor')->updateOrInsert(
                ['idval' => $valor['idval']], 
                [
                    'iddom'  => $valor['iddom'],
                    'nomval' => $valor['nomval'],
                    'parval' => $valor['parval'],
                    'actval' => $valor['actval']
                ]
            );
        }
    }
}