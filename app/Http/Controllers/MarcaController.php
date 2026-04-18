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
}
