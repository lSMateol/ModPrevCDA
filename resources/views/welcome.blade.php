@extends('layouts.app')

@section('content')
<div class="bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold text-gray-800">¡Panel de Control Listo!</h1>
    <p class="text-gray-600 mt-2">Bienvenido, <strong>{{ Auth::user()->name ?? 'Usuario de Prueba' }}</strong>.</p>
    <div class="mt-4 p-4 bg-blue-50 border-l-4 border-blue-500 text-blue-700">
        Esta es la zona de contenido dinámico del CDA Rastrillantas.
    </div>
</div>
@endsection