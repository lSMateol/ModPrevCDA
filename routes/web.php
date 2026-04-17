<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiagnosticoController;

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

// ==========================================
// RUTAS ADMINISTRADOR (Acceso Total)
// ==========================================
Route::middleware(['auth', 'role:Administrador'])->prefix('admin')->name('admin.')->group(function () {
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
    
    // Módulo MUP (Entidades)
    Route::prefix('entidades/mup')->name('mup.')->group(function () {
        Route::get('/conductores', [\App\Http\Controllers\Admin\MupController::class, 'conductores'])->name('conductores');
        Route::post('/conductores', [\App\Http\Controllers\Admin\MupController::class, 'storeConductor'])->name('conductores.store');
        
        // Perfiles
        Route::get('/perfil/nuevo', [\App\Http\Controllers\Admin\MupController::class, 'nuevoPerfil'])->name('perfil.nuevo');
        Route::post('/perfil/nuevo', [\App\Http\Controllers\Admin\MupController::class, 'storePerfil'])->name('perfil.store');

        // Usuarios (Master Dash)
        Route::get('/usuarios', [\App\Http\Controllers\Admin\MupController::class, 'usuarios'])->name('usuarios');
        Route::post('/usuarios', [\App\Http\Controllers\Admin\MupController::class, 'storeUsuario'])->name('usuarios.store');

        // Propietarios
        Route::get('/propietarios', [\App\Http\Controllers\Admin\MupController::class, 'propietarios'])->name('propietarios');
        Route::post('/propietarios', [\App\Http\Controllers\Admin\MupController::class, 'storePropietario'])->name('propietarios.store');

        // Empresas
        Route::get('/empresas', [\App\Http\Controllers\Admin\MupController::class, 'empresas'])->name('empresas');
        Route::post('/empresas', [\App\Http\Controllers\Admin\MupController::class, 'storeEmpresa'])->name('empresas.store');
    });

    // Aquí irán tus rutas de usuarios, roles y configuración global
});

// ==========================================
// RUTAS DIGITADOR (Operativo)
// ==========================================
Route::middleware(['auth', 'role:Digitador'])->prefix('digitador')->name('digitador.')->group(function () {
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
    // Aquí irán tus rutas de vehículos y diagnósticos
});

// ==========================================
// RUTAS EMPRESA (Restringido)
// ==========================================
Route::middleware(['auth', 'role:Empresa'])->prefix('empresa')->name('empresa.')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard'); 
    })->name('dashboard');
    
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
