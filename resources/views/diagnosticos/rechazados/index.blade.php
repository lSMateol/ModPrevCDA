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
    <section class="bg-gray-50 p-6 rounded-2xl flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[250px]">
            <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 block ml-1">Buscar por placa, marca o modelo</label>
            <div class="relative group">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-gray-400 group-focus-within:text-[#ffba20]">search</span>
                <input type="text" class="w-full bg-white border-none rounded-xl py-3 pl-12 pr-4 text-sm font-medium shadow-sm transition-all focus:ring-2 focus:ring-[#ffba20]" placeholder="KVM-091...">
            </div>
        </div>
        <div class="w-64">
            <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 block ml-1">Inspector Anterior</label>
            <select class="w-full bg-white border-none rounded-xl py-3 px-4 text-sm font-medium shadow-sm focus:ring-2 focus:ring-[#ffba20]">
                <option value="">Todos</option>
                @foreach($inspectores as $insp)
                    <option value="{{ $insp->idper }}">{{ $insp->nombre_completo }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-64">
            <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 block ml-1">Fecha de Inspección</label>
            <input type="date" class="w-full bg-white border-none rounded-xl py-3 px-4 text-sm font-medium shadow-sm focus:ring-2 focus:ring-[#ffba20]">
        </div>
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
                            <div class="w-8 h-8 rounded-full bg-gray-100 overflow-hidden">
                                <span class="material-symbols-outlined text-gray-300 translate-y-1">person</span>
                            </div>
                            <span class="text-xs font-bold text-[#001834]">{{ $rechazo->inspector->nombre_completo ?? 'N/A' }}</span>
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
