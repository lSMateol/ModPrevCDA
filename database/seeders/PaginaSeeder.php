<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaginaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pagina')->insert([
            ['idpag' => 1, 'nompag' => 'Dashboard',          'rutpag' => '/dashboard',       'mospag' => 1, 'ordpag' => 1, 'icopag' => 'dashboard',    'despag' => 'Panel principal'],
            ['idpag' => 2, 'nompag' => 'Diagnóstico',        'rutpag' => '/diagnostico',     'mospag' => 1, 'ordpag' => 2, 'icopag' => 'diagnostico',  'despag' => 'Gestión de diagnósticos'],
            ['idpag' => 3, 'nompag' => 'Vehículos',          'rutpag' => '/vehiculos',       'mospag' => 1, 'ordpag' => 3, 'icopag' => 'vehiculo',     'despag' => 'Gestión vehicular'],
            ['idpag' => 4, 'nompag' => 'Alertas',            'rutpag' => '/alertas',         'mospag' => 1, 'ordpag' => 4, 'icopag' => 'alerta',       'despag' => 'Alertas de vencimientos'],
            ['idpag' => 5, 'nompag' => 'Mantenimiento',      'rutpag' => '/mantenimiento',   'mospag' => 1, 'ordpag' => 5, 'icopag' => 'mant',         'despag' => 'Historial de mantenimientos'],
            ['idpag' => 6, 'nompag' => 'Empresas',           'rutpag' => '/empresas',        'mospag' => 1, 'ordpag' => 6, 'icopag' => 'empresa',      'despag' => 'Gestión de empresas'],
            ['idpag' => 7, 'nompag' => 'Usuarios',           'rutpag' => '/usuarios',        'mospag' => 1, 'ordpag' => 7, 'icopag' => 'usuario',      'despag' => 'Gestión de usuarios'],
        ]);

        // Asignar páginas por perfil
        DB::table('pagper')->insert([
            // Administrador: acceso total
            ['idpag' => 1, 'idpef' => 1], ['idpag' => 2, 'idpef' => 1],
            ['idpag' => 3, 'idpef' => 1], ['idpag' => 4, 'idpef' => 1],
            ['idpag' => 5, 'idpef' => 1], ['idpag' => 6, 'idpef' => 1],
            ['idpag' => 7, 'idpef' => 1],
            // Inspector: diagnóstico y vehículos
            ['idpag' => 1, 'idpef' => 2], ['idpag' => 2, 'idpef' => 2],
            ['idpag' => 3, 'idpef' => 2],
            // Empresa Cliente: alertas y vehículos
            ['idpag' => 1, 'idpef' => 3], ['idpag' => 3, 'idpef' => 3],
            ['idpag' => 4, 'idpef' => 3],
        ]);
    }
}
