<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarcaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('marca')->insert([
            ['idmar' => 1, 'nommarlin' => 'Chevrolet Spark GT',  'depmar' => null],
            ['idmar' => 2, 'nommarlin' => 'Toyota Hilux',        'depmar' => null],
            ['idmar' => 3, 'nommarlin' => 'Hino Dutro',          'depmar' => null],
            ['idmar' => 4, 'nommarlin' => 'Kenworth T800',       'depmar' => null],
            ['idmar' => 5, 'nommarlin' => 'Mazda 3',             'depmar' => null],
            ['idmar' => 6, 'nommarlin' => 'Ford Fiesta',         'depmar' => null],
            ['idmar' => 7, 'nommarlin' => 'Renault Duster',      'depmar' => null],
        ]);
    }
}
