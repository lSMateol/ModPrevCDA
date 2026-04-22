@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/mup.css') }}">
<script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>

@php
    $mupBase = auth()->user()->hasRole('Administrador') ? 'admin' : 'digitador';
    $mupPrefix = $mupBase . '.mup';
    
    // Preparar datos para Alpine
    $usuariosData = $usuarios->map(function($u) {
        return [
            'id' => $u->id,
            'name' => $u->name,
            'username' => $u->username,
            'email' => $u->email,
            'ndocper' => $u->persona->ndocper ?? 0,
            'tdocper' => $u->persona->tdocper ?? '',
            'telper' => $u->persona->telper ?? '',
            'actper' => $u->persona->actper ?? 1,
            'nompef' => $u->persona->perfil->nompef ?? 'Sin Rol',
            'idpef' => $u->persona->idpef ?? '',
            'nomdoc' => $u->persona->tipoDocumento->nomval ?? 'C.C.',
        ];
    });
@endphp

<div class="px-4 sm:px-10 pb-20 max-w-[1600px] mx-auto" x-data="usuariosManager()" x-init="init()" x-cloak>
    
    <!-- HEADER & BENTO METRICS -->
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-end gap-6 mb-8 mt-4">
        <div>
            <h1 class="font-headline font-black text-[#002D54] text-2xl md:text-3xl tracking-tight">Gestión de Accesos</h1>
            <p class="text-on-surface-variant font-body text-sm mt-1">Control de perfiles administrativos y seguridad del sistema</p>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 w-full xl:w-auto">
            <div class="bg-white p-4 rounded-2xl shadow-sm border-b-4 border-[#0d3b5a] flex flex-col justify-center min-w-[120px]">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Personal Total</span>
                <span class="text-2xl font-black text-[#001834]" x-text="usuarios.length"></span>
            </div>
            <div class="bg-white p-4 rounded-2xl shadow-sm border-b-4 border-blue-500 flex flex-col justify-center min-w-[120px]">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Activos</span>
                <span class="text-2xl font-black text-blue-600" x-text="usuarios.filter(u => u.actper).length"></span>
            </div>
            <div class="bg-[#001834] p-4 rounded-2xl shadow-lg flex flex-col justify-center min-w-[120px]">
                <span class="text-[10px] font-black text-white/40 uppercase tracking-widest mb-1">Ingenieros</span>
                <span class="text-2xl font-black text-primary-fixed-dim" x-text="usuarios.filter(u => u.nompef === 'Ingeniero').length"></span>
            </div>
            <button @click="openCreate()" class="bg-gradient-to-br from-blue-600 to-blue-700 text-white p-4 rounded-2xl shadow-lg shadow-blue-500/20 flex flex-col items-center justify-center gap-1 hover:scale-[1.03] transition-all group">
                <iconify-icon icon="lucide:user-plus" class="text-xl group-hover:scale-110 transition-transform"></iconify-icon>
                <span class="text-[9px] font-black uppercase tracking-tighter">Nuevo Usuario</span>
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
                <input type="text" x-model="search" placeholder="Buscar por Nombre, Usuario o Rol..." 
                    class="w-full bg-white border-2 border-transparent focus:border-blue-500/20 focus:ring-4 focus:ring-blue-500/5 rounded-2xl py-4 pl-12 pr-4 shadow-sm text-sm font-semibold transition-all">
                
                <div class="absolute right-4 top-1/2 -translate-y-1/2">
                    <button @click="exportCsv()" class="p-2 text-gray-400 hover:text-[#0d3b5a] transition-colors" title="Exportar CSV">
                        <iconify-icon icon="lucide:download" class="text-lg"></iconify-icon>
                    </button>
                </div>
            </div>

            <!-- Listado de Usuarios (Scrollable) -->
            <div class="flex flex-col gap-3 max-h-[calc(100vh-350px)] overflow-y-auto pr-2 custom-scrollbar">
                <template x-for="u in filteredUsuarios()" :key="u.id">
                    <div @click="selectUser(u)" 
                        class="group bg-white p-4 rounded-2xl border-2 transition-all cursor-pointer relative overflow-hidden"
                        :class="selectedId === u.id ? 'border-blue-500 shadow-md translate-x-2' : 'border-transparent hover:border-gray-200 shadow-sm'">
                        
                        <div class="flex items-center gap-4">
                            <!-- Avatar Circular -->
                            <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-black text-sm shrink-0 transition-transform group-hover:rotate-12"
                                :class="u.actper ? 'bg-gradient-to-br from-[#0d3b5a] to-blue-600' : 'bg-gray-300'"
                                x-text="getInitials(u.name)">
                            </div>
                            
                            <div class="min-w-0 flex-1">
                                <div class="flex justify-between items-start">
                                    <h3 class="font-bold text-[#001834] text-sm truncate uppercase tracking-tight" x-text="u.name"></h3>
                                    <span class="text-[9px] font-black px-2 py-0.5 rounded-full uppercase tracking-tighter"
                                        :class="{
                                            'bg-blue-50 text-blue-600': u.nompef === 'Administrador',
                                            'bg-purple-50 text-purple-600': u.nompef === 'Ingeniero',
                                            'bg-amber-50 text-amber-600': u.nompef === 'Digitador',
                                            'bg-emerald-50 text-emerald-600': u.nompef === 'Inspector',
                                            'bg-gray-50 text-gray-400': !['Administrador','Ingeniero','Digitador','Inspector'].includes(u.nompef)
                                        }"
                                        x-text="u.nompef">
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <iconify-icon icon="lucide:user" class="text-gray-400 text-[10px]"></iconify-icon>
                                    <span class="text-[11px] font-bold text-gray-500 truncate" x-text="u.username"></span>
                                    <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                    <span class="text-[11px] font-bold text-gray-400" x-text="u.nomdoc + ': ' + u.ndocper"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Indicador de selección activa -->
                        <div x-show="selectedId === u.id" class="absolute left-0 top-0 bottom-0 w-1 bg-blue-500"></div>
                    </div>
                </template>

                <div x-show="filteredUsuarios().length === 0" class="bg-white/50 border-2 border-dashed border-gray-200 rounded-3xl p-10 text-center flex flex-col items-center gap-3">
                    <iconify-icon icon="lucide:users" class="text-4xl text-gray-200"></iconify-icon>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">No se encontraron usuarios</p>
                </div>
            </div>
        </div>

        <!-- DETAIL COLUMN: USER PROFILE & PERMISSIONS -->
        <div class="col-span-12 lg:col-span-7 xl:col-span-8">
            
            <template x-if="selectedUser">
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden animate-in fade-in slide-in-from-right-4 duration-500">
                    
                    <!-- Profile Header -->
                    <div class="p-6 sm:p-8 bg-gradient-to-r from-[#001834] to-[#0d3b5a] text-white">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6">
                            <div class="flex items-center gap-5">
                                <div class="w-20 h-20 rounded-full bg-white/10 backdrop-blur-md flex items-center justify-center text-3xl font-black shadow-inner border-4 border-white/20"
                                    x-text="getInitials(selectedUser.name)">
                                </div>
                                <div>
                                    <div class="flex items-center gap-3">
                                        <h2 class="text-2xl font-black tracking-tight uppercase" x-text="selectedUser.name"></h2>
                                        <span x-show="selectedUser.actper" class="bg-emerald-500/20 text-emerald-300 text-[10px] font-black px-3 py-1 rounded-full border border-emerald-500/30 uppercase tracking-widest">Cuenta Activa</span>
                                        <span x-show="!selectedUser.actper" class="bg-red-500/20 text-red-300 text-[10px] font-black px-3 py-1 rounded-full border border-red-500/30 uppercase tracking-widest">Bloqueado</span>
                                    </div>
                                    <p class="text-white/60 text-xs font-medium mt-1 flex items-center gap-2">
                                        <iconify-icon icon="lucide:shield-check"></iconify-icon>
                                        <span x-text="'Perfil: ' + selectedUser.nompef"></span>
                                        <span class="opacity-30">|</span>
                                        <iconify-icon icon="lucide:fingerprint"></iconify-icon>
                                        <span x-text="'ID: USR-' + String(selectedUser.id).padStart(3, '0')"></span>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex gap-2 w-full sm:w-auto">
                                <button @click="openEdit(selectedUser)" class="flex-1 sm:flex-none bg-white text-[#001834] px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest transition-all hover:bg-blue-50">
                                    Editar Perfil
                                </button>
                                <button @click="openDelete(selectedUser)" class="p-3 bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white rounded-xl transition-all border border-red-500/20">
                                    <iconify-icon icon="lucide:trash-2" class="text-xl"></iconify-icon>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Detail Body -->
                    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Columna: Datos de Contacto -->
                        <div class="space-y-6">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-3">Información de Contacto</label>
                                <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100 space-y-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-blue-500 shadow-sm">
                                            <iconify-icon icon="lucide:mail" class="text-xl"></iconify-icon>
                                        </div>
                                        <div>
                                            <span class="block text-[9px] font-black text-gray-400 uppercase tracking-tighter">Correo Electrónico</span>
                                            <p class="text-sm font-bold text-[#001834]" x-text="selectedUser.email"></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-blue-500 shadow-sm">
                                            <iconify-icon icon="lucide:phone" class="text-xl"></iconify-icon>
                                        </div>
                                        <div>
                                            <span class="block text-[9px] font-black text-gray-400 uppercase tracking-tighter">Teléfono Principal</span>
                                            <p class="text-sm font-bold text-[#001834]" x-text="selectedUser.telper || 'Sin registrar'"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-3">Identidad Institucional</label>
                                <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100 space-y-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-blue-500 shadow-sm">
                                            <iconify-icon icon="lucide:id-card" class="text-xl"></iconify-icon>
                                        </div>
                                        <div>
                                            <span class="block text-[9px] font-black text-gray-400 uppercase tracking-tighter">Número de Documento</span>
                                            <p class="text-sm font-bold text-[#001834]" x-text="selectedUser.ndocper"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna: Seguridad y Accesos -->
                        <div class="space-y-6">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-3">Credenciales de Sistema</label>
                                <div class="bg-[#001834] p-6 rounded-3xl text-white shadow-2xl relative overflow-hidden">
                                    <div class="relative z-10">
                                        <div class="flex justify-between items-center mb-6 pb-6 border-b border-white/10">
                                            <span class="text-[10px] font-bold text-white/40 uppercase tracking-widest">Username / Alias</span>
                                            <span class="text-sm font-black tracking-widest" x-text="selectedUser.username"></span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <div class="flex items-center gap-2">
                                                <iconify-icon icon="lucide:shield" class="text-blue-400"></iconify-icon>
                                                <span class="text-[10px] font-bold text-white/40 uppercase tracking-widest">Nivel de Acceso</span>
                                            </div>
                                            <span class="bg-blue-500 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-tighter" x-text="selectedUser.nompef"></span>
                                        </div>
                                    </div>
                                    <!-- Decoración de fondo -->
                                    <iconify-icon icon="lucide:lock" class="absolute -bottom-4 -right-4 text-9xl text-white/5"></iconify-icon>
                                </div>
                            </div>

                            <div class="bg-blue-50 p-5 rounded-2xl border border-blue-100 flex items-start gap-4">
                                <iconify-icon icon="lucide:info" class="text-2xl text-blue-500 shrink-0 mt-0.5"></iconify-icon>
                                <div class="space-y-1">
                                    <h4 class="text-xs font-black text-[#001834] uppercase tracking-tight">Auditoría de Perfil</h4>
                                    <p class="text-[11px] text-blue-700 font-medium leading-relaxed">
                                        Este usuario tiene permisos basados en el rol <span class="font-black" x-text="selectedUser.nompef"></span>. 
                                        Cualquier cambio de permisos afectará a todos los usuarios bajo este mismo perfil.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Placeholder cuando no hay selección -->
            <div x-show="!selectedUser" class="h-full min-h-[500px] bg-white rounded-[40px] border-2 border-dashed border-gray-100 flex flex-col items-center justify-center text-center p-10 animate-pulse">
                <div class="w-32 h-32 bg-gray-50 rounded-full flex items-center justify-center mb-6">
                    <iconify-icon icon="lucide:shield-check" class="text-6xl text-gray-200"></iconify-icon>
                </div>
                <h3 class="text-xl font-black text-[#001834] tracking-tight">Panel de Seguridad</h3>
                <p class="text-sm text-gray-400 mt-2 max-w-xs">Seleccione un miembro del personal para gestionar sus credenciales, roles y estados de acceso.</p>
            </div>
        </div>
    </div>

    <!-- MODAL: REGISTRO (SIN EMPRESA ASOCIADA) -->
    <div x-show="createDrawer" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-[#001834]/60 backdrop-blur-md" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div class="bg-white rounded-[32px] shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col" @click.away="createDrawer = false">
            <div class="p-8 bg-[#001834] text-white flex justify-between items-center shrink-0">
                <div>
                    <h2 class="text-2xl font-black tracking-tight">Alta de Usuario</h2>
                    <p class="text-white/60 text-[10px] font-bold uppercase tracking-widest mt-1">Personal Administrativo CDA</p>
                </div>
                <button @click="createDrawer = false" class="w-10 h-10 rounded-full hover:bg-white/10 flex items-center justify-center transition-all">
                    <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
                </button>
            </div>

            <form action="{{ route($mupPrefix . '.usuarios.store') }}" method="POST" class="p-8 overflow-y-auto flex-1 custom-scrollbar">
                @csrf
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="sm:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Nombre Completo <span class="text-blue-500">*</span></label>
                        <input type="text" name="nombre_completo" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" placeholder="Ej. Juan Manuel Pérez" required>
                    </div>
                    
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Tipo de Documento <span class="text-blue-500">*</span></label>
                        <select name="tdocper" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" required>
                            @foreach($tiposDoc as $tipo)
                                <option value="{{ $tipo->idval }}">{{ $tipo->nomval }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Nro. Documento <span class="text-blue-500">*</span></label>
                        <input type="number" name="ndocper" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" placeholder="1000000000" required>
                    </div>

                    <div class="sm:col-span-2 pt-4 border-t border-gray-50 mt-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Datos de Contacto</label>
                    </div>
                    
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Email <span class="text-blue-500">*</span></label>
                        <input type="email" name="emaper" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" placeholder="correo@institucional.com" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Teléfono</label>
                        <input type="text" name="telper" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" placeholder="Número contacto">
                    </div>

                    <div class="sm:col-span-2 pt-4 border-t border-gray-50 mt-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Configuración de Acceso</label>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Nombre de Usuario <span class="text-blue-500">*</span></label>
                        <input type="text" name="username" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" placeholder="Ej. jperez.digitador" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Contraseña <span class="text-blue-500">*</span></label>
                        <input type="password" name="password" x-model="password" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Confirmar <span class="text-blue-500">*</span></label>
                        <input type="password" name="password_confirmation" x-model="password_confirmation" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" required>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Rol Operativo <span class="text-blue-500">*</span></label>
                        <select name="idpef" class="w-full bg-gray-50 border-2 border-transparent focus:border-blue-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" required>
                            @foreach($perfiles->whereIn('nompef', ['Administrador', 'Digitador', 'Inspector', 'Ingeniero']) as $perf)
                                <option value="{{ $perf->idpef }}">{{ $perf->nompef }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-10 flex gap-4">
                    <button type="button" @click="createDrawer = false" class="flex-1 py-4 text-gray-400 font-bold hover:bg-gray-50 rounded-2xl transition-all">Cancelar</button>
                    <button type="submit" class="flex-1 bg-[#001834] text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-[#001834]/20 transition-all hover:scale-[1.02]" :disabled="!passwordsMatch">
                        Crear Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: EDICIÓN -->
    <div x-show="editDrawer" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-[#001834]/60 backdrop-blur-md" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div class="bg-white rounded-[32px] shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col" @click.away="editDrawer = false">
            <div class="p-8 bg-amber-500 text-white flex justify-between items-center shrink-0">
                <div>
                    <h2 class="text-2xl font-black tracking-tight" x-text="'Editar: ' + selectedUser?.name"></h2>
                    <p class="text-white/60 text-[10px] font-bold uppercase tracking-widest mt-1">Actualización de Perfil</p>
                </div>
                <button @click="editDrawer = false" class="w-10 h-10 rounded-full hover:bg-white/10 flex items-center justify-center transition-all">
                    <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
                </button>
            </div>

            <form :action="'{{ url($mupBase . '/entidades/mup/usuarios') }}/' + selectedUser?.id" method="POST" class="p-8 overflow-y-auto flex-1 custom-scrollbar">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="sm:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Nombre Completo <span class="text-amber-600">*</span></label>
                        <input type="text" name="nombre_completo" x-model="selectedUser.name" class="w-full bg-gray-50 border-2 border-transparent focus:border-amber-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" required>
                    </div>
                    
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Tipo Documento</label>
                        <select name="tdocper" x-model="selectedUser.tdocper" class="w-full bg-gray-50 border-2 border-transparent focus:border-amber-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" required>
                            @foreach($tiposDoc as $tipo)
                                <option value="{{ $tipo->idval }}">{{ $tipo->nomval }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Nro. Documento</label>
                        <input type="number" name="ndocper" x-model="selectedUser.ndocper" class="w-full bg-gray-50 border-2 border-transparent focus:border-amber-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" required>
                    </div>

                    <div class="sm:col-span-2 pt-4 border-t border-gray-50 mt-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Email y Estado</label>
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Correo <span class="text-amber-600">*</span></label>
                        <input type="email" name="emaper" x-model="selectedUser.email" class="w-full bg-gray-50 border-2 border-transparent focus:border-amber-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Estado de Cuenta</label>
                        <select name="actper" x-model="selectedUser.actper" class="w-full bg-gray-50 border-2 border-transparent focus:border-amber-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" required>
                            <option value="1">ACTIVO (CON ACCESO)</option>
                            <option value="0">INACTIVO (BLOQUEADO)</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2 pt-4 border-t border-gray-50 mt-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Rol y Seguridad</label>
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Rol Operativo</label>
                        <select name="idpef" x-model="selectedUser.idpef" class="w-full bg-gray-50 border-2 border-transparent focus:border-amber-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" required>
                            @foreach($perfiles->whereIn('nompef', ['Administrador', 'Digitador', 'Inspector', 'Ingeniero']) as $perf)
                                <option value="{{ $perf->idpef }}">{{ $perf->nompef }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-1">Nueva Contraseña</label>
                        <input type="password" name="password" x-model="password" class="w-full bg-gray-50 border-2 border-transparent focus:border-amber-500/20 focus:ring-0 rounded-2xl p-4 text-sm font-semibold transition-all" placeholder="Vacío para no cambiar">
                    </div>
                </div>

                <div class="mt-10 flex gap-4">
                    <button type="button" @click="editDrawer = false" class="flex-1 py-4 text-gray-400 font-bold hover:bg-gray-50 rounded-2xl transition-all">Cancelar</button>
                    <button type="submit" class="flex-1 bg-amber-500 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-amber-500/20 transition-all hover:scale-[1.02]">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: ELIMINAR -->
    <div x-show="deleteModal" class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-[#001834]/80 backdrop-blur-md" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <div class="bg-white p-8 rounded-[32px] shadow-2xl max-w-sm w-full text-center" @click.away="deleteModal = false">
            <div class="w-24 h-24 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-8 shadow-inner">
                <iconify-icon icon="lucide:user-x" style="font-size: 48px;"></iconify-icon>
            </div>
            <h3 class="text-2xl font-black text-[#001834] tracking-tight mb-2">¿Eliminar Usuario?</h3>
            <p class="text-sm text-gray-400 mb-10 leading-relaxed px-4">
                Se revocarán todos los permisos de <span class="text-[#001834] font-black" x-text="selectedUser?.name"></span>. Esta acción no se puede deshacer.
            </p>
            
            <div class="flex gap-4">
                <button @click="deleteModal = false" class="flex-1 py-4 text-gray-400 font-bold hover:bg-gray-50 rounded-2xl transition-all">Cancelar</button>
                <form :action="'{{ url($mupBase . '/entidades/mup/usuarios') }}/' + selectedUser?.id" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-4 bg-red-600 text-white font-black text-xs uppercase tracking-widest rounded-2xl shadow-xl shadow-red-200 hover:scale-[1.05] transition-all">Confirmar Borrado</button>
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
function usuariosManager() {
    return {
        search: '',
        createDrawer: false,
        editDrawer: false,
        deleteModal: false,
        selectedId: null,
        selectedUser: null,
        usuarios: @json($usuariosData),
        tiposDoc: @json($tiposDoc->pluck('nomval', 'idval')),
        password: '',
        password_confirmation: '',
        
        init() {
            if (this.usuarios.length > 0) {
                this.selectUser(this.usuarios[0]);
            }
        },

        get passwordsMatch() {
            if (!this.password && !this.password_confirmation) return true;
            return this.password === this.password_confirmation;
        },

        selectUser(u) {
            this.selectedId = u.id;
            this.selectedUser = u;
        },

        getInitials(name) {
            if (!name) return '??';
            const parts = name.trim().split(' ');
            if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
            return name.substring(0, 2).toUpperCase();
        },

        filteredUsuarios() {
            if (!this.search) return this.usuarios;
            const q = this.search.toLowerCase();
            return this.usuarios.filter(u => 
                (u.name || '').toLowerCase().includes(q) || 
                (u.username || '').toLowerCase().includes(q) ||
                (u.nompef || '').toLowerCase().includes(q) ||
                (u.ndocper || '').toString().includes(q)
            );
        },

        getDocType(id) {
            if (id === null || id === undefined || id === '') return 'N/A';
            const key = String(id);
            return this.tiposDoc[key] || this.tiposDoc[id] || 'N/A';
        },

        exportCsv() {
            const cols = ['id', 'name', 'username', 'email', 'ndocper', 'nompef', 'actper'];
            const list = this.filteredUsuarios();
            const esc = (v) => {
                if (v === null || v === undefined) return '';
                const s = String(v);
                if (/[",\n]/.test(s)) return '"' + s.replace(/"/g, '""') + '"';
                return s;
            };
            let csv = cols.join(',') + '\n';
            for (const u of list) {
                csv += cols.map((c) => esc(u[c])).join(',') + '\n';
            }
            const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'usuarios_cda_' + new Date().toISOString().slice(0, 10) + '.csv';
            a.click();
            URL.revokeObjectURL(url);
        },

        openCreate() {
            this.password = '';
            this.password_confirmation = '';
            this.createDrawer = true;
        },

        openEdit(u) {
            this.selectedUser = { ...u };
            this.password = '';
            this.password_confirmation = '';
            this.editDrawer = true;
        },

        openDelete(u) {
            this.selectedUser = u;
            this.deleteModal = true;
        }
    }
}
</script>
@endsection
