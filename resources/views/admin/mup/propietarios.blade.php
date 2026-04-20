@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/mup.css') }}">
<script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>

<div class="mup-container" x-data="propietarioManager()">
    <header class="mup-topbar">
        <div class="mup-page-title">
            <h1 class="flex items-center gap-3">
                <div class="p-2 bg-[#0d3b5a] rounded-lg shadow-lg shadow-[#0d3b5a]/20">
                    <iconify-icon icon="lucide:user-cog" class="text-white text-xl"></iconify-icon>
                </div>
                <span class="text-[#0d3b5a] font-black tracking-tight">Gestión de Propietarios</span>
            </h1>
            <p>Control maestro de propietarios de vehículos, licencias y perfiles operativos vinculados al CDA.</p>
        </div>
        <div class="mup-tabs">
            <a href="{{ route('admin.mup.conductores.index') }}" class="mup-tab">
                <iconify-icon icon="lucide:contact"></iconify-icon>
                Conductor
            </a>
            <a href="{{ route('admin.mup.propietarios.index') }}" class="mup-tab active">
                <iconify-icon icon="lucide:user-cog"></iconify-icon>
                Propietario
            </a>
            <a href="{{ route('admin.mup.empresas.index') }}" class="mup-tab">
                <iconify-icon icon="lucide:building"></iconify-icon>
                Empresas
            </a>
            <a href="{{ route('admin.mup.usuarios.index') }}" class="mup-tab">
                <iconify-icon icon="lucide:users-round"></iconify-icon>
                Usuario
            </a>
        </div>
    </header>

    <div class="mup-content-scroll">
        <div class="space-y-6 pb-12">
            
            {{-- SECCIÓN: Listado Maestro --}}
            <section class="mup-card animate-fade-in shadow-xl">
                <div class="mup-card-header-plain flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <div class="mup-card-title text-gray-800">Directorio de Propietarios</div>
                        <div class="mup-card-subtitle">Consulta avanzada de identificación y estados operativos.</div>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                        <div class="flex border rounded-xl p-1 bg-gray-50/50 shadow-sm overflow-hidden border-gray-200">
                            <button class="px-4 py-1.5 text-[10px] font-black text-blue-600 hover:bg-white hover:text-blue-800 transition rounded-lg">CSV</button>
                            <button class="px-4 py-1.5 text-[10px] font-black text-green-600 hover:bg-white hover:text-green-800 transition rounded-lg mx-1">EXCEL</button>
                            <button class="px-4 py-1.5 text-[10px] font-black text-red-600 hover:bg-white hover:text-red-800 transition rounded-lg">PDF</button>
                        </div>
                        <div class="relative flex-1 md:flex-none">
                            <input type="text" x-model="search" placeholder="Buscar por nombre, documento o ciudad..." 
                                   class="pl-11 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm w-full md:w-80 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#0d3b5a]/10 transition-all">
                            <div class="absolute left-4 top-3 text-gray-400">
                                <iconify-icon icon="lucide:search" class="text-lg"></iconify-icon>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mup-table-wrap overflow-x-auto">
                    <table class="mup-data-table min-w-[900px]">
                        <thead>
                            <tr class="bg-gray-50/80">
                                <th class="w-16">ID</th>
                                <th>Propietario</th>
                                <th>Identificación</th>
                                <th>Localización</th>
                                <th class="text-center">Estado</th>
                                <th class="text-right px-6">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="p in filteredPropietarios()" :key="p.idper">
                                <tr class="hover:bg-[#0d3b5a]/[0.02] transition-colors border-b border-gray-50 last:border-0 group">
                                    <td class="text-xs font-bold text-gray-400" x-text="'P-'+p.idper"></td>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-[#0d3b5a]/10 flex items-center justify-center text-[#0d3b5a] font-black text-xs" 
                                                 x-text="p.nomper[0] + (p.apeper ? p.apeper[0] : '')"></div>
                                            <div>
                                                <div class="font-bold text-gray-800" x-text="p.nomper + ' ' + (p.apeper || '')"></div>
                                                <div class="text-[11px] text-gray-400" x-text="p.emaper"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-sm text-gray-700 font-medium" x-text="numberFormat(p.ndocper)"></div>
                                        <div class="text-[10px] text-gray-400 uppercase tracking-tighter" x-text="'Tipo: ' + getDocType(p.tdocper)"></div>
                                    </td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-[#0d3b5a]" x-text="p.ciuper || 'Sin ciudad'"></span>
                                            <span class="text-[10px] text-gray-500 flex items-center gap-1">
                                                <iconify-icon icon="lucide:map-pin" class="text-gray-400 text-[8px]"></iconify-icon>
                                                <span x-text="p.dirper || 'Sin dirección'"></span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span :class="p.actper ? 'mup-state-active' : 'mup-state-inactive'" class="inline-flex items-center shadow-sm">
                                            <div class="w-1.5 h-1.5 rounded-full bg-current mr-1.5"></div>
                                            <span x-text="p.actper ? 'Activo' : 'Inactivo'"></span>
                                        </span>
                                    </td>
                                    <td class="text-right px-6">
                                        <div class="flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="editPropietario(p)" class="p-2.5 text-blue-600 hover:bg-blue-50 rounded-xl transition-all" title="Editar">
                                                <iconify-icon icon="lucide:pencil"></iconify-icon>
                                            </button>
                                            <button @click="deletePropietario(p)" class="p-2.5 text-red-600 hover:bg-red-50 rounded-xl transition-all" title="Eliminar">
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

            <div class="mup-management-row">
                {{-- FORMULARIO: Registro Propietario --}}
                <section class="mup-card shadow-xl border-t-4 border-[#0d3b5a]">
                    <div class="mup-card-header-soft pb-6">
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-[#0d3b5a]/10 rounded-xl">
                                <iconify-icon icon="lucide:user-plus" class="text-2xl text-[#0d3b5a]"></iconify-icon>
                            </div>
                            <div>
                                <div class="mup-card-title text-xl tracking-tight">Registro de Propietario</div>
                                <div class="mup-card-subtitle">Alta de ficha personal y datos de localización.</div>
                            </div>
                        </div>
                    </div>
                    <div class="mup-card-body">
                        <form action="{{ route('admin.mup.propietarios.store') }}" method="POST">
                            @csrf
                            <div class="text-[11px] font-black text-[#0d3b5a]/40 mb-6 uppercase tracking-[0.2em] flex items-center gap-3">
                                <span>Información Identitaria</span>
                                <div class="flex-1 h-px bg-gray-100"></div>
                            </div>

                            <div class="mup-form-grid">
                                <div class="mup-form-group span-2">
                                    <label class="mup-label">Nombre Completo <span class="mup-required">*</span></label>
                                    <input type="text" name="nombre_completo" class="mup-input" placeholder="Ej. Juan Manuel Galán" required>
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Tipo Documento <span class="mup-required">*</span></label>
                                    <select name="tdocper" class="mup-input" required>
                                        @foreach($tiposDoc as $tipo)
                                            <option value="{{ $tipo->idval }}">{{ $tipo->nomval }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">No. Documento <span class="mup-required">*</span></label>
                                    <input type="number" name="ndocper" class="mup-input" placeholder="Sin puntos ni comas" required>
                                </div>
                            </div>

                            <div class="text-[11px] font-black text-[#0d3b5a]/40 mt-10 mb-6 uppercase tracking-[0.2em] flex items-center gap-3">
                                <span>Localización y Contacto</span>
                                <div class="flex-1 h-px bg-gray-100"></div>
                            </div>

                            <div class="mup-form-grid">
                                <div class="mup-form-group">
                                    <label class="mup-label">Ciudad</label>
                                    <input type="text" name="ciuper" class="mup-input" placeholder="Ej. Bogotá">
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Dirección</label>
                                    <input type="text" name="dirper" class="mup-input" placeholder="Ej. Carrera 7 # 12-34">
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">E-mail <span class="mup-required">*</span></label>
                                    <input type="email" name="emaper" class="mup-input" placeholder="propietario@email.com" required>
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Teléfono</label>
                                    <input type="text" name="telper" class="mup-input" placeholder="300 123 4567">
                                </div>
                                
                                {{-- Campos adicionales operativos que requiere Persona para Propietarios --}}
                                <div class="mup-form-group">
                                    <label class="mup-label">Cat. Licencia <span class="mup-required">*</span></label>
                                    <select name="catcon" class="mup-input" required>
                                        @foreach($categorias as $cat)
                                            <option value="{{ $cat->idval }}">{{ $cat->nomval }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">No. Licencia <span class="mup-required">*</span></label>
                                    <input type="text" name="nliccon" class="mup-input" placeholder="Nro de pase" required>
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Vencimiento <span class="mup-required">*</span></label>
                                    <input type="date" name="fvencon" class="mup-input" required>
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Estado Inicial</label>
                                    <select name="actper" class="mup-input" required>
                                        <option value="1">Activo</option>
                                        <option value="0">Inactivo</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-10 flex justify-end gap-3 pt-6 border-t border-gray-100">
                                <button type="reset" class="mup-btn mup-btn-outline">Reiniciar</button>
                                <button type="submit" class="mup-btn mup-btn-primary shadow-lg shadow-[#0d3b5a]/20">
                                    <iconify-icon icon="lucide:save" class="text-sm"></iconify-icon>
                                    Guardar Propietario
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                {{-- CARD: Permisos Estáticos (Mismo estilo que Usuarios) --}}
                <section class="mup-card bg-[#f8fafc] border border-gray-200">
                    <div class="mup-card-header-soft pb-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-3 bg-white shadow-sm border border-gray-100 rounded-xl">
                                    <iconify-icon icon="lucide:shield-check" class="text-2xl text-[#0d3b5a]"></iconify-icon>
                                </div>
                                <div>
                                    <div class="mup-card-title text-xl tracking-tight">Permisos de Perfil</div>
                                    <div class="mup-card-subtitle">Acceso global para el rol de Propietario.</div>
                                </div>
                            </div>
                            <button class="text-[10px] bg-[#0d3b5a] text-white px-3 py-1.5 rounded-lg font-black uppercase tracking-widest">Marcar Todos</button>
                        </div>
                    </div>
                    
                    <div class="mup-card-body">
                        <div class="space-y-3">
                            @php
                                $modulosProp = ['Dashboard', 'Agenda de revisión', 'Historial de vehículos', 'Documentos asociados', 'Alertas de vencimiento', 'Actualización de datos'];
                            @endphp
                            <div class="grid grid-cols-6 text-[9px] font-black text-gray-400 uppercase tracking-widest mb-4">
                                <div class="col-span-2">Módulo</div>
                                <div class="text-center">Ver</div>
                                <div class="text-center">Crear</div>
                                <div class="text-center">Edit</div>
                                <div class="text-center">Elim</div>
                            </div>
                            @foreach($modulosProp as $mod)
                            <div class="grid grid-cols-6 py-3 px-4 bg-white border border-gray-100 rounded-2xl items-center shadow-sm">
                                <div class="col-span-2 text-xs font-bold text-gray-700">{{ $mod }}</div>
                                <div class="flex justify-center"><input type="checkbox" checked class="w-4 h-4 rounded border-gray-300 text-[#0d3b5a] focus:ring-[#0d3b5a]"></div>
                                <div class="flex justify-center"><input type="checkbox" checked class="w-4 h-4 rounded border-gray-300 text-[#0d3b5a] focus:ring-[#0d3b5a]"></div>
                                <div class="flex justify-center"><input type="checkbox" checked class="w-4 h-4 rounded border-gray-300 text-[#0d3b5a] focus:ring-[#0d3b5a]"></div>
                                <div class="flex justify-center opacity-10"><input type="checkbox" disabled class="w-4 h-4 rounded border-gray-300"></div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    {{-- MODAL DE EDICIÓN --}}
    <div x-show="editing" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-[#0d3b5a]/40 backdrop-blur-sm">
        
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden animate-zoom-in" @click.away="closeModal()">
            <div class="p-8 bg-[#0d3b5a] text-white flex justify-between items-center bg-gradient-to-br from-[#0d3b5a] to-[#1a4f73]">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-white/10 rounded-2xl">
                        <iconify-icon icon="lucide:user-round-cog" class="text-2xl"></iconify-icon>
                    </div>
                    <div>
                        <h2 class="text-xl font-black tracking-tight" x-text="'Editando: ' + currentProp.nomper"></h2>
                        <p class="text-[#89b3d0] text-[11px] uppercase tracking-widest font-bold">Actualización de ficha de propiedad</p>
                    </div>
                </div>
                <button @click="closeModal()" class="p-2 hover:bg-white/10 rounded-xl transition-all">
                    <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
                </button>
            </div>

            <form :action="'{{ url('entidades/mup/propietarios') }}/' + currentProp.idper" method="POST" class="p-8">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label class="mup-label">Nombre Completo</label>
                        <input type="text" name="nombre_completo" x-model="currentProp.nombre_completo" class="mup-input" required>
                    </div>
                    <div>
                        <label class="mup-label">No. Documento</label>
                        <input type="number" name="ndocper" x-model="currentProp.ndocper" class="mup-input" required>
                    </div>
                    <div>
                        <label class="mup-label">Email</label>
                        <input type="email" name="emaper" x-model="currentProp.emaper" class="mup-input" required>
                    </div>
                    <div>
                        <label class="mup-label">Ciudad</label>
                        <input type="text" name="ciuper" x-model="currentProp.ciuper" class="mup-input">
                    </div>
                    <div>
                        <label class="mup-label">Dirección</label>
                        <input type="text" name="dirper" x-model="currentProp.dirper" class="mup-input">
                    </div>
                    <div>
                        <label class="mup-label">Estado</label>
                        <select name="actper" x-model="currentProp.actper" class="mup-input" required>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                    {{-- Hidden defaults --}}
                    <input type="hidden" name="tdocper" x-model="currentProp.tdocper">
                    <input type="hidden" name="catcon" x-model="currentProp.catcon">
                    <input type="hidden" name="nliccon" x-model="currentProp.nliccon">
                    <input type="hidden" name="fvencon" x-model="currentProp.fvencon">
                </div>

                <div class="mt-10 flex gap-4">
                    <button type="button" @click="closeModal()" class="flex-1 py-4 text-gray-500 font-bold hover:bg-gray-50 rounded-2xl transition-all">Cancelar</button>
                    <button type="submit" class="flex-1 py-4 bg-[#0d3b5a] text-white font-bold rounded-2xl shadow-xl shadow-[#0d3b5a]/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                        Actualizar Propietario
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL ELIMINAR --}}
    <div x-show="deleting" class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-[#0d3b5a]/40 backdrop-blur-sm">
        <div class="bg-white p-8 rounded-3xl shadow-2xl max-w-sm w-full text-center animate-zoom-in" @click.away="deleting = false">
            <div class="w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6">
                <iconify-icon icon="lucide:alert-circle" style="font-size: 40px;"></iconify-icon>
            </div>
            <h3 class="text-xl font-black text-gray-800 mb-2">¿Eliminar Propietario?</h3>
            <p class="text-sm text-gray-400 mb-8 leading-relaxed">
                Esta acción es definitiva. Se borrará la ficha de <span class="text-gray-900 font-bold" x-text="currentProp.nomper"></span>.
            </p>
            
            <div class="flex gap-3">
                <button @click="deleting = false" class="flex-1 py-3 text-gray-500 font-bold hover:bg-gray-50 rounded-xl transition-all">No, volver</button>
                <form :action="'{{ url('entidades/mup/propietarios') }}/' + currentProp.idper" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-3 bg-red-600 text-white font-bold rounded-xl shadow-lg shadow-red-200">Sí, eliminar</button>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
function propietarioManager() {
    return {
        search: '',
        editing: false,
        deleting: false,
        currentProp: {},
        propietarios: @json($propietarios),

        filteredPropietarios() {
            if (!this.search) return this.propietarios;
            const q = this.search.toLowerCase();
            return this.propietarios.filter(p => 
                p.nomper.toLowerCase().includes(q) || 
                (p.apeper && p.apeper.toLowerCase().includes(q)) ||
                p.ndocper.toString().includes(q) ||
                (p.ciuper && p.ciuper.toLowerCase().includes(q))
            );
        },

        editPropietario(p) {
            this.currentProp = { 
                ...p, 
                nombre_completo: p.nomper + ' ' + (p.apeper || '') 
            };
            this.editing = true;
        },

        deletePropietario(p) {
            this.currentProp = p;
            this.deleting = true;
        },

        closeModal() {
            this.editing = false;
            this.currentProp = {};
        },

        getDocType(id) {
            const types = @json($tiposDoc->pluck('nomval', 'idval'));
            return types[id] || 'N/A';
        },

        numberFormat(num) {
            return new Intl.NumberFormat('es-CO').format(num);
        }
    }
}
</script>

<style>
    .animate-zoom-in { animation: zoomIn 0.3s ease-out; }
    @keyframes zoomIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
    .animate-fade-in { animation: fadeIn 0.6s ease-out forwards; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection
