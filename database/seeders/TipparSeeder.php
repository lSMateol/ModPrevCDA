<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipparSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tippar')->insert([
            // Grupos existentes (si ya los tienes, ajusta los IDs o usa upsert)
            ['idtip' => 1, 'nomtip' => 'Luces',               'tittip' => 'Revisión de Luces',                   'idpef' => 2, 'acttip' => 1, 'icotip' => 'luces'],
            ['idtip' => 2, 'nomtip' => 'Motor Diesel',        'tittip' => 'Parámetros de Motor Diesel',          'idpef' => 2, 'acttip' => 1, 'icotip' => 'motor_diesel'],
            ['idtip' => 3, 'nomtip' => 'Defectos',            'tittip' => 'Defectos Generales (SI/NO/N/A)',      'idpef' => 2, 'acttip' => 1, 'icotip' => 'defectos'],
            ['idtip' => 4, 'nomtip' => 'Defectos Inspeccion visual y sensorial',   'tittip' => 'Defectos Inspección Visual y Sensorial', 'idpef' => 2, 'acttip' => 1, 'icotip' => 'visual'],
        ]);

        DB::table('param')->insert([
            // Luces (idtip=1)
            ['idpar' => 1, 'nompar' => 'luz_izquierda', 'idtip' => 1, 'rini' => null, 'rfin' => null, 'control' => 'radio', 'nomcampo' => 'luz_izquierda', 'unipar' => null, 'colum' => 1, 'actpar' => 1, 'can' => 1],
            ['idpar' => 2, 'nompar' => 'luz_derecha',   'idtip' => 1, 'rini' => null, 'rfin' => null, 'control' => 'radio', 'nomcampo' => 'luz_derecha',   'unipar' => null, 'colum' => 1, 'actpar' => 1, 'can' => 1],

            // Motor Diesel (idtip=2)
            ['idpar' => 3, 'nompar' => 'temp_c',        'idtip' => 2, 'rini' => 71,      'rfin' => 80,  'control' => 'number', 'nomcampo' => 'temp_c',        'unipar' => '°C', 'colum' => 1, 'actpar' => 1, 'can' => 1],
            ['idpar' => 4, 'nompar' => 'rpm',           'idtip' => 2, 'rini' => 3500,    'rfin' => 4200,  'control' => 'number', 'nomcampo' => 'rpm',           'unipar' => 'rpm','colum' => 1, 'actpar' => 1, 'can' => 1],
            ['idpar' => 5, 'nompar' => 'ciclo1',        'idtip' => 2, 'rini' => 3,       'rfin' => 4,  'control' => 'number', 'nomcampo' => 'ciclo1',        'unipar' => '%',  'colum' => 1, 'actpar' => 1, 'can' => 1],
            ['idpar' => 6, 'nompar' => 'ciclo2',        'idtip' => 2, 'rini' => 2.80,       'rfin' => 2.99,  'control' => 'number', 'nomcampo' => 'ciclo2',        'unipar' => '%',  'colum' => 1, 'actpar' => 1, 'can' => 1],
            ['idpar' => 7, 'nompar' => 'ciclo3',        'idtip' => 2, 'rini' => 2.50,       'rfin' => 2.79,  'control' => 'number', 'nomcampo' => 'ciclo3',        'unipar' => '%',  'colum' => 1, 'actpar' => 1, 'can' => 1],
            ['idpar' => 8, 'nompar' => 'ciclo4',        'idtip' => 2, 'rini' => 2.30,       'rfin' => 2.49,  'control' => 'number', 'nomcampo' => 'ciclo4',        'unipar' => '%',  'colum' => 1, 'actpar' => 1, 'can' => 1],
            ['idpar' => 9, 'nompar' => 'resultado_diesel','idtip'=>2, 'rini' => 0,       'rfin' => 35,  'control' => 'number', 'nomcampo' => 'resultado_diesel','unipar' => '%','colum' => 1, 'actpar' => 1, 'can' => 1],

            // Defectos (idtip=3)
            ['idpar' => 10, 'nompar' => 'dilusion_gasolina',          'idtip' => 3, 'rini' => null, 'rfin' => null, 'control' => 'radio', 'nomcampo' => 'defecto_dilusion',          'unipar' => null, 'colum' => 1, 'actpar' => 1, 'can' => 1],
            ['idpar' => 11, 'nompar' => 'Criterios_de_validacion',  'idtip' => 3, 'rini' => null, 'rfin' => null, 'control' => 'radio', 'nomcampo' => 'defecto_criterios_diesel',  'unipar' => null, 'colum' => 1, 'actpar' => 1, 'can' => 1],

            // Inspección Visual (idtip=4)
            ['idpar' => 12, 'nompar' => 'grupo_inspeccion',   'idtip' => 4, 'rini' => null, 'rfin' => null, 'control' => 'text',     'nomcampo' => 'grupo_inspeccion',   'unipar' => null, 'colum' => 1, 'actpar' => 1, 'can' => 1],
            ['idpar' => 13, 'nompar' => 'tipo_defecto',       'idtip' => 4, 'rini' => null, 'rfin' => null, 'control' => 'text',     'nomcampo' => 'tipo_defecto',       'unipar' => null, 'colum' => 1, 'actpar' => 1, 'can' => 1],
            ['idpar' => 14, 'nompar' => 'desc_inspeccion',    'idtip' => 4, 'rini' => null, 'rfin' => null, 'control' => 'textarea', 'nomcampo' => 'desc_inspeccion',    'unipar' => null, 'colum' => 1, 'actpar' => 1, 'can' => 1],
        ]);
    }
}
