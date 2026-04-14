<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DominioSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('dominio')->insert([
            ['iddom' => 1, 'nomdom' => 'Clase de Vehículo'],
            ['iddom' => 2, 'nomdom' => 'Tipo de Combustible'],
            ['iddom' => 3, 'nomdom' => 'Tipo de Motor'],
            ['iddom' => 4, 'nomdom' => 'Tipo de Carga'],
            ['iddom'=> 5, 'nomdom'=> 'Carga de Vehiculo'],

            ]);

        DB::table('valor')->insert([
            // Clase de Vehículo
            ['idval' => 1,  'iddom' => 1, 'nomval' => 'Automóvil',     'parval' => 'AUTO',  'actval' => 1],
            ['idval' => 2,  'iddom' => 1, 'nomval' => 'Bus',           'parval' => 'BUS',   'actval' => 1],
            ['idval' => 3,  'iddom' => 1, 'nomval' => 'Buseta',        'parval' => 'BUSE',  'actval' => 1],
            ['idval' => 4,  'iddom' => 1, 'nomval' => 'Camión',        'parval' => 'CMN',   'actval' => 1],
            ['idval' => 5,  'iddom' => 1, 'nomval' => 'Camioneta',     'parval' => 'CAM',   'actval' => 1],
            ['idval' => 6,  'iddom' => 1, 'nomval' => 'Microbus',      'parval' => 'MIC',   'actval' => 1],
            // Combustible
            ['idval' => 91, 'iddom' => 2, 'nomval' => 'Gasolina',      'parval' => 'GAS',   'actval' => 1],
            ['idval' => 92, 'iddom' => 2, 'nomval' => 'Diésel',        'parval' => 'DIE',   'actval' => 1],
            ['idval' => 93, 'iddom' => 2, 'nomval' => 'Gas Natural',   'parval' => 'GNV',   'actval' => 1],
            // Tipo Motor
            ['idval' => 101,'iddom' => 3, 'nomval' => '4 Tiempos',     'parval' => '4T',    'actval' => 1],
            // Carga
            ['idval' => 111,'iddom' => 4, 'nomval' => 'Liviana',       'parval' => 'LIV',   'actval' => 1],
            ['idval' => 112,'iddom' => 4, 'nomval' => 'Pesada',        'parval' => 'PES',   'actval' => 1],
        ]);
    }
}
