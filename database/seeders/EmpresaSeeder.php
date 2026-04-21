<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        $empresas = [
            ['idemp' => 1, 'nonitem' => '900123456-7', 'razsoem' => 'Transmetropolis S.A.',     'direm' => 'Cra 7 # 32-10',   'telem' => '6011111111', 'idpef' => 3, 'emaem' => 'contacto@transmetropolis.com', 'nomger' => 'Carlos Mendoza',  'codcons' => null, 'codubi' => 1],
            ['idemp' => 2, 'nonitem' => '800987654-3', 'razsoem' => 'Logiroute Express',        'direm' => 'Av 68 # 45-20',   'telem' => '6012222222', 'idpef' => 3, 'emaem' => 'info@logiroute.com',           'nomger' => 'María Gómez',     'codcons' => null, 'codubi' => 1],
            ['idemp' => 3, 'nonitem' => '900555666-4', 'razsoem' => 'Transportes del Norte',    'direm' => 'Cll 80 # 20-30',  'telem' => '6013333333', 'idpef' => 3, 'emaem' => 'admin@transnorte.com',         'nomger' => 'Luis Fernando',   'codcons' => null, 'codubi' => 2],
            ['idemp' => 4, 'nonitem' => '800111222-5', 'razsoem' => 'AgroSur S.A.',             'direm' => 'Cll 5 # 10-15',   'telem' => '6024444444', 'idpef' => 3, 'emaem' => 'gerencia@agrosur.com',         'nomger' => 'Pedro Díaz',      'codcons' => null, 'codubi' => 3],
        ];

        foreach ($empresas as $e) {
            DB::table('empresa')->updateOrInsert(
                ['nonitem' => $e['nonitem']],
                $e
            );
        }
    }
}
