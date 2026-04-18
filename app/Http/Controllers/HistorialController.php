<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Diag;

class HistorialController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = Diag::with(['vehiculo', 'vehiculo.marca', 'rechazo'])
                    ->orderBy('fecdia', 'desc');

        // RBAC: Si es Empresa, solo ve historiales de sus vehículos
        if ($user->hasRole('Empresa')) {
            $idemp = $user->idemp;
            $query->whereHas('vehiculo', function($q) use ($idemp) {
                $q->where('idemp', $idemp);
            });
        }

        // Filtros (si vienen por request, opcional backend)
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->whereHas('vehiculo', function($q) use ($searchTerm) {
                $q->where('placaveh', 'like', "%{$searchTerm}%")
                  ->orWhere('linveh', 'like', "%{$searchTerm}%"); // Ojo con linveh (es idmar)
            });
        }

        if ($request->filled('estado')) {
            $estado = $request->estado;
            if ($estado === 'aprobado') {
                $query->where('aprobado', 1);
            } elseif ($estado === 'no_aprobado') {
                $query->where('aprobado', 0)->whereDoesntHave('rechazo', function($q){
                    $q->where('estadorec', 'Reasignado');
                });
            } elseif ($estado === 'reasignado') {
                $query->where('aprobado', 0)->whereHas('rechazo', function($q) {
                    $q->where('estadorec', 'Reasignado');
                });
            } elseif ($estado === 'pendiente') {
                $query->whereNull('aprobado');
            }
        }

        if ($request->filled('fecha')) {
            $query->whereDate('fecdia', $request->fecha);
        }

        $diagnosticos = $query->paginate(5);

        return view('vehiculos.historial', compact('diagnosticos'));
    }
}
