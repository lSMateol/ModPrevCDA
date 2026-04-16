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

        // 2. SEEDER DE VALORES (Relacionados con los IDs correctos del PDF)
        $valores = [
            // Clase de Vehículo (iddom: 1)
            ['idval' => 1,  'iddom' => 1, 'nomval' => 'Automóvil',     'parval' => 'AUTO',  'actval' => 1],
            ['idval' => 2,  'iddom' => 1, 'nomval' => 'Bus',           'parval' => 'BUS',   'actval' => 1],
            ['idval' => 3,  'iddom' => 1, 'nomval' => 'Buseta',        'parval' => 'BUSE',  'actval' => 1],
            ['idval' => 4,  'iddom' => 1, 'nomval' => 'Camión',        'parval' => 'CMN',   'actval' => 1],
            ['idval' => 5,  'iddom' => 1, 'nomval' => 'Camioneta',     'parval' => 'CAM',   'actval' => 1],
            ['idval' => 6,  'iddom' => 1, 'nomval' => 'Microbus',      'parval' => 'MIC',   'actval' => 1],

            // Tipo de Combustible (iddom: 2)
            ['idval' => 91, 'iddom' => 2, 'nomval' => 'Gasolina',      'parval' => 'GAS',   'actval' => 1],
            ['idval' => 92, 'iddom' => 2, 'nomval' => 'Diésel',        'parval' => 'DIE',   'actval' => 1],
            ['idval' => 93, 'iddom' => 2, 'nomval' => 'Gas Natural',   'parval' => 'GNV',   'actval' => 1],

            // Tipo de Motor (iddom: 9 - Corregido según PDF)
            ['idval' => 101,'iddom' => 9, 'nomval' => '4 Tiempos',     'parval' => '4T',    'actval' => 1],
            ['idval' => 102,'iddom' => 9, 'nomval' => '2 Tiempos',     'parval' => '2T',    'actval' => 1],

            // Carga Vehículo (iddom: 10 - Corregido según PDF)
            ['idval' => 111,'iddom' => 10, 'nomval' => 'Liviana',       'parval' => 'LIV',   'actval' => 1],
            ['idval' => 112,'iddom' => 10, 'nomval' => 'Pesada',        'parval' => 'PES',   'actval' => 1],
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