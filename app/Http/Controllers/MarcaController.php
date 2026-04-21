<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Marca;

class MarcaController extends Controller
{
    public function index()
    {
        $marcas = Marca::withCount('vehiculos')->orderBy('idmar', 'asc')->get();
        return view('vehiculos.marcas', compact('marcas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nommarlin' => 'required|string|max:255',
            'depmar' => 'nullable|integer'
        ]);

        $marca = Marca::create([
            'nommarlin' => $request->nommarlin,
            'depmar' => $request->depmar == 0 ? null : $request->depmar
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Marca creada exitosamente.',
            'marca' => $marca->loadCount('vehiculos')
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nommarlin' => 'required|string|max:255',
            'depmar' => 'nullable|integer'
        ]);

        $marca = Marca::findOrFail($id);
        $marca->update([
            'nommarlin' => $request->nommarlin,
            'depmar' => $request->depmar == 0 ? null : $request->depmar
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Marca actualizada exitosamente.',
            'marca' => $marca->loadCount('vehiculos')
        ]);
    }

    public function destroy($id)
    {
        $marca = Marca::withCount('vehiculos')->findOrFail($id);
        
        if ($marca->vehiculos_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la marca porque tiene ' . $marca->vehiculos_count . ' vehículos asociados.'
            ], 422);
        }

        // Verificar si es padre de otras marcas/líneas
        $childrenCount = Marca::where('depmar', $id)->count();
        if ($childrenCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar porque existen ' . $childrenCount . ' líneas que dependen de esta marca.'
            ], 422);
        }

        $marca->delete();

        return response()->json([
            'success' => true,
            'message' => 'Marca eliminada exitosamente.'
        ]);
    }
}
