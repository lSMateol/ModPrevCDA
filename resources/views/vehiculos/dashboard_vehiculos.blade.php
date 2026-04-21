@extends('layouts.app')

@section('content')
<div class="px-10 pb-20 max-w-full mx-auto"
    x-data="{
        vehiculos: {{ $vehiculos->toJson() }},
        search: '',
        selectedVehiculo: null,
        tab: 'todos',
        empresaFiltro: '',
        claseFiltro: '',
        servicioFiltro: '',
        editMode: false,
        showAll: false,
        maxRows: 5,
        vinculoMode: false,
        vinculoSaving: false,
        vinculoForm: { prop: '', cond: '', idemp: '' },
        personas: {{ $personas->toJson() }},
        allEmpresas: {{ $empresasFiltro->toJson() }},
        allMarcas: {{ $marcas->toJson() }},
        allClases: {{ $clasesFiltro->toJson() }},
        allCombustibles: {{ $combustibles->toJson() }},
        allCargas: {{ $cargas->toJson() }},
        editForm: {},
        editSaving: false,

        get filteredVehiculos() {
            return this.vehiculos.filter(v => {
                const searchTerm = this.search.toLowerCase();
                const matchSearch = !searchTerm ||
                    (v.placaveh && v.placaveh.toLowerCase().includes(searchTerm)) ||
                    (v.nordveh && v.nordveh.toLowerCase().includes(searchTerm));
                const matchEmpresa = this.empresaFiltro === '' || (v.empresa && v.empresa.razsoem === this.empresaFiltro);
                const matchClase = this.claseFiltro === '' || (v.clase && v.clase.nomval === this.claseFiltro);
                const matchServicio = this.servicioFiltro === '' || String(v.tipo_servicio) === this.servicioFiltro;
                return matchSearch && matchEmpresa && matchClase && matchServicio;
            });
        },

        get displayedVehiculos() {
            return this.showAll ? this.filteredVehiculos : this.filteredVehiculos.slice(0, this.maxRows);
        },

        get hasMore() {
            return this.filteredVehiculos.length > this.maxRows;
        },

        servicioLabel(tipo) {
            const map = {1: 'Particular', 2: 'Público'};
            return map[tipo] || 'N/A';
        },
        empresaDisplay(v) {
            if (v.empresa && v.empresa.razsoem) return v.empresa.razsoem;
            if (v.tipo_servicio === 1) return 'Particular — sin empresa';
            return 'Sin empresa asignada';
        },
        polizaLabel(tipo) {
            const map = {1: 'Todo Riesgo', 2: 'Terceros'};
            return map[tipo] || 'N/A';
        },
        blindajeLabel(tipo) {
            const map = {1: 'S\u00ed', 2: 'No'};
            return map[tipo] || 'N/A';
        },

        toggleEdit() {
            this.editForm = {
                linveh: this.selectedVehiculo.linveh || '',
                clveh: this.selectedVehiculo.clveh || '',
                tipo_servicio: this.selectedVehiculo.tipo_servicio || '',
                idemp: this.selectedVehiculo.idemp || '',
                combuveh: this.selectedVehiculo.combuveh || '',
                crgveh: this.selectedVehiculo.crgveh || '',
                blinveh: this.selectedVehiculo.blinveh || '',
                polaveh: this.selectedVehiculo.polaveh || '',
                prop: this.selectedVehiculo.prop || '',
                cond: this.selectedVehiculo.cond || '',
            };
            this.editMode = true;
        },
        cancelEdit() {
            this.editMode = false;
            const original = this.vehiculos.find(v => v.idveh === this.selectedVehiculo.idveh);
            if (original) this.selectedVehiculo = JSON.parse(JSON.stringify(original));
        },

        async saveInlineEdit() {
            this.editSaving = true;
            try {
                const prefix = document.querySelector('meta[name=url-prefix]')?.content || '';
                const url = '/' + prefix + '/vehiculos/' + this.selectedVehiculo.idveh + '/edicion-rapida';
                const res = await fetch(url, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.editForm)
                });
                const data = await res.json();
                if (data.success) {
                    const idx = this.vehiculos.findIndex(v => v.idveh === this.selectedVehiculo.idveh);
                    if (idx !== -1) this.vehiculos[idx] = data.vehiculo;
                    this.selectedVehiculo = data.vehiculo;
                    this.editMode = false;
                } else {
                    alert('Error: ' + (data.message || 'Verifique los datos'));
                }
            } catch (e) { console.error('Error:', e); alert('Error de red al guardar'); }
            finally { this.editSaving = false; }
        },

        init() {
            if (this.vehiculos.length > 0) {
                this.selectedVehiculo = this.vehiculos[0];
            }
        },

        openVinculoEdit() {
            this.vinculoForm.prop = this.selectedVehiculo.prop || '';
            this.vinculoForm.cond = this.selectedVehiculo.cond || '';
            this.vinculoForm.idemp = this.selectedVehiculo.idemp || '';
            this.vinculoMode = true;
        },

        cancelVinculo() {
            this.vinculoMode = false;
        },

        async saveVinculos() {
            this.vinculoSaving = true;
            try {
                const prefix = document.querySelector('meta[name=url-prefix]')?.content || '';
                const url = '/' + prefix + '/vehiculos/' + this.selectedVehiculo.idveh + '/vinculos';
                const res = await fetch(url, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        prop: this.vinculoForm.prop || null,
                        cond: this.vinculoForm.cond || null,
                        idemp: this.vinculoForm.idemp || null,
                    })
                });
                const data = await res.json();
                if (data.success) {
                    // Actualizar vehiculo en la lista local
                    const idx = this.vehiculos.findIndex(v => v.idveh === this.selectedVehiculo.idveh);
                    if (idx !== -1) this.vehiculos[idx] = data.vehiculo;
                    this.selectedVehiculo = data.vehiculo;
                    this.vinculoMode = false;
                }
            } catch (e) {
                console.error('Error guardando v\u00ednculos:', e);
            } finally {
                this.vinculoSaving = false;
            }
        }
    }">

    <style>
        .vehiculo-module { font-family: 'Inter', system-ui, -apple-system, sans-serif; color: #0b2540; }
        .vehiculo-module .vbtn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 16px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; transition: 0.2s; border: 1px solid transparent; white-space: nowrap; }
        .vehiculo-module .vbtn-primary { background-color: #0b3a5a; color: #ffffff; }
        .vehiculo-module .vbtn-primary:hover { background-color: #082d46; }
        .vehiculo-module .vbtn-secondary { background-color: #ffffff; border-color: #d1d5db; color: #374151; }
        .vehiculo-module .vbtn-secondary:hover { background-color: #f9fafb; }
        .vehiculo-module .vbtn-outline { background-color: transparent; border-color: #d1d5db; color: #374151; }
        .vehiculo-module .vbtn-outline:hover { background-color: #f3f4f6; }
        .vehiculo-module .vbtn-outline-primary { background-color: transparent; border-color: #0b3a5a; color: #0b3a5a; }
        .vehiculo-module .vbtn-outline-primary:hover { background-color: #eff6ff; }
        .vehiculo-module .vbtn-edit { background-color: #f59e0b; color: #ffffff; }
        .vehiculo-module .vbtn-edit:hover { background-color: #d97706; }
        .vehiculo-module .vbtn-danger { background-color: #ef4444; color: #ffffff; }
        .vehiculo-module .vbtn-danger:hover { background-color: #dc2626; }
        .vehiculo-module .vcard { background: #ffffff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.025); border: 1px solid rgba(0,0,0,0.08); overflow: hidden; }
        .vehiculo-module .page-title { font-size: 24px; font-weight: 700; margin: 0 0 6px 0; }
        .vehiculo-module .page-subtitle { font-size: 14px; color: #6b7280; margin: 0; }

        /* Tabla */
        .vehiculo-module .data-table { width: 100%; border-collapse: collapse; text-align: left; }
        .vehiculo-module .data-table th { padding: 14px 24px; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; background-color: #f9fafb; border-bottom: 1px solid rgba(0,0,0,0.08); white-space: nowrap; }
        .vehiculo-module .data-table td { padding: 16px 24px; font-size: 14px; color: #374151; border-bottom: 1px solid rgba(0,0,0,0.08); vertical-align: middle; white-space: nowrap; }
        .vehiculo-module .data-table tbody tr { cursor: pointer; transition: background-color 0.2s; }
        .vehiculo-module .data-table tbody tr:hover { background-color: #f9fafb; }
        .vehiculo-module .data-table tbody tr.active-row { background-color: #eff6ff; }
        .vehiculo-module .plate-badge { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 4px 8px; border-radius: 4px; font-weight: 700; font-family: monospace; font-size: 14px; display: inline-block; }
        .vehiculo-module .status-badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .vehiculo-module .status-badge.success { background-color: #dcfce7; color: #15803d; }
        .vehiculo-module .status-badge.info { background-color: #dbeafe; color: #1d4ed8; }
        .vehiculo-module .icon-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; border: none; background: transparent; color: #6b7280; cursor: pointer; transition: 0.2s; }
        .vehiculo-module .icon-btn:hover { background: #e5e7eb; color: #111827; }

        /* Sección de detalle */
        .vehiculo-module .details-section { display: grid; grid-template-columns: 2.5fr 1fr; gap: 24px; align-items: start; }

        /* Tabs */
        .vehiculo-module .vtabs { display: flex; gap: 24px; border-bottom: 1px solid rgba(0,0,0,0.08); padding: 0 24px; }
        .vehiculo-module .vtab { padding: 12px 0; background: none; border: none; font-size: 14px; font-weight: 500; color: #6b7280; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -1px; transition: 0.2s; }
        .vehiculo-module .vtab:hover { color: #111827; }
        .vehiculo-module .vtab.active { color: #0b3a5a; border-bottom-color: #0b3a5a; font-weight: 600; }

        /* Formulario */
        .vehiculo-module .form-scroll-area { max-height: 520px; overflow-y: auto; background-color: #f9fafb; }
        .vehiculo-module .form-scroll-area::-webkit-scrollbar { width: 6px; }
        .vehiculo-module .form-scroll-area::-webkit-scrollbar-track { background: transparent; }
        .vehiculo-module .form-scroll-area::-webkit-scrollbar-thumb { background-color: #d1d5db; border-radius: 10px; }
        .vehiculo-module .form-section { padding: 24px; background: #ffffff; border-bottom: 1px solid rgba(0,0,0,0.08); }
        .vehiculo-module .form-section:last-child { border-bottom: none; }
        .vehiculo-module .form-section-title { margin: 0 0 20px 0; font-size: 15px; font-weight: 600; color: #111827; display: flex; align-items: center; gap: 8px; }
        .vehiculo-module .form-section-title i { color: #6b7280; font-size: 14px; }
        .vehiculo-module .form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px 20px; }
        .vehiculo-module .form-group { display: flex; flex-direction: column; gap: 6px; }
        .vehiculo-module .form-group label { font-size: 13px; font-weight: 500; color: #4b5563; }
        .vehiculo-module .form-group input,
        .vehiculo-module .form-group select { height: 40px; border: 1px solid #d1d5db; border-radius: 6px; padding: 0 12px; font-size: 14px; outline: none; background: #ffffff; width: 100%; color: #111827; }
        .vehiculo-module .form-group input:read-only { background-color: #f3f4f6; color: #6b7280; cursor: default; }
        .vehiculo-module .form-group input.editable { background-color: #ffffff; color: #111827; border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1); }
        .vehiculo-module .form-group input:focus:not(:read-only) { border-color: #0b3a5a; box-shadow: 0 0 0 2px rgba(11, 58, 90, 0.1); }
        .vehiculo-module .na-text { color: #9ca3af; font-style: italic; }

        /* Relaciones */
        .vehiculo-module .relation-list { display: flex; flex-direction: column; padding: 24px; }
        .vehiculo-module .relation-item { display: flex; align-items: center; gap: 16px; padding: 14px; border: 1px solid #e5e7eb; border-radius: 8px; background: #f9fafb; }
        .vehiculo-module .relation-item.highlighted { border-color: #93c5fd; background-color: #eff6ff; }
        .vehiculo-module .relation-icon { width: 44px; height: 44px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; background-color: #ffffff; border: 1px solid #e5e7eb; color: #4b5563; }
        .vehiculo-module .relation-item.highlighted .relation-icon { background-color: #dbeafe; color: #1d4ed8; border: none; }
        .vehiculo-module .relation-connector { width: 2px; height: 20px; background-color: #e5e7eb; margin-left: 35px; }
        .vehiculo-module .relation-info { display: flex; flex-direction: column; gap: 2px; }
        .vehiculo-module .relation-label { font-size: 12px; color: #6b7280; font-weight: 500; }
        .vehiculo-module .relation-value { font-size: 14px; color: #111827; font-weight: 600; }

        /* Edit mode indicator */
        .vehiculo-module .edit-banner { background: linear-gradient(135deg, #fef3c7, #fde68a); border: 1px solid #f59e0b; border-radius: 8px; padding: 10px 16px; display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 500; color: #92400e; margin-bottom: 16px; }
        .vehiculo-module .edit-banner i { font-size: 16px; }

        /* Show more */
        .vehiculo-module .show-more-bar { padding: 12px 24px; text-align: center; border-top: 1px solid rgba(0,0,0,0.05); background: #f9fafb; }
        .vehiculo-module .show-more-bar button { background: none; border: none; color: #0b3a5a; font-weight: 600; font-size: 13px; cursor: pointer; padding: 6px 16px; border-radius: 6px; transition: 0.2s; }
        .vehiculo-module .show-more-bar button:hover { background: #eff6ff; }

        @media (max-width: 1024px) {
            .vehiculo-module .details-section { grid-template-columns: 1fr; }
            .vehiculo-module .form-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>

    <div class="vehiculo-module">
        <!-- Page Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;">
            <div>
                <h1 class="page-title">Vehículos</h1>
                <p class="page-subtitle">Gestión de activos vehiculares del CDA</p>
            </div>
            @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Digitador'))
            @php $prefix = auth()->user()->hasRole('Administrador') ? 'admin' : 'digitador'; @endphp
            <a href="{{ route($prefix . '.vehiculos.create') }}" class="vbtn vbtn-primary" style="text-decoration: none;">
                <i class="fa-solid fa-plus"></i> Nuevo vehículo
            </a>
            @endif
        </div>

        <!-- Filters Bar -->
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 24px; flex-wrap: wrap;">
            <div style="position: relative; flex: 1; max-width: 360px; min-width: 200px;">
                <i class="fa-solid fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6b7280; font-size: 14px;"></i>
                <input x-model="search" type="text" placeholder="Buscar por placa, no. interno..." style="width: 100%; height: 40px; padding-left: 38px; padding-right: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; outline: none;" />
            </div>
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                {{-- Filtro Empresa (Admin/Digitador ven todas, Empresa solo la suya) --}}
                @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Digitador'))
                <select x-model="empresaFiltro" style="height: 40px; border: 1px solid #d1d5db; border-radius: 8px; padding: 0 32px 0 12px; font-size: 14px; background-color: #ffffff; outline: none;">
                    <option value="">Todas las empresas</option>
                    @foreach($empresasFiltro as $emp)
                        <option value="{{ $emp->razsoem }}">{{ $emp->razsoem }}</option>
                    @endforeach
                </select>
                @endif

                {{-- Filtro Tipo de Servicio --}}
                <select x-model="servicioFiltro" style="height: 40px; border: 1px solid #d1d5db; border-radius: 8px; padding: 0 32px 0 12px; font-size: 14px; background-color: #ffffff; outline: none;">
                    <option value="">Tipo de servicio</option>
                    <option value="1">Particular</option>
                    <option value="2">Público</option>
                </select>

                {{-- Filtro Clase de Vehículo --}}
                @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Digitador'))
                <select x-model="claseFiltro" style="height: 40px; border: 1px solid #d1d5db; border-radius: 8px; padding: 0 32px 0 12px; font-size: 14px; background-color: #ffffff; outline: none;">
                    <option value="">Clase de vehículo</option>
                    @foreach($clasesFiltro as $clase)
                        <option value="{{ $clase->nomval }}">{{ $clase->nomval }}</option>
                    @endforeach
                </select>
                @endif

                {{-- Botón Limpiar filtros --}}
                <button class="vbtn vbtn-outline" @click="search=''; empresaFiltro=''; servicioFiltro=''; claseFiltro=''">
                    <i class="fa-solid fa-rotate-left"></i> Limpiar
                </button>
            </div>
        </div>

        <!-- Results count -->
        <div style="margin-bottom: 12px; font-size: 13px; color: #6b7280;">
            Mostrando <span style="font-weight: 600; color: #111827;" x-text="displayedVehiculos.length"></span> de <span style="font-weight: 600; color: #111827;" x-text="filteredVehiculos.length"></span> vehículos
            <span x-show="!showAll && hasMore" style="color: #0b3a5a;"> (limitado a <span x-text="maxRows"></span>)</span>
        </div>

        <!-- Data Table -->
        <div class="vcard" style="overflow-x: auto; margin-bottom: 24px;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No. Interno</th>
                        <th>Placa</th>
                        <th>Línea</th>
                        <th>Clase</th>
                        <th>Servicio</th>
                        <th>Empresa</th>
                        <th>Combustible</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="vehiculo in displayedVehiculos" :key="vehiculo.idveh">
                        <tr @click="selectedVehiculo = vehiculo; editMode = false; tab = 'todos'; setTimeout(() => { const el = document.getElementById('vehiculo-detalles'); if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 50);" :class="{'active-row': selectedVehiculo && selectedVehiculo.idveh === vehiculo.idveh}">
                            <td><span style="font-weight: 500; color: #111827;" x-text="vehiculo.nordveh || 'N/A'"></span></td>
                            <td><div class="plate-badge" x-text="vehiculo.placaveh"></div></td>
                            <td x-text="vehiculo.marca?.nommarlin || 'N/A'"></td>
                            <td x-text="vehiculo.clase?.nomval || 'N/A'"></td>
                            <td><span class="status-badge" :class="vehiculo.tipo_servicio === 1 ? 'success' : 'info'" x-text="servicioLabel(vehiculo.tipo_servicio)"></span></td>
                            <td x-text="empresaDisplay(vehiculo)"></td>
                            <td x-text="vehiculo.combustible?.nomval || 'N/A'"></td>
                            <td style="display: flex; justify-content: flex-end; gap: 8px; padding: 16px 24px;">
                                @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Digitador'))
                                <a class="icon-btn" title="Editar en formulario" :href="'/' + '{{ $prefix }}' + '/vehiculos/' + vehiculo.idveh + '/editar'" @click.stop style="text-decoration:none;"><i class="fa-solid fa-pen-to-square"></i></a>
                                <button class="icon-btn" title="Eliminar" style="color: #ef4444;"><i class="fa-solid fa-trash-can"></i></button>
                                @endif
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredVehiculos.length === 0">
                        <td colspan="8" style="text-align: center; padding: 32px; color: #6b7280;">
                            <i class="fa-solid fa-car-burst" style="font-size: 24px; margin-bottom: 8px; display: block; opacity: 0.3;"></i>
                            Ningún vehículo encontrado.
                        </td>
                    </tr>
                </tbody>
            </table>
            {{-- Show more / Show less --}}
            <div class="show-more-bar" x-show="hasMore">
                <button @click="showAll = !showAll">
                    <i class="fa-solid" :class="showAll ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    <span x-text="showAll ? 'Mostrar menos' : 'Ver todos los vehículos (' + filteredVehiculos.length + ')'"></span>
                </button>
            </div>
        </div>

        <!-- Master/Detail Bottom Section -->
        <div class="details-section" x-show="selectedVehiculo" x-cloak id="vehiculo-detalles">
            <!-- Form Panel -->
            <div class="vcard" style="display: flex; flex-direction: column;">
                <div style="padding-top: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0 24px; margin-bottom: 16px;">
                        <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">Detalle del Vehículo</h3>
                        <div style="display: flex; gap: 8px;">
                            {{-- Botón Editar (solo Admin/Digitador, solo en modo consulta) --}}
                            @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Digitador'))
                            <button class="vbtn vbtn-edit" x-show="!editMode" @click="toggleEdit()">
                                <i class="fa-solid fa-pen"></i> Editar
                            </button>
                            @endif
                        </div>
                    </div>

                    {{-- Banner modo edición --}}
                    <div class="edit-banner" x-show="editMode" x-cloak style="margin: 0 24px 12px 24px;">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        Modo edición activado — los cambios no se guardarán hasta confirmar.
                    </div>

                    <div class="vtabs">
                        <button class="vtab" :class="{'active': tab === 'todos'}" @click="tab = 'todos'">Todos los datos</button>
                        <button class="vtab" :class="{'active': tab === 'generales'}" @click="tab = 'generales'">Datos generales</button>
                        <button class="vtab" :class="{'active': tab === 'documentos'}" @click="tab = 'documentos'">Documentación</button>
                        <button class="vtab" :class="{'active': tab === 'vinculacion'}" @click="tab = 'vinculacion'">Vinculación</button>
                    </div>
                </div>

                <div class="form-scroll-area" style="flex: 1;">
                    <!-- ================================ -->
                    <!-- Section 1: Datos Generales      -->
                    <!-- ================================ -->
                    <div class="form-section" x-show="tab === 'todos' || tab === 'generales'">
                        <h4 class="form-section-title"><i class="fa-solid fa-car-side"></i> Datos Generales</h4>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>No. interno</label>
                                <input type="text" :value="selectedVehiculo.nordveh || 'N/A'" :readonly="!editMode" :class="{'editable': editMode}" />
                            </div>
                            <div class="form-group">
                                <label>No. placa</label>
                                <input type="text" :value="selectedVehiculo.placaveh || 'N/A'" :readonly="!editMode" :class="{'editable': editMode}" />
                            </div>
                            <div class="form-group">
                                <label>Línea (Marca)</label>
                                <template x-if="!editMode">
                                    <input type="text" :value="selectedVehiculo.marca?.nommarlin || 'N/A'" readonly />
                                </template>
                                <template x-if="editMode">
                                    <select x-model="editForm.linveh" style="height: 40px; border: 1px solid #3b82f6; border-radius: 6px; padding: 0 12px; font-size: 14px; background: #ffffff; width: 100%; color: #111827; box-shadow: 0 0 0 2px rgba(59,130,246,0.1);">
                                        <option value="">Seleccionar línea</option>
                                        <template x-for="m in allMarcas" :key="m.idmar">
                                            <option :value="m.idmar" x-text="m.idmar + ' - ' + m.nommarlin"></option>
                                        </template>
                                    </select>
                                </template>
                            </div>
                            <div class="form-group">
                                <label>Modelo (Año)</label>
                                <input type="text" :value="selectedVehiculo.modveh || 'N/A'" :readonly="!editMode" :class="{'editable': editMode}" />
                            </div>
                            <div class="form-group">
                                <label>Clase de vehículo</label>
                                <template x-if="!editMode">
                                    <input type="text" :value="selectedVehiculo.clase?.nomval || 'N/A'" readonly />
                                </template>
                                <template x-if="editMode">
                                    <select x-model="editForm.clveh" style="height: 40px; border: 1px solid #3b82f6; border-radius: 6px; padding: 0 12px; font-size: 14px; background: #ffffff; width: 100%; color: #111827; box-shadow: 0 0 0 2px rgba(59,130,246,0.1);">
                                        <option value="">Seleccionar clase</option>
                                        <template x-for="c in allClases" :key="c.idval">
                                            <option :value="c.idval" x-text="c.nomval"></option>
                                        </template>
                                    </select>
                                </template>
                            </div>
                            <div class="form-group">
                                <label>Tipo de servicio</label>
                                <template x-if="!editMode">
                                    <input type="text" :value="servicioLabel(selectedVehiculo.tipo_servicio)" readonly />
                                </template>
                                <template x-if="editMode">
                                    <select x-model="editForm.tipo_servicio" style="height: 40px; border: 1px solid #3b82f6; border-radius: 6px; padding: 0 12px; font-size: 14px; background: #ffffff; width: 100%; color: #111827; box-shadow: 0 0 0 2px rgba(59,130,246,0.1);">
                                        <option value="1">Particular</option>
                                        <option value="2">Público</option>
                                    </select>
                                </template>
                            </div>
                            <div class="form-group" style="grid-column: span 2;">
                                <label>Empresa asociada</label>
                                <template x-if="!editMode">
                                    <input type="text" :value="empresaDisplay(selectedVehiculo)" readonly />
                                </template>
                                <template x-if="editMode">
                                    <select x-model="editForm.idemp" style="height: 40px; border: 1px solid #3b82f6; border-radius: 6px; padding: 0 12px; font-size: 14px; background: #ffffff; width: 100%; color: #111827; box-shadow: 0 0 0 2px rgba(59,130,246,0.1);">
                                        <option value="">Sin empresa / Independiente</option>
                                        <template x-for="e in allEmpresas" :key="e.idemp">
                                            <option :value="e.idemp" x-text="e.razsoem"></option>
                                        </template>
                                    </select>
                                </template>
                            </div>
                            <div class="form-group">
                                <label>Color del vehículo</label>
                                <input type="text" :value="selectedVehiculo.colveh || 'N/A'" :readonly="!editMode" :class="{'editable': editMode}" />
                            </div>
                            <div class="form-group">
                                <label>Cilindraje</label>
                                <input type="text" :value="selectedVehiculo.cilveh ? selectedVehiculo.cilveh + ' cc' : 'N/A'" :readonly="!editMode" :class="{'editable': editMode}" />
                            </div>
                            <div class="form-group">
                                <label>Tipo de combustible</label>
                                <template x-if="!editMode">
                                    <input type="text" :value="selectedVehiculo.combustible?.nomval || 'N/A'" readonly />
                                </template>
                                <template x-if="editMode">
                                    <select x-model="editForm.combuveh" style="height: 40px; border: 1px solid #3b82f6; border-radius: 6px; padding: 0 12px; font-size: 14px; background: #ffffff; width: 100%; color: #111827; box-shadow: 0 0 0 2px rgba(59,130,246,0.1);">
                                        <option value="">Seleccionar combustible</option>
                                        <template x-for="cb in allCombustibles" :key="cb.idval">
                                            <option :value="cb.idval" x-text="cb.nomval"></option>
                                        </template>
                                    </select>
                                </template>
                            </div>
                            <div class="form-group">
                                <label>Tipo de motor</label>
                                <input type="text" :value="selectedVehiculo.tipo_motor?.nomval || '4 T'" readonly />
                            </div>
                            <div class="form-group">
                                <label>Categoría de carga</label>
                                <template x-if="!editMode">
                                    <input type="text" :value="selectedVehiculo.categoria_carga?.nomval || 'N/A'" readonly />
                                </template>
                                <template x-if="editMode">
                                    <select x-model="editForm.crgveh" style="height: 40px; border: 1px solid #3b82f6; border-radius: 6px; padding: 0 12px; font-size: 14px; background: #ffffff; width: 100%; color: #111827; box-shadow: 0 0 0 2px rgba(59,130,246,0.1);">
                                        <option value="">Seleccionar carga</option>
                                        <template x-for="cr in allCargas" :key="cr.idval">
                                            <option :value="cr.idval" x-text="cr.nomval"></option>
                                        </template>
                                    </select>
                                </template>
                            </div>
                            <div class="form-group">
                                <label>Número de sillas</label>
                                <input type="text" :value="selectedVehiculo.capveh || 'N/A'" :readonly="!editMode" :class="{'editable': editMode}" />
                            </div>
                            {{-- País de fabricación: RETIRADO (siempre es Colombia, ver tabla de cambios) --}}
                            <div class="form-group">
                                <label>Blindaje</label>
                                <template x-if="!editMode">
                                    <input type="text" :value="blindajeLabel(selectedVehiculo.blinveh)" readonly />
                                </template>
                                <template x-if="editMode">
                                    <select x-model="editForm.blinveh" style="height: 40px; border: 1px solid #3b82f6; border-radius: 6px; padding: 0 12px; font-size: 14px; background: #ffffff; width: 100%; color: #111827; box-shadow: 0 0 0 2px rgba(59,130,246,0.1);">
                                        <option value="2">No</option>
                                        <option value="1">Sí</option>
                                    </select>
                                </template>
                            </div>
                            <div class="form-group">
                                <label>No. motor</label>
                                <input type="text" :value="selectedVehiculo.nmotveh || 'N/A'" :readonly="!editMode" :class="{'editable': editMode}" />
                            </div>
                            <div class="form-group" style="grid-column: span 2;">
                                <label>No. chasis</label>
                                <input type="text" :value="selectedVehiculo.nchaveh || 'N/A'" :readonly="!editMode" :class="{'editable': editMode}" />
                            </div>
                            <div class="form-group">
                                <label>Radio de acción</label>
                                <input type="text" :value="selectedVehiculo.radaccveh || 'N/A'" :readonly="!editMode" :class="{'editable': editMode}" />
                            </div>
                            <div class="form-group">
                                <label>Tipo de póliza</label>
                                <template x-if="!editMode">
                                    <input type="text" :value="polizaLabel(selectedVehiculo.polaveh)" readonly />
                                </template>
                                <template x-if="editMode">
                                    <select x-model="editForm.polaveh" style="height: 40px; border: 1px solid #3b82f6; border-radius: 6px; padding: 0 12px; font-size: 14px; background: #ffffff; width: 100%; color: #111827; box-shadow: 0 0 0 2px rgba(59,130,246,0.1);">
                                        <option value="1">Todo Riesgo</option>
                                        <option value="2">Terceros</option>
                                    </select>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- ====================================== -->
                    <!-- Section 2: Documentación y Seguros     -->
                    <!-- ====================================== -->
                    <div class="form-section" x-show="tab === 'todos' || tab === 'documentos'">
                        <h4 class="form-section-title"><i class="fa-solid fa-file-shield"></i> Documentación y Seguros</h4>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Licencia de tránsito</label>
                                <input type="text" :value="selectedVehiculo.lictraveh || 'N/A'" :readonly="!editMode" :class="{'editable': editMode}" />
                            </div>
                            <div class="form-group">
                                <label>Fecha de matrícula</label>
                                <input type="text" :value="selectedVehiculo.fmatv || 'N/A'" :readonly="!editMode" :class="{'editable': editMode}" />
                            </div>
                            <div class="form-group">
                                <label>Venc. tarjeta de operación</label>
                                <input type="text" :value="selectedVehiculo.fecvenr || 'N/A'" :readonly="!editMode" :class="{'editable': editMode}" />
                            </div>
                            {{-- Tarjeta de operación (taroperveh) y Expedición (fecexpr): RETIRADOS, ver tabla de cambios --}}

                            {{-- SOAT --}}
                            <div class="form-group">
                                <label>SOAT</label>
                                <input type="text" :value="selectedVehiculo.soat || 'N/A'" :readonly="!editMode" :class="{'editable': editMode}" />
                            </div>
                            <div class="form-group">
                                <label>Vencimiento SOAT</label>
                                <input type="text" :value="selectedVehiculo.fecvens || 'N/A'" :readonly="!editMode" :class="{'editable': editMode}" />
                            </div>
                            <div class="form-group" style="border-right: none;"></div>

                            {{-- Tecnomecánica --}}
                            <div class="form-group">
                                <label>Tecnomecánica</label>
                                <input type="text" :value="selectedVehiculo.tecmecveh || 'N/A'" :readonly="!editMode" :class="{'editable': editMode}" />
                            </div>
                            <div class="form-group">
                                <label>Vencimiento tecnomecánica</label>
                                <input type="text" :value="selectedVehiculo.fecvent || 'N/A'" :readonly="!editMode" :class="{'editable': editMode}" />
                            </div>
                            <div class="form-group" style="border-right: none;"></div>

                            {{-- Extracontractual --}}
                            <div class="form-group">
                                <label>Póliza extracontractual</label>
                                <input type="text" :value="selectedVehiculo.extcontveh || 'N/A'" :readonly="!editMode" :class="{'editable': editMode}" />
                            </div>
                            <div class="form-group">
                                <label>Vencimiento extracontractual</label>
                                <input type="text" :value="selectedVehiculo.fecvene || 'N/A'" :readonly="!editMode" :class="{'editable': editMode}" />
                            </div>
                        </div>
                    </div>

                    <!-- ====================================== -->
                    <!-- Section 3: Vinculación                 -->
                    <!-- ====================================== -->
                    <div class="form-section" x-show="tab === 'todos' || tab === 'vinculacion'">
                        <h4 class="form-section-title"><i class="fa-solid fa-users"></i> Vinculación Asignada</h4>
                        <div class="form-grid">
                            <div class="form-group" style="grid-column: span 2;">
                                <label>Propietario</label>
                                <template x-if="!editMode">
                                    <input type="text" :value="selectedVehiculo.propietario ? (selectedVehiculo.propietario.nomper + ' ' + (selectedVehiculo.propietario.apeper || '') + ' — CC. ' + (selectedVehiculo.propietario.ndocper || 'N/A')) : 'N/A'" readonly />
                                </template>
                                <template x-if="editMode">
                                    <select x-model="editForm.prop" style="height: 40px; border: 1px solid #3b82f6; border-radius: 6px; padding: 0 12px; font-size: 14px; background: #ffffff; width: 100%; color: #111827; box-shadow: 0 0 0 2px rgba(59,130,246,0.1);">
                                        <option value="">Sin asignar</option>
                                        <template x-for="p in personas" :key="'prop-'+p.idper">
                                            <option :value="p.idper" x-text="p.nomper + ' ' + (p.apeper || '') + ' — ' + (p.ndocper || '')"></option>
                                        </template>
                                    </select>
                                </template>
                            </div>
                            <div class="form-group">
                                <label>Conductor principal</label>
                                <template x-if="!editMode">
                                    <input type="text" :value="selectedVehiculo.conductor ? (selectedVehiculo.conductor.nomper + ' ' + (selectedVehiculo.conductor.apeper || '') + ' — CC. ' + (selectedVehiculo.conductor.ndocper || 'N/A')) : 'N/A'" readonly />
                                </template>
                                <template x-if="editMode">
                                    <select x-model="editForm.cond" style="height: 40px; border: 1px solid #3b82f6; border-radius: 6px; padding: 0 12px; font-size: 14px; background: #ffffff; width: 100%; color: #111827; box-shadow: 0 0 0 2px rgba(59,130,246,0.1);">
                                        <option value="">Sin asignar</option>
                                        <template x-for="p in personas" :key="'cond-'+p.idper">
                                            <option :value="p.idper" x-text="p.nomper + ' ' + (p.apeper || '') + ' — ' + (p.ndocper || '')"></option>
                                        </template>
                                    </select>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer: Botones solo en modo edición -->
                <div x-show="editMode" x-cloak style="padding: 16px 24px; border-top: 1px solid rgba(0,0,0,0.08); display: flex; justify-content: flex-end; gap: 12px; background: #ffffff;">
                    <button class="vbtn vbtn-secondary" @click="cancelEdit()" :disabled="editSaving">
                        <i class="fa-solid fa-xmark"></i> Descartar cambios
                    </button>
                    <button class="vbtn vbtn-primary" @click="saveInlineEdit()" :disabled="editSaving">
                        <i class="fa-solid fa-floppy-disk" x-show="!editSaving"></i>
                        <i class="fa-solid fa-spinner fa-spin" x-show="editSaving"></i>
                        <span x-text="editSaving ? 'Guardando...' : 'Guardar cambios'"></span>
                    </button>
                </div>
            </div>

            <!-- Relational Graph Panel -->
            <div class="vcard">
                <div style="padding: 20px 24px; border-bottom: 1px solid rgba(0,0,0,0.08);">
                    <h3 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0;">Resumen de Vínculos</h3>
                </div>
                <div class="relation-list">
                    <!-- Vehicle + Service Badge -->
                    <div class="relation-item highlighted">
                        <div class="relation-icon">
                            <i class="fa-solid fa-car-side"></i>
                        </div>
                        <div class="relation-info">
                            <span class="relation-label">Vehículo Seleccionado</span>
                            <span class="relation-value" style="display: flex; align-items: center; gap: 8px;">
                                <span x-text="selectedVehiculo.placaveh"></span>
                                <span class="status-badge" :class="selectedVehiculo.tipo_servicio === 1 ? 'success' : 'info'" x-text="servicioLabel(selectedVehiculo.tipo_servicio)" style="font-size: 10px;"></span>
                            </span>
                        </div>
                    </div>

                    <div class="relation-connector"></div>

                    <!-- Propietario -->
                    <div class="relation-item">
                        <div class="relation-icon">
                            <i class="fa-solid fa-briefcase"></i>
                        </div>
                        <div class="relation-info">
                            <span class="relation-label">Propietario</span>
                            <span class="relation-value" x-text="selectedVehiculo.propietario ? (selectedVehiculo.propietario.nomper + ' ' + (selectedVehiculo.propietario.apeper || '')) : 'Sin asignar'" :style="!selectedVehiculo.propietario && 'color: #9ca3af; font-style: italic;'"></span>
                        </div>
                    </div>

                    <div class="relation-connector"></div>

                    <!-- Conductor -->
                    <div class="relation-item">
                        <div class="relation-icon">
                            <i class="fa-solid fa-id-card"></i>
                        </div>
                        <div class="relation-info">
                            <span class="relation-label">Conductor</span>
                            <span class="relation-value" x-text="selectedVehiculo.conductor ? (selectedVehiculo.conductor.nomper + ' ' + (selectedVehiculo.conductor.apeper || '')) : 'Sin asignar'" :style="!selectedVehiculo.conductor && 'color: #9ca3af; font-style: italic;'"></span>
                        </div>
                    </div>

                    <div class="relation-connector"></div>

                    <!-- Empresa -->
                    <div class="relation-item">
                        <div class="relation-icon">
                            <i class="fa-solid fa-building"></i>
                        </div>
                        <div class="relation-info">
                            <span class="relation-label">Empresa</span>
                            <span class="relation-value" x-text="empresaDisplay(selectedVehiculo)" :style="!selectedVehiculo.empresa && 'color: #9ca3af; font-style: italic;'"></span>
                        </div>
                    </div>

                    {{-- Formulario de edición de vínculos (inline) --}}
                    @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Digitador'))
                    <div x-show="vinculoMode" x-cloak style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(0,0,0,0.08);">
                        <p style="font-size: 13px; font-weight: 600; color: #111827; margin: 0 0 12px 0;"><i class="fa-solid fa-link" style="color: #6b7280; margin-right: 6px;"></i>Editar vínculos</p>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <div class="form-group">
                                <label>Propietario</label>
                                <select x-model="vinculoForm.prop" style="height: 36px; border: 1px solid #d1d5db; border-radius: 6px; padding: 0 10px; font-size: 13px; width: 100%;">
                                    <option value="">Sin asignar</option>
                                    <template x-for="p in personas" :key="p.idper">
                                        <option :value="p.idper" x-text="p.nomper + ' ' + (p.apeper || '') + ' — ' + (p.ndocper || '')"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Conductor</label>
                                <select x-model="vinculoForm.cond" style="height: 36px; border: 1px solid #d1d5db; border-radius: 6px; padding: 0 10px; font-size: 13px; width: 100%;">
                                    <option value="">Sin asignar</option>
                                    <template x-for="p in personas" :key="p.idper">
                                        <option :value="p.idper" x-text="p.nomper + ' ' + (p.apeper || '') + ' — ' + (p.ndocper || '')"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Empresa</label>
                                <select x-model="vinculoForm.idemp" style="height: 36px; border: 1px solid #d1d5db; border-radius: 6px; padding: 0 10px; font-size: 13px; width: 100%;">
                                    <option value="">Sin empresa</option>
                                    <template x-for="e in allEmpresas" :key="e.idemp">
                                        <option :value="e.idemp" x-text="e.razsoem"></option>
                                    </template>
                                </select>
                            </div>
                            <div style="display: flex; gap: 8px; margin-top: 4px;">
                                <button class="vbtn vbtn-secondary" style="flex: 1; font-size: 13px; padding: 8px;" @click="cancelVinculo()" :disabled="vinculoSaving">
                                    <i class="fa-solid fa-xmark"></i> Cancelar
                                </button>
                                <button class="vbtn vbtn-primary" style="flex: 1; font-size: 13px; padding: 8px;" @click="saveVinculos()" :disabled="vinculoSaving">
                                    <i class="fa-solid fa-check" x-show="!vinculoSaving"></i>
                                    <i class="fa-solid fa-spinner fa-spin" x-show="vinculoSaving"></i>
                                    <span x-text="vinculoSaving ? 'Guardando...' : 'Guardar'"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Botones de acción --}}
                    <div x-show="!vinculoMode" style="margin-top: 24px; padding-top: 24px; border-top: 1px solid rgba(0,0,0,0.08);">
                        <button class="vbtn vbtn-outline" style="width: 100%; justify-content: center;" @click="openVinculoEdit()">
                            <i class="fa-solid fa-pen-to-square"></i> Editar vínculos
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
