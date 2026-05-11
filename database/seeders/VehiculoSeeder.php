<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehiculoSeeder extends Seeder
{
    public function run(): void
    {
        // Los datos de prueba estáticos han sido eliminados.
        // La población de la tabla 'vehiculo' y la resolución dinámica de llaves foráneas 
        // (incluyendo el mapeo a los nuevos perfiles unificados ID 8 para 'prop' y 'cond')
        // se realizará exclusivamente a través del LegacyImportSeeder.
    }
}