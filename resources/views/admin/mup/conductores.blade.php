@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/mup.css') }}">
<script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>

<div class="mup-container" x-data="conductorManager()">
    <header class="mup-topbar">
        <div class="mup-page-title">
            <h1 class="flex items-center gap-3">
                <div class="p-2 bg-[#0d3b5a] rounded-lg shadow-lg shadow-[#0d3b5a]/20">
                    <iconify-icon icon="lucide:user-round" class="text-white text-xl"></iconify-icon>
                </div>
                <span class="text-[#0d3b5a] font-black tracking-tight">Gestión de Conductores</span>
            </h1>
            <p>Administra la base de datos de conductores, licencias y estados operativos del CDA.</p>
        </div>
        <div class="mup-tabs">
            <a href="{{ route('admin.mup.conductores') }}" class="mup-tab active">
                <iconify-icon icon="lucide:contact"></iconify-icon>
                Conductor
            </a>
            <a href="{{ route('admin.mup.propietarios') }}" class="mup-tab">
                <iconify-icon icon="lucide:user-cog"></iconify-icon>
                Propietario
            </a>
            <a href="{{ route('admin.mup.empresas') }}" class="mup-tab">
                <iconify-icon icon="lucide:building"></iconify-icon>
                Empresas
            </a>
            <a href="{{ route('admin.mup.usuarios') }}" class="mup-tab">
                <iconify-icon icon="lucide:users-round"></iconify-icon>
                Usuario
            </a>
        </div>
    </header>

    <div class="mup-content-scroll">
        <div class="space-y-6 pb-12">
            
            {{-- SECCIÓN: Listado de Conductores --}}
            <section class="mup-card animate-fade-in shadow-xl">
                <div class="mup-card-header-plain flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <div class="mup-card-title text-gray-800">Listado Maestro de Conductores</div>
                        <div class="mup-card-subtitle">Consulta, edita y exporta el listado de conductores registrados.</div>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                        <div class="flex border rounded-xl p-1 bg-gray-50/50 shadow-sm overflow-hidden border-gray-200">
                            <button class="px-4 py-1.5 text-[10px] font-black text-blue-600 hover:bg-white hover:text-blue-800 transition rounded-lg">CSV</button>
                            <button class="px-4 py-1.5 text-[10px] font-black text-green-600 hover:bg-white hover:text-green-800 transition rounded-lg mx-1">EXCEL</button>
                            <button class="px-4 py-1.5 text-[10px] font-black text-red-600 hover:bg-white hover:text-red-800 transition rounded-lg">PDF</button>
                        </div>
                        <div class="relative flex-1 md:flex-none">
                            <input type="text" x-model="search" placeholder="Buscar por nombre, documento o licencia..." 
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
                                <th>Conductor</th>
                                <th>Identificación</th>
                                <th>Licencia / Vencimiento</th>
                                <th class="text-center">Estado</th>
                                <th class="text-right px-6">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="con in filteredConductores()" :key="con.idper">
                                <tr class="hover:bg-[#0d3b5a]/[0.02] transition-colors border-b border-gray-50 last:border-0 group">
                                    <td class="text-xs font-bold text-gray-400" x-text="'C-'+con.idper"></td>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-[#0d3b5a]/10 flex items-center justify-center text-[#0d3b5a] font-black text-xs" 
                                                 x-text="con.nomper[0] + (con.apeper ? con.apeper[0] : '')"></div>
                                            <div>
                                                <div class="font-bold text-gray-800" x-text="con.nomper + ' ' + (con.apeper || '')"></div>
                                                <div class="text-[11px] text-gray-400" x-text="con.emaper"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-sm text-gray-700 font-medium" x-text="con.ndocper"></div>
                                        <div class="text-[10px] text-gray-400 uppercase tracking-tighter" x-text="'Tipo: ' + getDocType(con.tdocper)"></div>
                                    </td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-[#0d3b5a]" x-text="con.nliccon || 'N/A'"></span>
                                            <span class="text-[10px] text-gray-500 flex items-center gap-1">
                                                <iconify-icon icon="lucide:calendar-clock" class="text-gray-400"></iconify-icon>
                                                <span x-text="formatDate(con.fvencon)"></span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span :class="con.actper ? 'mup-state-active' : 'mup-state-inactive'" class="inline-flex items-center shadow-sm">
                                            <div class="w-1.5 h-1.5 rounded-full bg-current mr-1.5"></div>
                                            <span x-text="con.actper ? 'Activo' : 'Inactivo'"></span>
                                        </span>
                                    </td>
                                    <td class="text-right px-6">
                                        <div class="flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="editConductor(con)" class="p-2.5 text-blue-600 hover:bg-blue-50 rounded-xl transition-all" title="Editar Conductor">
                                                <iconify-icon icon="lucide:pencil"></iconify-icon>
                                            </button>
                                            <button @click="deleteConductor(con)" class="p-2.5 text-red-600 hover:bg-red-50 rounded-xl transition-all" title="Eliminar">
                                                <iconify-icon icon="lucide:trash-2"></iconify-icon>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="filteredConductores().length === 0">
                                <td colspan="6" class="text-center py-20">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <iconify-icon icon="lucide:user-search" class="text-5xl mb-3 opacity-20"></iconify-icon>
                                        <p class="text-sm font-medium">No se encontraron conductores con ese criterio.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="mup-management-row">
                {{-- FORMULARIO: Nuevo Conductor --}}
                <section class="mup-card shadow-xl border-t-4 border-[#0d3b5a]">
                    <div class="mup-card-header-soft pb-6">
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-[#0d3b5a]/10 rounded-xl">
                                <iconify-icon icon="lucide:user-plus" class="text-2xl text-[#0d3b5a]"></iconify-icon>
                            </div>
                            <div>
                                <div class="mup-card-title text-xl tracking-tight">Registro de Conductor</div>
                                <div class="mup-card-subtitle">Ingresa la información básica y datos de licencia.</div>
                            </div>
                        </div>
                    </div>
                    <div class="mup-card-body">
                        <form action="{{ route('admin.mup.conductores.store') }}" method="POST" id="createForm">
                            @csrf
                            <div class="text-[11px] font-black text-[#0d3b5a]/40 mb-6 uppercase tracking-[0.2em] flex items-center gap-3">
                                <span>Información Identitaria</span>
                                <div class="flex-1 h-px bg-gray-100"></div>
                            </div>
                            
                            <div class="mup-form-grid">
                                <div class="mup-form-group span-2">
                                    <label class="mup-label">Nombre Completo <span class="mup-required">*</span></label>
                                    <input type="text" name="nombre_completo" class="mup-input" placeholder="Ej. Carlos Alberto Soto" required>
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
                                    <label class="mup-label">Identificación <span class="mup-required">*</span></label>
                                    <input type="number" name="ndocper" class="mup-input" placeholder="Sin puntos ni comas" required>
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Correo Electrónico <span class="mup-required">*</span></label>
                                    <input type="email" name="emaper" class="mup-input" placeholder="ejemplo@email.com" required>
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Celular / Teléfono</label>
                                    <input type="text" name="telper" class="mup-input" placeholder="300 000 0000">
                                </div>
                            </div>

                            <div class="text-[11px] font-black text-[#0d3b5a]/40 mt-10 mb-6 uppercase tracking-[0.2em] flex items-center gap-3">
                                <span>Capacidad Operativa</span>
                                <div class="flex-1 h-px bg-gray-100"></div>
                            </div>

                            <div class="mup-form-grid">
                                <div class="mup-form-group">
                                    <label class="mup-label">Categoría Licencia <span class="mup-required">*</span></label>
                                    <select name="catcon" class="mup-input" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($categorias as $cat)
                                            <option value="{{ $cat->idval }}">{{ $cat->nomval }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Nro de Licencia <span class="mup-required">*</span></label>
                                    <input type="text" name="nliccon" class="mup-input" placeholder="Nro de pase" required>
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Vencimiento Licencia <span class="mup-required">*</span></label>
                                    <input type="date" name="fvencon" class="mup-input" required>
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Estado Inicial</label>
                                    <select name="actper" class="mup-input" required>
                                        <option value="1">Disponible / Activo</option>
                                        <option value="0">Fuera de Servicio / Inactivo</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-10 flex justify-end gap-3 pt-6 border-t border-gray-100">
                                <button type="reset" class="mup-btn mup-btn-outline">Limpiar todo</button>
                                <button type="submit" class="mup-btn mup-btn-primary shadow-lg shadow-[#0d3b5a]/20">
                                    <iconify-icon icon="lucide:save" class="text-sm"></iconify-icon>
                                    Registrar Conductor
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                {{-- CARD: Resumen / Info --}}
                <section class="mup-card bg-[#0d3b5a] text-white overflow-hidden relative">
                    <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
                        <iconify-icon icon="lucide:user-check" style="font-size: 240px;"></iconify-icon>
                    </div>
                    <div class="mup-card-body relative z-10 h-full flex flex-col justify-between py-10">
                        <div>
                            <div class="text-3xl font-black mb-4 tracking-tighter">Perfiles Operativos</div>
                            <p class="text-[#89b3d0] text-sm leading-relaxed mb-8">
                                Los conductores registrados aquí son asignados a vehículos para la generación de diagnósticos preventivos y revisiones técnicas según las normas del CDA.
                            </p>
                            
                            <div class="space-y-4">
                                <div class="bg-white/5 border border-white/10 rounded-2xl p-5 backdrop-blur-sm">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center">
                                            <iconify-icon icon="lucide:file-warnings" class="text-xl text-yellow-400"></iconify-icon>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold">Alertas de Vencimiento</div>
                                            <div class="text-[11px] text-[#89b3d0]">El sistema notificará 30 días antes del vencimiento de la licencia.</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white/5 border border-white/10 rounded-2xl p-5 backdrop-blur-sm">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center">
                                            <iconify-icon icon="lucide:shield-check" class="text-xl text-green-400"></iconify-icon>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold">Integridad de Datos</div>
                                            <div class="text-[11px] text-[#89b3d0]">Se recomienda que el correo electrónico sea real para notificaciones legales.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-8 pt-8 border-t border-white/10 text-center">
                            <span class="text-xs font-semibold text-white/40 uppercase tracking-widest">CDA RASTRILLANTAS LTDA</span>
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
                        <iconify-icon icon="lucide:pencil-line" class="text-2xl"></iconify-icon>
                    </div>
                    <div>
                        <h2 class="text-xl font-black tracking-tight" x-text="'Editando: ' + currentCon.nomper"></h2>
                        <p class="text-[#89b3d0] text-[11px] uppercase tracking-widest font-bold">Actualización de ficha operativa</p>
                    </div>
                </div>
                <button @click="closeModal()" class="p-2 hover:bg-white/10 rounded-xl transition-all">
                    <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
                </button>
            </div>

            <form :action="'{{ url('entidades/mup/conductores') }}/' + currentCon.idper" method="POST" class="p-8">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label class="mup-label">Nombre Completo</label>
                        <input type="text" name="nombre_completo" x-model="currentCon.nombre_completo" class="mup-input" required>
                    </div>
                    <div>
                        <label class="mup-label">Identificación</label>
                        <input type="number" name="ndocper" x-model="currentCon.ndocper" class="mup-input" required>
                    </div>
                    <div>
                        <label class="mup-label">Email</label>
                        <input type="email" name="emaper" x-model="currentCon.emaper" class="mup-input" required>
                    </div>
                    <div>
                        <label class="mup-label">Licencia</label>
                        <input type="text" name="nliccon" x-model="currentCon.nliccon" class="mup-input" required>
                    </div>
                    <div>
                        <label class="mup-label">Vencimiento</label>
                        <input type="date" name="fvencon" x-model="currentCon.fvencon" class="mup-input" required>
                    </div>
                    <div>
                        <label class="mup-label">Categoría</label>
                        <select name="catcon" x-model="currentCon.catcon" class="mup-input" required>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->idval }}">{{ $cat->nomval }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mup-label">Estado</label>
                        <select name="actper" x-model="currentCon.actper" class="mup-input" required>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                    <input type="hidden" name="tdocper" x-model="currentCon.tdocper">
                </div>

                <div class="mt-10 flex gap-4">
                    <button type="button" @click="closeModal()" class="flex-1 py-4 text-gray-500 font-bold hover:bg-gray-50 rounded-2xl transition-all">Cancelar</button>
                    <button type="submit" class="flex-1 py-4 bg-[#0d3b5a] text-white font-bold rounded-2xl shadow-xl shadow-[#0d3b5a]/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                        Actualizar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL ELIMINAR --}}
    <div x-show="deleting" class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-[#0d3b5a]/40 backdrop-blur-sm">
        <div class="bg-white p-8 rounded-3xl shadow-2xl max-w-sm w-full text-center animate-zoom-in" @click.away="deleting = false">
            <div class="w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6">
                <iconify-icon icon="lucide:alert-triangle" style="font-size: 40px;"></iconify-icon>
            </div>
            <h3 class="text-xl font-black text-gray-800 mb-2">¿Eliminar Conductor?</h3>
            <p class="text-sm text-gray-400 mb-8 leading-relaxed">
                Esta acción es permanente y eliminará toda la ficha de <span class="text-gray-900 font-bold" x-text="currentCon.nomper"></span>.
            </p>
            
            <div class="flex gap-3">
                <button @click="deleting = false" class="flex-1 py-3 text-gray-500 font-bold hover:bg-gray-50 rounded-xl transition-all">No, volver</button>
                <form :action="'{{ url('entidades/mup/conductores') }}/' + currentCon.idper" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-3 bg-red-600 text-white font-bold rounded-xl shadow-lg shadow-red-200">Sí, eliminar</button>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
function conductorManager() {
    return {
        search: '',
        editing: false,
        deleting: false,
        currentCon: {},
        conductores: @json($conductores),
        
        init() {
            // Pre-procesamiento de datos si es necesario
        },

        filteredConductores() {
            if (!this.search) return this.conductores;
            const q = this.search.toLowerCase();
            return this.conductores.filter(c => 
                c.nomper.toLowerCase().includes(q) || 
                (c.apeper && c.apeper.toLowerCase().includes(q)) ||
                c.ndocper.toString().includes(q) ||
                (c.nliccon && c.nliccon.toLowerCase().includes(q))
            );
        },

        editConductor(con) {
            this.currentCon = { 
                ...con, 
                nombre_completo: con.nomper + ' ' + (con.apeper || '') 
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
            const types = @json($tiposDoc->pluck('nomval', 'idval'));
            return types[id] || 'N/A';
        },

        formatDate(dateStr) {
            if (!dateStr) return 'N/A';
            const date = new Date(dateStr);
            return date.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
        }
    }
}
</script>

<style>
    .animate-zoom-in {
        animation: zoomIn 0.3s ease-out;
    }
    @keyframes zoomIn {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }
    .animate-fade-in {
        animation: fadeIn 0.6s ease-out forwards;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
