@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/mup.css') }}">
<script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>

<div class="mup-container" x-data="empresaManager()">
    <header class="mup-topbar">
        <div class="mup-page-title">
            <h1 class="flex items-center gap-3">
                <div class="p-2 bg-[#0d3b5a] rounded-lg shadow-lg shadow-[#0d3b5a]/20">
                    <iconify-icon icon="lucide:building-2" class="text-white text-xl"></iconify-icon>
                </div>
                <span class="text-[#0d3b5a] font-black tracking-tight">Gestión Corporativa</span>
            </h1>
            <p>Control de empresas aliadas, facturación corporativa y accesos de seguridad empresarial.</p>
        </div>
        <div class="mup-tabs">
            <a href="{{ route('admin.mup.conductores.index') }}" class="mup-tab">
                <iconify-icon icon="lucide:contact"></iconify-icon> Conductor
            </a>
            <a href="{{ route('admin.mup.propietarios.index') }}" class="mup-tab">
                <iconify-icon icon="lucide:user-cog"></iconify-icon> Propietario
            </a>
            <a href="{{ route('admin.mup.empresas.index') }}" class="mup-tab active">
                <iconify-icon icon="lucide:building-2"></iconify-icon> Empresas
            </a>
            <a href="{{ route('admin.mup.usuarios.index') }}" class="mup-tab">
                <iconify-icon icon="lucide:users-round"></iconify-icon> Usuario
            </a>
        </div>
    </header>

    <div class="mup-content-scroll">
        <div class="space-y-6 pb-12">
            
            {{-- SECCIÓN: Directorio Empresarial --}}
            <section class="mup-card animate-fade-in shadow-xl">
                <div class="mup-card-header-plain flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <div class="mup-card-title text-gray-800">Directorio Empresarial</div>
                        <div class="mup-card-subtitle">Administración de NITs, cuentas de acceso y estados de facturación.</div>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                        <div class="relative flex-1 md:flex-none">
                            <input type="text" x-model="search" placeholder="Buscar por nombre, NIT o gerente..." 
                                   class="pl-11 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm w-full md:w-96 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#0d3b5a]/10 transition-all">
                            <div class="absolute left-4 top-3 text-gray-400">
                                <iconify-icon icon="lucide:search" class="text-lg"></iconify-icon>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mup-table-wrap overflow-x-auto">
                    <table class="mup-data-table min-w-[1000px]">
                        <thead>
                            <tr class="bg-gray-50/80">
                                <th class="w-16">ID</th>
                                <th>Empresa</th>
                                <th>NIT Fiscal</th>
                                <th>Email Corporativo</th>
                                <th class="text-center">Estado</th>
                                <th class="text-right px-6">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="e in filteredEmpresas()" :key="e.idemp">
                                <tr class="hover:bg-[#0d3b5a]/[0.02] transition-colors border-b border-gray-50 last:border-0 group">
                                    <td class="text-xs font-bold text-gray-400" x-text="'EMP-'+e.idemp"></td>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-[#0d3b5a] flex items-center justify-center text-white font-black text-xs shadow-md" 
                                                 x-text="e.abremp ? e.abremp.substring(0,3) : e.razsoem.substring(0,2).toUpperCase()"></div>
                                            <div>
                                                <div class="font-bold text-gray-800" x-text="e.razsoem"></div>
                                                <div class="text-[10px] uppercase text-gray-400 font-bold tracking-widest" x-text="'Gerente: ' + e.nomger"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-mono text-sm text-[#0d3b5a] font-bold" x-text="e.nonitem"></div>
                                        <div class="text-[10px] text-gray-400" x-text="e.direm || 'S/D'"></div>
                                    </td>
                                    <td>
                                        <div class="text-sm font-medium text-gray-700" x-text="e.emaem"></div>
                                        <div class="text-[10px] text-gray-400 flex items-center gap-1">
                                            <iconify-icon icon="lucide:phone" class="text-[8px]"></iconify-icon>
                                            <span x-text="e.telem"></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="mup-state-badge mup-state-active inline-flex items-center shadow-sm">
                                            <div class="w-1.5 h-1.5 rounded-full bg-current mr-1.5"></div>
                                            Activo
                                        </span>
                                    </td>
                                    <td class="text-right px-6">
                                        <div class="flex justify-end gap-1">
                                            <button @click="editEmpresa(e)" class="p-2.5 text-blue-600 hover:bg-blue-50 rounded-xl transition-all" title="Editar">
                                                <iconify-icon icon="lucide:file-edit"></iconify-icon>
                                            </button>
                                            <button @click="deleteEmpresa(e)" class="p-2.5 text-red-600 hover:bg-red-50 rounded-xl transition-all" title="Eliminar">
                                                <iconify-icon icon="lucide:trash-2"></iconify-icon>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-start">
                {{-- CARD: Registro de Empresa --}}
                <section class="lg:col-span-3 mup-card shadow-xl border-t-4 border-[#0d3b5a]">
                    <div class="mup-card-header-soft pb-6">
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-[#0d3b5a]/10 rounded-xl">
                                <iconify-icon icon="lucide:building-plus" class="text-2xl text-[#0d3b5a]"></iconify-icon>
                            </div>
                            <div>
                                <div class="mup-card-title text-xl tracking-tight">Alta Corporativa</div>
                                <div class="mup-card-subtitle">Registro de entidades aliadas y credenciales de acceso.</div>
                            </div>
                        </div>
                    </div>
                    <div class="mup-card-body">
                        <form action="{{ route('admin.mup.empresas.store') }}" method="POST">
                            @csrf
                            
                            {{-- BLOQUE 1: Información Legal --}}
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-[10px] font-black text-gray-400">01</div>
                                <div class="text-[11px] font-black text-[#0d3b5a] uppercase tracking-[0.2em]">Información Corporativa</div>
                                <div class="flex-1 h-px bg-gray-100"></div>
                            </div>

                            <div class="mup-form-grid">
                                <div class="mup-form-group span-2">
                                    <label class="mup-label">Nombre de Empresa / Razón Social <span class="mup-required">*</span></label>
                                    <input type="text" name="razsoem" class="mup-input" placeholder="Ej. Transportes Unidos S.A." required>
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">NIT <span class="mup-required">*</span></label>
                                    <input type="text" name="nonitem" class="mup-input" placeholder="900.123.456-7" required>
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Abreviatura</label>
                                    <input type="text" name="abremp" class="mup-input" placeholder="Ej. TUSA">
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Dirección</label>
                                    <input type="text" name="direm" class="mup-input" placeholder="Dirección principal">
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Ciudad</label>
                                    <input type="text" name="ciudeem" class="mup-input" placeholder="Ej. Medellín">
                                </div>
                            </div>

                            {{-- BLOQUE 2: Contacto --}}
                            <div class="flex items-center gap-4 mt-10 mb-6">
                                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-[10px] font-black text-gray-400">02</div>
                                <div class="text-[11px] font-black text-[#0d3b5a] uppercase tracking-[0.2em]">Contacto Corporativo</div>
                                <div class="flex-1 h-px bg-gray-100"></div>
                            </div>

                            <div class="mup-form-grid">
                                <div class="mup-form-group">
                                    <label class="mup-label">Nombre del Gerente <span class="mup-required">*</span></label>
                                    <input type="text" name="nomger" class="mup-input" placeholder="Ej. Luis Miguel Restrepo" required>
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Teléfono Corporativo <span class="mup-required">*</span></label>
                                    <input type="text" name="telem" class="mup-input" placeholder="Ej. 604 123 4567" required>
                                </div>
                                <div class="mup-form-group span-2">
                                    <label class="mup-label">Email de Contacto <span class="mup-required">*</span></label>
                                    <input type="email" name="emaem" class="mup-input" placeholder="contacto@empresa.com" required>
                                </div>
                            </div>

                            {{-- BLOQUE 3: Seguridad --}}
                            <div class="flex items-center gap-4 mt-10 mb-6 px-6 py-4 bg-gray-50 rounded-2xl border border-gray-100">
                                <div class="w-10 h-10 rounded-xl bg-[#0d3b5a] flex items-center justify-center text-white">
                                    <iconify-icon icon="lucide:key-round" class="text-xl"></iconify-icon>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-bold text-[#0d3b5a]">Acceso al Sistema</div>
                                    <div class="text-[10px] text-gray-400 uppercase font-bold tracking-tight">Credenciales para el portal corporativo</div>
                                </div>
                            </div>

                            <div class="mup-form-grid">
                                <div class="mup-form-group">
                                    <label class="mup-label">Nombre de Usuario <span class="mup-required">*</span></label>
                                    <input type="text" name="username" class="mup-input" placeholder="Ej. transportes.unidos" required>
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Contraseña <span class="mup-required">*</span></label>
                                    <input type="password" name="password" class="mup-input" required>
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Confirmar Contraseña <span class="mup-required">*</span></label>
                                    <input type="password" name="password_confirmation" class="mup-input" required>
                                </div>
                            </div>

                            <div class="mt-10 flex justify-end gap-3 pt-6 border-t border-gray-100">
                                <button type="reset" class="mup-btn mup-btn-outline">Reiniciar</button>
                                <button type="submit" class="mup-btn mup-btn-primary shadow-lg shadow-[#0d3b5a]/20">
                                    <iconify-icon icon="lucide:save"></iconify-icon>
                                    Registrar Empresa
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                {{-- CARD: Permisos Corporativos --}}
                <section class="lg:col-span-2 mup-card h-full flex flex-col">
                    <form :action="'{{ url('admin/entidades/mup/perfil') }}/' + perfil.idpef" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="nompef" :value="perfil.nompef">

                        <div class="mup-card-header-soft pb-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-3 bg-white shadow-sm border border-gray-100 rounded-xl">
                                        <iconify-icon icon="lucide:shield-check" class="text-2xl text-blue-600"></iconify-icon>
                                    </div>
                                    <div>
                                        <div class="mup-card-title text-lg tracking-tight">Privilegios Corporativos</div>
                                        <div class="mup-card-subtitle text-[11px]">Control de módulos para empresas.</div>
                                    </div>
                                </div>
                                <button type="button" @click="toggleAllPermissions()" class="text-[9px] bg-[#0d3b5a] text-white px-3 py-2 rounded-lg font-black uppercase tracking-widest hover:bg-[#1a4f73] transition-all">Marcar todos</button>
                            </div>
                        </div>
                        <div class="mup-card-body flex-1">
                            <div class="space-y-3">
                                <template x-for="mod in modulos" :key="mod.idpag">
                                    <div class="flex items-center justify-between p-4 bg-gray-50 border border-gray-100 rounded-2xl hover:bg-white hover:shadow-md transition-all group">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-blue-800 shadow-sm transition-transform group-hover:scale-110">
                                                <iconify-icon :icon="mod.icopag || 'lucide:check-square'" class="text-sm"></iconify-icon>
                                            </div>
                                            <span class="text-xs font-bold text-gray-700" x-text="mod.nompag"></span>
                                        </div>
                                        <div class="flex gap-2">
                                            <div class="flex flex-col items-center gap-1">
                                                <input type="checkbox" :name="'permisos[' + mod.nompag + '][ver]'" 
                                                    :checked="hasPermission(mod.nompag, 'ver')"
                                                    @change="togglePermission(mod.nompag, 'ver')"
                                                    class="w-4 h-4 rounded border-gray-300 text-[#0d3b5a] focus:ring-[#0d3b5a]">
                                                <span class="text-[7px] font-black uppercase text-gray-400">Ver</span>
                                            </div>
                                            <div class="flex flex-col items-center gap-1" x-show="!['Dashboard', 'Rechazados'].includes(mod.nompag)">
                                                <input type="checkbox" :name="'permisos[' + mod.nompag + '][crear]'" 
                                                    :checked="hasPermission(mod.nompag, 'crear')"
                                                    @change="togglePermission(mod.nompag, 'crear')"
                                                    class="w-4 h-4 rounded border-gray-300 text-[#0d3b5a] focus:ring-[#0d3b5a]">
                                                <span class="text-[7px] font-black uppercase text-gray-400">Add</span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="mt-8 p-4 bg-blue-50/50 border border-blue-100 rounded-2xl text-[10px] text-blue-800 leading-relaxed italic">
                                <iconify-icon icon="lucide:info" class="mr-1"></iconify-icon>
                                Los cambios en este panel afectan globalmente a todos los perfiles de tipo Empresa vinculados a este CDA.
                            </div>

                            <div class="mt-6 flex justify-end gap-3 pt-6 border-t border-gray-100">
                                <button type="submit" class="mup-btn mup-btn-primary w-full shadow-lg shadow-[#0d3b5a]/20">
                                    <iconify-icon icon="lucide:shield-check"></iconify-icon>
                                    Guardar cambios de perfil
                                </button>
                            </div>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>

    {{-- MODAL DE EDICIÓN CORPORATIVA --}}
    <div x-show="editing" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-[#0d3b5a]/40 backdrop-blur-sm">
        
        <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-2xl overflow-hidden" @click.away="closeModal()">
            <div class="p-8 bg-[#0d3b5a] text-white flex justify-between items-center relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -mr-20 -mt-20"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="p-4 bg-white text-[#0d3b5a] rounded-2xl shadow-xl">
                        <iconify-icon icon="lucide:settings-2" class="text-2xl"></iconify-icon>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black tracking-tight" x-text="currentEmp.razsoem"></h2>
                        <p class="text-[#89b3d0] text-xs font-bold uppercase tracking-widest">Edición de ficha corporativa</p>
                    </div>
                </div>
                <button @click="closeModal()" class="p-2 hover:bg-white/10 rounded-xl transition-all relative z-10">
                    <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
                </button>
            </div>

            <form :action="'{{ url('admin/entidades/mup/empresas') }}/' + currentEmp.idemp" method="POST" class="p-8">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label class="mup-label">Razón Social</label>
                        <input type="text" name="razsoem" x-model="currentEmp.razsoem" class="mup-input" required>
                    </div>
                    <div>
                        <label class="mup-label">NIT</label>
                        <input type="text" name="nonitem" x-model="currentEmp.nonitem" class="mup-input" required>
                    </div>
                    <div>
                        <label class="mup-label">Nombre Gerente</label>
                        <input type="text" name="nomger" x-model="currentEmp.nomger" class="mup-input" required>
                    </div>
                    <div>
                        <label class="mup-label">Email Corporativo</label>
                        <input type="email" name="emaem" x-model="currentEmp.emaem" class="mup-input" required>
                    </div>
                    <div>
                        <label class="mup-label">Teléfono</label>
                        <input type="text" name="telem" x-model="currentEmp.telem" class="mup-input" required>
                    </div>
                    <div class="col-span-2">
                        <label class="mup-label">Dirección</label>
                        <input type="text" name="direm" x-model="currentEmp.direm" class="mup-input">
                    </div>
                </div>

                <div class="mt-10 flex gap-4">
                    <button type="button" @click="closeModal()" class="flex-1 py-4 text-gray-500 font-bold hover:bg-gray-50 rounded-2xl transition-all">Cancelar</button>
                    <button type="submit" class="flex-1 py-4 bg-[#0d3b5a] text-white font-bold rounded-2xl shadow-xl shadow-[#0d3b5a]/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL ELIMINAR --}}
    <div x-show="deleting" class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-[#0d3b5a]/40 backdrop-blur-sm">
        <div class="bg-white p-10 rounded-[2.5rem] shadow-2xl max-w-md w-full text-center animate-zoom-in" @click.away="deleting = false">
            <div class="w-24 h-24 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-8">
                <iconify-icon icon="lucide:alert-triangle" style="font-size: 48px;"></iconify-icon>
            </div>
            <h3 class="text-2xl font-black text-gray-800 mb-2">¿Eliminar Empresa?</h3>
            <p class="text-sm text-gray-400 mb-10 leading-relaxed font-medium">
                Esta acción eliminará permanentemente la empresa <span class="text-red-600 font-black" x-text="currentEmp.razsoem"></span> y revocará todos los accesos vinculados.
            </p>
            
            <div class="flex gap-4">
                <button @click="deleting = false" class="flex-1 py-4 text-gray-500 font-bold hover:bg-gray-100 rounded-2xl transition-all">No, cancelar</button>
                <form :action="'{{ url('admin/entidades/mup/empresas') }}/' + currentEmp.idemp" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-4 bg-red-600 text-white font-bold rounded-2xl shadow-xl shadow-red-200">Confirmar</button>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
function empresaManager() {
    return {
        search: '',
        editing: false,
        deleting: false,
        currentEmp: {},
        empresas: @json($empresas),
        perfil: @json($perfil),
        modulos: @json($modulos),

        hasPermission(moduloNom, action) {
            if (!this.perfil || !this.perfil.permission_names) return false;
            const baseRoute = this.mapModuloToRoute(moduloNom);
            if (!baseRoute) return false;

            let representativePermission = '';
            switch(action) {
                case 'ver': 
                    representativePermission = (['admin.dashboard', 'admin.alertas', 'admin.rechazados'].includes(baseRoute)) 
                        ? baseRoute : baseRoute + '.index'; break;
                case 'crear': representativePermission = baseRoute + '.create'; break;
            }
            return this.perfil.permission_names.includes(representativePermission);
        },

        togglePermission(moduloNom, action) {
            const baseRoute = this.mapModuloToRoute(moduloNom);
            if (!baseRoute) return;

            let routesToAdd = [];
            switch(action) {
                case 'ver': 
                    routesToAdd = (['admin.dashboard', 'admin.alertas', 'admin.rechazados'].includes(baseRoute)) 
                        ? [baseRoute] : [baseRoute + '.index', baseRoute + '.show']; break;
                case 'crear': routesToAdd = [baseRoute + '.create', baseRoute + '.store']; break;
            }

            const exists = this.perfil.permission_names.includes(routesToAdd[0]);
            if (exists) {
                this.perfil.permission_names = this.perfil.permission_names.filter(p => !routesToAdd.includes(p));
            } else {
                routesToAdd.forEach(r => {
                    if (!this.perfil.permission_names.includes(r)) this.perfil.permission_names.push(r);
                });
            }
        },

        mapModuloToRoute(nom) {
            const map = {
                'Dashboard': 'admin.dashboard',
                'Diagnóstico': 'admin.diagnosticos',
                'Vehículos': 'admin.vehiculos',
                'Alertas': 'admin.alertas',
                'Mantenimiento': 'admin.dashboard',
                'Empresas': 'admin.mup.empresas',
                'Usuarios': 'admin.mup.usuarios',
                'Conductores': 'admin.mup.conductores',
                'Propietarios': 'admin.mup.propietarios',
                'Rechazados': 'admin.rechazados'
            };
            return map[nom] || null;
        },

        filteredEmpresas() {
            if (!this.search) return this.empresas;
            const q = this.search.toLowerCase();
            return this.empresas.filter(e => 
                e.razsoem.toLowerCase().includes(q) || 
                e.nonitem.toLowerCase().includes(q) ||
                e.nomger.toLowerCase().includes(q) ||
                e.emaem.toLowerCase().includes(q)
            );
        },

        toggleAllPermissions() {
            const allModNames = this.modulos.map(m => m.nompag);
            const allAllowed = allModNames.every(m => this.hasPermission(m, 'ver'));
            
            allModNames.forEach(m => {
                if (allAllowed) {
                    // Si todos están marcados, desmarcamos todos
                    if (this.hasPermission(m, 'ver')) this.togglePermission(m, 'ver');
                    if (this.hasPermission(m, 'crear')) this.togglePermission(m, 'crear');
                } else {
                    // Si falta alguno, marcamos todos
                    if (!this.hasPermission(m, 'ver')) this.togglePermission(m, 'ver');
                    if (!this.hasPermission(m, 'crear')) this.togglePermission(m, 'crear');
                }
            });
        },

        editEmpresa(e) {
            this.currentEmp = { ...e };
            this.editing = true;
        },

        deleteEmpresa(e) {
            this.currentEmp = e;
            this.deleting = true;
        },

        closeModal() {
            this.editing = false;
            this.currentEmp = {};
        }
    }
}
</script>

<style>
    .animate-fade-in { animation: fadeIn 0.6s ease-out forwards; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .animate-zoom-in { animation: zoomIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
    @keyframes zoomIn { from { opacity: 0; transform: scale(0.85); } to { opacity: 1; transform: scale(1); } }
</style>
@endsection
