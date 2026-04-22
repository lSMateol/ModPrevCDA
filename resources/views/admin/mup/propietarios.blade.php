@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/mup.css') }}">
<script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>

@php
    $mupBase = auth()->user()->hasRole('Administrador') ? 'admin' : 'digitador';
    $mupPrefix = $mupBase . '.mup';
@endphp

<div class="px-4 sm:px-10 pb-20 max-w-[1600px] mx-auto" x-data="propietarioManager()" x-init="init()" x-cloak>
    
    <!-- HEADER & BENTO METRICS -->
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-end gap-6 mb-8 mt-4">
        <div>
            <h1 class="font-headline font-black text-[#002D54] text-2xl md:text-3xl tracking-tight">Directorio de Propietarios</h1>
            <p class="text-on-surface-variant font-body text-sm mt-1">Control maestro de propietarios y vinculación de activos</p>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 w-full xl:w-auto">
            <div class="bg-white p-4 rounded-2xl shadow-sm border-b-4 border-[#0d3b5a] flex flex-col justify-center min-w-[120px]">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total</span>
                <span class="text-2xl font-black text-[#001834]" x-text="propietarios.length"></span>
            </div>
            <div class="bg-white p-4 rounded-2xl shadow-sm border-b-4 border-emerald-500 flex flex-col justify-center min-w-[120px]">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Activos</span>
                <span class="text-2xl font-black text-emerald-600" x-text="activeCount()"></span>
            </div>
            <div class="bg-[#001834] p-4 rounded-2xl shadow-lg flex flex-col justify-center min-w-[120px]">
                <span class="text-[10px] font-black text-white/40 uppercase tracking-widest mb-1">Filtrados</span>
                <span class="text-2xl font-black text-primary-fixed-dim" x-text="filteredPropietarios().length"></span>
            </div>
            <button @click="showCreateModal = true" class="bg-gradient-to-br from-blue-600 to-blue-700 text-white p-4 rounded-2xl shadow-lg shadow-blue-500/20 flex flex-col items-center justify-center gap-1 hover:scale-[1.03] transition-all group">
                <iconify-icon icon="lucide:user-plus" class="text-xl group-hover:rotate-12 transition-transform"></iconify-icon>
                <span class="text-[9px] font-black uppercase tracking-tighter">Nuevo</span>
            </button>
        </div>
    </div>

    @include('admin.mup.partials.navigation')

    @include('admin.mup.partials.flash')

    <!-- MAIN CONTENT: MASTER-DETAIL SPLIT VIEW -->
    <div class="grid grid-cols-12 gap-8 mt-6">
        
        <!-- MASTER COLUMN: SEARCH & LIST -->
        <div class="col-span-12 lg:col-span-5 xl:col-span-4 flex flex-col gap-6">
            
            <!-- Barra de Búsqueda Premium -->
            <div class="relative group">
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 transition-colors">
                    <iconify-icon icon="lucide:search" class="text-xl"></iconify-icon>
                </div>
                <input type="text" x-model="search" placeholder="Buscar por nombre, documento, ciudad..." 
                    class="w-full bg-white border-2 border-transparent focus:border-blue-500/20 focus:ring-4 focus:ring-blue-500/5 rounded-2xl py-4 pl-12 pr-4 shadow-sm text-sm font-semibold transition-all">
                
                <div class="absolute right-4 top-1/2 -translate-y-1/2 flex gap-2">
                    <button @click="exportCsv()" class="p-2 text-gray-400 hover:text-[#0d3b5a] transition-colors" title="Exportar CSV">
                        <iconify-icon icon="lucide:download" class="text-lg"></iconify-icon>
                    </button>
                </div>
            </div>

            <!-- Listado de Resultados (Scrollable) -->
            <div class="flex flex-col gap-3 max-h-[calc(100vh-350px)] overflow-y-auto pr-2 custom-scrollbar">
                <template x-for="p in filteredPropietarios()" :key="p.idper">
                    <div @click="selectPropietario(p)" 
                        class="group bg-white p-4 rounded-2xl border-2 transition-all cursor-pointer relative overflow-hidden"
                        :class="selectedId === p.idper ? 'border-blue-500 shadow-md translate-x-2' : 'border-transparent hover:border-gray-200 shadow-sm'">
                        
                        <div class="flex items-center gap-4">
                            <!-- Avatar con iniciales -->
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-black text-sm shrink-0 transition-transform group-hover:scale-110"
                                :class="p.actper ? 'bg-gradient-to-br from-[#0d3b5a] to-[#1a4f73]' : 'bg-gray-300'"
                                x-text="p.nomper[0] + (p.apeper ? p.apeper[0] : '')">
                            </div>
                            
                            <div class="min-w-0 flex-1">
                                <div class="flex justify-between items-start">
                                    <h3 class="font-bold text-[#001834] text-sm truncate uppercase tracking-tight" x-text="p.nomper + ' ' + (p.apeper || '')"></h3>
                                    <span :class="p.actper ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-50 text-gray-400'" 
                                        class="text-[9px] font-black px-2 py-0.5 rounded-full uppercase tracking-tighter"
                                        x-text="p.actper ? 'Activo' : 'Inactivo'"></span>
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <iconify-icon icon="lucide:map-pin" class="text-gray-400 text-[10px]"></iconify-icon>
                                    <span class="text-[11px] font-bold text-gray-500 truncate" x-text="p.ciuper || 'N/A'"></span>
                                    <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                    <span class="text-[11px] font-bold text-gray-400" x-text="numberFormat(p.ndocper)"></span>
                                </div>
                            </div>

                            <iconify-icon icon="lucide:chevron-right" class="text-gray-300 group-hover:text-blue-500 transition-colors"></iconify-icon>
                        </div>

                        <!-- Indicador de selección activa -->
                        <div x-show="selectedId === p.idper" class="absolute left-0 top-0 bottom-0 w-1 bg-blue-500"></div>
                    </div>
                </template>

                <div x-show="filteredPropietarios().length === 0" class="bg-white/50 border-2 border-dashed border-gray-200 rounded-3xl p-10 text-center flex flex-col items-center gap-3">
                    <iconify-icon icon="lucide:search-x" class="text-4xl text-gray-200"></iconify-icon>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Sin resultados</p>
                </div>
            </div>
        </div>

        <!-- DETAIL COLUMN: INFORMATION & GRAPH -->
        <div class="col-span-12 lg:col-span-7 xl:col-span-8">
            
            <template x-if="selectedPropietario">
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden animate-in fade-in slide-in-from-right-4 duration-500">
                    
                    <!-- Detail Header -->
                    <div class="p-6 sm:p-8 bg-gradient-to-r from-[#001834] to-[#0d3b5a] text-white">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
                            <div class="flex items-center gap-5">
                                <div class="w-20 h-20 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-3xl font-black shadow-inner"
                                    x-text="selectedPropietario.nomper[0] + (selectedPropietario.apeper ? selectedPropietario.apeper[0] : '')">
                                </div>
                                <div>
                                    <div class="flex items-center gap-3">
                                        <h2 class="text-2xl font-black tracking-tight" x-text="selectedPropietario.nomper + ' ' + (selectedPropietario.apeper || '')"></h2>
                                        <span x-show="selectedPropietario.actper" class="bg-blue-500/20 text-blue-300 text-[10px] font-black px-3 py-1 rounded-full border border-blue-500/30 uppercase tracking-widest">Vigente</span>
                                    </div>
                                    <p class="text-white/60 text-xs font-medium mt-1 flex items-center gap-2">
                                        <iconify-icon icon="lucide:mail"></iconify-icon>
                                        <span x-text="selectedPropietario.emaper"></span>
                                        <span class="opacity-30">|</span>
                                        <iconify-icon icon="lucide:id-card"></iconify-icon>
                                        <span x-text="getDocType(selectedPropietario.tdocper) + ': ' + numberFormat(selectedPropietario.ndocper)"></span>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex gap-2 w-full sm:w-auto">
                                <button @click="editPropietario(selectedPropietario)" class="flex-1 sm:flex-none bg-white/10 hover:bg-white/20 text-white px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-widest transition-all border border-white/10">
                                    Editar Datos
                                </button>
                                <button @click="confirmDelete(selectedPropietario)" class="p-3 bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white rounded-xl transition-all border border-red-500/20">
                                    <iconify-icon icon="lucide:trash-2" class="text-xl"></iconify-icon>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Tabs -->
                    <div class="flex border-b border-gray-100 bg-gray-50/50 px-8">
                        <button @click="detailTab = 'info'" 
                            class="px-6 py-4 text-[11px] font-black uppercase tracking-widest transition-all border-b-2"
                            :class="detailTab === 'info' ? 'border-blue-500 text-[#0d3b5a]' : 'border-transparent text-gray-400 hover:text-gray-600'">
                            Perfil del Propietario
                        </button>
                        <button @click="detailTab = 'vehiculos'" 
                            class="px-6 py-4 text-[11px] font-black uppercase tracking-widest transition-all border-b-2"
                            :class="detailTab === 'vehiculos' ? 'border-blue-500 text-[#0d3b5a]' : 'border-transparent text-gray-400 hover:text-gray-600'">
                            Vehículos de su Propiedad
                        </button>
                        <button @click="detailTab = 'conduccion'" 
                            class="px-6 py-4 text-[11px] font-black uppercase tracking-widest transition-all border-b-2"
                            :class="detailTab === 'conduccion' ? 'border-blue-500 text-[#0d3b5a]' : 'border-transparent text-gray-400 hover:text-gray-600'">
                            Como Conductor
                        </button>
                    </div>

                    <!-- Detail Content -->
                    <div class="p-8">
                        <!-- TAB: INFO -->
                        <div x-show="detailTab === 'info'" class="grid grid-cols-1 md:grid-cols-2 gap-8 animate-in fade-in duration-300">
                            <div class="space-y-6">
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Localización</label>
                                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center text-blue-500 shadow-sm">
                                                <iconify-icon icon="lucide:map-pin"></iconify-icon>
                                            </div>
                                            <p class="text-lg font-black text-[#001834]" x-text="selectedPropietario.ciuper || 'No especificada'"></p>
                                        </div>
                                        <p class="text-xs font-bold text-gray-500 pl-11" x-text="selectedPropietario.dirper || 'Sin dirección registrada'"></p>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Contacto Directo</label>
                                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 flex items-center gap-4">
                                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-blue-500 shadow-sm border border-gray-100">
                                            <iconify-icon icon="lucide:smartphone" class="text-xl"></iconify-icon>
                                        </div>
                                        <p class="text-lg font-black text-[#001834]" x-text="selectedPropietario.telper || 'N/A'"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-6">
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Ficha del Sistema</label>
                                    <div class="bg-white p-5 rounded-2xl border-2 border-dashed border-gray-100">
                                        <div class="flex justify-between items-center mb-4">
                                            <span class="text-xs font-bold text-gray-500">ID Maestro</span>
                                            <span class="bg-[#001834] text-white px-3 py-1 rounded-lg text-[10px] font-black" x-text="'P-' + selectedPropietario.idper"></span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-xs font-bold text-gray-500">Rol Operativo</span>
                                            <span class="text-xs font-black text-blue-600 uppercase tracking-tighter">Propietario de Activos</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB: VEHICULOS (GRAFO) -->
                        <div x-show="detailTab === 'vehiculos'" class="animate-in fade-in duration-300">
                            <div class="flex flex-col items-center py-6">
                                <!-- Nodo Central -->
                                <div class="relative z-10">
                                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-[#001834] to-[#0d3b5a] flex items-center justify-center text-white text-2xl font-black shadow-xl border-4 border-white"
                                        x-text="selectedPropietario.nomper[0] + (selectedPropietario.apeper ? selectedPropietario.apeper[0] : '')">
                                    </div>
                                    <div class="absolute -bottom-2 -right-2 bg-white p-1.5 rounded-full shadow-md">
                                        <iconify-icon icon="lucide:shield-check" class="text-lg text-blue-500"></iconify-icon>
                                    </div>
                                </div>

                                <!-- Línea Conectora Vertical -->
                                <div x-show="selectedPropietario.vehiculos_propios.length > 0" class="w-0.5 h-10 bg-gradient-to-b from-[#001834] to-transparent"></div>

                                <!-- Lista de Vehículos Vinculados -->
                                <div class="w-full mt-4 space-y-4">
                                    <template x-for="veh in selectedPropietario.vehiculos_propios" :key="veh.idveh">
                                        <div class="flex items-center gap-6 group">
                                            <div class="flex-1 h-px bg-gradient-to-r from-transparent via-gray-200 to-gray-200"></div>
                                            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4 min-w-[300px] hover:border-blue-200 hover:shadow-lg hover:translate-y-[-2px] transition-all duration-300">
                                                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
                                                    <iconify-icon icon="lucide:car" class="text-2xl"></iconify-icon>
                                                </div>
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xs font-black text-white bg-[#001834] px-2 py-0.5 rounded border border-[#001834] font-mono tracking-wider" x-text="veh.placaveh"></span>
                                                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-tighter">Propiedad Registrada</span>
                                                    </div>
                                                    <p class="text-xs font-bold text-gray-700 mt-1" x-text="'ORD: ' + (veh.nordveh || 'S/I')"></p>
                                                </div>
                                                <div class="ml-auto">
                                                    <a :href="'/' + mupBase + '/vehiculos/' + veh.idveh + '/editar'" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-300 hover:bg-gray-50 hover:text-blue-600 transition-all" title="Ver Detalle Vehículo">
                                                        <iconify-icon icon="lucide:external-link" class="text-lg"></iconify-icon>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="flex-1 h-px bg-gradient-to-l from-transparent via-gray-200 to-gray-200"></div>
                                        </div>
                                    </template>

                                    <div x-show="selectedPropietario.vehiculos_propios.length === 0" class="text-center py-10 opacity-40">
                                        <iconify-icon icon="lucide:link-2-off" class="text-4xl mb-2"></iconify-icon>
                                        <p class="text-[10px] font-black uppercase tracking-widest">Sin vehículos a su nombre</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB: CONDUCCION (Si aplica) -->
                        <div x-show="detailTab === 'conduccion'" class="animate-in fade-in duration-300">
                            <div class="bg-gray-50 rounded-3xl p-8 border border-gray-100">
                                <div class="flex items-center gap-6 mb-8">
                                    <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center text-blue-500 shadow-sm border border-gray-100">
                                        <iconify-icon icon="lucide:id-card" class="text-3xl"></iconify-icon>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-black text-[#001834]">Información de Conducción</h4>
                                        <p class="text-xs text-gray-400 font-medium">Datos de licencia si el propietario también es conductor.</p>
                                    </div>
                                </div>

                                <div x-show="!selectedPropietario.nliccon" class="text-center py-6">
                                    <p class="text-sm font-bold text-gray-400">Este propietario no tiene datos de licencia registrados.</p>
                                </div>

                                <div x-show="selectedPropietario.nliccon" class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div class="bg-white p-5 rounded-2xl border border-gray-100">
                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Categoría</label>
                                        <p class="text-2xl font-black text-blue-600" x-text="selectedPropietario.catcon || '—'"></p>
                                    </div>
                                    <div class="bg-white p-5 rounded-2xl border border-gray-100">
                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Vencimiento</label>
                                        <p class="text-2xl font-black text-[#001834]" x-text="formatDate(selectedPropietario.fvencon)"></p>
                                    </div>
                                    <div class="bg-white p-5 rounded-2xl border border-gray-100 sm:col-span-2">
                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Número de Pase</label>
                                        <p class="text-lg font-bold text-gray-700" x-text="selectedPropietario.nliccon || 'N/A'"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Placeholder cuando no hay selección -->
            <div x-show="!selectedPropietario" class="h-full min-h-[500px] bg-white rounded-3xl border-2 border-dashed border-gray-100 flex flex-col items-center justify-center text-center p-10">
                <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-6 animate-pulse">
                    <iconify-icon icon="lucide:user-search" class="text-5xl text-gray-200"></iconify-icon>
                </div>
                <h3 class="text-xl font-black text-[#001834] tracking-tight">Seleccione un Propietario</h3>
                <p class="text-sm text-gray-400 mt-2 max-w-xs">Visualice los vehículos vinculados y la información de contacto detallada seleccionando un registro.</p>
            </div>
        </div>
    </div>

    <!-- MODAL: REGISTRO (CONSERVANDO LÓGICA ORIGINAL) -->
    <div x-show="showCreateModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-[#001834]/60 backdrop-blur-md" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div class="bg-white rounded-[32px] shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col" @click.away="showCreateModal = false">
            <div class="p-8 bg-[#001834] text-white flex justify-between items-center shrink-0">
                <div>
                    <h2 class="text-2xl font-black tracking-tight">Nuevo Propietario</h2>
                    <p class="text-white/60 text-[10px] font-bold uppercase tracking-widest mt-1">Alta en Registro Maestro</p>
                </div>
                <button @click="showCreateModal = false" class="w-10 h-10 rounded-full hover:bg-white/10 flex items-center justify-center transition-all">
                    <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
                </button>
            </div>

            <form action="{{ route($mupPrefix . '.propietarios.store') }}" method="POST" id="createForm" class="p-8 overflow-y-auto flex-1 custom-scrollbar">
                @csrf
                <input type="hidden" name="_propietario_form" value="create">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="sm:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Nombre Completo <span class="text-blue-500">*</span></label>
                        <input type="text" name="nombre_completo" value="{{ old('nombre_completo') }}" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" placeholder="Ej. Juan Manuel Galán" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Tipo Documento <span class="text-blue-500">*</span></label>
                        <select name="tdocper" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all appearance-none" required>
                            @foreach($tiposDoc as $tipo)
                                <option value="{{ $tipo->idval }}" @selected(old('tdocper') == $tipo->idval)>{{ $tipo->nomval }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Identificación <span class="text-blue-500">*</span></label>
                        <input type="number" name="ndocper" value="{{ old('ndocper') }}" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" placeholder="Solo números" required>
                    </div>

                    <div class="sm:col-span-2 pt-4">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="text-[10px] font-black text-[#001834]/40 uppercase tracking-[0.2em]">Ubicación y Contacto</span>
                            <div class="flex-1 h-px bg-gray-100"></div>
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Ciudad</label>
                        <input type="text" name="ciuper" value="{{ old('ciuper') }}" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" placeholder="Ej. Cali">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Teléfono</label>
                        <input type="text" name="telper" value="{{ old('telper') }}" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" placeholder="3001234567" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Dirección</label>
                        <input type="text" name="dirper" value="{{ old('dirper') }}" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" placeholder="Calle 10 # 5-20">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">E-mail <span class="text-blue-500">*</span></label>
                        <input type="email" name="emaper" value="{{ old('emaper') }}" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" placeholder="propietario@email.com" required>
                    </div>

                    <div class="sm:col-span-2 pt-4">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="text-[10px] font-black text-[#001834]/40 uppercase tracking-[0.2em]">Otros Datos (Opcional)</span>
                            <div class="flex-1 h-px bg-gray-100"></div>
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Categoría Lic.</label>
                        <select name="catcon" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all appearance-none">
                            <option value="">— Sin licencia —</option>
                            @foreach($licenciaCategorias as $cat)
                                <option value="{{ $cat }}" @selected(old('catcon') === $cat)>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Estado <span class="text-blue-500">*</span></label>
                        <select name="actper" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all appearance-none" required>
                            <option value="1" @selected(old('actper', '1') == '1')>Activo</option>
                            <option value="0" @selected((string) old('actper') === '0')>Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="mt-10 flex gap-4">
                    <button type="button" @click="showCreateModal = false" class="flex-1 py-4 text-gray-400 font-bold hover:bg-gray-50 rounded-2xl transition-all">Cancelar</button>
                    <button type="submit" class="flex-1 bg-[#001834] text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-[#001834]/20 transition-all hover:scale-[1.02]">
                        Guardar Propietario
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: EDICIÓN (CONSERVANDO LÓGICA ORIGINAL) -->
    <div x-show="editing" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-[#001834]/60 backdrop-blur-md" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div class="bg-white rounded-[32px] shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col" @click.away="closeModal()">
            <div class="p-8 bg-blue-600 text-white flex justify-between items-center shrink-0">
                <div>
                    <h2 class="text-2xl font-black tracking-tight" x-text="'Editar: ' + currentProp.nomper"></h2>
                    <p class="text-white/60 text-[10px] font-bold uppercase tracking-widest mt-1">Actualización de Registro Maestro</p>
                </div>
                <button @click="closeModal()" class="w-10 h-10 rounded-full hover:bg-white/10 flex items-center justify-center transition-all">
                    <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
                </button>
            </div>

            <form :action="'{{ url($mupBase . '/entidades/mup/propietarios') }}/' + currentProp.idper" method="POST" class="p-8 overflow-y-auto flex-1 custom-scrollbar">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="sm:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Nombre Completo <span class="text-blue-500">*</span></label>
                        <input type="text" name="nombre_completo" x-model="currentProp.nombre_completo" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Tipo Documento <span class="text-blue-500">*</span></label>
                        <select name="tdocper" x-model="currentProp.tdocper" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all appearance-none" required>
                            @foreach($tiposDoc as $tipo)
                                <option value="{{ $tipo->idval }}">{{ $tipo->nomval }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Identificación <span class="text-blue-500">*</span></label>
                        <input type="number" name="ndocper" x-model="currentProp.ndocper" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" required>
                    </div>

                    <div class="sm:col-span-2 pt-4">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="text-[10px] font-black text-[#001834]/40 uppercase tracking-[0.2em]">Ubicación y Contacto</span>
                            <div class="flex-1 h-px bg-gray-100"></div>
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Ciudad</label>
                        <input type="text" name="ciuper" x-model="currentProp.ciuper" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Teléfono</label>
                        <input type="text" name="telper" x-model="currentProp.telper" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" placeholder="300 000 0000" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Dirección</label>
                        <input type="text" name="dirper" x-model="currentProp.dirper" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">E-mail <span class="text-blue-500">*</span></label>
                        <input type="email" name="emaper" x-model="currentProp.emaper" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" required>
                    </div>

                    <div class="sm:col-span-2 pt-4">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="text-[10px] font-black text-[#001834]/40 uppercase tracking-[0.2em]">Otros Datos (Opcional)</span>
                            <div class="flex-1 h-px bg-gray-100"></div>
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Categoría Lic.</label>
                        <select name="catcon" x-model="currentProp.catcon" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all appearance-none">
                            <option value="">— Sin licencia —</option>
                            @foreach($licenciaCategorias as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Estado Operativo <span class="text-blue-500">*</span></label>
                        <select name="actper" x-model="currentProp.actper" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all appearance-none" required>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="mt-10 flex gap-4">
                    <button type="button" @click="closeModal()" class="flex-1 py-4 text-gray-400 font-bold hover:bg-gray-50 rounded-2xl transition-all">Descartar</button>
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-blue-500/20 transition-all hover:scale-[1.02]">
                        Actualizar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: ELIMINAR (CONSERVANDO LÓGICA ORIGINAL) -->
    <div x-show="deleteModal" class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-[#001834]/80 backdrop-blur-md" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <div class="bg-white p-8 rounded-[32px] shadow-2xl max-w-sm w-full text-center" @click.away="deleteModal = false">
            <div class="w-24 h-24 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-8 shadow-inner">
                <iconify-icon icon="lucide:alert-triangle" style="font-size: 48px;"></iconify-icon>
            </div>
            <h3 class="text-2xl font-black text-[#001834] tracking-tight mb-2">¿Eliminar Propietario?</h3>
            <p class="text-sm text-gray-400 mb-10 leading-relaxed px-4">
                Esta acción es permanente y eliminará la ficha de <span class="text-[#001834] font-black" x-text="currentProp.nomper"></span> y sus vínculos históricos.
            </p>
            
            <div class="flex gap-4">
                <button @click="deleteModal = false" class="flex-1 py-4 text-gray-400 font-bold hover:bg-gray-50 rounded-2xl transition-all">No, volver</button>
                <form :action="'{{ url($mupBase . '/entidades/mup/propietarios') }}/' + currentProp.idper" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-4 bg-red-600 text-white font-black text-xs uppercase tracking-widest rounded-2xl shadow-xl shadow-red-200 hover:scale-[1.05] transition-all">Sí, borrar</button>
                </form>
            </div>
        </div>
    </div>

</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #d1d5db; }
</style>

<script>
function propietarioManager() {
    return {
        search: '',
        editing: false,
        deleteModal: false,
        showCreateModal: false,
        detailTab: 'info',
        selectedId: null,
        selectedPropietario: null,
        currentProp: {},
        propietarios: @json($propietarios),
        tiposDoc: @json($tiposDoc->pluck('nomval', 'idval')),
        
        init() {
            // Seleccionar el primero por defecto si existe
            if (this.propietarios.length > 0) {
                this.selectPropietario(this.propietarios[0]);
            }

            @if($errors->any() && old('_propietario_form') === 'create')
                this.showCreateModal = true;
            @endif
        },

        selectPropietario(p) {
            this.selectedId = p.idper;
            this.selectedPropietario = p;
        },

        exportCsv() {
            const cols = ['idper', 'nomper', 'apeper', 'ndocper', 'tdocper', 'emaper', 'telper', 'ciuper', 'dirper', 'actper', 'catcon', 'nliccon', 'fvencon'];
            const list = this.filteredPropietarios();
            const esc = (v) => {
                if (v === null || v === undefined) return '';
                const s = String(v);
                if (/[",\n]/.test(s)) return '"' + s.replace(/"/g, '""') + '"';
                return s;
            };
            let csv = cols.join(',') + '\n';
            for (const p of list) {
                csv += cols.map((c) => esc(p[c])).join(',') + '\n';
            }
            const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'propietarios_mup_' + new Date().toISOString().slice(0, 10) + '.csv';
            a.click();
            URL.revokeObjectURL(url);
        },

        filteredPropietarios() {
            if (!this.search) return this.propietarios;
            const q = this.search.toLowerCase();
            return this.propietarios.filter(p => 
                p.nomper.toLowerCase().includes(q) || 
                (p.apeper && p.apeper.toLowerCase().includes(q)) ||
                p.ndocper.toString().includes(q) ||
                (p.ciuper && p.ciuper.toLowerCase().includes(q)) ||
                (p.emaper && p.emaper.toLowerCase().includes(q))
            );
        },

        editPropietario(p) {
            const fven = p.fvencon ? String(p.fvencon).slice(0, 10) : '';
            this.currentProp = { 
                ...p, 
                nombre_completo: p.nomper + ' ' + (p.apeper || ''),
                tdocper: p.tdocper != null ? String(p.tdocper) : '',
                actper: p.actper != null ? String(p.actper) : '1',
                catcon: p.catcon || '',
                nliccon: p.nliccon || '',
                fvencon: fven,
            };
            this.editing = true;
        },

        confirmDelete(p) {
            this.currentProp = p;
            this.deleteModal = true;
        },

        closeModal() {
            this.editing = false;
            this.currentProp = {};
        },

        getDocType(id) {
            if (id === null || id === undefined || id === '') return 'N/A';
            const key = String(id);
            return this.tiposDoc[key] || this.tiposDoc[id] || 'N/A';
        },

        formatDate(dateStr) {
            if (!dateStr) return 'N/A';
            const date = new Date(dateStr);
            return date.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
        },

        numberFormat(num) {
            return new Intl.NumberFormat('es-CO').format(num);
        },

        activeCount() {
            return this.propietarios.filter(p => Number(p.actper) === 1).length;
        }
    }
}
</script>
@endsection
