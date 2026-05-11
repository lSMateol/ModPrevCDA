<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonaSeeder extends Seeder
{
    public function run(): void
    {
        // Los datos de prueba estáticos han sido eliminados.
        // La población de la tabla 'persona' ahora se realizará exclusivamente a través del 
        // LegacyImportSeeder, el cual aplicará las reglas de negocio de MUP (ID 8), 
        // consolidación de superadministradores y unificación de accesos al sistema.
    }
}
