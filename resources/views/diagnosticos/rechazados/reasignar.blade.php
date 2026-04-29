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
        <div class="lg:col-span-2 space-y-6 md:space-y-8">
            <form action="{{ route($prefix . '.rechazados.store-reasignacion', $diagnostico->iddia) }}" method="POST" class="bg-white p-6 md:p-10 rounded-2xl md:rounded-[2.5rem] border border-gray-100 shadow-sm space-y-8 md:space-y-10">
                @csrf
                
                <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pb-6 border-b border-gray-50">
                    <div>
                        <h2 class="font-headline text-xl md:text-2xl font-black text-[#001834] tracking-tight">Nueva agenda de inspección</h2>
                        <p class="text-sm text-gray-400 font-medium mt-1">Configure la nueva asignación del vehículo.</p>
                    </div>
                    <span class="bg-red-50 text-red-500 px-4 py-2 rounded-full text-[9px] md:text-[10px] font-black uppercase tracking-widest">Rechazado</span>
                </header>

                <div class="bg-gray-50/50 p-4 md:p-6 rounded-2xl md:rounded-3xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 group hover:bg-[#001834] transition-all duration-500">
                    <div class="flex items-center gap-4 md:gap-6">
                        <div class="w-12 h-12 md:w-14 md:h-14 bg-[#001834] rounded-xl md:rounded-2xl flex items-center justify-center text-[#ffba20] group-hover:bg-white group-hover:text-[#001834] transition-all">
                            <span class="material-symbols-outlined">directions_car</span>
                        </div>
                        <div>
                            <h3 class="font-headline text-xl md:text-2xl font-black text-[#001834] group-hover:text-white transition-all">{{ $diagnostico->vehiculo->placaveh }}</h3>
                            <p class="text-[9px] md:text-[10px] font-bold text-gray-400 group-hover:text-white/60 uppercase tracking-widest transition-all">
                                {{ $diagnostico->vehiculo->marca->nommar ?? 'N/A' }} • {{ $diagnostico->vehiculo->modveh }}
                            </p>
                        </div>
                    </div>
                    <div class="sm:text-right border-t sm:border-t-0 border-gray-100 pt-3 sm:pt-0 w-full sm:w-auto">
                        <p class="text-[9px] font-bold text-gray-400 group-hover:text-white/60 uppercase tracking-widest transition-all">Última inspección</p>
                        <p class="text-xs font-black text-[#001834] group-hover:text-white transition-all">{{ \Carbon\Carbon::parse($diagnostico->fecdia)->format('d M Y') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:gap-8">
                    <!-- Kilometraje -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Kilometraje Actual</label>
                        <div class="relative">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 material-symbols-outlined text-gray-400">speed</span>
                            <input type="number" name="kilomt" value="{{ $diagnostico->kilomt }}" required class="w-full bg-white border-2 border-gray-50 focus:border-[#ffba20] focus:ring-0 rounded-2xl py-4 pl-14 pr-6 text-sm font-bold shadow-sm transition-all" placeholder="Ingrese el recorrido actual">
                        </div>
                    </div>

                    <!-- Inspector -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Inspector Asignado</label>
                        <div class="relative">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 material-symbols-outlined text-gray-400">engineering</span>
                            <select name="idinsp_nuevo" required class="w-full bg-white border-2 border-gray-50 focus:border-[#ffba20] focus:ring-0 rounded-2xl py-4 pl-14 pr-6 text-sm font-bold shadow-sm transition-all">
                                <option value="">Seleccione inspector...</option>
                                @foreach($inspectores as $insp)
                                    <option value="{{ $insp->idper }}" {{ $diagnostico->idinsp == $insp->idper ? 'selected' : '' }}>
                                        {{ $insp->nombre_completo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Ingeniero -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Ingeniero Responsable</label>
                        <div class="relative">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 material-symbols-outlined text-gray-400">manage_accounts</span>
                            <select name="iding_nuevo" required class="w-full bg-white border-2 border-gray-50 focus:border-[#ffba20] focus:ring-0 rounded-2xl py-4 pl-14 pr-6 text-sm font-bold shadow-sm transition-all">
                                <option value="">Seleccione ingeniero...</option>
                                @foreach($ingenieros as $ing)
                                    <option value="{{ $ing->idper }}" {{ $diagnostico->iding == $ing->idper ? 'selected' : '' }}>
                                        {{ $ing->nombre_completo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Tipo de Formulario -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Tipo de Formulario</label>
                        <div class="relative">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 material-symbols-outlined text-gray-400">assignment</span>
                            <select name="tipo_formulario" required class="w-full bg-white border-2 border-gray-50 focus:border-[#ffba20] focus:ring-0 rounded-2xl py-4 pl-14 pr-6 text-sm font-bold shadow-sm transition-all">
                                @php
                                    $isDiesel = str_contains(strtolower($diagnostico->vehiculo->combustible->nomval ?? ''), 'diesel');
                                @endphp
                                @if($isDiesel)
                                    <option value="diesel_basico" {{ ($diagnostico->tipo_formulario ?? '') == 'diesel_basico' ? 'selected' : '' }}>Diésel Básico</option>
                                    <option value="diesel_con_gases" {{ ($diagnostico->tipo_formulario ?? '') == 'diesel_con_gases' ? 'selected' : '' }}>Diésel con Gases</option>
                                @else
                                    <option value="otto_completo" {{ ($diagnostico->tipo_formulario ?? '') == 'otto_completo' ? 'selected' : '' }}>Otto Completo (Con Gases)</option>
                                    <option value="otto_sin_gases" {{ ($diagnostico->tipo_formulario ?? '') == 'otto_sin_gases' ? 'selected' : '' }}>Otto Sin Gases</option>
                                    <option value="solo_gases" {{ ($diagnostico->tipo_formulario ?? '') == 'solo_gases' ? 'selected' : '' }}>Solo Gases</option>
                                @endif
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row items-center justify-between gap-6 pt-10 border-t border-gray-50">
                    <div class="flex items-center gap-3 text-[9px] md:text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center md:text-left">
                        <div class="hidden md:block w-2 h-2 rounded-full bg-[#ffba20] animate-pulse"></div>
                        Confirmación iniciará un nuevo formulario de inspección
                    </div>
                    <div class="flex w-full md:w-auto gap-4">
                        <a href="{{ route($prefix . '.rechazados') }}" class="flex-1 md:flex-none text-center px-8 py-4 rounded-xl border border-gray-100 font-black text-[10px] uppercase tracking-widest text-gray-400 hover:bg-gray-50 transition-all">Cancelar</a>
                        <button type="submit" class="flex-1 md:flex-none bg-[#ffba20] text-[#001834] px-10 py-4 rounded-xl font-black text-[10px] uppercase tracking-widest shadow-xl shadow-[#ffba20]/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                            Confirmar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Resumen lateral -->
        <aside class="space-y-6">
            <div class="bg-gray-50 p-6 md:p-8 rounded-2xl md:rounded-[2rem] border border-gray-100 space-y-6">
                <h4 class="font-headline text-xl font-black text-[#001834] tracking-tight">Resumen del rechazo</h4>
                
                <div class="space-y-4">
                    <div class="space-y-1">
                        <p class="text-[9px] font-black uppercase tracking-widest text-gray-400">Motivo principal</p>
                        <p class="text-sm font-bold text-[#001834]">{{ $diagnostico->rechazo->motivo ?? 'Sin motivo registrado' }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-[9px] font-black uppercase tracking-widest text-gray-400">Observaciones adicionales</p>
                        <p class="text-[11px] font-medium text-gray-600 leading-relaxed italic">{{ $diagnostico->rechazo->notas ?? 'Sin observaciones detalladas.' }}</p>
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
                        <p class="text-[9px] font-black uppercase tracking-widest text-gray-400">Propietario / Empresa</p>
                        <p class="text-sm font-bold text-[#001834]">{{ $diagnostico->vehiculo->empresa->razsoem ?? 'N/A' }}</p>
                    </div>

                    <div class="pt-4 border-t border-gray-200">
                        <p class="text-[9px] font-black uppercase tracking-widest text-gray-400">Sincronizado</p>
                        <p class="text-sm font-bold text-gray-500">{{ now()->format('d M, h:i A') }}</p>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
