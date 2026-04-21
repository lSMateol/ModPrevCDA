@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/mup.css') }}">
<script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>

<div class="mup-container" x-data="propietarioManager()" x-cloak>
    <header class="mup-topbar">
        <div class="mup-page-title">
            <h1>MUP - Módulo de Usuarios y Perfiles</h1>
            <p>Gestión de propietarios de vehículos y control operativo vinculado al CDA.</p>
        </div>
        <div class="mup-tabs">
            <a href="{{ route('admin.mup.conductores.index') }}" class="mup-tab">Conductor</a>
            <a href="{{ route('admin.mup.propietarios.index') }}" class="mup-tab active">Propietario</a>
            <a href="{{ route('admin.mup.empresas.index') }}" class="mup-tab">Empresas</a>
            <a href="{{ route('admin.mup.usuarios.index') }}" class="mup-tab">Usuario</a>
        </div>
    </header>

    <div class="mup-content-scroll">
        <div class="space-y-6 pb-12">
            <section class="mup-card">
                <div class="mup-card-header-plain" style="flex-wrap: wrap;">
                    <div>
                        <div class="mup-card-title text-gray-800">Directorio de Propietarios</div>
                        <div class="mup-card-subtitle">Administración de datos personales, licencias y estado operativo.</div>
                    </div>
                    <div class="flex items-center gap-3 flex-wrap w-full md:w-auto">
                        <div class="export-group">
                            <button class="export-btn csv"><iconify-icon icon="lucide:file-text"></iconify-icon> CSV</button>
                            <button class="export-btn excel"><iconify-icon icon="lucide:file-spreadsheet"></iconify-icon> Excel</button>
                            <button class="export-btn pdf"><iconify-icon icon="lucide:file"></iconify-icon> PDF</button>
                        </div>
                        <div class="relative">
                            <input type="text" x-model="search" placeholder="Buscar por nombre, documento o ciudad..." class="pl-10 pr-4 py-2 border rounded-md text-sm w-72 bg-gray-50">
                            <div class="absolute left-3 top-2.5 text-gray-400">
                                <iconify-icon icon="lucide:search"></iconify-icon>
                            </div>
                        </div>
                    <button @click="openCreateDrawer()" class="mup-btn mup-btn-primary h-10">
                        <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon>
                        Nuevo Propietario
                    </button>
                </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 px-6 pb-4">
                    <div class="bg-gray-50 border border-gray-100 rounded-lg px-4 py-3">
                        <div class="text-[11px] text-gray-500 uppercase tracking-wider">Total</div>
                        <div class="text-xl font-bold text-gray-800" x-text="propietarios.length"></div>
                    </div>
                    <div class="bg-green-50 border border-green-100 rounded-lg px-4 py-3">
                        <div class="text-[11px] text-green-700 uppercase tracking-wider">Activos</div>
                        <div class="text-xl font-bold text-green-700" x-text="activeCount()"></div>
                    </div>
                    <div class="bg-blue-50 border border-blue-100 rounded-lg px-4 py-3">
                        <div class="text-[11px] text-blue-700 uppercase tracking-wider">Resultados filtro</div>
                        <div class="text-xl font-bold text-blue-700" x-text="filteredPropietarios().length"></div>
                    </div>
                </div>

                <div class="mup-table-wrap overflow-x-auto">
                    <table class="mup-data-table">
                        <thead>
                            <tr>
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
                                <tr class="hover:bg-blue-50/30 transition-colors border-b border-gray-50 last:border-0 group">
                                    <td class="text-[10px] font-bold text-gray-400" x-text="'P-'+p.idper"></td>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#0d3b5a] to-[#1a4f73] text-white flex items-center justify-center font-black text-[10px] shadow-sm uppercase" 
                                                 x-text="p.nomper[0] + (p.apeper ? p.apeper[0] : '')"></div>
                                            <div>
                                                <div class="font-bold text-gray-800 text-sm leading-tight" x-text="p.nomper + ' ' + (p.apeper || '')"></div>
                                                <div class="text-[10px] text-gray-400" x-text="p.emaper"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-xs text-gray-700 font-bold" x-text="numberFormat(p.ndocper)"></div>
                                        <div class="text-[9px] text-gray-400 uppercase font-black" x-text="getDocType(p.tdocper)"></div>
                                    </td>
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="text-[11px] font-bold text-[#0d3b5a]" x-text="p.ciuper || 'N/A'"></span>
                                            <span class="text-[10px] text-gray-400 truncate max-w-[150px]" x-text="p.dirper || 'Sin dirección'"></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span :class="p.actper ? 'mup-badge-active' : 'mup-badge-inactive'">
                                            <div class="w-1 h-1 rounded-full bg-current"></div>
                                            <span x-text="p.actper ? 'Activo' : 'Inactivo'"></span>
                                        </span>
                                    </td>
                                    <td class="text-right px-6">
                                        <div class="flex justify-end gap-1">
                                            <button @click="viewDetail(p)" class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition" title="Ver Detalle">
                                                <iconify-icon icon="lucide:eye"></iconify-icon>
                                            </button>
                                            <button @click="editPropietario(p)" class="p-2 text-amber-500 hover:bg-amber-50 rounded-lg transition" title="Editar">
                                                <iconify-icon icon="lucide:pencil"></iconify-icon>
                                            </button>
                                            <button @click="confirmDelete(p)" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                                                <iconify-icon icon="lucide:trash-2"></iconify-icon>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="filteredPropietarios().length === 0">
                                <td colspan="6" class="text-center py-10">
                                    <div class="flex flex-col items-center gap-2 text-gray-500">
                                        <iconify-icon icon="lucide:search-x" class="text-2xl"></iconify-icon>
                                        <span class="text-sm font-medium">No hay propietarios para la búsqueda aplicada.</span>
                                        <span class="text-xs">Ajusta los filtros o registra un nuevo propietario.</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="px-6 py-4 border-t flex justify-between items-center text-xs text-gray-400">
                    <div x-text="filteredPropietarios().length + ' propietario(s) registrado(s)'"></div>
                    <div>Última actualización: {{ date('d/m/Y H:i') }}</div>
                </div>
            </section>
        </div>
    </div>

    {{-- DRAWER: Crear Propietario --}}
    <div x-show="createDrawer" class="mup-drawer-overlay" x-cloak
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="mup-drawer" @click.away="createDrawer = false">
            <form action="{{ route('admin.mup.propietarios.store') }}" method="POST" class="flex flex-col h-full">
                @csrf
                <div class="mup-drawer-header">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#0d3b5a] text-white flex items-center justify-center">
                            <iconify-icon icon="lucide:user-plus" class="text-xl"></iconify-icon>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-[15px]">Nuevo Propietario</h3>
                            <p class="text-xs text-gray-400">Registra un nuevo propietario en el sistema.</p>
                        </div>
                    </div>
                    <button type="button" @click="createDrawer = false" class="text-gray-400 hover:text-red-500 transition">
                        <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
                    </button>
                </div>

                <div class="mup-drawer-body">
                    <div class="text-[11px] font-bold text-[#0d3b5a] mb-4 uppercase tracking-widest flex items-center gap-2">
                        <iconify-icon icon="lucide:id-card" class="text-sm"></iconify-icon>
                        Información Identitaria
                    </div>
                    <div class="space-y-4 mb-8">
                        <div class="mup-form-group">
                            <label class="mup-label">Nombre Completo <span class="mup-required">*</span></label>
                            <input type="text" name="nombre_completo" class="mup-input" placeholder="Ej. Juan Manuel Galán" required>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
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
                                <input type="number" name="ndocper" class="mup-input" placeholder="12345678" required>
                            </div>
                        </div>
                    </div>

                    <div class="text-[11px] font-bold text-[#0d3b5a] mb-4 uppercase tracking-widest flex items-center gap-2">
                        <iconify-icon icon="lucide:map-pin" class="text-sm"></iconify-icon>
                        Localización y Contacto
                    </div>
                    <div class="space-y-4 mb-8">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="mup-form-group">
                                <label class="mup-label">Ciudad</label>
                                <input type="text" name="ciuper" class="mup-input" placeholder="Cali">
                            </div>
                            <div class="mup-form-group">
                                <label class="mup-label">Teléfono</label>
                                <input type="text" name="telper" class="mup-input" placeholder="3001234567">
                            </div>
                        </div>
                        <div class="mup-form-group">
                            <label class="mup-label">Dirección</label>
                            <input type="text" name="dirper" class="mup-input" placeholder="Calle 10 # 5-20">
                        </div>
                        <div class="mup-form-group">
                            <label class="mup-label">E-mail <span class="mup-required">*</span></label>
                            <input type="email" name="emaper" class="mup-input" placeholder="propietario@email.com" required>
                        </div>
                    </div>

                    <div class="text-[11px] font-bold text-[#0d3b5a] mb-4 uppercase tracking-widest flex items-center gap-2">
                        <iconify-icon icon="lucide:award" class="text-sm"></iconify-icon>
                        Datos de Licencia y Estado
                    </div>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
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
                                <input type="text" name="nliccon" class="mup-input" placeholder="NRO-123" required>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
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
                    </div>
                </div>

                <div class="mup-drawer-footer">
                    <button type="button" @click="createDrawer = false" class="mup-btn mup-btn-outline">Cancelar</button>
                    <button type="submit" class="mup-btn mup-btn-primary">
                        <iconify-icon icon="lucide:save"></iconify-icon>
                        Guardar Propietario
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- DRAWER: Editar Propietario --}}
    <div x-show="editDrawer" class="mup-drawer-overlay" x-cloak
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="mup-drawer" @click.away="editDrawer = false">
            <form :action="'{{ url('admin/entidades/mup/propietarios') }}/' + currentProp.idper" method="POST" class="flex flex-col h-full">
                @csrf
                @method('PUT')
                <div class="mup-drawer-header">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center">
                            <iconify-icon icon="lucide:pencil" class="text-xl"></iconify-icon>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-[15px]" x-text="'Editar: ' + currentProp.nomper"></h3>
                            <p class="text-xs text-gray-400">Actualiza los datos del propietario.</p>
                        </div>
                    </div>
                    <button type="button" @click="editDrawer = false" class="text-gray-400 hover:text-red-500 transition">
                        <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
                    </button>
                </div>

                <div class="mup-drawer-body">
                    <div class="text-[11px] font-bold text-[#0d3b5a] mb-4 uppercase tracking-widest flex items-center gap-2">
                        <iconify-icon icon="lucide:id-card" class="text-sm"></iconify-icon>
                        Información Identitaria
                    </div>
                    <div class="space-y-4 mb-8">
                        <div class="mup-form-group">
                            <label class="mup-label">Nombre Completo <span class="mup-required">*</span></label>
                            <input type="text" name="nombre_completo" x-model="currentProp.nombre_completo" class="mup-input" required>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="mup-form-group">
                                <label class="mup-label">Tipo Documento <span class="mup-required">*</span></label>
                                <select name="tdocper" x-model="currentProp.tdocper" class="mup-input" required>
                                    @foreach($tiposDoc as $tipo)
                                        <option value="{{ $tipo->idval }}">{{ $tipo->nomval }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mup-form-group">
                                <label class="mup-label">No. Documento <span class="mup-required">*</span></label>
                                <input type="number" name="ndocper" x-model="currentProp.ndocper" class="mup-input" required>
                            </div>
                        </div>
                    </div>

                    <div class="text-[11px] font-bold text-[#0d3b5a] mb-4 uppercase tracking-widest flex items-center gap-2">
                        <iconify-icon icon="lucide:map-pin" class="text-sm"></iconify-icon>
                        Localización y Contacto
                    </div>
                    <div class="space-y-4 mb-8">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="mup-form-group">
                                <label class="mup-label">Ciudad</label>
                                <input type="text" name="ciuper" x-model="currentProp.ciuper" class="mup-input">
                            </div>
                            <div class="mup-form-group">
                                <label class="mup-label">Teléfono</label>
                                <input type="text" name="telper" x-model="currentProp.telper" class="mup-input">
                            </div>
                        </div>
                        <div class="mup-form-group">
                            <label class="mup-label">Dirección</label>
                            <input type="text" name="dirper" x-model="currentProp.dirper" class="mup-input">
                        </div>
                        <div class="mup-form-group">
                            <label class="mup-label">E-mail <span class="mup-required">*</span></label>
                            <input type="email" name="emaper" x-model="currentProp.emaper" class="mup-input" required>
                        </div>
                    </div>

                    <div class="text-[11px] font-bold text-[#0d3b5a] mb-4 uppercase tracking-widest flex items-center gap-2">
                        <iconify-icon icon="lucide:award" class="text-sm"></iconify-icon>
                        Datos de Licencia y Estado
                    </div>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="mup-form-group">
                                <label class="mup-label">Cat. Licencia <span class="mup-required">*</span></label>
                                <select name="catcon" x-model="currentProp.catcon" class="mup-input" required>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->idval }}">{{ $cat->nomval }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mup-form-group">
                                <label class="mup-label">No. Licencia <span class="mup-required">*</span></label>
                                <input type="text" name="nliccon" x-model="currentProp.nliccon" class="mup-input" required>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="mup-form-group">
                                <label class="mup-label">Vencimiento <span class="mup-required">*</span></label>
                                <input type="date" name="fvencon" x-model="currentProp.fvencon" class="mup-input" required>
                            </div>
                            <div class="mup-form-group">
                                <label class="mup-label">Estado</label>
                                <select name="actper" x-model="currentProp.actper" class="mup-input" required>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mup-drawer-footer">
                    <button type="button" @click="editDrawer = false" class="mup-btn mup-btn-outline">Cancelar</button>
                    <button type="submit" class="mup-btn mup-btn-primary">
                        <iconify-icon icon="lucide:save"></iconify-icon>
                        Actualizar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: Ver Detalle --}}
    <div x-show="detailModal" class="mup-modal-overlay" x-cloak>
        <div class="mup-modal" @click.away="detailModal = false">
            <div class="p-6">
                <div class="flex justify-between items-start mb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-[#0d3b5a] text-white flex items-center justify-center text-xl font-black shadow-lg"
                             x-text="currentProp.nomper ? currentProp.nomper[0] + (currentProp.apeper ? currentProp.apeper[0] : '') : ''"></div>
                        <div>
                            <h2 class="text-xl font-black text-gray-800" x-text="currentProp.nomper + ' ' + (currentProp.apeper || '')"></h2>
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest" x-text="'ID: P-' + currentProp.idper"></p>
                        </div>
                    </div>
                    <button @click="detailModal = false" class="text-gray-400 hover:text-red-500 transition">
                        <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-6 bg-gray-50 rounded-2xl p-6 border border-gray-100">
                    <div>
                        <div class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Identificación</div>
                        <div class="text-sm font-bold text-gray-800" x-text="getDocType(currentProp.tdocper) + ': ' + numberFormat(currentProp.ndocper)"></div>
                    </div>
                    <div>
                        <div class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">E-mail</div>
                        <div class="text-sm font-bold text-gray-800" x-text="currentProp.emaper"></div>
                    </div>
                    <div>
                        <div class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Teléfono</div>
                        <div class="text-sm font-bold text-gray-800" x-text="currentProp.telper || 'N/A'"></div>
                    </div>
                    <div>
                        <div class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Ciudad</div>
                        <div class="text-sm font-bold text-gray-800" x-text="currentProp.ciuper || 'N/A'"></div>
                    </div>
                    <div class="col-span-2">
                        <div class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Dirección</div>
                        <div class="text-sm font-bold text-gray-800" x-text="currentProp.dirper || 'No registrada'"></div>
                    </div>
                </div>

                <div class="mt-6 p-4 border border-blue-100 bg-blue-50/30 rounded-2xl flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-500 text-white flex items-center justify-center shadow-sm">
                        <iconify-icon icon="lucide:award" class="text-xl"></iconify-icon>
                    </div>
                    <div class="flex-1">
                        <div class="text-[10px] text-blue-600 font-black uppercase tracking-widest">Licencia de Conducción</div>
                        <div class="text-sm font-bold text-[#0d3b5a]" x-text="'Cat. ' + getCatName(currentProp.catcon) + ' - #' + currentProp.nliccon"></div>
                    </div>
                    <div class="text-right">
                        <div class="text-[9px] text-gray-400 font-bold">Vencimiento</div>
                        <div class="text-[11px] font-black text-gray-700" x-text="currentProp.fvencon"></div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button @click="detailModal = false" class="mup-btn mup-btn-primary px-8">Entendido</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: Eliminar --}}
    <div x-show="deleteModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white p-8 rounded-3xl shadow-2xl max-w-sm w-full text-center" @click.away="deleteModal = false">
            <div class="w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6">
                <iconify-icon icon="lucide:trash-2" style="font-size: 40px;"></iconify-icon>
            </div>
            <h3 class="text-xl font-black text-gray-800 mb-2">¿Eliminar Propietario?</h3>
            <p class="text-sm text-gray-400 mb-8 leading-relaxed">
                Esta acción borrará la ficha de <span class="text-gray-900 font-bold" x-text="currentProp.nomper"></span> permanentemente.
            </p>
            <div class="flex gap-3">
                <button @click="deleteModal = false" class="flex-1 py-3 text-gray-500 font-bold hover:bg-gray-50 rounded-xl transition-all">Cancelar</button>
                <form :action="'{{ url('admin/entidades/mup/propietarios') }}/' + currentProp.idper" method="POST" class="flex-1">
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
        createDrawer: false,
        editDrawer: false,
        detailModal: false,
        deleteModal: false,
        currentProp: {},
        propietarios: @json($propietarios),
        tiposDoc: @json($tiposDoc->pluck('nomval', 'idval')),
        categorias: @json($categorias->pluck('nomval', 'idval')),

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

        openCreateDrawer() {
            this.createDrawer = true;
        },

        viewDetail(p) {
            this.currentProp = p;
            this.detailModal = true;
        },

        editPropietario(p) {
            this.currentProp = { 
                ...p, 
                nombre_completo: p.nomper + ' ' + (p.apeper || '') 
            };
            this.editDrawer = true;
        },

        confirmDelete(p) {
            this.currentProp = p;
            this.deleteModal = true;
        },

        getDocType(id) {
            return this.tiposDoc[id] || 'N/A';
        },

        getCatName(id) {
            return this.categorias[id] || 'N/A';
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
