<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DynamicFieldsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Asegurar que existan los nuevos tipos de parámetros (Dominios)
        $nuevosGrupos = [
            ['idtip' => 5, 'nomtip' => 'V. Ciclo OTTO',     'tittip' => 'Parámetros Ciclo OTTO',   'idpef' => 2, 'acttip' => 1, 'icotip' => 'settings_input_component'],
            ['idtip' => 6, 'nomtip' => 'Emisión de gases',  'tittip' => 'Prueba de Emisión de Gases', 'idpef' => 2, 'acttip' => 1, 'icotip' => 'co2'],
        ];

        foreach ($nuevosGrupos as $grupo) {
            DB::table('tippar')->updateOrInsert(['idtip' => $grupo['idtip']], $grupo);
        }

        // 2. Crear parámetros técnicos basados en la tabla proporcionada
        $nuevosParametros = [
            // Emisión de gases (idtip=6)
            ['idpar' => 15, 'nompar' => 'temperatura_gases',      'idtip' => 6, 'rini' => 0,      'rfin' => 0,       'control' => 'number', 'nomcampo' => 'temp_gases',       'unipar' => '°C',  'colum' => 1, 'actpar' => 1, 'can' => 1, 'se_mantiene' => 0],
            ['idpar' => 16, 'nompar' => 'rpm_gases',              'idtip' => 6, 'rini' => 800,    'rfin' => 1200,    'control' => 'number', 'nomcampo' => 'rpm_gases',        'unipar' => 'rpm', 'colum' => 1, 'actpar' => 1, 'can' => 1, 'se_mantiene' => 1],
            ['idpar' => 17, 'nompar' => 'co_ralenti',             'idtip' => 6, 'rini' => 0,      'rfin' => 0.80,    'control' => 'number', 'nomcampo' => 'co_ralenti',       'unipar' => '%',   'colum' => 1, 'actpar' => 1, 'can' => 1, 'se_mantiene' => 1],
            ['idpar' => 18, 'nompar' => 'co_crucero',             'idtip' => 6, 'rini' => 0,      'rfin' => 0.80,    'control' => 'number', 'nomcampo' => 'co_crucero',       'unipar' => '%',   'colum' => 1, 'actpar' => 1, 'can' => 1, 'se_mantiene' => 1],
            ['idpar' => 19, 'nompar' => 'co2_ralenti',            'idtip' => 6, 'rini' => 10,     'rfin' => 11,      'control' => 'number', 'nomcampo' => 'co2_ralenti',      'unipar' => '%',   'colum' => 1, 'actpar' => 1, 'can' => 1, 'se_mantiene' => 1],
            ['idpar' => 20, 'nompar' => 'co2_crucero',            'idtip' => 6, 'rini' => 10,     'rfin' => 11,      'control' => 'number', 'nomcampo' => 'co2_crucero',      'unipar' => '%',   'colum' => 1, 'actpar' => 1, 'can' => 1, 'se_mantiene' => 1],

            // V. Ciclo OTTO (idtip=5)
            ['idpar' => 21, 'nompar' => 'o2_ralenti',             'idtip' => 5, 'rini' => 0,      'rfin' => 5,       'control' => 'number', 'nomcampo' => 'o2_ralenti',       'unipar' => '%',   'colum' => 1, 'actpar' => 1, 'can' => 1, 'se_mantiene' => 1],
            ['idpar' => 22, 'nompar' => 'o2_crucero',             'idtip' => 5, 'rini' => 0,      'rfin' => 5,       'control' => 'number', 'nomcampo' => 'o2_crucero',       'unipar' => '%',   'colum' => 1, 'actpar' => 1, 'can' => 1, 'se_mantiene' => 1],
            ['idpar' => 23, 'nompar' => 'hc_ralenti',             'idtip' => 5, 'rini' => 0,      'rfin' => 160,     'control' => 'number', 'nomcampo' => 'hc_ralenti',       'unipar' => 'ppm', 'colum' => 1, 'actpar' => 1, 'can' => 1, 'se_mantiene' => 1],
            ['idpar' => 24, 'nompar' => 'hc_crucero',             'idtip' => 5, 'rini' => 0,      'rfin' => 160,     'control' => 'number', 'nomcampo' => 'hc_crucero',       'unipar' => 'ppm', 'colum' => 1, 'actpar' => 1, 'can' => 1, 'se_mantiene' => 1],
            ['idpar' => 25, 'nompar' => 'no_ralenti',             'idtip' => 5, 'rini' => 0,      'rfin' => 0,       'control' => 'number', 'nomcampo' => 'no_ralenti',       'unipar' => null,  'colum' => 1, 'actpar' => 1, 'can' => 1, 'se_mantiene' => 0],
            ['idpar' => 26, 'nompar' => 'no_crucero',             'idtip' => 5, 'rini' => 0,      'rfin' => 0,       'control' => 'number', 'nomcampo' => 'no_crucero',       'unipar' => null,  'colum' => 1, 'actpar' => 1, 'can' => 1, 'se_mantiene' => 0],
        ];

        foreach ($nuevosParametros as $param) {
            DB::table('param')->updateOrInsert(['idpar' => $param['idpar']], $param);
        }

        // 3. Configurar la relación Tipo Vehículo -> Parámetros
        // IDs de Combustibles: Diesel (43), Gasolina (37), Gas-Natural (40)
        
        $configuraciones = [];

        // --- DIESEL (43) ---
        $idsDiesel = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14];
        foreach($idsDiesel as $idpar) {
            $p = DB::table('param')->where('idpar', $idpar)->first();
            if($p) {
                $configuraciones[] = ['idval_combu' => 43, 'idtip' => $p->idtip, 'idpar' => $idpar, 'orden' => $idpar];
            }
        }

        // --- GASOLINA (37) ---
        $idsGasolina = [1, 2, 21, 22, 23, 24, 25, 26, 15, 16, 17, 18, 19, 20, 10, 11, 12, 13, 14];
        foreach($idsGasolina as $idpar) {
            $p = DB::table('param')->where('idpar', $idpar)->first();
            if($p) {
                $configuraciones[] = ['idval_combu' => 37, 'idtip' => $p->idtip, 'idpar' => $idpar, 'orden' => $idpar];
            }
        }

        // --- GAS-NATURAL (40) ---
        $idsGasNatural = [1, 2, 21, 22, 23, 24, 25, 26, 15, 16, 17, 18, 19, 20, 10, 11, 12, 13, 14];
        foreach($idsGasNatural as $idpar) {
            $p = DB::table('param')->where('idpar', $idpar)->first();
            if($p) {
                $configuraciones[] = ['idval_combu' => 40, 'idtip' => $p->idtip, 'idpar' => $idpar, 'orden' => $idpar];
            }
        }

        DB::table('tipo_vehiculo_config')->truncate();
        DB::table('tipo_vehiculo_config')->insert($configuraciones);
    }
}
