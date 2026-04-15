<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // <-- IMPORTANTE: Necesario para la prueba

Route::get('/', function () {
    return view('welcome');
});

// RUTA TEMPORAL DE PRUEBA PARA ROL EMPRESA
Route::get('/forzar-empresa', function () {
    Auth::loginUsingId(3); // <-- Usamos el ID 3 que corresponde al rol Empresa
    return redirect('/dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';