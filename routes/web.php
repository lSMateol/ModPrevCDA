<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiagnosticoController;
use App\Http\Controllers\VehiculoController;

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
    Route::resource('diagnosticos', DiagnosticoController::class)->names('diagnosticos');
    
    // Gestión Vehicular para Administrador
    Route::get('/vehiculos', [VehiculoController::class, 'index'])->name('vehiculos.index');
    Route::get('/vehiculos/crear', [VehiculoController::class, 'create'])->name('vehiculos.create');
    Route::post('/vehiculos', [VehiculoController::class, 'store'])->name('vehiculos.store');
    Route::get('/vehiculos/{id}/editar', [VehiculoController::class, 'edit'])->name('vehiculos.edit');
    Route::put('/vehiculos/{id}', [VehiculoController::class, 'update'])->name('vehiculos.update');
    Route::put('/vehiculos/{id}/vinculos', [VehiculoController::class, 'updateVinculos'])->name('vehiculos.vinculos');

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
    Route::resource('diagnosticos', DiagnosticoController::class)->names('diagnosticos');
    
    // Gestión Vehicular para Digitador
    Route::get('/vehiculos', [VehiculoController::class, 'index'])->name('vehiculos.index');
    Route::get('/vehiculos/crear', [VehiculoController::class, 'create'])->name('vehiculos.create');
    Route::post('/vehiculos', [VehiculoController::class, 'store'])->name('vehiculos.store');
    Route::get('/vehiculos/{id}/editar', [VehiculoController::class, 'edit'])->name('vehiculos.edit');
    Route::put('/vehiculos/{id}', [VehiculoController::class, 'update'])->name('vehiculos.update');
    Route::put('/vehiculos/{id}/vinculos', [VehiculoController::class, 'updateVinculos'])->name('vehiculos.vinculos');
});

// ==========================================
// RUTAS EMPRESA (Restringido)
// ==========================================
Route::middleware(['auth', 'role:Empresa'])->prefix('empresa')->name('empresa.')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard'); 
    })->name('dashboard');
    
    // Gestión Vehicular para Empresa (filtrado automático por idemp)
    Route::get('/vehiculos', [VehiculoController::class, 'index'])->name('vehiculos.index');

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


require __DIR__.'/auth.php';
