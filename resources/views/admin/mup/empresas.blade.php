@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/mup.css') }}">
<script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>

@php
    $mupBase = auth()->user()->hasRole('Administrador') ? 'admin' : 'digitador';
    $mupPrefix = $mupBase . '.mup';
@endphp

<div class="px-4 sm:px-10 pb-20 max-w-[1600px] mx-auto" x-data="empresaManager()" x-init="init()" x-cloak>
    
    <!-- HEADER & BENTO METRICS -->
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-end gap-6 mb-8 mt-4">
        <div>
            <h1 class="font-headline font-black text-[#002D54] text-2xl md:text-3xl tracking-tight">Directorio Empresarial</h1>
            <p class="text-on-surface-variant font-body text-sm mt-1">Gestión corporativa, flotas y cuentas de acceso</p>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 w-full xl:w-auto">
            <div class="bg-white p-4 rounded-2xl shadow-sm border-b-4 border-[#0d3b5a] flex flex-col justify-center min-w-[120px]">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total NITs</span>
                <span class="text-2xl font-black text-[#001834]" x-text="empresas.length"></span>
            </div>
            <div class="bg-white p-4 rounded-2xl shadow-sm border-b-4 border-amber-500 flex flex-col justify-center min-w-[120px]">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Activas</span>
                <span class="text-2xl font-black text-amber-600" x-text="empresas.length"></span> <!-- Asumiendo todas activas por ahora según vista previa -->
            </div>
            <div class="bg-[#001834] p-4 rounded-2xl shadow-lg flex flex-col justify-center min-w-[120px]">
                <span class="text-[10px] font-black text-white/40 uppercase tracking-widest mb-1">Filtradas</span>
                <span class="text-2xl font-black text-primary-fixed-dim" x-text="filteredEmpresas().length"></span>
            </div>
            <button @click="openCreate()" class="bg-gradient-to-br from-blue-600 to-blue-700 text-white p-4 rounded-2xl shadow-lg shadow-blue-500/20 flex flex-col items-center justify-center gap-1 hover:scale-[1.03] transition-all group">
                <iconify-icon icon="lucide:plus-circle" class="text-xl group-hover:rotate-90 transition-transform"></iconify-icon>
                <span class="text-[9px] font-black uppercase tracking-tighter">Nueva Empresa</span>
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
                <input type="text" x-model="search" placeholder="Buscar por Razón Social, NIT o Gerente..." 
                    class="w-full bg-white border-2 border-transparent focus:border-blue-500/20 focus:ring-4 focus:ring-blue-500/5 rounded-2xl py-4 pl-12 pr-4 shadow-sm text-sm font-semibold transition-all">
                
                <div class="absolute right-4 top-1/2 -translate-y-1/2 flex gap-2">
                    <button @click="exportCsv()" class="p-2 text-gray-400 hover:text-[#0d3b5a] transition-colors" title="Exportar CSV">
                        <iconify-icon icon="lucide:download" class="text-lg"></iconify-icon>
                    </button>
                </div>
            </div>

            <!-- Listado de Resultados (Scrollable) -->
            <div class="flex flex-col gap-3 max-h-[calc(100vh-350px)] overflow-y-auto pr-2 custom-scrollbar">
                <template x-for="e in filteredEmpresas()" :key="e.idemp">
                    <div @click="selectEmpresa(e)" 
                        class="group bg-white p-4 rounded-2xl border-2 transition-all cursor-pointer relative overflow-hidden"
                        :class="selectedId === e.idemp ? 'border-blue-500 shadow-md translate-x-2' : 'border-transparent hover:border-gray-200 shadow-sm'">
                        
                        <div class="flex items-center gap-4">
                            <!-- Avatar Corporativo -->
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-black text-sm shrink-0 transition-transform group-hover:scale-110 bg-gradient-to-br from-[#0d3b5a] to-[#1a4f73]"
                                x-text="e.abremp ? e.abremp.substring(0,2) : e.razsoem.substring(0,2).toUpperCase()">
                            </div>
                            
                            <div class="min-w-0 flex-1">
                                <div class="flex justify-between items-start">
                                    <h3 class="font-bold text-[#001834] text-sm truncate uppercase tracking-tight" x-text="e.razsoem"></h3>
                                    <span class="bg-blue-50 text-blue-600 text-[9px] font-black px-2 py-0.5 rounded-full uppercase tracking-tighter">Empresa</span>
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <iconify-icon icon="lucide:hash" class="text-gray-400 text-[10px]"></iconify-icon>
                                    <span class="text-[11px] font-bold text-gray-500 truncate" x-text="e.nonitem"></span>
                                    <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                    <span class="text-[11px] font-bold text-gray-400" x-text="(e.vehiculos ? e.vehiculos.length : 0) + ' Veh.'"></span>
                                </div>
                            </div>

                            <iconify-icon icon="lucide:chevron-right" class="text-gray-300 group-hover:text-blue-500 transition-colors"></iconify-icon>
                        </div>

                        <!-- Indicador de selección activa -->
                        <div x-show="selectedId === e.idemp" class="absolute left-0 top-0 bottom-0 w-1 bg-blue-500"></div>
                    </div>
                </template>

                <div x-show="filteredEmpresas().length === 0" class="bg-white/50 border-2 border-dashed border-gray-200 rounded-3xl p-10 text-center flex flex-col items-center gap-3">
                    <iconify-icon icon="lucide:building-2" class="text-4xl text-gray-200"></iconify-icon>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Sin empresas registradas</p>
                </div>
            </div>
        </div>

        <!-- DETAIL COLUMN: INFORMATION & FLEET GRAPH -->
        <div class="col-span-12 lg:col-span-7 xl:col-span-8">
            
            <template x-if="selectedEmpresa">
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden animate-in fade-in slide-in-from-right-4 duration-500">
                    
                    <!-- Detail Header -->
                    <div class="p-6 sm:p-8 bg-gradient-to-r from-[#001834] to-[#0d3b5a] text-white">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
                            <div class="flex items-center gap-5">
                                <div class="w-20 h-20 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-3xl font-black shadow-inner"
                                    x-text="selectedEmpresa.abremp ? selectedEmpresa.abremp.substring(0,2) : selectedEmpresa.razsoem.substring(0,2).toUpperCase()">
                                </div>
                                <div>
                                    <div class="flex items-center gap-3">
                                        <h2 class="text-2xl font-black tracking-tight uppercase" x-text="selectedEmpresa.razsoem"></h2>
                                        <span class="bg-emerald-500/20 text-emerald-300 text-[10px] font-black px-3 py-1 rounded-full border border-emerald-500/30 uppercase tracking-widest">Activo Fiscal</span>
                                    </div>
                                    <p class="text-white/60 text-xs font-medium mt-1 flex items-center gap-2">
                                        <iconify-icon icon="lucide:hash"></iconify-icon>
                                        <span x-text="'NIT: ' + selectedEmpresa.nonitem"></span>
                                        <span class="opacity-30">|</span>
                                        <iconify-icon icon="lucide:user"></iconify-icon>
                                        <span x-text="'Gerente: ' + selectedEmpresa.nomger"></span>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex gap-2 w-full sm:w-auto">
                                <button @click="openEdit(selectedEmpresa)" class="flex-1 sm:flex-none bg-white/10 hover:bg-white/20 text-white px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-widest transition-all border border-white/10">
                                    Editar Empresa
                                </button>
                                <button @click="openDelete(selectedEmpresa)" class="p-3 bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white rounded-xl transition-all border border-red-500/20">
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
                            Perfil Corporativo
                        </button>
                        <button @click="detailTab = 'flota'" 
                            class="px-6 py-4 text-[11px] font-black uppercase tracking-widest transition-all border-b-2"
                            :class="detailTab === 'flota' ? 'border-blue-500 text-[#0d3b5a]' : 'border-transparent text-gray-400 hover:text-gray-600'">
                            Flota Vehicular
                        </button>
                    </div>

                    <!-- Detail Content -->
                    <div class="p-8">
                        <!-- TAB: INFO -->
                        <div x-show="detailTab === 'info'" class="grid grid-cols-1 md:grid-cols-2 gap-8 animate-in fade-in duration-300">
                            <div class="space-y-6">
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Ubicación Fiscal</label>
                                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center text-blue-500 shadow-sm">
                                                <iconify-icon icon="lucide:map-pin"></iconify-icon>
                                            </div>
                                            <p class="text-sm font-black text-[#001834]" x-text="selectedEmpresa.direm || 'No especificada'"></p>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Contacto de Emergencia/Soporte</label>
                                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 space-y-3">
                                        <div class="flex items-center gap-4">
                                            <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center text-blue-500 shadow-sm border border-gray-100">
                                                <iconify-icon icon="lucide:phone"></iconify-icon>
                                            </div>
                                            <p class="text-sm font-bold text-[#001834]" x-text="selectedEmpresa.telem || 'N/A'"></p>
                                        </div>
                                        <div class="flex items-center gap-4">
                                            <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center text-blue-500 shadow-sm border border-gray-100">
                                                <iconify-icon icon="lucide:at-sign"></iconify-icon>
                                            </div>
                                            <p class="text-sm font-bold text-[#001834]" x-text="selectedEmpresa.emaem || 'N/A'"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-6">
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Detalles de Cuenta</label>
                                    <div class="bg-[#001834] p-5 rounded-2xl text-white shadow-xl">
                                        <div class="flex justify-between items-center mb-4 pb-4 border-b border-white/10">
                                            <span class="text-[10px] font-bold text-white/40 uppercase">Abreviatura</span>
                                            <span class="text-sm font-black tracking-widest" x-text="selectedEmpresa.abremp || '—'"></span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-[10px] font-bold text-white/40 uppercase">ID Sistema</span>
                                            <span class="bg-white/10 px-3 py-1 rounded text-[10px] font-black" x-text="'EMP-' + selectedEmpresa.idemp"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB: FLOTA (GRAFO) -->
                        <div x-show="detailTab === 'flota'" class="animate-in fade-in duration-300">
                            <div class="flex flex-col items-center py-6">
                                <!-- Nodo Central (Empresa) -->
                                <div class="relative z-10">
                                    <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-[#001834] to-[#0d3b5a] flex items-center justify-center text-white text-2xl font-black shadow-xl border-4 border-white"
                                        x-text="selectedEmpresa.abremp ? selectedEmpresa.abremp.substring(0,2) : selectedEmpresa.razsoem.substring(0,2).toUpperCase()">
                                    </div>
                                    <div class="absolute -bottom-2 -right-2 bg-white p-1.5 rounded-full shadow-md">
                                        <iconify-icon icon="lucide:briefcase" class="text-lg text-blue-500"></iconify-icon>
                                    </div>
                                </div>

                                <!-- Línea Conectora Vertical -->
                                <div x-show="selectedEmpresa.vehiculos && selectedEmpresa.vehiculos.length > 0" class="w-0.5 h-10 bg-gradient-to-b from-[#001834] to-transparent"></div>

                                <!-- Lista de Flota -->
                                <div class="w-full mt-4 space-y-4 max-h-[400px] overflow-y-auto pr-4 custom-scrollbar">
                                    <template x-for="veh in selectedEmpresa.vehiculos" :key="veh.idveh">
                                        <div class="flex items-center gap-6 group">
                                            <div class="flex-1 h-px bg-gradient-to-r from-transparent via-gray-200 to-gray-200"></div>
                                            <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4 min-w-[350px] hover:border-blue-200 hover:shadow-lg hover:translate-y-[-2px] transition-all duration-300">
                                                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
                                                    <iconify-icon icon="lucide:truck" class="text-2xl"></iconify-icon>
                                                </div>
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xs font-black text-white bg-[#001834] px-2 py-0.5 rounded border border-[#001834] font-mono tracking-wider" x-text="veh.placaveh"></span>
                                                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-tighter" x-text="'ORD: ' + (veh.nordveh || 'S/I')"></span>
                                                    </div>
                                                    <p class="text-xs font-bold text-gray-700 mt-1 uppercase" x-text="veh.marveh + ' ' + (veh.modveh || '')"></p>
                                                </div>
                                                <div class="ml-auto">
                                                    <a :href="'/' + mupBase + '/vehiculos/' + veh.idveh + '/editar'" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-300 hover:bg-blue-50 hover:text-blue-600 transition-all" title="Ver Detalle Vehículo">
                                                        <iconify-icon icon="lucide:arrow-up-right" class="text-lg"></iconify-icon>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="flex-1 h-px bg-gradient-to-l from-transparent via-gray-200 to-gray-200"></div>
                                        </div>
                                    </template>

                                    <div x-show="!selectedEmpresa.vehiculos || selectedEmpresa.vehiculos.length === 0" class="text-center py-10 opacity-40">
                                        <iconify-icon icon="lucide:package-2" class="text-4xl mb-2"></iconify-icon>
                                        <p class="text-[10px] font-black uppercase tracking-widest">Esta empresa no tiene vehículos vinculados</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Placeholder cuando no hay selección -->
            <div x-show="!selectedEmpresa" class="h-full min-h-[500px] bg-white rounded-3xl border-2 border-dashed border-gray-100 flex flex-col items-center justify-center text-center p-10">
                <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-6 animate-pulse">
                    <iconify-icon icon="lucide:building" class="text-5xl text-gray-200"></iconify-icon>
                </div>
                <h3 class="text-xl font-black text-[#001834] tracking-tight">Gestión de Entidades</h3>
                <p class="text-sm text-gray-400 mt-2 max-w-xs">Seleccione una empresa para administrar sus credenciales, flota y perfiles asociados.</p>
            </div>
        </div>
    </div>

    <!-- MODAL: REGISTRO (CONSERVANDO LÓGICA ORIGINAL) -->
    <div x-show="createDrawer" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-[#001834]/60 backdrop-blur-md" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div class="bg-white rounded-[32px] shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col" @click.away="createDrawer = false">
            <div class="p-8 bg-[#001834] text-white flex justify-between items-center shrink-0">
                <div>
                    <h2 class="text-2xl font-black tracking-tight">Alta Corporativa</h2>
                    <p class="text-white/60 text-[10px] font-bold uppercase tracking-widest mt-1">Nuevo NIT en el Sistema</p>
                </div>
                <button @click="createDrawer = false" class="w-10 h-10 rounded-full hover:bg-white/10 flex items-center justify-center transition-all">
                    <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
                </button>
            </div>

            <form action="{{ route($mupPrefix . '.empresas.store') }}" method="POST" class="p-8 overflow-y-auto flex-1 custom-scrollbar">
                @csrf
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="sm:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Razón Social <span class="text-blue-500">*</span></label>
                        <input type="text" name="razsoem" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" placeholder="Ej. Transportes Unidos S.A." required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">NIT / Identificación <span class="text-blue-500">*</span></label>
                        <input type="text" name="nonitem" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" placeholder="900000000" required inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Ciudad <span class="text-blue-500">*</span></label>
                        <input type="text" name="ciudeem" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" placeholder="Ej. Bogotá" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Abreviatura</label>
                        <input type="text" name="abremp" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" placeholder="Ej. TUSA">
                    </div>

                    <div class="sm:col-span-2 pt-4 border-t border-gray-50 mt-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Contacto Corporativo</label>
                    </div>
                    
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Nombre Gerente <span class="text-blue-500">*</span></label>
                        <input type="text" name="nomger" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" placeholder="Nombre completo" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Teléfono <span class="text-blue-500">*</span></label>
                        <input type="text" name="telem" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" placeholder="Número contacto" required inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Email Principal <span class="text-blue-500">*</span></label>
                        <input type="email" name="emaem" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" placeholder="contacto@empresa.com" required>
                    </div>

                    <div class="sm:col-span-2 pt-4 border-t border-gray-50 mt-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Credenciales de Acceso</label>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Nombre de Usuario <span class="text-blue-500">*</span></label>
                        <input type="text" name="username" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" placeholder="Ej. admin.empresa" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Contraseña <span class="text-blue-500">*</span></label>
                        <input type="password" name="password" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Confirmar <span class="text-blue-500">*</span></label>
                        <input type="password" name="password_confirmation" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" required>
                    </div>
                </div>

                <div class="mt-10 flex gap-4">
                    <button type="button" @click="createDrawer = false" class="flex-1 py-4 text-gray-400 font-bold hover:bg-gray-50 rounded-2xl transition-all">Cancelar</button>
                    <button type="submit" class="flex-1 bg-[#001834] text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-[#001834]/20 transition-all hover:scale-[1.02]">
                        Registrar Empresa
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: EDICIÓN (CONSERVANDO LÓGICA ORIGINAL) -->
    <div x-show="editDrawer" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-[#001834]/60 backdrop-blur-md" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div class="bg-white rounded-[32px] shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col" @click.away="editDrawer = false">
            <div class="p-8 bg-amber-500 text-white flex justify-between items-center shrink-0">
                <div>
                    <h2 class="text-2xl font-black tracking-tight" x-text="'Editar: ' + currentEmp.razsoem"></h2>
                    <p class="text-white/60 text-[10px] font-bold uppercase tracking-widest mt-1">Actualización Corporativa</p>
                </div>
                <button @click="editDrawer = false" class="w-10 h-10 rounded-full hover:bg-white/10 flex items-center justify-center transition-all">
                    <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
                </button>
            </div>

            <form :action="'{{ url($mupBase . '/entidades/mup/empresas') }}/' + currentEmp.idemp" method="POST" class="p-8 overflow-y-auto flex-1 custom-scrollbar">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="sm:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Razón Social <span class="text-amber-600">*</span></label>
                        <input type="text" name="razsoem" x-model="currentEmp.razsoem" class="w-full bg-gray-50 border-2 border-transparent focus:border-amber-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">NIT / Identificación <span class="text-amber-600">*</span></label>
                        <input type="text" name="nonitem" x-model="currentEmp.nonitem" class="w-full bg-gray-50 border-2 border-transparent focus:border-amber-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" required inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Ciudad <span class="text-amber-600">*</span></label>
                        <input type="text" name="ciudeem" x-model="currentEmp.ciudeem" class="w-full bg-gray-50 border-2 border-transparent focus:border-amber-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Abreviatura</label>
                        <input type="text" name="abremp" x-model="currentEmp.abremp" class="w-full bg-gray-50 border-2 border-transparent focus:border-amber-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Dirección Fiscal</label>
                        <input type="text" name="direm" x-model="currentEmp.direm" class="w-full bg-gray-50 border-2 border-transparent focus:border-amber-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all">
                    </div>

                    <div class="sm:col-span-2 pt-4 border-t border-gray-50 mt-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Contacto y Acceso</label>
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Gerente <span class="text-amber-600">*</span></label>
                        <input type="text" name="nomger" x-model="currentEmp.nomger" class="w-full bg-gray-50 border-2 border-transparent focus:border-amber-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Teléfono <span class="text-amber-600">*</span></label>
                        <input type="text" name="telem" x-model="currentEmp.telem" class="w-full bg-gray-50 border-2 border-transparent focus:border-amber-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" required inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Email Corporativo <span class="text-amber-600">*</span></label>
                        <input type="email" name="emaem" x-model="currentEmp.emaem" class="w-full bg-gray-50 border-2 border-transparent focus:border-amber-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" required>
                    </div>

                    <div class="sm:col-span-2">
                        <div class="bg-amber-50 p-4 rounded-2xl border border-amber-100 flex items-center gap-3">
                            <iconify-icon icon="lucide:info" class="text-xl text-amber-500"></iconify-icon>
                            <p class="text-[11px] text-amber-700 font-bold leading-tight">Para actualizar la contraseña, complete los campos en el módulo de Usuarios. Aquí solo se gestiona el perfil empresarial.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-10 flex gap-4">
                    <button type="button" @click="editDrawer = false" class="flex-1 py-4 text-gray-400 font-bold hover:bg-gray-50 rounded-2xl transition-all">Cancelar</button>
                    <button type="submit" class="flex-1 bg-amber-500 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-amber-500/20 transition-all hover:scale-[1.02]">
                        Actualizar Empresa
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: ELIMINAR (CONSERVANDO LÓGICA ORIGINAL) -->
    <div x-show="deleteModal" class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-[#001834]/80 backdrop-blur-md" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <div class="bg-white p-8 rounded-[32px] shadow-2xl max-w-sm w-full text-center" @click.away="deleteModal = false">
            <div class="w-24 h-24 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-8 shadow-inner">
                <iconify-icon icon="lucide:trash-2" style="font-size: 48px;"></iconify-icon>
            </div>
            <h3 class="text-2xl font-black text-[#001834] tracking-tight mb-2">¿Eliminar Empresa?</h3>
            <p class="text-sm text-gray-400 mb-10 leading-relaxed px-4">
                Se revocará el acceso a <span class="text-[#001834] font-black" x-text="currentEmp.razsoem"></span> y se desvincularán sus activos. Esta acción no se puede deshacer.
            </p>
            
            <div class="flex gap-4">
                <button @click="deleteModal = false" class="flex-1 py-4 text-gray-400 font-bold hover:bg-gray-50 rounded-2xl transition-all">Cancelar</button>
                <form :action="'{{ url($mupBase . '/entidades/mup/empresas') }}/' + currentEmp.idemp" method="POST" class="flex-1">
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
function empresaManager() {
    return {
        search: '',
        createDrawer: false,
        editDrawer: false,
        deleteModal: false,
        detailTab: 'info',
        selectedId: null,
        selectedEmpresa: null,
        currentEmp: {},
        empresas: @json($empresas),
        
        init() {
            if (this.empresas.length > 0) {
                this.selectEmpresa(this.empresas[0]);
            }
        },

        selectEmpresa(e) {
            this.selectedId = e.idemp;
            this.selectedEmpresa = e;
        },

        filteredEmpresas() {
            if (!this.search) return this.empresas;
            const q = this.search.toLowerCase();
            return this.empresas.filter(e => 
                (e.razsoem || '').toLowerCase().includes(q) || 
                (e.nonitem || '').toLowerCase().includes(q) ||
                (e.nomger || '').toLowerCase().includes(q)
            );
        },

        exportCsv() {
            const cols = ['idemp', 'razsoem', 'nonitem', 'abremp', 'emaem', 'telem', 'nomger'];
            const list = this.filteredEmpresas();
            const esc = (v) => {
                if (v === null || v === undefined) return '';
                const s = String(v);
                if (/[",\n]/.test(s)) return '"' + s.replace(/"/g, '""') + '"';
                return s;
            };
            let csv = cols.join(',') + '\n';
            for (const e of list) {
                csv += cols.map((c) => esc(e[c])).join(',') + '\n';
            }
            const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'empresas_mup_' + new Date().toISOString().slice(0, 10) + '.csv';
            a.click();
            URL.revokeObjectURL(url);
        },

        openCreate() {
            this.createDrawer = true;
        },

        openEdit(e) {
            this.currentEmp = { ...e };
            this.editDrawer = true;
        },

        openDelete(e) {
            this.currentEmp = e;
            this.deleteModal = true;
        }
    }
}
</script>
@endsection
