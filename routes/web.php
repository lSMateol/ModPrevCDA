<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiagnosticoController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\VehiculoEmpresaController;
use App\Http\Controllers\HistorialController;
use App\Http\Controllers\MarcaController;

// ==========================================
// REDIRECCIÓN INICIAL (Seguridad)
// ==========================================
Route::get('/', function () {
    return redirect()->route('login');
});

// ==========================================
// RUTA BASE (Distribuidor Inteligente)
// ==========================================
Route::get('/dashboard', function () {
    $user = request()->user();
    
    if ($user->hasRole('Administrador')) return redirect()->route('admin.dashboard');
    if ($user->hasRole('Digitador')) return redirect()->route('digitador.dashboard');
    if ($user->hasRole('Empresa')) return redirect()->route('empresa.dashboard');
    
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// RUTAS ADMINISTRADOR (Acceso Total + Control de Rutas)
// ==========================================
Route::middleware(['auth', 'role:Administrador', 'check.routes'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard'); 
    })->name('dashboard');

    // CRUD de Diagnósticos para Administrador
    Route::get('/diagnosticos/data', [DiagnosticoController::class, 'dataForModal'])->name('diagnosticos.data');
    Route::get('/alertas', [DiagnosticoController::class, 'alertas'])->name('alertas');
    
    // Rutas de Rechazados
    Route::get('/rechazados', [DiagnosticoController::class, 'rechazados'])->name('rechazados');
    Route::get('/rechazados/{id}/edit', [DiagnosticoController::class, 'editRechazo'])->name('rechazados.edit');
    Route::put('/rechazados/{id}', [DiagnosticoController::class, 'updateRechazo'])->name('rechazados.update');
    Route::get('/rechazados/{id}/reasignar', [DiagnosticoController::class, 'reasignar'])->name('rechazados.reasignar');
    Route::post('/rechazados/{id}/reasignar', [DiagnosticoController::class, 'storeReasignacion'])->name('rechazados.store-reasignacion');

    Route::resource('diagnosticos', DiagnosticoController::class)->names('diagnosticos');
    Route::get('/diagnosticos/{id}/fotos', [DiagnosticoController::class, 'getFotos'])->name('diagnosticos.get-fotos');
    Route::post('/diagnosticos/{id}/fotos', [DiagnosticoController::class, 'uploadFotos'])->name('diagnosticos.upload-fotos');
    Route::post('/diagnosticos/{id}/aprobar', [DiagnosticoController::class, 'approve'])->name('diagnosticos.approve');
    Route::post('/diagnosticos/{id}/rechazar', [DiagnosticoController::class, 'reject'])->name('diagnosticos.reject');
    Route::post('/diagnosticos/{id}/asignacion', [DiagnosticoController::class, 'updateAsignacion'])->name('diagnosticos.update-asignacion');
    Route::get('/diagnosticos/{id}/export', [DiagnosticoController::class, 'export'])->name('diagnosticos.export');
    
    // Módulo MUP (Entidades)
    Route::prefix('entidades/mup')->name('mup.')->group(function () {
        Route::get('/conductores', [\App\Http\Controllers\Admin\MupController::class, 'conductores'])->name('conductores.index');
        Route::post('/conductores', [\App\Http\Controllers\Admin\MupController::class, 'storeConductor'])->name('conductores.store');
        Route::put('/conductores/{id}', [\App\Http\Controllers\Admin\MupController::class, 'updateConductor'])->name('conductores.update');
        Route::delete('/conductores/{id}', [\App\Http\Controllers\Admin\MupController::class, 'destroyConductor'])->name('conductores.destroy');
        
        // Perfiles
        Route::get('/perfil/nuevo', [\App\Http\Controllers\Admin\MupController::class, 'nuevoPerfil'])->name('perfil.nuevo');
        Route::post('/perfil/nuevo', [\App\Http\Controllers\Admin\MupController::class, 'storePerfil'])->name('perfil.store');
        Route::put('/perfil/{id}', [\App\Http\Controllers\Admin\MupController::class, 'updatePerfil'])->name('perfil.update');

        // Usuarios (Master Dash)
        Route::get('/usuarios', [\App\Http\Controllers\Admin\MupController::class, 'usuarios'])->name('usuarios.index');
        Route::post('/usuarios', [\App\Http\Controllers\Admin\MupController::class, 'storeUsuario'])->name('usuarios.store');
        Route::put('/usuarios/{id}', [\App\Http\Controllers\Admin\MupController::class, 'updateUsuario'])->name('usuarios.update');
        Route::delete('/usuarios/{id}', [\App\Http\Controllers\Admin\MupController::class, 'destroyUsuario'])->name('usuarios.destroy');

        // Propietarios
        Route::get('/propietarios', [\App\Http\Controllers\Admin\MupController::class, 'propietarios'])->name('propietarios.index');
        Route::post('/propietarios', [\App\Http\Controllers\Admin\MupController::class, 'storePropietario'])->name('propietarios.store');
        Route::put('/propietarios/{id}', [\App\Http\Controllers\Admin\MupController::class, 'updatePropietario'])->name('propietarios.update');
        Route::delete('/propietarios/{id}', [\App\Http\Controllers\Admin\MupController::class, 'destroyPropietario'])->name('propietarios.destroy');

        // Empresas
        Route::get('/empresas', [\App\Http\Controllers\Admin\MupController::class, 'empresas'])->name('empresas.index');
        Route::post('/empresas', [\App\Http\Controllers\Admin\MupController::class, 'storeEmpresa'])->name('empresas.store');
        Route::put('/empresas/{id}', [\App\Http\Controllers\Admin\MupController::class, 'updateEmpresa'])->name('empresas.update');
        Route::delete('/empresas/{id}', [\App\Http\Controllers\Admin\MupController::class, 'destroyEmpresa'])->name('empresas.destroy');
    });

    
    // Gestión Vehicular para Administrador
    Route::get('/vehiculos', [VehiculoController::class, 'index'])->name('vehiculos.index');
    Route::get('/vehiculos/crear', [VehiculoController::class, 'create'])->name('vehiculos.create');
    Route::post('/vehiculos', [VehiculoController::class, 'store'])->name('vehiculos.store');
    Route::get('/vehiculos/{id}/editar', [VehiculoController::class, 'edit'])->name('vehiculos.edit');
    Route::put('/vehiculos/{id}', [VehiculoController::class, 'update'])->name('vehiculos.update');
    Route::delete('/vehiculos/{id}', [VehiculoController::class, 'destroy'])->name('vehiculos.destroy');
    Route::put('/vehiculos/{id}/vinculos', [VehiculoController::class, 'updateVinculos'])->name('vehiculos.vinculos');
    Route::put('/vehiculos/{id}/edicion-rapida', [VehiculoController::class, 'quickUpdate'])->name('vehiculos.quick-update');

    // Vehículos por Empresa
    Route::get('/vehiculos-empresa', [VehiculoEmpresaController::class, 'index'])->name('vehiculos-empresa.index');
    Route::get('/vehiculos-empresa/export-flota', [VehiculoEmpresaController::class, 'exportFlota'])->name('vehiculos-empresa.export-flota');
    Route::get('/vehiculos-empresa/{id}', [VehiculoEmpresaController::class, 'show'])->name('vehiculos-empresa.show');
    Route::put('/vehiculos-empresa/{id}/vinculo', [VehiculoEmpresaController::class, 'updateVinculoEmpresa'])->name('vehiculos-empresa.update-vinculo');
    Route::put('/vehiculos-empresa/perfil/{id}', [VehiculoEmpresaController::class, 'updatePerfil'])->name('vehiculos-empresa.perfil.update');

    Route::get('/historial', [HistorialController::class, 'index'])->name('historial.index');
    Route::get('/historial/reporte', [HistorialController::class, 'exportarReporte'])->name('historial.reporte');
    Route::resource('marcas', MarcaController::class)->except(['create', 'show', 'edit']);

    // Aquí irán tus rutas de usuarios, roles y configuración global
});

