<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PerfilSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('perfil')->insert([
            ['idpef' => 1, 'nompef' => 'Administrador',     'pagpri' => null],
            ['idpef' => 2, 'nompef' => 'Digitador',         'pagpri' => null],
            ['idpef' => 3, 'nompef' => 'Empresa',           'pagpri' => null],
        ]);
    }
}
