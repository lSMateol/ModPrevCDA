<?php

namespace App\Observers;

use App\Models\Vehiculo;
use App\Models\Historial;

class VehiculoObserver
{
    /**
     * Handle the Vehiculo "created" event.
     */
    public function created(Vehiculo $vehiculo): void
    {
        $idper = auth()->check() ? auth()->user()->idper : null;
        $esSistema = auth()->check() ? false : true;

        Historial::create([
            'tabla_ref'   => 'vehiculo',
            'id_ref'      => $vehiculo->idveh,
            'accion'      => 'Creación de vehículo',
            'descripcion' => 'Se registró un nuevo vehículo con placa ' . $vehiculo->placaveh . '.',
            'idper'       => $idper,
            'es_sistema'  => $esSistema,
        ]);
    }
}
