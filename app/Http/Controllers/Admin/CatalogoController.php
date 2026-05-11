<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dominio;
use App\Models\Valor;
use App\Models\Tippar;
use App\Models\Param;
use App\Models\Perfil;

class CatalogoController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'dominios');
        
        $dominios = Dominio::withCount('valores')->get();
        $tipospar = Tippar::withCount('params')->get();
        $perfiles = Perfil::all(); // For Tippar dropdown

        $dominioId = $request->get('dominio_id');
        $dominioSeleccionado = null;
        if ($dominioId) {
            $dominioSeleccionado = Dominio::with('valores')->find($dominioId);
        } elseif ($dominios->count() > 0) {
            $dominioSeleccionado = Dominio::with('valores')->first();
        }

        $tipparId = $request->get('tippar_id');
        $tipparSeleccionado = null;
        if ($tipparId) {
            $tipparSeleccionado = Tippar::with('params')->find($tipparId);
        } elseif ($tipospar->count() > 0) {
            $tipparSeleccionado = Tippar::with('params')->first();
        }

        return view('admin.catalogos.index', compact(
            'tab', 
            'dominios', 
            'dominioSeleccionado', 
            'tipospar', 
            'tipparSeleccionado',
            'perfiles'
        ));
    }

    // --- DOMINIOS ---
    public function storeDominio(Request $request)
    {
        $request->validate(['nomdom' => 'required|string|max:255']);
        $dominio = Dominio::create(['nomdom' => $request->nomdom]);
        return redirect()->route('admin.catalogos.index', ['tab' => 'dominios', 'dominio_id' => $dominio->iddom])
                         ->with('success', 'Dominio creado exitosamente.');
    }

    public function updateDominio(Request $request, $iddom)
    {
        $request->validate(['nomdom' => 'required|string|max:255']);
        $dominio = Dominio::findOrFail($iddom);
        $dominio->update(['nomdom' => $request->nomdom]);
        return redirect()->route('admin.catalogos.index', ['tab' => 'dominios', 'dominio_id' => $dominio->iddom])
                         ->with('success', 'Dominio actualizado exitosamente.');
    }

    public function destroyDominio($iddom)
    {
        try {
            $dominio = Dominio::findOrFail($iddom);
            $dominio->delete();
            return redirect()->route('admin.catalogos.index', ['tab' => 'dominios'])
                             ->with('success', 'Dominio eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return back()->withErrors('No se puede eliminar el Dominio porque tiene valores o dependencias activas en el sistema.');
            }
            return back()->withErrors('Error de base de datos al intentar eliminar el registro.');
        }
    }

    // --- VALORES ---
    public function storeValor(Request $request)
    {
        $request->validate([
            'iddom' => 'required|exists:dominio,iddom',
            'nomval' => 'required|string|max:255',
            'parval' => 'nullable|string|max:100',
            'actval' => 'nullable|boolean'
        ]);

        Valor::create([
            'iddom' => $request->iddom,
            'nomval' => $request->nomval,
            'parval' => $request->parval,
            'actval' => $request->has('actval') ? 1 : 0
        ]);

        return redirect()->route('admin.catalogos.index', ['tab' => 'dominios', 'dominio_id' => $request->iddom])
                         ->with('success', 'Valor creado exitosamente.');
    }

    public function updateValor(Request $request, $idval)
    {
        $request->validate([
            'nomval' => 'required|string|max:255',
            'parval' => 'nullable|string|max:100',
            'actval' => 'nullable|boolean'
        ]);

        $valor = Valor::findOrFail($idval);
        $valor->update([
            'nomval' => $request->nomval,
            'parval' => $request->parval,
            'actval' => $request->has('actval') ? 1 : 0
        ]);

        return redirect()->route('admin.catalogos.index', ['tab' => 'dominios', 'dominio_id' => $valor->iddom])
                         ->with('success', 'Valor actualizado exitosamente.');
    }

    public function destroyValor($idval)
    {
        try {
            $valor = Valor::findOrFail($idval);
            $iddom = $valor->iddom;
            $valor->delete();

            return redirect()->route('admin.catalogos.index', ['tab' => 'dominios', 'dominio_id' => $iddom])
                             ->with('success', 'Valor eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return back()->withErrors('No se puede eliminar este Valor porque ya está siendo usado por vehículos u otros registros.');
            }
            return back()->withErrors('Error de base de datos al intentar eliminar el registro.');
        }
    }

    // --- TIPOS DE PARAMETRO ---
    public function storeTippar(Request $request)
    {
        $request->validate([
            'nomtip' => 'required|string|max:70',
            'tittip' => 'required|string|max:150',
            'idpef' => 'required|exists:perfil,idpef',
            'icotip' => 'nullable|string|max:250',
            'acttip' => 'nullable|boolean'
        ]);

        $tippar = Tippar::create([
            'nomtip' => $request->nomtip,
            'tittip' => $request->tittip,
            'idpef' => $request->idpef,
            'icotip' => $request->icotip,
            'acttip' => $request->has('acttip') ? 1 : 0
        ]);

        return redirect()->route('admin.catalogos.index', ['tab' => 'parametros', 'tippar_id' => $tippar->idtip])
                         ->with('success', 'Tipo de Parámetro creado exitosamente.');
    }

    public function updateTippar(Request $request, $idtip)
    {
        $request->validate([
            'nomtip' => 'required|string|max:70',
            'tittip' => 'required|string|max:150',
            'idpef' => 'required|exists:perfil,idpef',
            'icotip' => 'nullable|string|max:250',
            'acttip' => 'nullable|boolean'
        ]);

        $tippar = Tippar::findOrFail($idtip);
        $tippar->update([
            'nomtip' => $request->nomtip,
            'tittip' => $request->tittip,
            'idpef' => $request->idpef,
            'icotip' => $request->icotip,
            'acttip' => $request->has('acttip') ? 1 : 0
        ]);

        return redirect()->route('admin.catalogos.index', ['tab' => 'parametros', 'tippar_id' => $tippar->idtip])
                         ->with('success', 'Tipo de Parámetro actualizado exitosamente.');
    }

    public function destroyTippar($idtip)
    {
        try {
            $tippar = Tippar::findOrFail($idtip);
            $tippar->delete();

            return redirect()->route('admin.catalogos.index', ['tab' => 'parametros'])
                             ->with('success', 'Tipo de Parámetro eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return back()->withErrors('No se puede eliminar esta Agrupación porque tiene parámetros vinculados u otros registros asociados.');
            }
            return back()->withErrors('Error de base de datos al intentar eliminar el registro.');
        }
    }

    // --- PARAMETROS ---
    public function storeParam(Request $request)
    {
        $request->validate([
            'idtip' => 'required|exists:tippar,idtip',
            'nompar' => 'required|string|max:100',
            'nomcampo' => 'required|string|max:30',
            'control' => 'required|string|max:50',
            'rini' => 'nullable|numeric',
            'rfin' => 'nullable|numeric',
            'unipar' => 'nullable|string|max:50',
            'colum' => 'nullable|integer',
            'can' => 'required|integer',
            'actpar' => 'nullable|boolean'
        ]);

        Param::create([
            'idtip' => $request->idtip,
            'nompar' => $request->nompar,
            'nomcampo' => $request->nomcampo,
            'control' => $request->control,
            'rini' => $request->rini,
            'rfin' => $request->rfin,
            'unipar' => $request->unipar,
            'colum' => $request->colum,
            'can' => $request->can,
            'actpar' => $request->has('actpar') ? 1 : 0
        ]);

        return redirect()->route('admin.catalogos.index', ['tab' => 'parametros', 'tippar_id' => $request->idtip])
                         ->with('success', 'Parámetro creado exitosamente.');
    }

    public function updateParam(Request $request, $idpar)
    {
        $request->validate([
            'nompar' => 'required|string|max:100',
            'nomcampo' => 'required|string|max:30',
            'control' => 'required|string|max:50',
            'rini' => 'nullable|numeric',
            'rfin' => 'nullable|numeric',
            'unipar' => 'nullable|string|max:50',
            'colum' => 'nullable|integer',
            'can' => 'required|integer',
            'actpar' => 'nullable|boolean'
        ]);

        $param = Param::findOrFail($idpar);
        $param->update([
            'nompar' => $request->nompar,
            'nomcampo' => $request->nomcampo,
            'control' => $request->control,
            'rini' => $request->rini,
            'rfin' => $request->rfin,
            'unipar' => $request->unipar,
            'colum' => $request->colum,
            'can' => $request->can,
            'actpar' => $request->has('actpar') ? 1 : 0
        ]);

        return redirect()->route('admin.catalogos.index', ['tab' => 'parametros', 'tippar_id' => $param->idtip])
                         ->with('success', 'Parámetro actualizado exitosamente.');
    }

    public function destroyParam($idpar)
    {
        try {
            $param = Param::findOrFail($idpar);
            $idtip = $param->idtip;
            $param->delete();

            return redirect()->route('admin.catalogos.index', ['tab' => 'parametros', 'tippar_id' => $idtip])
                             ->with('success', 'Parámetro eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return back()->withErrors('No se puede eliminar este Parámetro porque ya existen diagnósticos u operaciones que lo están usando.');
            }
            return back()->withErrors('Error de base de datos al intentar eliminar el registro.');
        }
    }
}
