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
            ['idtip' => 4, 'nomtip' => 'Inspección Visual',   'tittip' => 'Defectos Inspección Visual y Sensorial', 'idpef' => 2, 'acttip' => 1, 'icotip' => 'visual'],
        ]);

        DB::table('param')->insert([
            // Luces (idtip=1)
            ['idpar' => 1, 'nompar' => 'luz_izquierda', 'idtip' => 1, 'rini' => null, 'rfin' => null, 'control' => 'radio', 'nomcampo' => 'luz_izquierda', 'unipar' => null, 'colum' => 1, 'actpar' => 1, 'can' => 1],
            ['idpar' => 2, 'nompar' => 'luz_derecha',   'idtip' => 1, 'rini' => null, 'rfin' => null, 'control' => 'radio', 'nomcampo' => 'luz_derecha',   'unipar' => null, 'colum' => 1, 'actpar' => 1, 'can' => 1],

            // Motor Diesel (idtip=2)
            ['idpar' => 3, 'nompar' => 'temp_c',        'idtip' => 2, 'rini' => -50,  'rfin' => 200,  'control' => 'number', 'nomcampo' => 'temp_c',        'unipar' => '°C', 'colum' => 1, 'actpar' => 1, 'can' => 1],
            ['idpar' => 4, 'nompar' => 'rpm',           'idtip' => 2, 'rini' => 0,    'rfin' => 500,  'control' => 'number', 'nomcampo' => 'rpm',           'unipar' => 'rpm','colum' => 1, 'actpar' => 1, 'can' => 1],
            ['idpar' => 5, 'nompar' => 'ciclo1',        'idtip' => 2, 'rini' => 0,    'rfin' => 100,  'control' => 'number', 'nomcampo' => 'ciclo1',        'unipar' => '%',  'colum' => 1, 'actpar' => 1, 'can' => 1],
            ['idpar' => 6, 'nompar' => 'ciclo2',        'idtip' => 2, 'rini' => 0,    'rfin' => 100,  'control' => 'number', 'nomcampo' => 'ciclo2',        'unipar' => '%',  'colum' => 1, 'actpar' => 1, 'can' => 1],
            ['idpar' => 7, 'nompar' => 'ciclo3',        'idtip' => 2, 'rini' => 0,    'rfin' => 100,  'control' => 'number', 'nomcampo' => 'ciclo3',        'unipar' => '%',  'colum' => 1, 'actpar' => 1, 'can' => 1],
            ['idpar' => 8, 'nompar' => 'ciclo4',        'idtip' => 2, 'rini' => 0,    'rfin' => 100,  'control' => 'number', 'nomcampo' => 'ciclo4',        'unipar' => '%',  'colum' => 1, 'actpar' => 1, 'can' => 1],
            ['idpar' => 9, 'nompar' => 'resultado_diesel','idtip'=>2, 'rini' => 0,    'rfin' => 100,  'control' => 'number', 'nomcampo' => 'resultado_diesel','unipar' => '%','colum' => 1, 'actpar' => 1, 'can' => 1],

            // Defectos (idtip=3)
            ['idpar' => 10, 'nompar' => 'defecto_dilusion',          'idtip' => 3, 'rini' => null, 'rfin' => null, 'control' => 'radio', 'nomcampo' => 'defecto_dilusion',          'unipar' => null, 'colum' => 1, 'actpar' => 1, 'can' => 1],
            ['idpar' => 11, 'nompar' => 'defecto_criterios_diesel',  'idtip' => 3, 'rini' => null, 'rfin' => null, 'control' => 'radio', 'nomcampo' => 'defecto_criterios_diesel',  'unipar' => null, 'colum' => 1, 'actpar' => 1, 'can' => 1],
            ['idpar' => 12, 'nompar' => 'defecto_potencia',          'idtip' => 3, 'rini' => null, 'rfin' => null, 'control' => 'radio', 'nomcampo' => 'defecto_potencia',          'unipar' => null, 'colum' => 1, 'actpar' => 1, 'can' => 1],
            ['idpar' => 13, 'nompar' => 'defecto_diametro',          'idtip' => 3, 'rini' => null, 'rfin' => null, 'control' => 'radio', 'nomcampo' => 'defecto_diametro',          'unipar' => null, 'colum' => 1, 'actpar' => 1, 'can' => 1],

            // Inspección Visual (idtip=4)
            ['idpar' => 14, 'nompar' => 'grupo_inspeccion',   'idtip' => 4, 'rini' => null, 'rfin' => null, 'control' => 'text',     'nomcampo' => 'grupo_inspeccion',   'unipar' => null, 'colum' => 1, 'actpar' => 1, 'can' => 1],
            ['idpar' => 15, 'nompar' => 'tipo_defecto',       'idtip' => 4, 'rini' => null, 'rfin' => null, 'control' => 'text',     'nomcampo' => 'tipo_defecto',       'unipar' => null, 'colum' => 1, 'actpar' => 1, 'can' => 1],
            ['idpar' => 16, 'nompar' => 'desc_inspeccion',    'idtip' => 4, 'rini' => null, 'rfin' => null, 'control' => 'textarea', 'nomcampo' => 'desc_inspeccion',    'unipar' => null, 'colum' => 1, 'actpar' => 1, 'can' => 1],
        ]);
    }
}
