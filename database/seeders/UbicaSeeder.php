<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UbicaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ubica')->insert([
            ['codubi' => 1, 'nomubi' => 'Bogotá D.C.',    'depubi' => null],
            ['codubi' => 2, 'nomubi' => 'Medellín',        'depubi' => null],
            ['codubi' => 3, 'nomubi' => 'Cali',            'depubi' => null],
            ['codubi' => 4, 'nomubi' => 'Barranquilla',    'depubi' => null],
            ['codubi' => 5, 'nomubi' => 'Cartagena',       'depubi' => null],
        ]);
    }
}
