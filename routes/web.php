<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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
        return view('dashboard'); // Cambiar por vista específica
    })->name('dashboard');
    
    // Configuración, usuarios, roles, etc.
});

// ==========================================
// RUTAS DIGITADOR (Operativo)
// ==========================================
Route::middleware(['auth', 'role:Digitador'])->prefix('digitador')->name('digitador.')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard'); // Cambiar por vista específica
    })->name('dashboard');
    
    // Accesos a diagnóstico, vehículos, etc.
});

// ==========================================
// RUTAS EMPRESA (Restringido)
// ==========================================
Route::middleware(['auth', 'role:Empresa'])->prefix('empresa')->name('empresa.')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard'); // Cambiar por vista específica
    })->name('dashboard');
    
    // Perfil, visualizar sus diagnósticos, subir archivos
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
