<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipparSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tippar')->insert([
            ['idtip' => 1, 'nomtip' => 'Luces',         'tittip' => 'Revisión de Luces',       'idpef' => 2, 'acttip' => 1, 'icotip' => 'luces'],
            ['idtip' => 2, 'nomtip' => 'Motor',         'tittip' => 'Revisión de Motor',       'idpef' => 2, 'acttip' => 1, 'icotip' => 'motor'],
            ['idtip' => 3, 'nomtip' => 'Frenos',        'tittip' => 'Revisión de Frenos',      'idpef' => 2, 'acttip' => 1, 'icotip' => 'frenos'],
            ['idtip' => 4, 'nomtip' => 'Emisiones',     'tittip' => 'Prueba de Emisiones',     'idpef' => 2, 'acttip' => 1, 'icotip' => 'emisiones'],
            ['idtip' => 5, 'nomtip' => 'Visual',        'tittip' => 'Inspección Visual',       'idpef' => 2, 'acttip' => 1, 'icotip' => 'visual'],
        ]);

        DB::table('param')->insert([
            // Luces
            ['idpar' => 1, 'nompar' => 'Luz delantera izquierda', 'idtip' => 1, 'rini' => null, 'rfin' => null, 'control' => 'radio',    'nomcampo' => 'luzdelizq', 'unipar' => null, 'colum' => 6,  'actpar' => 1, 'can' => 1],
            ['idpar' => 2, 'nompar' => 'Luz delantera derecha',   'idtip' => 1, 'rini' => null, 'rfin' => null, 'control' => 'radio',    'nomcampo' => 'luzdeldar', 'unipar' => null, 'colum' => 6,  'actpar' => 1, 'can' => 1],
            // Motor
            ['idpar' => 3, 'nompar' => 'Temperatura',             'idtip' => 2, 'rini' => 60,   'rfin' => 95,   'control' => 'number',   'nomcampo' => 'tempc',     'unipar' => '°C', 'colum' => 6,  'actpar' => 1, 'can' => 1],
            ['idpar' => 4, 'nompar' => 'RPM',                     'idtip' => 2, 'rini' => 600,  'rfin' => 3000, 'control' => 'number',   'nomcampo' => 'rpm',       'unipar' => 'rpm','colum' => 6,  'actpar' => 1, 'can' => 1],
            // Frenos
            ['idpar' => 5, 'nompar' => 'Freno delantero izq',    'idtip' => 3, 'rini' => 0,    'rfin' => 30,   'control' => 'number',   'nomcampo' => 'frenodelizq','unipar' => '%', 'colum' => 6, 'actpar' => 1, 'can' => 1],
            ['idpar' => 6, 'nompar' => 'Freno delantero der',    'idtip' => 3, 'rini' => 0,    'rfin' => 30,   'control' => 'number',   'nomcampo' => 'frenodelder','unipar' => '%', 'colum' => 6, 'actpar' => 1, 'can' => 1],
            // Emisiones
            ['idpar' => 7, 'nompar' => 'Ciclo 1 (%)',             'idtip' => 4, 'rini' => 0,    'rfin' => 100,  'control' => 'number',   'nomcampo' => 'ciclo1',    'unipar' => '%', 'colum' => 6,  'actpar' => 1, 'can' => 1],
            ['idpar' => 8, 'nompar' => 'Ciclo 2 (%)',             'idtip' => 4, 'rini' => 0,    'rfin' => 100,  'control' => 'number',   'nomcampo' => 'ciclo2',    'unipar' => '%', 'colum' => 6,  'actpar' => 1, 'can' => 1],
        ]);
    }
}