// RUTAS DIGITADOR (Operativo + Control de Rutas)
// ==========================================
Route::middleware(['auth', 'role:Digitador', 'check.routes'])->prefix('digitador')->name('digitador.')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard'); 
    })->name('dashboard');
    
    // CRUD de Diagnósticos para Digitador
    Route::get('/diagnosticos/data', [DiagnosticoController::class, 'dataForModal'])->name('diagnosticos.data');
    Route::get('/alertas', [DiagnosticoController::class, 'alertas'])->name('alertas');

    // Rutas de Rechazados
    Route::get('/rechazados', [DiagnosticoController::class, 'rechazados'])->name('rechazados');
    Route::get('/rechazados/{id}/edit', [DiagnosticoController::class, 'editRechazo'])->name('rechazados.edit');
    Route::put('/rechazados/{id}', [DiagnosticoController::class, 'updateRechazo'])->name('rechazados.update');
    Route::get('/rechazados/{id}/reasignar', [DiagnosticoController::class, 'reasignar'])->name('rechazados.reasignar');
    Route::post('/rechazados/{id}/reasignar', [DiagnosticoController::class, 'storeReasignacion'])->name('rechazados.store-reasignacion');

    Route::resource('diagnosticos', DiagnosticoController::class)->names('diagnosticos');
    Route::get('/diagnosticos/{id}/fotos', [DiagnosticoController::class, 'getFotos'])->name('diagnosticos.get-fotos');
    Route::post('/diagnosticos/{id}/fotos', [DiagnosticoController::class, 'uploadFotos'])->name('diagnosticos.upload-fotos');
    Route::post('/diagnosticos/{id}/aprobar', [DiagnosticoController::class, 'approve'])->name('diagnosticos.approve');
    Route::post('/diagnosticos/{id}/rechazar', [DiagnosticoController::class, 'reject'])->name('diagnosticos.reject');
    Route::post('/diagnosticos/{id}/asignacion', [DiagnosticoController::class, 'updateAsignacion'])->name('diagnosticos.update-asignacion');
    Route::get('/diagnosticos/{id}/export', [DiagnosticoController::class, 'export'])->name('diagnosticos.export');
    // Aquí irán tus rutas de vehículos y diagnósticos
    
    // Gestión Vehicular para Digitador
    Route::get('/vehiculos', [VehiculoController::class, 'index'])->name('vehiculos.index');
    Route::get('/vehiculos/crear', [VehiculoController::class, 'create'])->name('vehiculos.create');
    Route::post('/vehiculos', [VehiculoController::class, 'store'])->name('vehiculos.store');
    Route::get('/vehiculos/{id}/editar', [VehiculoController::class, 'edit'])->name('vehiculos.edit');
    Route::put('/vehiculos/{id}', [VehiculoController::class, 'update'])->name('vehiculos.update');
    Route::delete('/vehiculos/{id}', [VehiculoController::class, 'destroy'])->name('vehiculos.destroy');
    Route::put('/vehiculos/{id}/vinculos', [VehiculoController::class, 'updateVinculos'])->name('vehiculos.vinculos');
    Route::put('/vehiculos/{id}/edicion-rapida', [VehiculoController::class, 'quickUpdate'])->name('vehiculos.quick-update');

    // Vehículos por Empresa
    Route::get('/vehiculos-empresa', [VehiculoEmpresaController::class, 'index'])->name('vehiculos-empresa.index');
    Route::get('/vehiculos-empresa/export-flota', [VehiculoEmpresaController::class, 'exportFlota'])->name('vehiculos-empresa.export-flota');
    Route::get('/vehiculos-empresa/{id}', [VehiculoEmpresaController::class, 'show'])->name('vehiculos-empresa.show');
    Route::put('/vehiculos-empresa/{id}/vinculo', [VehiculoEmpresaController::class, 'updateVinculoEmpresa'])->name('vehiculos-empresa.update-vinculo');
    Route::put('/vehiculos-empresa/perfil/{id}', [VehiculoEmpresaController::class, 'updatePerfil'])->name('vehiculos-empresa.perfil.update');

    Route::get('/historial', [HistorialController::class, 'index'])->name('historial.index');
    Route::get('/historial/reporte', [HistorialController::class, 'exportarReporte'])->name('historial.reporte');
    Route::resource('marcas', MarcaController::class)->except(['create', 'show', 'edit']);
});

