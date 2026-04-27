<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PerfilSeeder extends Seeder
{
    public function run(): void
    {
        $perfiles = [
            ['idpef' => 1, 'nompef' => 'Administrador',             'pagpri' => null],
            ['idpef' => 2, 'nompef' => 'Digitador',                 'pagpri' => null],
            ['idpef' => 3, 'nompef' => 'Empresa',                   'pagpri' => null],
            ['idpef' => 4, 'nompef' => 'Inspector',                 'pagpri' => null],
            ['idpef' => 5, 'nompef' => 'Ingeniero Autorizado',      'pagpri' => null],
            ['idpef' => 6, 'nompef' => 'Propietario',               'pagpri' => null],
            ['idpef' => 7, 'nompef' => 'Conductor',                 'pagpri' => null],
            ['idpef' => 8, 'nompef' => 'Propietario / Conductor',   'pagpri' => null],
        ];

        foreach ($perfiles as $p) {
            DB::table('perfil')->updateOrInsert(
                ['idpef' => $p['idpef']],
                $p
            );
        }
    }
}
