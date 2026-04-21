@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/mup.css') }}">
<script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>

<div class="mup-container" x-data="conductorManager()">
    <header class="mup-topbar">
        <div class="mup-page-title">
            <h1>MUP - Módulo de Usuarios y Perfiles</h1>
            <p>Gestión de conductores, licencias y estados operativos del CDA.</p>
        </div>
        <div class="mup-tabs">
            <a href="{{ route('admin.mup.conductores.index') }}" class="mup-tab active">Conductor</a>
            <a href="{{ route('admin.mup.propietarios.index') }}" class="mup-tab">Propietario</a>
            <a href="{{ route('admin.mup.empresas.index') }}" class="mup-tab">Empresas</a>
            <a href="{{ route('admin.mup.usuarios.index') }}" class="mup-tab">Usuario</a>
        </div>
    </header>

    <div class="mup-content-scroll">
        <div class="space-y-6 pb-12">
            
            {{-- SECCIÓN: Listado de Conductores --}}
            <section class="mup-card">
                <div class="mup-card-header-plain" style="flex-wrap: wrap;">
                    <div>
                        <div class="mup-card-title text-gray-800">Listado Maestro de Conductores</div>
                        <div class="mup-card-subtitle">Consulta, edita y exporta el listado de conductores registrados.</div>
                    </div>
                    <div class="flex items-center gap-3 flex-wrap w-full md:w-auto">
                        <div class="export-group">
                            <button class="export-btn csv"><iconify-icon icon="lucide:file-text"></iconify-icon> CSV</button>
                            <button class="export-btn excel"><iconify-icon icon="lucide:file-spreadsheet"></iconify-icon> Excel</button>
                            <button class="export-btn pdf"><iconify-icon icon="lucide:file"></iconify-icon> PDF</button>
                        </div>
                        <div class="relative">
                            <input type="text" x-model="search" placeholder="Buscar por nombre, documento o licencia..." class="pl-10 pr-4 py-2 border rounded-md text-sm w-72 bg-gray-50">
                            <div class="absolute left-3 top-2.5 text-gray-400">
                                <iconify-icon icon="lucide:search"></iconify-icon>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 px-6 pb-4">
                    <div class="bg-gray-50 border border-gray-100 rounded-lg px-4 py-3">
                        <div class="text-[11px] text-gray-500 uppercase tracking-wider">Total</div>
                        <div class="text-xl font-bold text-gray-800" x-text="conductores.length"></div>
                    </div>
                    <div class="bg-green-50 border border-green-100 rounded-lg px-4 py-3">
                        <div class="text-[11px] text-green-700 uppercase tracking-wider">Activos</div>
                        <div class="text-xl font-bold text-green-700" x-text="activeCount()"></div>
                    </div>
                    <div class="bg-blue-50 border border-blue-100 rounded-lg px-4 py-3">
                        <div class="text-[11px] text-blue-700 uppercase tracking-wider">Resultados filtro</div>
                        <div class="text-xl font-bold text-blue-700" x-text="filteredConductores().length"></div>
                    </div>
                </div>
                
                <div class="mup-table-wrap overflow-x-auto">
                    <table class="mup-data-table">
                        <thead>
                            <tr>
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
                                <tr class="hover:bg-blue-50/30 transition-colors border-b border-gray-50 last:border-0 group">
                                    <td class="text-[10px] font-bold text-gray-400" x-text="'C-'+con.idper"></td>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#0d3b5a] to-[#1a4f73] text-white flex items-center justify-center font-black text-[10px] shadow-sm uppercase"
                                                 x-text="con.nomper[0] + (con.apeper ? con.apeper[0] : '')"></div>
                                            <div>
                                                <div class="font-bold text-gray-800 text-sm leading-tight" x-text="con.nomper + ' ' + (con.apeper || '')"></div>
                                                <div class="text-[10px] text-gray-400" x-text="con.emaper"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-xs text-gray-700 font-bold" x-text="numberFormat(con.ndocper)"></div>
                                        <div class="text-[9px] text-gray-400 uppercase font-black" x-text="getDocType(con.tdocper)"></div>
                                    </td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="text-[11px] font-bold text-[#0d3b5a]" x-text="con.nliccon || 'N/A'"></span>
                                            <span class="text-[10px] text-gray-400" x-text="formatDate(con.fvencon)"></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span :class="con.actper ? 'mup-badge-active' : 'mup-badge-inactive'">
                                            <div class="w-1 h-1 rounded-full bg-current"></div>
                                            <span x-text="con.actper ? 'Activo' : 'Inactivo'"></span>
                                        </span>
                                    </td>
                                    <td class="text-right px-6">
                                        <div class="flex justify-end gap-1">
                                            <button @click="editConductor(con)" class="p-2 text-amber-500 hover:bg-amber-50 rounded-lg transition" title="Editar Conductor">
                                                <iconify-icon icon="lucide:pencil"></iconify-icon>
                                            </button>
                                            <button @click="deleteConductor(con)" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                                                <iconify-icon icon="lucide:trash-2"></iconify-icon>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="filteredConductores().length === 0">
                                <td colspan="6" class="text-center py-10">
                                    <div class="flex flex-col items-center gap-2 text-gray-500">
                                        <iconify-icon icon="lucide:search-x" class="text-2xl"></iconify-icon>
                                        <span class="text-sm font-medium">No hay conductores para la búsqueda aplicada.</span>
                                        <span class="text-xs">Ajusta los filtros o registra un nuevo conductor.</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t flex justify-between items-center text-xs text-gray-400">
                    <div x-text="filteredConductores().length + ' conductor(es) registrado(s)'"></div>
                    <div>Última actualización: {{ date('d/m/Y H:i') }}</div>
                </div>
            </section>

            <div>
                <section class="mup-card">
                    <div class="mup-card-header-soft">
                        <div class="mup-card-title">Registro de Conductor</div>
                        <div class="mup-card-subtitle">Ingresa la información básica, contacto y datos de licencia.</div>
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
                                <button type="submit" class="mup-btn mup-btn-primary">
                                    <iconify-icon icon="lucide:save" class="text-sm"></iconify-icon>
                                    Registrar Conductor
                                </button>
                            </div>
                        </form>
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
        
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden" @click.away="closeModal()">
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
        <div class="bg-white p-8 rounded-3xl shadow-2xl max-w-sm w-full text-center" @click.away="deleting = false">
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
        tiposDoc: @json($tiposDoc->pluck('nomval', 'idval')),
        
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
            return this.tiposDoc[id] || 'N/A';
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
        }
    }
}
</script>
@endsection
