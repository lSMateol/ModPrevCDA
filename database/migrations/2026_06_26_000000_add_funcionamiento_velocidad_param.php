<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Insert/update param funcionamiento_velocidad
        DB::table('param')->updateOrInsert(
            ['idpar' => 31],
            [
                'nompar' => 'funcionamiento_velocidad',
                'idtip' => 3, // Defectos
                'rini' => null,
                'rfin' => null,
                'control' => 'radio',
                'nomcampo' => 'defecto_func_velocidad',
                'unipar' => null,
                'colum' => 1,
                'actpar' => 1,
                'can' => 1,
                'se_mantiene' => 0
            ]
        );

        // 2. Insert into tipo_vehiculo_config for all combustibles if not exists
        // Combustibles: Diesel (43), Gasolina (37), Gas-Natural (40)
        $combustibles = [43, 37, 40];
        foreach ($combustibles as $combu) {
            $exists = DB::table('tipo_vehiculo_config')
                ->where('idval_combu', $combu)
                ->where('idpar', 31)
                ->exists();

            if (!$exists) {
                // Find order to insert. Let's find max orden for this combu and add 1, or find order of idpar=11 and insert right after it.
                $refOrder = DB::table('tipo_vehiculo_config')
                    ->where('idval_combu', $combu)
                    ->where('idpar', 11) // Criterios de validación
                    ->value('orden');

                if ($refOrder) {
                    // Shift other parameters orders
                    DB::table('tipo_vehiculo_config')
                        ->where('idval_combu', $combu)
                        ->where('orden', '>', $refOrder)
                        ->increment('orden');
                    
                    DB::table('tipo_vehiculo_config')->insert([
                        'idval_combu' => $combu,
                        'idtip' => 3,
                        'idpar' => 31,
                        'orden' => $refOrder + 1
                    ]);
                } else {
                    $maxOrder = DB::table('tipo_vehiculo_config')
                        ->where('idval_combu', $combu)
                        ->max('orden') ?? 0;

                    DB::table('tipo_vehiculo_config')->insert([
                        'idval_combu' => $combu,
                        'idtip' => 3,
                        'idpar' => 31,
                        'orden' => $maxOrder + 1
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('tipo_vehiculo_config')->where('idpar', 31)->delete();
        DB::table('param')->where('idpar', 31)->delete();
    }
};
