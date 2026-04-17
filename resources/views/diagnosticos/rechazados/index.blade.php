@extends('layouts.app')

@section('content')
@php
    $prefix = Auth::user()->hasRole('Administrador') ? 'admin' : 'digitador';
@endphp
<div class="px-8 py-6 space-y-8">
    <!-- Encabezado -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="font-headline font-black text-3xl text-[#001834] tracking-tight">Vehículos Rechazados</h1>
            <p class="text-sm text-gray-500 font-medium">Gestione los vehículos que no superaron la inspección técnica.</p>
        </div>
        <button class="bg-[#001834] text-white px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-widest flex items-center gap-2">
            <span class="material-symbols-outlined text-sm">download</span>
            Descargar informe de rechazados
        </button>
    </div>

    <!-- Filtros -->
    <section class="bg-gray-50 p-8 rounded-3xl border border-gray-100 shadow-sm">
        <form action="{{ route($prefix . '.rechazados') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                <div class="md:col-span-5">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 block ml-1">Buscar por placa, marca o modelo</label>
                    <div class="relative group">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-gray-400 group-focus-within:text-[#ffba20] transition-colors">search</span>
                        <input type="text" name="placa" value="{{ request('placa') }}" class="w-full bg-white border-2 border-transparent focus:border-[#ffba20] focus:ring-0 rounded-2xl py-3.5 pl-12 pr-4 text-sm font-bold shadow-sm transition-all" placeholder="KVM-091...">
                    </div>
                </div>
                <div class="md:col-span-3">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 block ml-1">Inspector Anterior</label>
                    <select name="inspector" class="w-full bg-white border-2 border-transparent focus:border-[#ffba20] focus:ring-0 rounded-2xl py-3.5 px-5 text-sm font-bold shadow-sm transition-all">
                        <option value="">Todos los inspectores</option>
                        @foreach($inspectores as $insp)
                            <option value="{{ $insp->idper }}" {{ request('inspector') == $insp->idper ? 'selected' : '' }}>
                                {{ $insp->nombre_completo }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 block ml-1">Fecha Inspección</label>
                    <input type="date" name="fecha" value="{{ request('fecha') }}" class="w-full bg-white border-2 border-transparent focus:border-[#ffba20] focus:ring-0 rounded-2xl py-3.5 px-5 text-sm font-bold shadow-sm transition-all">
                </div>
                <div class="md:col-span-2 flex flex-col gap-2">
                    <button type="submit" class="bg-[#001834] text-white w-full py-4 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-[#ffba20] hover:text-[#001834] transition-all shadow-lg active:scale-95">
                        Buscar
                    </button>
                    <a href="{{ route($prefix . '.rechazados') }}" class="text-center text-[#001834] font-black text-[9px] uppercase tracking-widest hover:opacity-70 transition-opacity underline">
                        Limpiar Filtros
                    </a>
                </div>
            </div>
        </form>
    </section>

    <!-- Tabla de Rechazados -->
    <div class="bg-white rounded-3xl overflow-hidden shadow-sm border border-gray-100">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50/50 text-[10px] font-black uppercase tracking-widest text-gray-400 border-b border-gray-100">
                    <th class="px-8 py-5">Placa / Vehículo</th>
                    <th class="px-8 py-5">Fecha y Hora</th>
                    <th class="px-8 py-5">Motivo Principal de Rechazo</th>
                    <th class="px-8 py-5">Inspector Anterior</th>
                    <th class="px-8 py-5">Estado</th>
                    <th class="px-8 py-5 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($rechazados as $rechazo)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-8 py-6">
                        <div class="font-headline font-black text-lg text-[#001834]">{{ $rechazo->vehiculo->placaveh }}</div>
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                            {{ $rechazo->vehiculo->marca->nommar ?? 'N/A' }} • {{ $rechazo->vehiculo->modveh }}
                        </div>
                    </td>
                    <td class="px-8 py-6">
                        <div class="text-sm font-bold text-[#001834]">{{ \Carbon\Carbon::parse($rechazo->fecdia)->format('d M Y') }}</div>
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ \Carbon\Carbon::parse($rechazo->fecdia)->format('h:i A') }}</div>
                    </td>
                    <td class="px-8 py-6 max-w-sm">
                        <p class="text-xs font-medium text-gray-600 leading-relaxed">
                            {{ $rechazo->rechazo->motivo ?? 'No se especificó motivo.' }}
                        </p>
                    </td>
                    <td class="px-8 py-6">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gray-100 overflow-hidden flex items-center justify-center">
                                <span class="material-symbols-outlined text-gray-400 text-sm">person</span>
                            </div>
                            <span class="text-xs font-bold text-[#001834]">
                                {{ $rechazo->rechazo->inspectorAnterior->nombre_completo ?? $rechazo->inspector->nombre_completo ?? 'N/A' }}
                            </span>
                        </div>
                    </td>
                    <td class="px-8 py-6">
                        <span class="bg-red-50 text-red-500 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-tighter">
                            {{ $rechazo->rechazo->estadorec ?? 'Rechazado' }}
                        </span>
                    </td>
                    <td class="px-8 py-6">
                        <div class="flex flex-col gap-2 items-end">
                            <a href="{{ route($prefix . '.rechazados.reasignar', $rechazo->iddia) }}" class="bg-[#001834] text-white px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest flex items-center gap-2 hover:bg-[#002d54] transition-all">
                                <span class="material-symbols-outlined text-sm">person_add</span>
                                Reasignar Inspector
                            </a>
                            <a href="{{ route($prefix . '.rechazados.edit', $rechazo->iddia) }}" class="border border-gray-200 text-[#001834] px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest flex items-center gap-2 hover:bg-gray-50 transition-all">
                                <span class="material-symbols-outlined text-sm">edit</span>
                                Editar Campos
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="mt-6">
        {{ $rechazados->links() }}
    </div>
</div>
@endsection
