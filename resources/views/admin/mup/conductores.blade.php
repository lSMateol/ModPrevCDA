@extends('layouts.app')

@section('content')
@include('admin.mup.partials.flash')
<link rel="stylesheet" href="{{ asset('css/mup.css') }}">
<script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>

@php
    $mupBase = auth()->user()->hasRole('Administrador') ? 'admin' : 'digitador';
    $mupPrefix = $mupBase . '.mup';
@endphp

<div class="px-4 sm:px-10 pb-20 max-w-[1600px] mx-auto" x-data="conductorManager()" x-init="init()" x-cloak>
    
    <!-- HEADER & BENTO METRICS -->
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-end gap-6 mb-8 mt-4">
        <div>
            <h1 class="font-headline font-black text-[#002D54] text-2xl md:text-3xl tracking-tight">Gestión de Conductores</h1>
            <p class="text-on-surface-variant font-body text-sm mt-1">Directorio maestro y control de licencias operativas</p>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 w-full xl:w-auto">
            <div class="bg-white p-4 rounded-2xl shadow-sm border-b-4 border-[#0d3b5a] flex flex-col justify-center min-w-[120px]">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total</span>
                <span class="text-2xl font-black text-[#001834]" x-text="conductores.length"></span>
            </div>
            <div class="bg-white p-4 rounded-2xl shadow-sm border-b-4 border-emerald-500 flex flex-col justify-center min-w-[120px]">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Activos</span>
                <span class="text-2xl font-black text-emerald-600" x-text="activeCount()"></span>
            </div>
            <div class="bg-[#001834] p-4 rounded-2xl shadow-lg flex flex-col justify-center min-w-[120px]">
                <span class="text-[10px] font-black text-white/40 uppercase tracking-widest mb-1">Filtrados</span>
                <span class="text-2xl font-black text-primary-fixed-dim" x-text="filteredConductores().length"></span>
            </div>
            <button @click="showCreateModal = true" class="bg-gradient-to-br from-orange-500 to-orange-600 text-white p-4 rounded-2xl shadow-lg shadow-orange-500/20 flex flex-col items-center justify-center gap-1 hover:scale-[1.03] transition-all group">
                <iconify-icon icon="lucide:user-plus" class="text-xl group-hover:rotate-12 transition-transform"></iconify-icon>
                <span class="text-[9px] font-black uppercase tracking-tighter">Nuevo</span>
            </button>
        </div>
    </div>

    @include('admin.mup.partials.navigation')

    <!-- MAIN CONTENT: MASTER-DETAIL SPLIT VIEW -->
    <div class="grid grid-cols-12 gap-8 mt-6">
        
        <!-- MASTER COLUMN: SEARCH & LIST -->
        <div class="col-span-12 lg:col-span-5 xl:col-span-4 flex flex-col gap-6">
            
            <!-- Barra de Búsqueda Premium -->
            <div class="relative group">
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-orange-500 transition-colors">
                    <iconify-icon icon="lucide:search" class="text-xl"></iconify-icon>
                </div>
                <input type="text" x-model="search" placeholder="Buscar por nombre, documento..." 
                    class="w-full bg-white border-2 border-transparent focus:border-orange-500/20 focus:ring-4 focus:ring-orange-500/5 rounded-2xl py-4 pl-12 pr-4 shadow-sm text-sm font-semibold transition-all">
                
                <div class="absolute right-4 top-1/2 -translate-y-1/2 flex gap-2">
                    <button @click="exportCsv()" class="p-2 text-gray-400 hover:text-[#0d3b5a] transition-colors" title="Exportar CSV">
                        <iconify-icon icon="lucide:download" class="text-lg"></iconify-icon>
                    </button>
                </div>
            </div>

            <!-- Listado de Resultados (Scrollable) -->
            <div class="flex flex-col gap-3 max-h-[calc(100vh-350px)] overflow-y-auto pr-2 custom-scrollbar">
                <template x-for="con in filteredConductores()" :key="con.idper">
                    <div @click="selectConductor(con)" 
                        class="group bg-white p-4 rounded-2xl border-2 transition-all cursor-pointer relative overflow-hidden"
                        :class="selectedId === con.idper ? 'border-orange-500 shadow-md translate-x-2' : 'border-transparent hover:border-gray-200 shadow-sm'">
                        
                        <div class="flex items-center gap-4">
                            <!-- Avatar con iniciales -->
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-black text-sm shrink-0 transition-transform group-hover:scale-110"
                                :class="con.actper ? 'bg-gradient-to-br from-[#0d3b5a] to-[#1a4f73]' : 'bg-gray-300'"
                                x-text="con.nomper[0] + (con.apeper ? con.apeper[0] : '')">
                            </div>
                            
                            <div class="min-w-0 flex-1">
                                <div class="flex justify-between items-start">
                                    <h3 class="font-bold text-[#001834] text-sm truncate uppercase tracking-tight" x-text="con.nomper + ' ' + (con.apeper || '')"></h3>
                                    <span :class="con.actper ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-50 text-gray-400'" 
                                        class="text-[9px] font-black px-2 py-0.5 rounded-full uppercase tracking-tighter"
                                        x-text="con.actper ? 'Activo' : 'Inactivo'"></span>
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <iconify-icon icon="lucide:hash" class="text-gray-400 text-[10px]"></iconify-icon>
                                    <span class="text-[11px] font-bold text-gray-500" x-text="numberFormat(con.ndocper)"></span>
                                    <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                    <span class="text-[10px] text-gray-400 uppercase font-black" x-text="con.catcon ? 'CAT. ' + con.catcon : 'S/L'"></span>
                                </div>
                            </div>

                            <iconify-icon icon="lucide:chevron-right" class="text-gray-300 group-hover:text-orange-500 transition-colors"></iconify-icon>
                        </div>

                        <!-- Indicador de selección activa -->
                        <div x-show="selectedId === con.idper" class="absolute left-0 top-0 bottom-0 w-1 bg-orange-500"></div>
                    </div>
                </template>

                <div x-show="filteredConductores().length === 0" class="bg-white/50 border-2 border-dashed border-gray-200 rounded-3xl p-10 text-center flex flex-col items-center gap-3">
                    <iconify-icon icon="lucide:user-x" class="text-4xl text-gray-200"></iconify-icon>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Sin resultados</p>
                </div>
            </div>
        </div>

        <!-- DETAIL COLUMN: INFORMATION & GRAPH -->
        <div class="col-span-12 lg:col-span-7 xl:col-span-8">
            
            <template x-if="selectedConductor">
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden animate-in fade-in slide-in-from-right-4 duration-500">
                    
                    <!-- Detail Header -->
                    <div class="p-6 sm:p-8 bg-gradient-to-r from-[#0d3b5a] to-[#0a2d46] text-white">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
                            <div class="flex items-center gap-5">
                                <div class="w-20 h-20 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-3xl font-black shadow-inner"
                                    x-text="selectedConductor.nomper[0] + (selectedConductor.apeper ? selectedConductor.apeper[0] : '')">
                                </div>
                                <div>
                                    <div class="flex items-center gap-3">
                                        <h2 class="text-2xl font-black tracking-tight" x-text="selectedConductor.nomper + ' ' + (selectedConductor.apeper || '')"></h2>
                                        <span x-show="selectedConductor.actper" class="bg-emerald-500/20 text-emerald-300 text-[10px] font-black px-3 py-1 rounded-full border border-emerald-500/30 uppercase tracking-widest">Disponible</span>
                                    </div>
                                    <p class="text-white/60 text-xs font-medium mt-1 flex items-center gap-2">
                                        <iconify-icon icon="lucide:mail"></iconify-icon>
                                        <span x-text="selectedConductor.emaper"></span>
                                        <span class="opacity-30">|</span>
                                        <iconify-icon icon="lucide:phone"></iconify-icon>
                                        <span x-text="selectedConductor.telper || 'Sin teléfono'"></span>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex gap-2 w-full sm:w-auto">
                                <button @click="editConductor(selectedConductor)" class="flex-1 sm:flex-none bg-white/10 hover:bg-white/20 text-white px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-widest transition-all border border-white/10">
                                    Editar Perfil
                                </button>
                                <button @click="deleteConductor(selectedConductor)" class="p-3 bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white rounded-xl transition-all border border-red-500/20">
                                    <iconify-icon icon="lucide:trash-2" class="text-xl"></iconify-icon>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Tabs -->
                    <div class="flex border-b border-gray-100 bg-gray-50/50 px-8">
                        <button @click="detailTab = 'info'" 
                            class="px-6 py-4 text-[11px] font-black uppercase tracking-widest transition-all border-b-2"
                            :class="detailTab === 'info' ? 'border-orange-500 text-[#0d3b5a]' : 'border-transparent text-gray-400 hover:text-gray-600'">
                            Información General
                        </button>
                        <button @click="detailTab = 'licencia'" 
                            class="px-6 py-4 text-[11px] font-black uppercase tracking-widest transition-all border-b-2"
                            :class="detailTab === 'licencia' ? 'border-orange-500 text-[#0d3b5a]' : 'border-transparent text-gray-400 hover:text-gray-600'">
                            Licencia de Conducción
                        </button>
                        <button @click="detailTab = 'vinculos'" 
                            class="px-6 py-4 text-[11px] font-black uppercase tracking-widest transition-all border-b-2"
                            :class="detailTab === 'vinculos' ? 'border-orange-500 text-[#0d3b5a]' : 'border-transparent text-gray-400 hover:text-gray-600'">
                            Vehículos Vinculados
                        </button>
                    </div>

                    <!-- Detail Content -->
                    <div class="p-8">
                        <!-- TAB: INFO -->
                        <div x-show="detailTab === 'info'" class="grid grid-cols-1 md:grid-cols-2 gap-8 animate-in fade-in duration-300">
                            <div class="space-y-6">
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Documento de Identidad</label>
                                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                        <p class="text-sm font-bold text-[#0d3b5a]" x-text="getDocType(selectedConductor.tdocper)"></p>
                                        <p class="text-2xl font-black text-[#001834] mt-1" x-text="numberFormat(selectedConductor.ndocper)"></p>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Contacto Directo</label>
                                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 flex items-center gap-4">
                                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-orange-500 shadow-sm border border-gray-100">
                                            <iconify-icon icon="lucide:smartphone" class="text-xl"></iconify-icon>
                                        </div>
                                        <p class="text-lg font-black text-[#001834]" x-text="selectedConductor.telper || 'N/A'"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-6">
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Registro del Sistema</label>
                                    <div class="bg-white p-5 rounded-2xl border-2 border-dashed border-gray-100">
                                        <div class="flex justify-between items-center mb-4">
                                            <span class="text-xs font-bold text-gray-500">ID de Ficha</span>
                                            <span class="bg-[#0d3b5a] text-white px-3 py-1 rounded-lg text-[10px] font-black" x-text="'C-' + selectedConductor.idper"></span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-xs font-bold text-gray-500">Perfil CDA</span>
                                            <span class="text-xs font-black text-[#0d3b5a] uppercase tracking-tighter">Conductor Operativo</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB: LICENCIA -->
                        <div x-show="detailTab === 'licencia'" class="animate-in fade-in duration-300">
                            <div x-show="!selectedConductor.nliccon" class="bg-amber-50 border-l-4 border-amber-400 p-6 rounded-2xl flex items-center gap-4">
                                <iconify-icon icon="lucide:alert-circle" class="text-3xl text-amber-500"></iconify-icon>
                                <div>
                                    <p class="text-sm font-bold text-amber-900">Información de Licencia no Registrada</p>
                                    <p class="text-xs text-amber-700">Este conductor no tiene datos de licencia cargados en el sistema.</p>
                                </div>
                            </div>

                            <div x-show="selectedConductor.nliccon" class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 text-center">Categoría</p>
                                    <div class="text-3xl font-black text-orange-500 text-center" x-text="selectedConductor.catcon || '—'"></div>
                                </div>
                                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm col-span-2">
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Número de Licencia</p>
                                    <div class="text-2xl font-black text-[#001834]" x-text="selectedConductor.nliccon || 'N/A'"></div>
                                </div>
                                <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm col-span-3 flex items-center justify-between">
                                    <div>
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Fecha de Vencimiento</p>
                                        <p class="text-xl font-black text-[#0d3b5a]" x-text="formatDate(selectedConductor.fvencon)"></p>
                                    </div>
                                    <div :class="isExpiringSoon(selectedConductor.fvencon) ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600'" 
                                        class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest">
                                        <span x-text="isExpiringSoon(selectedConductor.fvencon) ? 'Vencimiento Próximo' : 'Licencia Vigente'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB: VINCULOS (GRAFO) -->
                        <div x-show="detailTab === 'vinculos'" class="animate-in fade-in duration-300">
                            <div class="flex flex-col items-center py-6">
                                <!-- Nodo Central -->
                                <div class="relative z-10">
                                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-[#0d3b5a] to-[#1a4f73] flex items-center justify-center text-white text-2xl font-black shadow-xl border-4 border-white"
                                        x-text="selectedConductor.nomper[0] + (selectedConductor.apeper ? selectedConductor.apeper[0] : '')">
                                    </div>
                                    <div class="absolute -bottom-2 -right-2 bg-white p-1.5 rounded-full shadow-md">
                                        <iconify-icon icon="lucide:user" class="text-lg text-orange-500"></iconify-icon>
                                    </div>
                                </div>

                                <!-- Línea Conectora Vertical -->
                                <div x-show="selectedConductor.vehiculos_conducidos.length > 0" class="w-0.5 h-10 bg-gradient-to-b from-[#0d3b5a] to-transparent"></div>

                                <!-- Lista de Vehículos Vinculados -->
                                <div class="w-full mt-4 space-y-4">
                                    <template x-for="veh in selectedConductor.vehiculos_conducidos" :key="veh.idveh">
                                        <div class="flex items-center gap-6 group">
                                            <div class="flex-1 h-px bg-gradient-to-r from-transparent via-gray-200 to-gray-200"></div>
                                            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4 min-w-[280px] hover:border-orange-200 hover:shadow-lg hover:translate-y-[-2px] transition-all duration-300">
                                                <div class="w-12 h-12 bg-gray-50 rounded-xl flex items-center justify-center text-[#0d3b5a]">
                                                    <iconify-icon icon="lucide:car" class="text-2xl"></iconify-icon>
                                                </div>
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xs font-black text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-200 font-mono" x-text="veh.placaveh"></span>
                                                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-tighter">Vehículo Vinculado</span>
                                                    </div>
                                                    <p class="text-xs font-bold text-gray-700 mt-1" x-text="(veh.nordveh ? 'INT: ' + veh.nordveh : 'S/I')"></p>
                                                </div>
                                                <div class="ml-auto">
                                                    <a :href="'/' + mupBase + '/vehiculos/' + veh.idveh + '/editar'" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-300 hover:bg-gray-50 hover:text-[#0d3b5a] transition-all" title="Gestionar Vehículo">
                                                        <iconify-icon icon="lucide:external-link" class="text-lg"></iconify-icon>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="flex-1 h-px bg-gradient-to-l from-transparent via-gray-200 to-gray-200"></div>
                                        </div>
                                    </template>

                                    <div x-show="selectedConductor.vehiculos_conducidos.length === 0" class="text-center py-10 opacity-40">
                                        <iconify-icon icon="lucide:link-2-off" class="text-4xl mb-2"></iconify-icon>
                                        <p class="text-[10px] font-black uppercase tracking-widest">Sin vehículos vinculados</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Placeholder cuando no hay selección -->
            <div x-show="!selectedConductor" class="h-full min-h-[500px] bg-white rounded-3xl border-2 border-dashed border-gray-100 flex flex-col items-center justify-center text-center p-10">
                <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-6 animate-pulse">
                    <iconify-icon icon="lucide:user-check" class="text-5xl text-gray-200"></iconify-icon>
                </div>
                <h3 class="text-xl font-black text-[#001834] tracking-tight">Seleccione un Conductor</h3>
                <p class="text-sm text-gray-400 mt-2 max-w-xs">Haga clic en un registro de la lista de la izquierda para ver su información detallada y grafo de vínculos.</p>
            </div>
        </div>
    </div>

    <!-- MODAL: REGISTRO (CONSERVANDO LÓGICA ORIGINAL) -->
    <div x-show="showCreateModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-[#001834]/60 backdrop-blur-md" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div class="bg-white rounded-[32px] shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col" @click.away="showCreateModal = false">
            <div class="p-8 bg-[#0d3b5a] text-white flex justify-between items-center shrink-0">
                <div>
                    <h2 class="text-2xl font-black tracking-tight">Registrar Nuevo Conductor</h2>
                    <p class="text-white/60 text-[10px] font-bold uppercase tracking-widest mt-1">Alta en Módulo MUP</p>
                </div>
                <button @click="showCreateModal = false" class="w-10 h-10 rounded-full hover:bg-white/10 flex items-center justify-center transition-all">
                    <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
                </button>
            </div>

            <form action="{{ route($mupPrefix . '.conductores.store') }}" method="POST" id="createForm" class="p-8 overflow-y-auto flex-1 custom-scrollbar">
                @csrf
                <input type="hidden" name="_mup_conductor_form" value="create">
                
                @if($errors->any() && old('_mup_conductor_form') === 'create')
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-2xl animate-in slide-in-from-top duration-300">
                        <div class="flex items-center gap-3 mb-2">
                            <iconify-icon icon="lucide:alert-circle" class="text-red-500 text-xl"></iconify-icon>
                            <span class="text-xs font-black text-red-700 uppercase tracking-widest">Errores de validación</span>
                        </div>
                        <ul class="text-xs text-red-600 font-medium space-y-1 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="sm:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Nombre Completo <span class="text-orange-500">*</span></label>
                        <input type="text" name="nombre_completo" value="{{ old('nombre_completo') }}" class="w-full bg-gray-50 border-2 border-transparent focus:border-orange-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" placeholder="Ej. Carlos Alberto Soto" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Tipo Documento <span class="text-orange-500">*</span></label>
                        <select name="tdocper" class="w-full bg-gray-50 border-2 border-transparent focus:border-orange-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all appearance-none" required>
                            @foreach($tiposDoc as $tipo)
                                <option value="{{ $tipo->idval }}" @selected(old('tdocper') == $tipo->idval)>{{ $tipo->nomval }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Identificación <span class="text-orange-500">*</span></label>
                        <input type="number" name="ndocper" value="{{ old('ndocper') }}" class="w-full bg-gray-50 border-2 border-transparent focus:border-orange-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" placeholder="Solo números" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Correo Electrónico <span class="text-orange-500">*</span></label>
                        <input type="email" name="emaper" value="{{ old('emaper') }}" class="w-full bg-gray-50 border-2 border-transparent focus:border-orange-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" placeholder="ejemplo@email.com" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Celular / Teléfono</label>
                        <input type="text" name="telper" value="{{ old('telper') }}" class="w-full bg-gray-50 border-2 border-transparent focus:border-orange-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" placeholder="3001234567" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" maxlength="20">
                    </div>

                    <div class="sm:col-span-2 pt-4">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="text-[10px] font-black text-[#0d3b5a]/40 uppercase tracking-[0.2em]">Datos de Licencia</span>
                            <div class="flex-1 h-px bg-gray-100"></div>
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Categoría <span class="text-orange-500">*</span></label>
                        <select name="catcon" class="w-full bg-gray-50 border-2 border-transparent focus:border-orange-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all appearance-none" required>
                            <option value="">Seleccione categoría</option>
                            @foreach($licenciaCategorias as $cat)
                                <option value="{{ $cat }}" @selected(old('catcon') === $cat)>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Nro de Licencia <span class="text-orange-500">*</span></label>
                        <input type="text" name="nliccon" value="{{ old('nliccon') }}" class="w-full bg-gray-50 border-2 border-transparent focus:border-orange-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" placeholder="Nro de pase" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Vencimiento <span class="text-orange-500">*</span></label>
                        <input type="date" name="fvencon" value="{{ old('fvencon') }}" class="w-full bg-gray-50 border-2 border-transparent focus:border-orange-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Estado Operativo <span class="text-orange-500">*</span></label>
                        <select name="actper" class="w-full bg-gray-50 border-2 border-transparent focus:border-orange-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all appearance-none" required>
                            <option value="1" @selected(old('actper', '1') == '1')>Disponible / Activo</option>
                            <option value="0" @selected((string) old('actper') === '0')>Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="mt-10 flex gap-4">
                    <button type="button" @click="showCreateModal = false" class="flex-1 py-4 text-gray-400 font-bold hover:bg-gray-50 rounded-2xl transition-all">Cancelar</button>
                    <button type="submit" class="flex-1 bg-[#0d3b5a] text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-[#0d3b5a]/20 transition-all hover:scale-[1.02]">
                        Finalizar Registro
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: EDICIÓN (CONSERVANDO LÓGICA ORIGINAL) -->
    <div x-show="editing" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-[#001834]/60 backdrop-blur-md" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div class="bg-white rounded-[32px] shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col" @click.away="closeModal()">
            <div class="p-8 bg-orange-500 text-white flex justify-between items-center shrink-0">
                <div>
                    <h2 class="text-2xl font-black tracking-tight" x-text="'Editar: ' + currentCon.nomper"></h2>
                    <p class="text-white/60 text-[10px] font-bold uppercase tracking-widest mt-1">Actualización de ficha</p>
                </div>
                <button @click="closeModal()" class="w-10 h-10 rounded-full hover:bg-white/10 flex items-center justify-center transition-all">
                    <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
                </button>
            </div>

            <form :action="'{{ url($mupBase . '/entidades/mup/conductores') }}/' + currentCon.idper" method="POST" class="p-8 overflow-y-auto flex-1 custom-scrollbar">
                @csrf
                @method('PUT')
                <input type="hidden" name="_mup_conductor_form" value="edit">
                <input type="hidden" name="idper" :value="currentCon.idper">

                @if($errors->any() && old('_mup_conductor_form') === 'edit')
                    <div class="mb-6 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-2xl animate-in slide-in-from-top duration-300">
                        <div class="flex items-center gap-3 mb-2">
                            <iconify-icon icon="lucide:alert-circle" class="text-amber-500 text-xl"></iconify-icon>
                            <span class="text-xs font-black text-amber-700 uppercase tracking-widest">Errores de validación</span>
                        </div>
                        <ul class="text-xs text-amber-600 font-medium space-y-1 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="sm:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Nombre Completo <span class="text-orange-500">*</span></label>
                        <input type="text" name="nombre_completo" x-model="currentCon.nombre_completo" class="w-full bg-gray-50 border-2 border-transparent focus:border-orange-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Tipo Documento <span class="text-orange-500">*</span></label>
                        <select name="tdocper" x-model="currentCon.tdocper" class="w-full bg-gray-50 border-2 border-transparent focus:border-orange-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all appearance-none" required>
                            @foreach($tiposDoc as $tipo)
                                <option value="{{ $tipo->idval }}">{{ $tipo->nomval }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Identificación <span class="text-orange-500">*</span></label>
                        <input type="number" name="ndocper" x-model="currentCon.ndocper" class="w-full bg-gray-50 border-2 border-transparent focus:border-orange-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Correo Electrónico <span class="text-orange-500">*</span></label>
                        <input type="email" name="emaper" x-model="currentCon.emaper" class="w-full bg-gray-50 border-2 border-transparent focus:border-orange-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Celular / Teléfono</label>
                        <input type="text" name="telper" x-model="currentCon.telper" class="w-full bg-gray-50 border-2 border-transparent focus:border-orange-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" placeholder="300 000 0000" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>

                    <div class="sm:col-span-2 pt-4">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="text-[10px] font-black text-[#0d3b5a]/40 uppercase tracking-[0.2em]">Datos de Licencia</span>
                            <div class="flex-1 h-px bg-gray-100"></div>
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Categoría <span class="text-orange-500">*</span></label>
                        <select name="catcon" x-model="currentCon.catcon" class="w-full bg-gray-50 border-2 border-transparent focus:border-orange-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all appearance-none" required>
                            <option value="">Seleccione categoría</option>
                            @foreach($licenciaCategorias as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Nro de Licencia <span class="text-orange-500">*</span></label>
                        <input type="text" name="nliccon" x-model="currentCon.nliccon" class="w-full bg-gray-50 border-2 border-transparent focus:border-orange-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Vencimiento <span class="text-orange-500">*</span></label>
                        <input type="date" name="fvencon" x-model="currentCon.fvencon" class="w-full bg-gray-50 border-2 border-transparent focus:border-orange-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Estado Operativo <span class="text-orange-500">*</span></label>
                        <select name="actper" x-model="currentCon.actper" class="w-full bg-gray-50 border-2 border-transparent focus:border-orange-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all appearance-none" required>
                            <option value="1">Disponible / Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="mt-10 flex gap-4">
                    <button type="button" @click="closeModal()" class="flex-1 py-4 text-gray-400 font-bold hover:bg-gray-50 rounded-2xl transition-all">Descartar</button>
                    <button type="submit" class="flex-1 bg-orange-500 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-orange-500/20 transition-all hover:scale-[1.02]">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: ELIMINAR (CONSERVANDO LÓGICA ORIGINAL) -->
    <div x-show="deleting" class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-[#001834]/80 backdrop-blur-md" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <div class="bg-white p-8 rounded-[32px] shadow-2xl max-w-sm w-full text-center" @click.away="deleting = false">
            <div class="w-24 h-24 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-8 shadow-inner">
                <iconify-icon icon="lucide:alert-triangle" style="font-size: 48px;"></iconify-icon>
            </div>
            <h3 class="text-2xl font-black text-[#001834] tracking-tight mb-2">¿Eliminar Conductor?</h3>
            <p class="text-sm text-gray-400 mb-10 leading-relaxed px-4">
                Esta acción es permanente y eliminará la ficha de <span class="text-[#001834] font-black" x-text="currentCon.nomper"></span> y sus vínculos históricos en el sistema.
            </p>
            
            <div class="flex gap-4">
                <button @click="deleting = false" class="flex-1 py-4 text-gray-400 font-bold hover:bg-gray-50 rounded-2xl transition-all">No, volver</button>
                <form :action="'{{ url($mupBase . '/entidades/mup/conductores') }}/' + currentCon.idper" method="POST" class="flex-1">
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
function conductorManager() {
    return {
        search: '',
        mupBase: '{{ $mupBase }}',
        editing: false,
        deleting: false,
        showCreateModal: false,
        detailTab: 'info',
        selectedId: null,
        selectedConductor: null,
        currentCon: {},
        conductores: @json($conductores),
        tiposDoc: @json($tiposDoc->pluck('nomval', 'idval')),
        
        init() {
            // Reabrir modal si hay errores
            @if($errors->any())
                @if(old('_mup_conductor_form') === 'create')
                    this.showCreateModal = true;
                @elseif(old('_mup_conductor_form') === 'edit')
                    const oldId = '{{ old('idper') }}';
                    if (oldId) {
                        const c = this.conductores.find(x => x.idper == oldId);
                        if (c) this.editConductor(c);
                    }
                @endif
            @endif

            // Seleccionar el primero por defecto si existe y no hay modal abierto
            if (this.conductores.length > 0 && !this.showCreateModal && !this.editing) {
                this.selectConductor(this.conductores[0]);
            }
        },

        selectConductor(con) {
            this.selectedId = con.idper;
            this.selectedConductor = con;
            // No reseteamos el tab a menos que sea necesario para mejorar UX
        },

        exportCsv() {
            const cols = ['idper', 'nomper', 'apeper', 'ndocper', 'tdocper', 'emaper', 'telper', 'actper', 'catcon', 'nliccon', 'fvencon'];
            const list = this.filteredConductores();
            const esc = (v) => {
                if (v === null || v === undefined) return '';
                const s = String(v);
                if (/[",\n]/.test(s)) return '"' + s.replace(/"/g, '""') + '"';
                return s;
            };
            let csv = cols.join(',') + '\n';
            for (const c of list) {
                csv += cols.map((col) => esc(c[col])).join(',') + '\n';
            }
            const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'conductores_mup_' + new Date().toISOString().slice(0, 10) + '.csv';
            a.click();
            URL.revokeObjectURL(url);
        },

        filteredConductores() {
            if (!this.search) return this.conductores;
            const q = this.search.toLowerCase();
            return this.conductores.filter(c => 
                c.nomper.toLowerCase().includes(q) || 
                (c.apeper && c.apeper.toLowerCase().includes(q)) ||
                c.ndocper.toString().includes(q) ||
                (c.nliccon && c.nliccon.toLowerCase().includes(q)) ||
                (c.catcon && String(c.catcon).toLowerCase().includes(q)) ||
                (c.emaper && c.emaper.toLowerCase().includes(q))
            );
        },

        editConductor(con) {
            const fven = con.fvencon ? String(con.fvencon).slice(0, 10) : '';
            this.currentCon = { 
                ...con, 
                nombre_completo: con.nomper + ' ' + (con.apeper || ''),
                tdocper: con.tdocper != null ? String(con.tdocper) : '',
                actper: con.actper != null ? String(con.actper) : '1',
                catcon: con.catcon || '',
                nliccon: con.nliccon || '',
                fvencon: fven,
            };
            this.editing = true;
        },

        deleteConductor(con) {
            this.currentCon = con;
            this.deleting = true;
        },

        closeModal() {
            this.editing = false;
            this.currentCon = {};
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
            return this.conductores.filter(c => Number(c.actper) === 1).length;
        },

        isExpiringSoon(dateStr) {
            if (!dateStr) return false;
            const date = new Date(dateStr);
            const today = new Date();
            const diff = date - today;
            return diff < (30 * 24 * 60 * 60 * 1000); // 30 días
        }
    }
}
</script>
@endsection
