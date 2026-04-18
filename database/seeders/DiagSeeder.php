<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiagSeeder extends Seeder
{
    public function run(): void
    {
        // Insertar cabeceras de diagnóstico (diag)
        DB::table('diag')->insert([
            [
                'iddia'   => 1,
                'fecdia'  => '2025-10-24 08:30:00',
                'idveh'   => 8,   // asegúrate que exista en vehiculo
                'aprobado'=> 0,
                'idper'   => 2,   // persona que realiza (digitador)
                'fecvig'  => '2026-04-24 08:30:00',
                'kilomt'  => 45200,
                'idinsp'  => 11,
                'iding'   => 12,
                'iddiapar' => null,
            ],
            [
                'iddia'   => 2,
                'fecdia'  => '2025-10-23 14:15:00',
                'idveh'   => 9,
                'aprobado'=> 0,
                'idper'   => 3,
                'fecvig'  => '2026-04-23 14:15:00',
                'kilomt'  => 32100,
                'idinsp'  => 11,
                'iding'   => 13,
                'iddiapar' => null,
            ],
            [
                'iddia'   => 3,
                'fecdia'  => '2025-10-22 10:45:00',
                'idveh'   => 10,
                'aprobado'=> 1,
                'idper'   => 2,
                'fecvig'  => '2026-04-22 10:45:00',
                'kilomt'  => 88500,
                'idinsp'  => 11,
                'iding'   => 12,
                'iddiapar' => null,
            ],
            [
                'iddia'   => 4,
                'fecdia'  => '2025-10-21 16:00:00',
                'idveh'   => 11,
                'aprobado'=> 0,
                'idper'   => 2,
                'fecvig'  => '2026-04-21 16:00:00',
                'kilomt'  => 150000,
                'idinsp'  => 11,
                'iding'   => 13,
                'iddiapar' => null,
            ],
        ]);

        // Insertar valores de parámetros (diapar) usando los idpar definidos en ParamSeeder
        DB::table('diapar')->insert([
            // Diagnóstico 1
            ['iddia' => 1, 'idpar' => 1,  'idper' => 2, 'valor' => 'no_funciona'],   // luz_izquierda
            ['iddia' => 1, 'idpar' => 2,  'idper' => 2, 'valor' => 'funciona'],       // luz_derecha
            ['iddia' => 1, 'idpar' => 3,  'idper' => 2, 'valor' => '85.5'],           // temp_c
            ['iddia' => 1, 'idpar' => 4,  'idper' => 2, 'valor' => '2450'],            // rpm
            ['iddia' => 1, 'idpar' => 5,  'idper' => 2, 'valor' => '78.2'],            // ciclo1
            ['iddia' => 1, 'idpar' => 6,  'idper' => 2, 'valor' => '82.1'],            // ciclo2
            ['iddia' => 1, 'idpar' => 7,  'idper' => 2, 'valor' => '79.5'],            // ciclo3
            ['iddia' => 1, 'idpar' => 8,  'idper' => 2, 'valor' => '81.0'],            // ciclo4
            ['iddia' => 1, 'idpar' => 9,  'idper' => 2, 'valor' => '80.2'],            // resultado_diesel
            ['iddia' => 1, 'idpar' => 10, 'idper' => 2, 'valor' => 'no'],              // defecto_dilusion
            ['iddia' => 1, 'idpar' => 11, 'idper' => 2, 'valor' => 'si'],              // defecto_criterios_diesel
            ['iddia' => 1, 'idpar' => 12, 'idper' => 2, 'valor' => 'Suspensión'],      // grupo_inspeccion
            ['iddia' => 1, 'idpar' => 13, 'idper' => 2, 'valor' => 'Golpes'],          // tipo_defecto
            ['iddia' => 1, 'idpar' => 14, 'idper' => 2, 'valor' => 'Amortiguadores con fugas'], // desc_inspeccion

            // Diagnóstico 2 (solo algunos parámetros a modo de ejemplo)
            ['iddia' => 2, 'idpar' => 1,  'idper' => 3, 'valor' => 'funciona'],
            ['iddia' => 2, 'idpar' => 2,  'idper' => 3, 'valor' => 'funciona'],
            ['iddia' => 2, 'idpar' => 3,  'idper' => 3, 'valor' => '92.0'],
            ['iddia' => 2, 'idpar' => 4,  'idper' => 3, 'valor' => '3100'],
            ['iddia' => 2, 'idpar' => 5,  'idper' => 3, 'valor' => '65.4'],
            ['iddia' => 2, 'idpar' => 6,  'idper' => 3, 'valor' => '68.2'],
            ['iddia' => 2, 'idpar' => 7,  'idper' => 3, 'valor' => '70.1'],
            ['iddia' => 2, 'idpar' => 8,  'idper' => 3, 'valor' => '67.8'],
            ['iddia' => 2, 'idpar' => 9,  'idper' => 3, 'valor' => '67.9'],
            ['iddia' => 2, 'idpar' => 10, 'idper' => 3, 'valor' => 'si'],
            ['iddia' => 2, 'idpar' => 11, 'idper' => 3, 'valor' => 'si'],
            ['iddia' => 2, 'idpar' => 12, 'idper' => 3, 'valor' => 'Dirección'],
            ['iddia' => 2, 'idpar' => 13, 'idper' => 3, 'valor' => 'Juego excesivo'],
            ['iddia' => 2, 'idpar' => 14, 'idper' => 3, 'valor' => 'La dirección tiene holgura lateral'],

            // Diagnóstico 3 (aprobado)
            ['iddia' => 3, 'idpar' => 1,  'idper' => 4, 'valor' => 'funciona'],
            ['iddia' => 3, 'idpar' => 2,  'idper' => 4, 'valor' => 'funciona'],
            ['iddia' => 3, 'idpar' => 3,  'idper' => 4, 'valor' => '88.2'],
            ['iddia' => 3, 'idpar' => 4,  'idper' => 4, 'valor' => '2200'],
            ['iddia' => 3, 'idpar' => 5,  'idper' => 4, 'valor' => '95.0'],
            ['iddia' => 3, 'idpar' => 6,  'idper' => 4, 'valor' => '94.5'],
            ['iddia' => 3, 'idpar' => 7,  'idper' => 4, 'valor' => '96.0'],
            ['iddia' => 3, 'idpar' => 8,  'idper' => 4, 'valor' => '95.2'],
            ['iddia' => 3, 'idpar' => 9,  'idper' => 4, 'valor' => '95.1'],
            ['iddia' => 3, 'idpar' => 10, 'idper' => 4, 'valor' => 'no'],
            ['iddia' => 3, 'idpar' => 11, 'idper' => 4, 'valor' => 'no'],
            ['iddia' => 3, 'idpar' => 12, 'idper' => 4, 'valor' => 'Carrocería'],
            ['iddia' => 3, 'idpar' => 13, 'idper' => 4, 'valor' => 'Sin novedad'],
            ['iddia' => 3, 'idpar' => 14, 'idper' => 4, 'valor' => 'Vehículo en buen estado estético'],
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
        // Fechas realistas: creación de vehículos → antes de diagnósticos
        // Diagnósticos: usar las fechas exactas del DiagSeeder
        DB::table('historial')->insert([
            // ═══ Creación de vehículos con empresa ═══
            [
                'tabla_ref'   => 'vehiculo',
                'id_ref'      => 1,  // XYZ123 → Transportes del Norte
                'accion'      => 'Creación de vehículo',
                'descripcion' => 'Se registró el vehículo con placa XYZ123 y se vinculó a Transportes del Norte.',
                'idper'       => 2,  // Carlos Ruiz (digitador)
                'es_sistema'  => false,
                'created_at'  => '2025-01-15 09:20:00',
                'updated_at'  => '2025-01-15 09:20:00',
            ],
            [
                'tabla_ref'   => 'vehiculo',
                'id_ref'      => 2,  // ABC987 → Logiroute Express
                'accion'      => 'Creación de vehículo',
                'descripcion' => 'Se registró el vehículo con placa ABC987 y se vinculó a Logiroute Express.',
                'idper'       => 2,
                'es_sistema'  => false,
                'created_at'  => '2025-01-18 10:45:00',
                'updated_at'  => '2025-01-18 10:45:00',
            ],
            [
                'tabla_ref'   => 'vehiculo',
                'id_ref'      => 3,  // DEF456 → AgroSur S.A.
                'accion'      => 'Creación de vehículo',
                'descripcion' => 'Se registró el vehículo con placa DEF456 y se vinculó a AgroSur S.A.',
                'idper'       => 3,  // María Gómez (digitadora)
                'es_sistema'  => false,
                'created_at'  => '2025-02-05 08:30:00',
                'updated_at'  => '2025-02-05 08:30:00',
            ],
            [
                'tabla_ref'   => 'vehiculo',
                'id_ref'      => 4,  // GHI312 → Transportes del Norte
                'accion'      => 'Creación de vehículo',
                'descripcion' => 'Se registró el vehículo con placa GHI312 y se vinculó a Transportes del Norte.',
                'idper'       => 2,
                'es_sistema'  => false,
                'created_at'  => '2025-02-12 14:10:00',
                'updated_at'  => '2025-02-12 14:10:00',
            ],
            [
                'tabla_ref'   => 'vehiculo',
                'id_ref'      => 5,  // KVM091 → Transmetropolis S.A.
                'accion'      => 'Creación de vehículo',
                'descripcion' => 'Se registró el vehículo con placa KVM091 y se vinculó a Transmetropolis S.A.',
                'idper'       => 3,
                'es_sistema'  => false,
                'created_at'  => '2025-03-01 11:00:00',
                'updated_at'  => '2025-03-01 11:00:00',
            ],
            [
                'tabla_ref'   => 'vehiculo',
                'id_ref'      => 6,  // DTS442 → Logiroute Express
                'accion'      => 'Creación de vehículo',
                'descripcion' => 'Se registró el vehículo con placa DTS442 y se vinculó a Logiroute Express.',
                'idper'       => 2,
                'es_sistema'  => false,
                'created_at'  => '2025-03-10 09:15:00',
                'updated_at'  => '2025-03-10 09:15:00',
            ],
            [
                'tabla_ref'   => 'vehiculo',
                'id_ref'      => 7,  // FGT102 → Transportes del Norte
                'accion'      => 'Creación de vehículo',
                'descripcion' => 'Se registró el vehículo con placa FGT102 y se vinculó a Transportes del Norte.',
                'idper'       => 4,  // Juan Perez (digitador)
                'es_sistema'  => false,
                'created_at'  => '2025-04-02 16:30:00',
                'updated_at'  => '2025-04-02 16:30:00',
            ],
            [
                'tabla_ref'   => 'vehiculo',
                'id_ref'      => 9,  // MKL445 → AgroSur S.A.
                'accion'      => 'Creación de vehículo',
                'descripcion' => 'Se registró el vehículo con placa MKL445 y se vinculó a AgroSur S.A.',
                'idper'       => 2,
                'es_sistema'  => false,
                'created_at'  => '2025-05-20 10:00:00',
                'updated_at'  => '2025-05-20 10:00:00',
            ],
            [
                'tabla_ref'   => 'vehiculo',
                'id_ref'      => 10, // RTY990 → Logiroute Express
                'accion'      => 'Creación de vehículo',
                'descripcion' => 'Se registró el vehículo con placa RTY990 y se vinculó a Logiroute Express.',
                'idper'       => 3,
                'es_sistema'  => false,
                'created_at'  => '2025-06-14 08:45:00',
                'updated_at'  => '2025-06-14 08:45:00',
            ],

            // ═══ Mantenimiento preventivo ═══
            [
                'tabla_ref'   => 'vehiculo',
                'id_ref'      => 3,  // DEF456 → AgroSur
                'accion'      => 'Mantenimiento registrado',
                'descripcion' => 'Se realizó mantenimiento preventivo al vehículo DEF456 (cambio de aceite y filtros).',
                'idper'       => 4,  // Juan Perez
                'es_sistema'  => false,
                'created_at'  => '2025-08-12 14:30:00',
                'updated_at'  => '2025-08-12 14:30:00',
            ],

            // ═══ Diagnósticos de vehículos con empresa ═══
            // Diag #2 → MKL445 (AgroSur) — fecha exacta del DiagSeeder: 2025-10-23 14:15
            [
                'tabla_ref'   => 'diag',
                'id_ref'      => 2,
                'accion'      => 'Diagnóstico creado',
                'descripcion' => 'Se realizó diagnóstico al vehículo MKL445. Resultado: No aprobado.',
                'idper'       => 3,  // María Gómez (persona que realizó)
                'es_sistema'  => false,
                'created_at'  => '2025-10-23 14:15:00',
                'updated_at'  => '2025-10-23 14:15:00',
            ],
            // Diag #3 → RTY990 (Logiroute) — fecha exacta del DiagSeeder: 2025-10-22 10:45
            [
                'tabla_ref'   => 'diag',
                'id_ref'      => 3,
                'accion'      => 'Diagnóstico creado',
                'descripcion' => 'Se realizó diagnóstico al vehículo RTY990. Resultado: Aprobado.',
                'idper'       => 2,  // Carlos Ruiz
                'es_sistema'  => false,
                'created_at'  => '2025-10-22 10:45:00',
                'updated_at'  => '2025-10-22 10:45:00',
            ],

            // ═══ Otros movimientos del sistema ═══
            [
                'tabla_ref'   => 'diag',
                'id_ref'      => 1,  // Diag de HTY092 (sin empresa, pero existe como dato)
                'accion'      => 'Actualización de estado',
                'descripcion' => 'El diagnóstico cambió de "Pendiente" a "Reasignado".',
                'idper'       => 3,
                'es_sistema'  => false,
                'created_at'  => '2025-10-24 09:00:00',
                'updated_at'  => '2025-10-24 09:00:00',
            ],
            [
                'tabla_ref'   => 'rechazo',
                'id_ref'      => 2,
                'accion'      => 'Reasignación automática',
                'descripcion' => 'El sistema reasignó el rechazo a un nuevo inspector por inactividad.',
                'idper'       => null,
                'es_sistema'  => true,
                'created_at'  => '2025-10-25 06:00:00',
                'updated_at'  => '2025-10-25 06:00:00',
            ],
        ]);
    }
}
