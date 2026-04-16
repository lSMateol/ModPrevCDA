@extends('layouts.app')

@section('content')
@php
    $prefix = Auth::user()->hasRole('Administrador') ? 'admin' : 'digitador';
@endphp
<div class="px-8 py-6 space-y-8 max-w-6xl mx-auto">
    <!-- Breadcrumbs -->
    <nav class="flex text-[10px] font-black uppercase tracking-widest text-gray-400 gap-2 mb-4">
        <a href="{{ route($prefix . '.rechazados') }}" class="hover:text-[#001834]">Vehículos Rechazados</a>
        <span>/</span>
        <span class="text-[#001834]">Reasignación</span>
    </nav>

    <h1 class="font-headline font-black text-3xl text-[#001834] tracking-tight">Reasignar Inspector</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Formulario principal -->
        <div class="lg:col-span-2 space-y-8">
            <form action="{{ route($prefix . '.rechazados.store-reasignacion', $diagnostico->iddia) }}" method="POST" class="bg-white p-10 rounded-[2.5rem] border border-gray-100 shadow-sm space-y-10">
                @csrf
                
                <header class="flex justify-between items-center pb-6 border-b border-gray-50">
                    <div>
                        <h2 class="font-headline text-2xl font-black text-[#001834] tracking-tight">Nueva agenda de inspección</h2>
                        <p class="text-sm text-gray-400 font-medium mt-1">Configure la nueva asignación del vehículo rechazado.</p>
                    </div>
                    <span class="bg-red-50 text-red-500 px-5 py-2 rounded-full text-[10px] font-black uppercase tracking-widest">Rechazado</span>
                </header>

                <div class="bg-gray-50/50 p-6 rounded-3xl flex items-center justify-between group hover:bg-[#001834] transition-all duration-500">
                    <div class="flex items-center gap-6">
                        <div class="w-14 h-14 bg-[#001834] rounded-2xl flex items-center justify-center text-[#ffba20] group-hover:bg-white group-hover:text-[#001834] transition-all">
                            <span class="material-symbols-outlined">directions_car</span>
                        </div>
                        <div>
                            <h3 class="font-headline text-2xl font-black text-[#001834] group-hover:text-white transition-all">{{ $diagnostico->vehiculo->placaveh }}</h3>
                            <p class="text-[10px] font-bold text-gray-400 group-hover:text-white/60 uppercase tracking-widest transition-all">
                                {{ $diagnostico->vehiculo->marca->nommar ?? 'N/A' }} • {{ $diagnostico->vehiculo->modveh }} • {{ $diagnostico->vehiculo->paiveh }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-bold text-gray-400 group-hover:text-white/60 uppercase tracking-widest transition-all">Última inspección</p>
                        <p class="text-xs font-black text-[#001834] group-hover:text-white transition-all">{{ \Carbon\Carbon::parse($diagnostico->fecdia)->format('d M Y') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2 col-span-full">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Inspector anterior</label>
                        <div class="flex items-center gap-4 bg-gray-50 border border-gray-100 rounded-2xl py-3 px-5">
                            <div class="w-8 h-8 rounded-full bg-[#001834] flex items-center justify-center text-white text-xs font-bold uppercase">
                                {{ substr($diagnostico->inspector->pernom, 0, 1) }}
                            </div>
                            <span class="text-sm font-bold text-[#001834]">{{ $diagnostico->inspector->nombre_completo }}</span>
                            <span class="ml-auto material-symbols-outlined text-gray-300">lock</span>
                        </div>
                    </div>

                    <div class="space-y-2 col-span-full">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Nuevo inspector asignado</label>
                        <select name="idinsp_nuevo" required class="w-full bg-white border-2 border-gray-50 focus:border-[#ffba20] focus:ring-0 rounded-2xl py-4 px-6 text-sm font-bold shadow-sm transition-all">
                            <option value="">Seleccionar nuevo inspector...</option>
                            @foreach($inspectores as $insp)
                                @if($insp->idper != $diagnostico->idinsp)
                                    <option value="{{ $insp->idper }}">{{ $insp->nombre_completo }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Fecha de revisión</label>
                        <input type="date" name="fecha" value="{{ now()->addDay()->format('Y-m-d') }}" required class="w-full bg-white border-2 border-gray-50 focus:border-[#ffba20] focus:ring-0 rounded-2xl py-4 px-6 text-sm font-bold shadow-sm transition-all">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Hora</label>
                        <input type="time" name="hora" required class="w-full bg-white border-2 border-gray-50 focus:border-[#ffba20] focus:ring-0 rounded-2xl py-4 px-6 text-sm font-bold shadow-sm transition-all">
                    </div>

                    <div class="space-y-2 col-span-full">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Campos a modificar</label>
                        <textarea name="campos_mod" rows="2" class="w-full bg-white border-2 border-gray-50 focus:border-[#ffba20] focus:ring-0 rounded-2xl py-4 px-6 text-sm font-bold shadow-sm transition-all" placeholder="Ej: Luces delanteras, frenos, etc."></textarea>
                    </div>

                    <div class="space-y-2 col-span-full">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Notas o instrucciones</label>
                        <textarea name="notas" rows="4" class="w-full bg-white border-2 border-gray-50 focus:border-[#ffba20] focus:ring-0 rounded-2xl py-4 px-6 text-sm font-bold shadow-sm transition-all" placeholder="Especifique si hay alguna indicación especial..."></textarea>
                    </div>
                    
                    <!-- Hidden field for motivo if needed -->
                    <input type="hidden" name="motivo" value="{{ $diagnostico->rechazo->motivo ?? 'Reasignación por rechazo previo.' }}">
                </div>

                <div class="flex items-center justify-between pt-10 border-t border-gray-50">
                    <div class="flex items-center gap-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                        <div class="w-2 h-2 rounded-full bg-[#ffba20] animate-pulse"></div>
                        Verifica fecha e inspector antes de confirmar
                    </div>
                    <div class="flex gap-4">
                        <a href="{{ route($prefix . '.rechazados') }}" class="px-8 py-4 rounded-2xl border border-gray-100 font-black text-[10px] uppercase tracking-widest text-gray-400 hover:bg-gray-50 transition-all">Cancelar</a>
                        <button type="submit" class="bg-[#ffba20] text-[#001834] px-10 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-xl shadow-[#ffba20]/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                            Confirmar reasignación
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Resumen lateral -->
        <aside class="space-y-6">
            <div class="bg-gray-50 p-8 rounded-[2rem] border border-gray-100 space-y-6">
                <h4 class="font-headline text-xl font-black text-[#001834] tracking-tight">Resumen del rechazo</h4>
                
                <div class="space-y-4">
                    <div class="space-y-1">
                        <p class="text-[9px] font-black uppercase tracking-widest text-gray-400">Motivo principal</p>
                        <p class="text-sm font-bold text-[#001834]">{{ $diagnostico->rechazo->motivo ?? 'Sin motivo registrado' }}</p>
                    </div>
                    
                    <div class="space-y-1">
                        <p class="text-[9px] font-black uppercase tracking-widest text-gray-400">Inspector actual</p>
                        <p class="text-sm font-bold text-[#001834]">{{ $diagnostico->inspector->nombre_completo }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <p class="text-[9px] font-black uppercase tracking-widest text-gray-400">Prioridad</p>
                            <p class="text-sm font-bold text-red-500">{{ $diagnostico->rechazo->prioridad ?? 'Alta' }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-[9px] font-black uppercase tracking-widest text-gray-400">Placa</p>
                            <p class="text-sm font-bold text-[#001834] font-headline">{{ $diagnostico->vehiculo->placaveh }}</p>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <p class="text-[9px] font-black uppercase tracking-widest text-gray-400">Propietario</p>
                        <p class="text-sm font-bold text-[#001834]">{{ $diagnostico->vehiculo->empresa->nomemp }}</p>
                    </div>

                    <div class="pt-4 border-t border-gray-200">
                        <p class="text-[9px] font-black uppercase tracking-widest text-gray-400">Última actualización</p>
                        <p class="text-sm font-bold text-gray-500">Hoy, {{ now()->format('h:i A') }}</p>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
