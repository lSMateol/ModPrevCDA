<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiagSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('diag')->insert([
            ['iddia'=>1, 'fecdia'=>'2025-10-24 08:30:00', 'idveh'=>8,  'aprobado'=>0, 'idper'=>9,  'fecvig'=>'2026-04-24 08:30:00','kilomt'=>45200, 'idinsp'=>2, 'iding'=>5, 'dpiddia'=>null],
            ['iddia'=>2, 'fecdia'=>'2025-10-23 14:15:00', 'idveh'=>9,  'aprobado'=>0, 'idper'=>10, 'fecvig'=>'2026-04-23 14:15:00','kilomt'=>32100, 'idinsp'=>3, 'iding'=>5, 'dpiddia'=>null],
            ['iddia'=>3, 'fecdia'=>'2025-10-22 10:45:00', 'idveh'=>10, 'aprobado'=>0, 'idper'=>7,  'fecvig'=>'2026-04-22 10:45:00','kilomt'=>88500, 'idinsp'=>4, 'iding'=>6, 'dpiddia'=>null],
            ['iddia'=>4, 'fecdia'=>'2025-10-22 16:20:00', 'idveh'=>11, 'aprobado'=>0, 'idper'=>9,  'fecvig'=>'2026-04-22 16:20:00','kilomt'=>21000, 'idinsp'=>2, 'iding'=>6, 'dpiddia'=>null],
            ['iddia'=>5, 'fecdia'=>'2025-10-21 09:00:00', 'idveh'=>5,  'aprobado'=>1, 'idper'=>9,  'fecvig'=>'2026-04-21 09:00:00','kilomt'=>65000, 'idinsp'=>3, 'iding'=>5, 'dpiddia'=>null],
        ]);

        // Parámetros de diagnóstico
        DB::table('diapar')->insert([
            ['iddia'=>1, 'idpar'=>1, 'idper'=>2, 'valor'=>'NO FUNCIONA'],
            ['iddia'=>1, 'idpar'=>2, 'idper'=>2, 'valor'=>'FUNCIONA'],
            ['iddia'=>1, 'idpar'=>5, 'idper'=>2, 'valor'=>'35'],
            ['iddia'=>2, 'idpar'=>7, 'idper'=>3, 'valor'=>'85'],
            ['iddia'=>2, 'idpar'=>8, 'idper'=>3, 'valor'=>'78'],
            ['iddia'=>3, 'idpar'=>1, 'idper'=>4, 'valor'=>'FUNCIONA'],
            ['iddia'=>3, 'idpar'=>3, 'idper'=>4, 'valor'=>'88'],
            ['iddia'=>3, 'idpar'=>4, 'idper'=>4, 'valor'=>'850'],
            ['iddia'=>5, 'idpar'=>1, 'idper'=>3, 'valor'=>'FUNCIONA'],
            ['iddia'=>5, 'idpar'=>2, 'idper'=>3, 'valor'=>'FUNCIONA'],
            ['iddia'=>5, 'idpar'=>3, 'idper'=>3, 'valor'=>'82'],
        ]);

        // Rechazos
        DB::table('rechazo')->insert([
            [
                'iddia'       => 1,
                'idper_ant'   => 2,   // inspector anterior (ej. Carlos Ruiz)
                'idper_nvo'   => 3,   // nuevo inspector (ej. María Gómez)
                'motivo'      => 'Inconsistencia en los datos del vehículo',
                'prioridad'   => 'Alta',
                'campos_mod'  => 'Placa, modelo, color',
                'notas'       => 'Revisar documentos del SOAT y técnico-mecánica',
                'fecreasig'   => '2025-04-01 10:30:00',
                'estadorec'   => 'Reasignado',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'iddia'       => 2,
                'idper_ant'   => 3,
                'idper_nvo'   => null,
                'motivo'      => 'Faltan fotografías del estado actual',
                'prioridad'   => 'Media',
                'campos_mod'  => 'Evidencia fotográfica',
                'notas'       => 'Subir al menos 4 fotos del vehículo',
                'fecreasig'   => null,
                'estadorec'   => 'Rechazado',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'iddia'       => 3,
                'idper_ant'   => 4,
                'idper_nvo'   => 2,
                'motivo'      => 'Error en el cálculo de la capacidad de carga',
                'prioridad'   => 'Alta',
                'campos_mod'  => 'crgveh, capveh',
                'notas'       => 'Verificar con la tarjeta de propiedad',
                'fecreasig'   => '2025-04-02 09:15:00',
                'estadorec'   => 'Reasignado',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'iddia'       => 4,
                'idper_ant'   => 2,
                'idper_nvo'   => 4,
                'motivo'      => 'Fecha de vencimiento del SOAT incorrecta',
                'prioridad'   => 'Baja',
                'campos_mod'  => 'soat, fecvens',
                'notas'       => 'Actualizar según certificado vigente',
                'fecreasig'   => '2025-04-03 14:20:00',
                'estadorec'   => 'Resuelto',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);

        // Historial de movimientos
        DB::table('historial')->insert([
            [
                'tabla_ref'   => 'vehiculo',
                'id_ref'      => 1,
                'accion'      => 'Creación de vehículo',
                'descripcion' => 'Se registró un nuevo vehículo con placa XYZ123.',
                'idper'       => 2,   // usuario (ej. Carlos Ruiz)
                'es_sistema'  => false,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'tabla_ref'   => 'diag',
                'id_ref'      => 1,
                'accion'      => 'Actualización de estado',
                'descripcion' => 'El diagnóstico cambió de "Pendiente" a "Aprobado".',
                'idper'       => 3,   // usuario (ej. María Gómez)
                'es_sistema'  => false,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'tabla_ref'   => 'persona',
                'id_ref'      => 5,
                'accion'      => 'Cambio de contraseña',
                'descripcion' => 'El usuario actualizó su contraseña por seguridad.',
                'idper'       => 5,
                'es_sistema'  => false,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'tabla_ref'   => 'rechazo',
                'id_ref'      => 2,
                'accion'      => 'Reasignación automática',
                'descripcion' => 'El sistema reasignó el rechazo a un nuevo inspector por inactividad.',
                'idper'       => null,
                'es_sistema'  => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'tabla_ref'   => 'vehiculo',
                'id_ref'      => 3,
                'accion'      => 'Mantenimiento registrado',
                'descripcion' => 'Se añadió un nuevo mantenimiento preventivo al vehículo.',
                'idper'       => 4,
                'es_sistema'  => false,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }
}
