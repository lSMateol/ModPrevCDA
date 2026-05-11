<?php

namespace App\Observers;

use App\Models\Diag;
use App\Models\Historial;

class DiagObserver
{
    /**
     * Handle the Diag "created" event.
     */
    public function created(Diag $diag): void
    {
        $idper = auth()->check() ? auth()->user()->idper : null;
        $esSistema = auth()->check() ? false : true;
        
        // Cargar la relación si no está presente
        $diag->loadMissing('vehiculo');
        $placa = $diag->vehiculo ? $diag->vehiculo->placaveh : 'Desconocida';
        
        $estado = $diag->aprobado === 1 ? 'Aprobado' : ($diag->aprobado === 0 ? 'No aprobado' : 'Pendiente');

        Historial::create([
            'tabla_ref'   => 'diag',
            'id_ref'      => $diag->iddia,
            'accion'      => 'Diagnóstico creado',
            'descripcion' => 'Se registró un nuevo diagnóstico para el vehículo ' . $placa . '. Resultado: ' . $estado . '.',
            'idper'       => $idper,
            'es_sistema'  => $esSistema,
        ]);
    }
}
