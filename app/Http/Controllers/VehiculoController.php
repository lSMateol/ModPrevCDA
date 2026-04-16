<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Models\Empresa;
use Illuminate\Http\Request;

class VehiculoController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // 1. Construir la consulta principal
        $query = Vehiculo::with([
            'empresa', 
            'propietario', 
            'conductor', 
            'marca',
            'clase' // Opcional, si hay relación en Vehiculo para clveh
        ]);

        // 2. Aplicar lógica de roles
        if ($user->hasRole('Empresa')) {
            // Solo ver vehículos asociados a su propia empresa
            // La relación $user->idemp ya nos proporciona el id de la empresa
            $query->where('idemp', $user->idemp);
        }

        // 3. Ejecutar y obtener datos listos para la vista
        $vehiculos = $query->latest('idveh')->get();

        // 4. Variables auxiliares para los filtros (opcional, pero útil)
        if ($user->hasRole('Empresa') && $user->empresa) {
            $empresasFiltro = collect([$user->empresa]);
        } else {
            $empresasFiltro = Empresa::orderBy('razsoem', 'ASC')->get();
        }

        // Se envía a la vista
        return view('vehiculos.index', compact('vehiculos', 'empresasFiltro'));
    }
}
