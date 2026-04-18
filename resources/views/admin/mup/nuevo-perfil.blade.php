@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/mup.css') }}">
<script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>

<div class="mup-container" x-data="perfilCreator()">
    <header class="mup-topbar">
        <div class="mup-page-title">
            <h1 class="flex items-center gap-3">
                <a href="{{ route('admin.mup.usuarios') }}" class="flex items-center justify-center bg-gray-100 p-2 rounded-md text-sm hover:bg-gray-200 transition">
                    <iconify-icon icon="lucide:arrow-left" style="font-size: 16px; color: var(--primary)"></iconify-icon>
                </a>
                <span class="text-[#0d3b5a] font-black tracking-tight">Nuevo Perfil del Sistema</span>
            </h1>
            <p>Crea un nuevo rol y define sus alcances, permisos y accesos en el sistema del CDA.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.mup.usuarios') }}" class="mup-btn mup-btn-outline">Cancelar</a>
            <button type="submit" form="form-nuevo-perfil" class="mup-btn mup-btn-primary" :disabled="submitting">
                <template x-if="!submitting">
                    <span class="flex items-center gap-2">
                        <iconify-icon icon="lucide:save"></iconify-icon>
                        Guardar perfil
                    </span>
                </template>
                <template x-if="submitting">
                    <span class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Guardando...
                    </span>
                </template>
            </button>
        </div>
    </header>

    <div class="mup-content-scroll">
        <div class="max-w-[1000px] mx-auto space-y-6 pb-12">
            <form id="form-nuevo-perfil" action="{{ route('admin.mup.perfil.store') }}" method="POST" @submit="submitting = true">
                @csrf
                
                {{-- CARD: Información del perfil --}}
                <section class="mup-card animate-fade-in shadow-xl border-t-4 border-[#0d3b5a]">
                    <div class="mup-card-header-soft">
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-[#0d3b5a]/10 rounded-xl">
                                <iconify-icon icon="lucide:shield-check" class="text-2xl text-[#0d3b5a]"></iconify-icon>
                            </div>
                            <div>
                                <div class="mup-card-title text-xl">Identidad del Rol</div>
                                <div class="mup-card-subtitle">Define el nombre y alcance principal de este nuevo perfil.</div>
                            </div>
                        </div>
                    </div>
                    <div class="mup-card-body">
                        <div class="mup-form-grid">
                            <div class="mup-form-group">
                                <label class="mup-label">Nombre del perfil <span class="mup-required">*</span></label>
                                <input type="text" name="nompef" class="mup-input focus:ring-2 focus:ring-[#0d3b5a]/20" placeholder="Ej. Supervisor de Operaciones" required value="{{ old('nompef') }}">
                                @error('nompef') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="mup-form-group">
                                <label class="mup-label">Categoría / Tipo <span class="mup-required">*</span></label>
                                <select name="tipo_pef" class="mup-input cursor-pointer">
                                    <option value="Operativo" @selected(old('tipo_pef') == 'Operativo')>Operativo</option>
                                    <option value="Administrativo" @selected(old('tipo_pef') == 'Administrativo')>Administrativo</option>
                                    <option value="Gerencial" @selected(old('tipo_pef') == 'Gerencial')>Gerencial</option>
                                    <option value="Externo" @selected(old('tipo_pef') == 'Externo')>Externo</option>
                                </select>
                            </div>
                            <div class="mup-form-group span-2">
                                <label class="mup-label">Descripción de responsabilidades</label>
                                <textarea name="des_pef" class="mup-input py-3 h-20 resize-none" placeholder="¿Qué funciones principales tendrá este perfil en el día a día?">{{ old('des_pef') }}</textarea>
                            </div>
                            <div class="mup-form-group">
                                <label class="mup-label">Estado de activación</label>
                                <div class="flex items-center gap-4 h-11 px-1">
                                    <label class="flex items-center gap-2 cursor-pointer group">
                                        <input type="radio" name="activo" value="1" checked class="w-4 h-4 text-[#0d3b5a] focus:ring-[#0d3b5a]">
                                        <span class="text-sm text-gray-600 group-hover:text-gray-900 transition">Habilitado</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer group">
                                        <input type="radio" name="activo" value="0" class="w-4 h-4 text-[#0d3b5a] focus:ring-[#0d3b5a]">
                                        <span class="text-sm text-gray-600 group-hover:text-gray-900 transition">Deshabilitado</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- CARD: Configuración de permisos --}}
                <section class="mup-card animate-fade-in shadow-xl" style="animation-delay: 100ms">
                    <div class="mup-card-body pt-8">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                            <div>
                                <div class="mup-card-title text-xl font-bold text-gray-800">Matriz de Acciones y Alcance</div>
                                <div class="mup-card-subtitle mt-1">Marca las acciones específicas que este perfil podrá realizar.</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="toggleGroup(true)" class="flex items-center gap-2 px-4 py-2 bg-gray-50 hover:bg-gray-100 text-[#0d3b5a] rounded-lg text-xs font-bold transition border border-gray-200 shadow-sm">
                                    <iconify-icon icon="lucide:check-circle"></iconify-icon>
                                    Marcar Todo en esta pestaña
                                </button>
                                <button type="button" @click="toggleGroup(false)" class="flex items-center gap-2 px-4 py-2 bg-white hover:bg-red-50 text-red-600 rounded-lg text-xs font-bold transition border border-red-100 shadow-sm">
                                    <iconify-icon icon="lucide:x-circle"></iconify-icon>
                                    Desmarcar Todo
                                </button>
                            </div>
                        </div>

                        <!-- NAVIGATION TABS -->
                        <div class="flex gap-1 border-b mb-8 p-1 bg-gray-50/50 rounded-xl overflow-x-auto">
                            <button type="button" 
                                @click="activeTab = 'principales'"
                                :class="activeTab === 'principales' ? 'bg-white text-[#0d3b5a] shadow-sm ring-1 ring-gray-200' : 'text-gray-400 hover:text-gray-600'"
                                class="px-6 py-2.5 rounded-lg text-sm font-bold transition-all whitespace-nowrap">
                                Módulos Principales
                            </button>
                            <button type="button" 
                                @click="activeTab = 'operativo'"
                                :class="activeTab === 'operativo' ? 'bg-white text-[#0d3b5a] shadow-sm ring-1 ring-gray-200' : 'text-gray-400 hover:text-gray-600'"
                                class="px-6 py-2.5 rounded-lg text-sm font-bold transition-all whitespace-nowrap">
                                Operativo y Entidades
                            </button>
                            <button type="button" 
                                @click="activeTab = 'config'"
                                :class="activeTab === 'config' ? 'bg-white text-[#0d3b5a] shadow-sm ring-1 ring-gray-200' : 'text-gray-400 hover:text-gray-600'"
                                class="px-6 py-2.5 rounded-lg text-sm font-bold transition-all whitespace-nowrap">
                                Configuración Global
                            </button>
                        </div>

                        <div class="mup-table-wrap !border-0 overflow-x-auto">
                            <div class="min-w-[800px]">
                                <div class="grid grid-cols-5 text-[11px] font-extrabold text-[#0d3b5a]/40 uppercase tracking-widest py-4 px-4 bg-gray-50/50 rounded-t-lg">
                                    <div class="col-span-1">Módulo del Sistema</div>
                                    <div class="text-center">Visualizar</div>
                                    <div class="text-center">Crear Nuevo</div>
                                    <div class="text-center">Editar / Act.</div>
                                    <div class="text-center">Eliminar</div>
                                </div>

                                <div class="divide-y divide-gray-100 border rounded-b-lg">
                                    @foreach($modulos as $modulo)
                                    <div x-show="isInTab('{{ $modulo->nompag }}')" 
                                        class="grid grid-cols-5 py-5 items-center px-4 hover:bg-[#0d3b5a]/[0.02] transition-colors group">
                                        
                                        <div class="col-span-1 flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center group-hover:bg-[#0d3b5a] transition-all transform group-hover:scale-110 shadow-sm overflow-hidden">
                                                <iconify-icon icon="{{ $modulo->icopag ?? 'lucide:box' }}" class="text-xl text-gray-500 group-hover:text-white transition-colors"></iconify-icon>
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-gray-700 group-hover:text-[#0d3b5a] transition-colors">{{ $modulo->nompag }}</div>
                                                <div class="text-[10px] text-gray-400 leading-none mt-1">{{ Str::limit($modulo->despag, 40) }}</div>
                                            </div>
                                        </div>

                                        @foreach(['ver', 'crear', 'editar', 'eliminar'] as $action)
                                        <div class="flex justify-center">
                                            <div class="relative flex items-center justify-center p-2 rounded-lg hover:bg-gray-100 transition group/cell">
                                                <input type="checkbox" 
                                                    name="permisos[{{ $modulo->nompag }}][{{ $action }}]" 
                                                    @change="handlePermission('{{ $modulo->nompag }}', '{{ $action }}', $event)"
                                                    :checked="permissions['{{ $modulo->nompag }}'] && permissions['{{ $modulo->nompag }}']['{{ $action }}']"
                                                    class="w-6 h-6 rounded-md border-gray-300 text-[#0d3b5a] focus:ring-[#0d3b5a] cursor-pointer transition-all transform group-hover/cell:scale-110">
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- LEGEND -->
                        <div class="mt-10 pt-8 border-t flex flex-wrap items-center gap-8">
                            <div class="flex items-center gap-3">
                                <div class="w-4 h-4 rounded shadow-inner bg-[#0d3b5a]"></div>
                                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Habilitado</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-4 h-4 rounded shadow-inner bg-white border border-gray-200"></div>
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Restringido</span>
                            </div>
                            <div class="ml-auto text-xs text-gray-400 italic">
                                * Nota: Algunos permisos dependen de otros. Al marcar "Crear", se habilitará "Visualizar" automáticamente.
                            </div>
                        </div>
                    </div>
                </section>
            </form>
        </div>
    </div>
</div>

<script>
function perfilCreator() {
    return {
        submitting: false,
        activeTab: 'principales',
        permissions: {},
        
        // Categorización manual de módulos para las pestañas
        tabs: {
            principales: ['Dashboard', 'Diagnóstico', 'Vehículos', 'Alertas', 'Mantenimiento'],
            operativo: ['Usuarios', 'Empresas', 'Conductores', 'Propietarios', 'Rechazados'],
            config: [] // Aquí se podrían añadir módulos como Perfiles si se separan
        },

        init() {
            // Inicializar objeto de permisos vacío para cada módulo disponible
            const modulos = {!! json_encode($modulos->pluck('nompag')) !!};
            modulos.forEach(m => {
                this.permissions[m] = { ver: false, crear: false, editar: false, eliminar: false };
            });
        },

        isInTab(nompag) {
            // Lógica de visualización por pestaña
            if (this.tabs[this.activeTab].includes(nompag)) return true;
            
            // Fallback: si estoy en 'principales' y el módulo no está en ninguna tab, mostrarlo
            if (this.activeTab === 'principales') {
                const groupedModules = [...this.tabs.operativo, ...this.tabs.config];
                return !groupedModules.includes(nompag);
            }
            
            return false;
        },

        handlePermission(modulo, action, event) {
            const isChecked = event.target.checked;
            this.permissions[modulo][action] = isChecked;

            // Dependencia funcional: Si se otorga cualquier permiso de acción, se habilita "ver"
            if (isChecked && (action === 'crear' || action === 'editar' || action === 'eliminar')) {
                this.permissions[modulo]['ver'] = true;
            }
        },

        toggleGroup(state) {
            // Afectar solo a los módulos visibles en la pestaña actual
            const modulosVisibles = {!! json_encode($modulos->pluck('nompag')) !!}.filter(m => this.isInTab(m));
            
            modulosVisibles.forEach(m => {
                this.permissions[m].ver = state;
                this.permissions[m].crear = state;
                this.permissions[m].editar = state;
                this.permissions[m].eliminar = state;
            });
        }
    }
}
</script>

<style>
    .animate-spin {
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .animate-fade-in {
        animation: fadeIn 0.5s ease-out forwards;
        opacity: 0;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .mup-input:focus {
        border-color: #0d3b5a;
        box-shadow: 0 0 0 4px rgba(13, 59, 90, 0.1);
    }
    
    /* Scrollbar personalizado para el contenedor de la tabla en móvil */
    .mup-table-wrap::-webkit-scrollbar {
        height: 6px;
    }
    .mup-table-wrap::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    .mup-table-wrap::-webkit-scrollbar-thumb {
        background: #0d3b5a44;
        border-radius: 10px;
    }
    .mup-table-wrap::-webkit-scrollbar-thumb:hover {
        background: #0d3b5a66;
    }
</style>
@endsection
