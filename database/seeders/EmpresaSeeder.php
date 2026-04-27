<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        // Los datos de prueba estáticos han sido eliminados.
        // La población de la tabla 'empresa' y la posterior creación obligatoria de sus accesos 
        // a la tabla 'users' se realizará exclusivamente a través del LegacyImportSeeder.
    }
}
