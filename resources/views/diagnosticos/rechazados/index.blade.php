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
    <section class="bg-gray-50 p-6 md:p-8 rounded-2xl md:rounded-3xl border border-gray-100 shadow-sm">
        <form id="rejected-filter-form" action="{{ route($prefix . '.rechazados') }}" method="GET">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 items-end">
                <div class="col-span-1">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 block ml-1">Buscar por placa o marca</label>
                    <div class="relative group">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-gray-400 group-focus-within:text-[#ffba20] transition-colors">search</span>
                        <input type="text" name="placa" oninput="debounceRejectedSubmit()" value="{{ request('placa') }}" class="w-full bg-white border-2 border-transparent focus:border-[#ffba20] focus:ring-0 rounded-2xl py-3.5 pl-12 pr-4 text-sm font-bold shadow-sm transition-all" placeholder="KVM-091...">
                    </div>
                </div>
                <div class="col-span-1">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 block ml-1">Empresa / Flota</label>
                    <select name="empresa_id" onchange="this.form.submit()" class="w-full bg-white border-2 border-transparent focus:border-[#ffba20] focus:ring-0 rounded-2xl py-3.5 px-5 text-sm font-bold shadow-sm transition-all cursor-pointer">
                        <option value="">Todas las Empresas</option>
                        @foreach($empresas as $emp)
                            <option value="{{ $emp->idemp }}" {{ request('empresa_id') == $emp->idemp ? 'selected' : '' }}>{{ $emp->razsoem }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-1">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 block ml-1">Inspector</label>
                    <select name="inspector" onchange="this.form.submit()" class="w-full bg-white border-2 border-transparent focus:border-[#ffba20] focus:ring-0 rounded-2xl py-3.5 px-5 text-sm font-bold shadow-sm transition-all cursor-pointer">
                        <option value="">Todos los inspectores</option>
                        @foreach($inspectores as $insp)
                            <option value="{{ $insp->idper }}" {{ request('inspector') == $insp->idper ? 'selected' : '' }}>
                                {{ $insp->nombre_completo }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-1">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 block ml-1">Fecha Inspección</label>
                    <input type="date" name="fecha" onchange="this.form.submit()" value="{{ request('fecha') }}" class="w-full bg-white border-2 border-transparent focus:border-[#ffba20] focus:ring-0 rounded-2xl py-3.5 px-5 text-sm font-bold shadow-sm transition-all cursor-pointer">
                </div>
                <div class="sm:col-span-2 lg:col-span-4 flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-gray-200/50 mt-2">
                    <a href="{{ route($prefix . '.rechazados') }}" class="bg-gray-200 text-gray-700 px-8 py-4 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-gray-300 transition-all text-center">
                        Limpiar Filtros
                    </a>
                    <div class="bg-[#001834]/5 text-[#001834]/40 px-10 py-4 rounded-xl font-black text-[10px] uppercase tracking-widest border border-dashed border-[#001834]/10 cursor-default text-center">
                        Auto-Filtro Activo
                    </div>
                </div>
            </div>
        </form>
    </section>

    <!-- Tabla de Rechazados -->
    <div class="bg-white rounded-3xl overflow-hidden shadow-sm border border-gray-100 overflow-x-auto">
        <table class="w-full text-left min-w-[800px] md:min-w-full">
            <thead>
                <tr class="bg-gray-50/50 text-[10px] font-black uppercase tracking-widest text-gray-400 border-b border-gray-100">
                    <th class="px-8 py-5">Placa / Vehículo</th>
                    <th class="px-8 py-5 hidden md:table-cell">Fecha y Hora</th>
                    <th class="px-8 py-5">Motivo de Rechazo</th>
                    <th class="px-8 py-5 hidden lg:table-cell">Inspector Anterior</th>
                    <th class="px-8 py-5">Estado</th>
                    <th class="px-8 py-5 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($rechazados as $rechazo)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-8 py-6">
                        <div class="font-headline font-black text-base md:text-lg text-[#001834]">{{ $rechazo->vehiculo->placaveh }}</div>
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                            {{ $rechazo->vehiculo->marca->nommar ?? 'N/A' }} • {{ $rechazo->vehiculo->modveh }}
                        </div>
                        <!-- Mostrar fecha en móvil ya que ocultamos la columna dedicada -->
                        <div class="md:hidden text-[9px] font-bold text-[#ffba20] uppercase mt-1">
                            {{ \Carbon\Carbon::parse($rechazo->fecdia)->format('d M, h:i A') }}
                        </div>
                    </td>
                    <td class="px-8 py-6 hidden md:table-cell">
                        <div class="text-sm font-bold text-[#001834]">{{ \Carbon\Carbon::parse($rechazo->fecdia)->format('d M Y') }}</div>
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ \Carbon\Carbon::parse($rechazo->fecdia)->format('h:i A') }}</div>
                    </td>
                    <td class="px-8 py-6 max-w-[200px] md:max-w-sm">
                        <p class="text-xs font-medium text-gray-600 leading-relaxed line-clamp-2 md:line-clamp-none">
                            {{ $rechazo->rechazo->motivo ?? 'No se especificó motivo.' }}
                        </p>
                    </td>
                    <td class="px-8 py-6 hidden lg:table-cell">
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
                            <a href="{{ route($prefix . '.rechazados.reasignar', $rechazo->iddia) }}" class="bg-[#001834] text-white px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 hover:bg-[#002d54] transition-all whitespace-nowrap w-full sm:w-auto justify-center">
                                <span class="material-symbols-outlined text-sm">person_add</span>
                                <span>Reasignar</span>
                            </a>
                            <a href="{{ route($prefix . '.rechazados.edit', $rechazo->iddia) }}" class="border-2 border-gray-100 text-[#001834] px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 hover:bg-gray-50 transition-all whitespace-nowrap w-full sm:w-auto justify-center">
                                <span class="material-symbols-outlined text-sm">edit</span>
                                <span>Modificar</span>
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

<script>
    let rejectedFilterTimeout = null;
    function debounceRejectedSubmit() {
        clearTimeout(rejectedFilterTimeout);
        rejectedFilterTimeout = setTimeout(() => {
            document.getElementById('rejected-filter-form').submit();
        }, 600);
    }
</script>
@endsection