// RUTAS EMPRESA (Restringido + Control de Rutas)
// ==========================================
Route::middleware(['auth', 'role:Empresa', 'check.routes'])->prefix('empresa')->name('empresa.')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard'); 
    })->name('dashboard');
    
    // Gestión Vehicular para Empresa (filtrado automático por idemp)
    Route::get('/vehiculos', [VehiculoController::class, 'index'])->name('vehiculos.index');

    // Vehículos por Empresa (solo lectura)
    Route::get('/vehiculos-empresa', [VehiculoEmpresaController::class, 'index'])->name('vehiculos-empresa.index');
    Route::get('/vehiculos-empresa/export-flota', [VehiculoEmpresaController::class, 'exportFlota'])->name('vehiculos-empresa.export-flota');
    Route::get('/vehiculos-empresa/{id}', [VehiculoEmpresaController::class, 'show'])->name('vehiculos-empresa.show');
    Route::put('/vehiculos-empresa/perfil/{id}', [VehiculoEmpresaController::class, 'updatePerfil'])->name('vehiculos-empresa.perfil.update');

    // Exportar diagnósticos individuales (lectura)
    Route::get('/diagnosticos/{id}/export', [DiagnosticoController::class, 'export'])->name('diagnosticos.export');

    Route::get('/historial', [HistorialController::class, 'index'])->name('historial.index');
    Route::get('/historial/reporte', [HistorialController::class, 'exportarReporte'])->name('historial.reporte');
    Route::resource('marcas', MarcaController::class)->only(['index']);

    // Aquí irán las rutas para que la empresa vea sus certificados
});

// ==========================================
// RUTAS DE PERFIL (Comunes a todos)
// ==========================================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/storage-fallback/{path}', [DiagnosticoController::class, 'serveFile'])->where('path', '.*')->name('storage.fallback');

require __DIR__.'/auth.php';
