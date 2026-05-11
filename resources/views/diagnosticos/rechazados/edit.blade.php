@extends('layouts.app')

@section('content')
@php
    $prefix = Auth::user()->hasRole('Administrador') ? 'admin' : 'digitador';
@endphp
<div class="px-8 py-6 space-y-8 max-w-4xl mx-auto">
    <!-- Breadcrumbs -->
    <nav class="flex text-[10px] font-black uppercase tracking-widest text-gray-400 gap-2 mb-4">
        <a href="{{ route($prefix . '.rechazados') }}" class="hover:text-[#001834]">Vehículos Rechazados</a>
        <span>/</span>
        <span class="text-[#001834]">Editar {{ $diagnostico->vehiculo->placaveh }}</span>
    </nav>

    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
        <div>
            <h1 class="font-headline font-black text-2xl md:text-3xl text-[#001834] tracking-tight">Editar Registro de Inspección</h1>
            <p class="text-sm text-gray-500 font-medium mt-1">Modifique los campos correspondientes a la inspección del vehículo {{ $diagnostico->vehiculo->placaveh }}.</p>
        </div>
        <div class="flex w-full md:w-auto gap-4">
            <a href="{{ route($prefix . '.rechazados') }}" class="flex-1 md:flex-none text-center px-6 py-3 rounded-xl border border-gray-200 font-bold text-xs uppercase tracking-widest text-gray-500 hover:bg-gray-50 transition-all">Cancelar</a>
            <button form="editRechazoForm" type="submit" class="flex-1 md:flex-none bg-[#001834] text-white px-8 py-3 rounded-xl font-bold text-xs uppercase tracking-widest shadow-xl shadow-[#001834]/10 hover:bg-[#002d54] transition-all flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-sm">save</span>
                Guardar
            </button>
        </div>
    </div>

    <form id="editRechazoForm" action="{{ route($prefix . '.rechazados.update', $diagnostico->iddia) }}" method="POST" class="space-y-6 md:space-y-8">
        @csrf
        @method('PUT')

        <!-- Sección 1: Información del Vehículo -->
        <div class="bg-white p-6 md:p-8 rounded-2xl md:rounded-3xl border border-gray-100 shadow-sm space-y-6">
            <div class="flex items-center gap-3 mb-2">
                <span class="material-symbols-outlined text-[#001834]">directions_car</span>
                <h2 class="font-headline text-lg font-black text-[#001834] tracking-tight">Información del Vehículo</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-8">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Placa</label>
                    <input type="text" value="{{ $diagnostico->vehiculo->placaveh }}" disabled class="w-full bg-gray-50 border-none rounded-xl py-4 px-6 text-sm font-bold opacity-70">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Marca</label>
                    <input type="text" value="{{ $diagnostico->vehiculo->marca->nommar ?? 'N/A' }}" disabled class="w-full bg-gray-50 border-none rounded-xl py-4 px-6 text-sm font-bold opacity-70">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Modelo</label>
                    <input type="text" value="{{ $diagnostico->vehiculo->modveh }}" disabled class="w-full bg-gray-50 border-none rounded-xl py-4 px-6 text-sm font-bold opacity-70">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Año</label>
                    <input type="text" value="{{ $diagnostico->vehiculo->paiveh }}" disabled class="w-full bg-gray-50 border-none rounded-xl py-4 px-6 text-sm font-bold opacity-70">
                </div>
            </div>
        </div>

        <!-- Sección 2: Detalles de la Inspección -->
        <div class="bg-white p-6 md:p-8 rounded-2xl md:rounded-3xl border border-gray-100 shadow-sm space-y-6">
            <div class="flex items-center gap-3 mb-2">
                <span class="material-symbols-outlined text-[#001834]">assignment_turned_in</span>
                <h2 class="font-headline text-lg font-black text-[#001834] tracking-tight">Detalles de la Inspección</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-8">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Fecha de Inspección</label>
                    <input type="text" value="{{ \Carbon\Carbon::parse($diagnostico->fecdia)->format('d/m/Y') }}" disabled class="w-full bg-gray-50 border-none rounded-xl py-4 px-6 text-sm font-bold opacity-70">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Hora de Inspección</label>
                    <input type="text" value="{{ \Carbon\Carbon::parse($diagnostico->fecdia)->format('h:i A') }}" disabled class="w-full bg-gray-50 border-none rounded-xl py-4 px-6 text-sm font-bold opacity-70">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Inspector Asignado</label>
                    <select name="idinsp" class="w-full bg-gray-50 border-none rounded-xl py-4 px-6 text-sm font-bold focus:ring-2 focus:ring-[#ffba20]">
                        @foreach($inspectores as $insp)
                            <option value="{{ $insp->idper }}" {{ $diagnostico->idinsp == $insp->idper ? 'selected' : '' }}>{{ $insp->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Estado de la Inspección</label>
                    <select disabled class="w-full bg-gray-50 border-none rounded-xl py-4 px-6 text-sm font-bold opacity-70">
                        <option>Rechazado</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Sección 3: Hallazgos y Observaciones -->
        <div class="bg-white p-6 md:p-8 rounded-2xl md:rounded-3xl border border-gray-100 shadow-sm space-y-6">
            <div class="flex items-center gap-3 mb-2">
                <span class="material-symbols-outlined text-[#001834]">emergency_home</span>
                <h2 class="font-headline text-lg font-black text-[#001834] tracking-tight">Hallazgos y Observaciones</h2>
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Motivo Principal de Rechazo</label>
                <textarea name="motivo" rows="3" class="w-full bg-gray-50 border-none rounded-2xl py-4 px-6 text-sm font-bold focus:ring-2 focus:ring-[#ffba20]" placeholder="Especifique el motivo del rechazo...">{{ $diagnostico->rechazo->motivo ?? '' }}</textarea>
            </div>
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Observaciones Adicionales</label>
                <textarea name="observaciones" rows="4" class="w-full bg-gray-50 border-none rounded-2xl py-4 px-6 text-sm font-bold focus:ring-2 focus:ring-[#ffba20]" placeholder="Ingrese detalles adicionales...">{{ $diagnostico->rechazo->notas ?? '' }}</textarea>
            </div>
        </div>
    </form>
    </form>
</div>
@endsection
