<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonaSeeder extends Seeder
{
    public function run(): void
    {
        $personas = [
            // Administrador
            ['idper' => 1, 'ndocper' => 10000001, 'tdocper' => 1, 'nomper' => 'Diego',    'apeper' => 'Agustin',   'telper' => '3001000001', 'codubi' => 1, 'idpef' => 1, 'pass' => md5('admin123'),    'emaper' => 'admin@cda.com',            'idemp' => null, 'actper' => 1, 'nliccon' => null, 'fvencon' => null, 'catcon' => null  ],
            // Digitadores
            ['idper' => 2, 'ndocper' => 10000002, 'tdocper' => 1, 'nomper' => 'Carlos',   'apeper' => 'Ruiz',      'telper' => '3001000002', 'codubi' => 1, 'idpef' => 2, 'pass' => md5('digitador1'),  'emaper' => 'carlos.ruiz@cda.com',      'idemp' => null, 'actper' => 1, 'nliccon' => null, 'fvencon' => null, 'catcon' => null  ],
            ['idper' => 3, 'ndocper' => 10000003, 'tdocper' => 1, 'nomper' => 'María',    'apeper' => 'Rojas',     'telper' => '3001000003', 'codubi' => 1, 'idpef' => 2, 'pass' => md5('digitador2'),  'emaper' => 'maria.gomez@cda.com',      'idemp' => null, 'actper' => 1, 'nliccon' => null, 'fvencon' => null, 'catcon' => null  ],
            ['idper' => 4, 'ndocper' => 10000004, 'tdocper' => 1, 'nomper' => 'Juan',     'apeper' => 'Perez',     'telper' => '3001000004', 'codubi' => 2, 'idpef' => 2, 'pass' => md5('digitador3'),  'emaper' => 'juan.perez@cda.com',       'idemp' => null, 'actper' => 1, 'nliccon' => null, 'fvencon' => null, 'catcon' => null  ],
            // Propietarios (idpef 6): uno con licencia completa para MUP
            ['idper' => 5, 'ndocper' => 10000005, 'tdocper' => 1, 'nomper' => 'Luis',     'apeper' => 'Martínez',  'telper' => '3001000005', 'codubi' => 1, 'idpef' => 6, 'pass' => null, 'emaper' => 'luis.martinez@cda.com', 'idemp' => null, 'actper' => 1, 'nliccon' => 'LIC-10000005', 'fvencon' => '2027-04-01', 'catcon' => 'B3'],
            ['idper' => 6, 'ndocper' => 10000006, 'tdocper' => 1, 'nomper' => 'Ana',      'apeper' => 'López',     'telper' => '3001000006', 'codubi' => 1, 'idpef' => 6, 'pass' => null, 'emaper' => 'ana.lopez@cda.com',     'idemp' => null, 'actper' => 1, 'nliccon' => null, 'fvencon' => null, 'catcon' => null],
            ['idper' => 7, 'ndocper' => 10000007, 'tdocper' => 1, 'nomper' => 'Pedro',    'apeper' => 'Ramírez',   'telper' => '3001000007', 'codubi' => 2, 'idpef' => 6, 'pass' => null, 'emaper' => 'pedro.ramirez@cda.com', 'idemp' => null, 'actper' => 1, 'nliccon' => null, 'fvencon' => null, 'catcon' => null],
            // Conductores (idpef 7): varios con catcon texto + licencia
            ['idper' => 8, 'ndocper' => 10000008, 'tdocper' => 1, 'nomper' => 'Sofía',    'apeper' => 'Castro',    'telper' => '3001000008', 'codubi' => 2, 'idpef' => 7, 'pass' => null, 'emaper' => 'sofia.castro@cda.com',  'idemp' => null, 'actper' => 1, 'nliccon' => 'LIC-10000008', 'fvencon' => '2026-12-19', 'catcon' => 'A1'],
            ['idper' => 9, 'ndocper' => 10000009, 'tdocper' => 1, 'nomper' => 'Jorge',    'apeper' => 'Mendoza',   'telper' => '3001000009', 'codubi' => 3, 'idpef' => 7, 'pass' => null, 'emaper' => 'jorge.mendoza@cda.com', 'idemp' => null, 'actper' => 1, 'nliccon' => 'LIC-10000009', 'fvencon' => '2025-08-17', 'catcon' => 'B2'],
            ['idper' => 10,'ndocper' => 10000010, 'tdocper' => 1, 'nomper' => 'Carmen',   'apeper' => 'Rojas',     'telper' => '3001000010', 'codubi' => 3, 'idpef' => 7, 'pass' => null, 'emaper' => 'carmen.rojas@cda.com',  'idemp' => null, 'actper' => 1, 'nliccon' => 'LIC-10000010', 'fvencon' => '2028-01-10', 'catcon' => 'C1'],
            // Personas para Agendamiento (inspectores y ingenieros)
            ['idper' => 11,'ndocper' => 10000011, 'tdocper' => 1, 'nomper' => 'Andrés',   'apeper' => 'Vargas',    'telper' => '3001000011', 'codubi' => 1, 'idpef' => 4,    'pass' => null, 'emaper' => 'andres.vargas@cda.com', 'idemp' => null, 'actper' => 1, 'nliccon' => null, 'fvencon' => null, 'catcon' => null],
            ['idper' => 12,'ndocper' => 10000012, 'tdocper' => 1, 'nomper' => 'María',    'apeper' => 'Gómez',     'telper' => '3001000012', 'codubi' => 2, 'idpef' => 5,    'pass' => null, 'emaper' => 'maria.gomez@cda.com',   'idemp' => null, 'actper' => 1, 'nliccon' => null, 'fvencon' => null, 'catcon' => null],
            ['idper' => 13,'ndocper' => 10000013, 'tdocper' => 1, 'nomper' => 'Luis',     'apeper' => 'Fernández', 'telper' => '3001000013', 'codubi' => 3, 'idpef' => 5,    'pass' => null, 'emaper' => 'luis.fernandez@cda.com','idemp' => null, 'actper' => 1, 'nliccon' => null, 'fvencon' => null, 'catcon' => null],
            // Cuarto conductor de ejemplo (MUP conductores)
            ['idper' => 14,'ndocper' => 10000014, 'tdocper' => 1, 'nomper' => 'Ricardo',  'apeper' => 'Mejía',     'telper' => '3001000014', 'codubi' => 1, 'idpef' => 7, 'pass' => null, 'emaper' => 'ricardo.mejia@cda.com', 'idemp' => null, 'actper' => 1, 'nliccon' => 'LIC-10000014', 'fvencon' => '2027-06-30', 'catcon' => 'C3'],
        ];

        foreach ($personas as $p) {
            $data = $p;
            unset($data['idper']);
            DB::table('persona')->updateOrInsert(
                ['ndocper' => $p['ndocper']],
                $data
            );
        }
    }
}
