<x-app-layout>
    <div class="flex h-screen bg-gray-100">
        <aside class="w-64 bg-slate-900 shadow-xl hidden md:block">
             @include('components.sidebar') 
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden w-full relative">
            <x-slot name="header">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Gestión de Vehículos') }}
                </h2>
            </x-slot>

            <main class="flex-1 overflow-x-hidden overflow-y-auto" style="background-color: #f6f8fa;">
                {{-- Contenedor del módulo con el CSS importado dinámicamente --}}
                <div class="main-content" style="padding: 32px 40px 40px 40px; display: flex; flex-direction: column; gap: 24px;"
                    x-data="{ 
                        vehiculos: {{ $vehiculos->toJson() }},
                        search: '',
                        selectedVehiculo: null,
                        tab: 'todos',
                        empresaFiltro: '',
                        
                        get filteredVehiculos() {
                            return this.vehiculos.filter(v => {
                                const matchSearch = v.placaveh.toLowerCase().includes(this.search.toLowerCase()) || 
                                                    (v.nordveh && v.nordveh.toLowerCase().includes(this.search.toLowerCase()));
                                const matchEmpresa = this.empresaFiltro === '' || (v.empresa && v.empresa.razsoem === this.empresaFiltro);
                                return matchSearch && matchEmpresa;
                            });
                        },
                        
                        init() {
                            if(this.vehiculos.length > 0) {
                                this.selectedVehiculo = this.vehiculos[0];
                            }
                        }
                    }">
                    
                    <style>
                        /* Estilos Extraídos e implementados para aislar el scope */
                        .vehiculo-module {
                            font-family: 'Inter', system-ui, -apple-system, sans-serif;
                            color: #0b2540;
                        }
                        .vehiculo-module .btn {
                            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
                            padding: 10px 16px; border-radius: 8px; font-size: 14px; font-weight: 500;
                            cursor: pointer; transition: 0.2s; border: 1px solid transparent; white-space: nowrap;
                        }
                        .vehiculo-module .btn-primary { background-color: #0b3a5a; color: #ffffff; }
                        .vehiculo-module .btn-primary:hover { background-color: #082d46; }
                        .vehiculo-module .btn-secondary { background-color: #ffffff; border-color: #d1d5db; color: #374151; }
                        .vehiculo-module .btn-outline { background-color: transparent; border-color: #d1d5db; color: #374151; }
                        .vehiculo-module .btn-outline-primary { background-color: transparent; border-color: #0b3a5a; color: #0b3a5a; }
                        .vehiculo-module .card { background: #ffffff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.025); border: 1px solid #00000014; overflow: hidden; }
                        .vehiculo-module .page-title { font-size: 24px; font-weight: 700; margin: 0 0 6px 0; }
                        .vehiculo-module .page-subtitle { font-size: 14px; color: #6b7280; margin: 0; }
                        .vehiculo-module .data-table { width: 100%; border-collapse: collapse; text-align: left; }
                        .vehiculo-module .data-table th { padding: 14px 24px; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; background-color: #f9fafb; border-bottom: 1px solid #00000014; white-space: nowrap; }
                        .vehiculo-module .data-table td { padding: 16px 24px; font-size: 14px; color: #374151; border-bottom: 1px solid #00000014; vertical-align: middle; white-space: nowrap; }
                        .vehiculo-module .data-table tbody tr { cursor: pointer; transition: background-color 0.2s; }
                        .vehiculo-module .data-table tbody tr:hover { background-color: #f9fafb; }
                        .vehiculo-module .data-table tbody tr.active-row { background-color: #eff6ff; }
                        .vehiculo-module .plate-badge { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 4px 8px; border-radius: 4px; font-weight: 700; font-family: monospace; font-size: 14px; display: inline-block; }
                        .vehiculo-module .status-badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 500; }
                        .vehiculo-module .status-badge.success { background-color: #e6eef6; color: #15803d; }
                        .vehiculo-module .icon-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; border: none; background: transparent; color: #6b7280; cursor: pointer; transition: 0.2s; }
                        .vehiculo-module .icon-btn:hover { background: #e5e7eb; color: #111827; }
                        .vehiculo-module .details-section { display: grid; grid-template-columns: 2.5fr 1fr; gap: 24px; align-items: start; }
                        
                        /* Formularios */
                        .vehiculo-module .tabs { display: flex; gap: 24px; border-bottom: 1px solid #00000014; padding: 0 24px; }
                        .vehiculo-module .tab { padding: 12px 0; background: none; border: none; font-size: 14px; font-weight: 500; color: #6b7280; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -1px; transition: 0.2s; }
                        .vehiculo-module .tab.active { color: #0b3a5a; border-bottom-color: #0b3a5a; font-weight: 600; }
                        .vehiculo-module .form-scroll-area { max-height: 500px; overflow-y: auto; background-color: #f9fafb; }
                        .vehiculo-module .form-section { padding: 24px; background: #ffffff; border-bottom: 1px solid #00000014; }
                        .vehiculo-module .form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px 20px; }
                        .vehiculo-module .form-group { display: flex; flex-direction: column; gap: 6px; }
                        .vehiculo-module .form-group label { font-size: 13px; font-weight: 500; color: #4b5563; }
                        .vehiculo-module .form-group input, .vehiculo-module .form-group select { height: 40px; border: 1px solid #d1d5db; border-radius: 6px; padding: 0 12px; font-size: 14px; outline: none; background: #ffffff; width: 100%; color: #111827;}
                        .vehiculo-module .form-group input:focus { border-color: #0b3a5a; }
                        
                        /* Relaciones */
                        .vehiculo-module .relation-list { display: flex; flex-direction: column; padding: 24px; }
                        .vehiculo-module .relation-item { display: flex; align-items: center; gap: 16px; padding: 14px; border: 1px solid #e5e7eb; border-radius: 8px; background: #f9fafb; }
                        .vehiculo-module .relation-item.highlighted { border-color: #93c5fd; background-color: #eff6ff; }
                        .vehiculo-module .relation-icon { width: 44px; height: 44px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; background-color: #ffffff; border: 1px solid #e5e7eb; color: #4b5563;}
                        .vehiculo-module .relation-item.highlighted .relation-icon { background-color: #dbeafe; color: #1d4ed8; border: none; }
                        .vehiculo-module .relation-connector { width: 2px; height: 20px; background-color: #e5e7eb; margin-left: 35px; }
                        .vehiculo-module .relation-info { display: flex; flex-direction: column; gap: 2px; }
                        .vehiculo-module .relation-label { font-size: 12px; color: #6b7280; font-weight: 500; }
                        .vehiculo-module .relation-value { font-size: 14px; color: #111827; font-weight: 600; }
                    </style>

                    <div class="vehiculo-module">
                        <!-- Page Header -->
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h1 class="page-title">Vehículos</h1>
                                <p class="page-subtitle">Gestión de activos vehiculares del CDA</p>
                            </div>
                            <button class="btn btn-primary">
                                <iconify-icon icon="lucide:plus"></iconify-icon> Nuevo vehículo
                            </button>
                        </div>

                        <!-- Filters Bar -->
                        <div class="flex justify-between items-center gap-4 mb-6">
                            <div class="relative flex-1 max-w-md">
                                <iconify-icon icon="lucide:search" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500"></iconify-icon>
                                <input x-model="search" type="text" placeholder="Buscar por placa, no. interno..." class="w-full h-10 pl-10 pr-3 border border-gray-300 rounded-lg text-sm outline-none focus:border-blue-900"/>
                            </div>
                            <div class="flex gap-3">
                                @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Digitador'))
                                <select x-model="empresaFiltro" class="h-10 border border-gray-300 rounded-lg px-3 text-sm bg-white outline-none">
                                    <option value="">Todas las empresas</option>
                                    <template x-for="emp in [...new Set(vehiculos.map(v => v.empresa?.razsoem).filter(Boolean))]" :key="emp">
                                        <option x-text="emp" :value="emp"></option>
                                    </template>
                                </select>
                                @endif
                                <button class="btn btn-outline"><iconify-icon icon="lucide:filter"></iconify-icon> Filtrar</button>
                            </div>
                        </div>

                        <!-- Data Table -->
                        <div class="card overflow-x-auto mb-6">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>No. Interno</th>
                                        <th>Placa</th>
                                        <th>Línea</th>
                                        <th>Empresa</th>
                                        <th>Propietario</th>
                                        <th>Conductor</th>
                                        <th>Estado</th>
                                        <th class="text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="vehiculo in filteredVehiculos" :key="vehiculo.idveh">
                                        <tr @click="selectedVehiculo = vehiculo" :class="{'active-row': selectedVehiculo && selectedVehiculo.idveh === vehiculo.idveh}">
                                            <td><span class="font-medium" x-text="vehiculo.nordveh || 'N/A'"></span></td>
                                            <td><div class="plate-badge" x-text="vehiculo.placaveh"></div></td>
                                            <td x-text="vehiculo.marca?.nommarlin || 'N/A'"></td>
                                            <td x-text="vehiculo.empresa?.razsoem || 'N/A'"></td>
                                            <td x-text="(vehiculo.propietario?.nomper || '') + ' ' + (vehiculo.propietario?.apeper || '')"></td>
                                            <td x-text="(vehiculo.conductor?.nomper || '') + ' ' + (vehiculo.conductor?.apeper || '')"></td>
                                            <td><span class="status-badge success">Activo</span></td>
                                            <td class="flex justify-end gap-2 px-6 py-4">
                                                <button class="icon-btn" title="Ver detalle"><iconify-icon icon="lucide:eye"></iconify-icon></button>
                                                <button class="icon-btn" title="Editar"><iconify-icon icon="lucide:edit-2"></iconify-icon></button>
                                                <button class="icon-btn" title="Vincular actor"><iconify-icon icon="lucide:link"></iconify-icon></button>
                                                @if(auth()->user()->hasRole('Administrador') || auth()->user()->hasRole('Digitador'))
                                                <button class="icon-btn text-red-500" title="Eliminar"><iconify-icon icon="lucide:trash-2"></iconify-icon></button>
                                                @endif
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="filteredVehiculos.length === 0">
                                        <td colspan="8" class="text-center py-8 text-gray-500">Ningún vehículo encontrado.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Master/Detail Bottom Section -->
                        <div class="details-section" x-show="selectedVehiculo">
                            <!-- Form Panel -->
                            <div class="card flex flex-col h-full">
                                <div class="pt-6">
                                    <h3 class="px-6 text-lg font-semibold text-gray-900 mb-4">Detalle del Vehículo</h3>
                                    <div class="tabs">
                                        <button class="tab" :class="{'active': tab === 'todos'}" @click="tab = 'todos'">Todos los datos</button>
                                        <button class="tab" :class="{'active': tab === 'generales'}" @click="tab = 'generales'">Datos generales</button>
                                        <button class="tab" :class="{'active': tab === 'documentos'}" @click="tab = 'documentos'">Documentación</button>
                                        <button class="tab" :class="{'active': tab === 'vinculacion'}" @click="tab = 'vinculacion'">Vinculación</button>
                                    </div>
                                </div>

                                <div class="form-scroll-area flex-1">
                                    <!-- Section 1: Datos Generales -->
                                    <div class="form-section" x-show="tab === 'todos' || tab === 'generales'">
                                        <h4 class="form-section-title">Datos Generales</h4>
                                        <div class="form-grid">
                                            <div class="form-group whitespace-nowrap">
                                                <label>No. interno</label>
                                                <input type="text" :value="selectedVehiculo.nordveh" readonly class="bg-gray-100" />
                                            </div>
                                            <div class="form-group whitespace-nowrap">
                                                <label>No. placa</label>
                                                <input type="text" :value="selectedVehiculo.placaveh" readonly />
                                            </div>
                                            <div class="form-group whitespace-nowrap">
                                                <label>Línea (Marca)</label>
                                                <input type="text" :value="selectedVehiculo.marca?.nommarlin" readonly />
                                            </div>
                                            <div class="form-group">
                                                <label>Modelo</label>
                                                <input type="text" :value="selectedVehiculo.modveh" readonly />
                                            </div>
                                            <div class="form-group" style="grid-column: span 2">
                                                <label>Empresa</label>
                                                <input type="text" :value="selectedVehiculo.empresa?.razsoem" readonly />
                                            </div>
                                            <div class="form-group">
                                                <label>Color del vehículo</label>
                                                <input type="text" :value="selectedVehiculo.colveh" readonly />
                                            </div>
                                            <div class="form-group">
                                                <label>Cilindraje</label>
                                                <input type="text" :value="selectedVehiculo.cilveh + ' cc'" readonly />
                                            </div>
                                            <div class="form-group">
                                                <label>País Fabricación</label>
                                                <input type="text" :value="selectedVehiculo.paiveh" readonly />
                                            </div>
                                            <div class="form-group">
                                                <label>No. motor</label>
                                                <input type="text" :value="selectedVehiculo.nmotveh" readonly />
                                            </div>
                                            <div class="form-group" style="grid-column: span 2">
                                                <label>No. chasis</label>
                                                <input type="text" :value="selectedVehiculo.nchaveh" readonly />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Section 2: Documentación -->
                                    <div class="form-section" x-show="tab === 'todos' || tab === 'documentos'">
                                        <h4 class="form-section-title">Documentación y Seguros</h4>
                                        <div class="form-grid">
                                            <div class="form-group">
                                                <label>Licencia de tránsito</label>
                                                <input type="text" :value="selectedVehiculo.lictraveh" readonly />
                                            </div>
                                            <div class="form-group" style="grid-column: span 2">
                                                <label>Fecha matrícula</label>
                                                <input type="date" :value="selectedVehiculo.fmatv" readonly />
                                            </div>
                                            <div class="form-group">
                                                <label>SOAT</label>
                                                <input type="text" :value="selectedVehiculo.soat" readonly />
                                            </div>
                                            <div class="form-group">
                                                <label>Vencimiento SOAT</label>
                                                <input type="date" :value="selectedVehiculo.fecvens" readonly />
                                            </div>
                                            <div class="form-group">
                                                <label>Tecnomecánica</label>
                                                <input type="text" :value="selectedVehiculo.tecmecveh" readonly />
                                            </div>
                                            <div class="form-group">
                                                <label>Vencimiento Tecno.</label>
                                                <input type="date" :value="selectedVehiculo.fecvent" readonly />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Section 3: Vinculación Rápida -->
                                    <div class="form-section" x-show="tab === 'todos' || tab === 'vinculacion'">
                                        <h4 class="form-section-title">Vinculación Asignada</h4>
                                        <div class="form-grid">
                                            <div class="form-group" style="grid-column: span 2">
                                                <label>Propietario</label>
                                                <input type="text" :value="(selectedVehiculo.propietario?.nomper || 'N/A') + ' ' + (selectedVehiculo.propietario?.apeper || '') + ' (CC. ' + (selectedVehiculo.propietario?.ndocper || '') + ')'" readonly />
                                            </div>
                                            <div class="form-group">
                                                <label>Conductor principal</label>
                                                <input type="text" :value="(selectedVehiculo.conductor?.nomper || 'N/A') + ' ' + (selectedVehiculo.conductor?.apeper || '') + ' (CC. ' + (selectedVehiculo.conductor?.ndocper || '') + ')'" readonly />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="px-6 py-4 bg-white border-t border-gray-100 flex justify-end gap-3">
                                    <button class="btn btn-secondary">Descartar cambios</button>
                                    <button class="btn btn-primary" disabled>Guardar vehículo</button>
                                </div>
                            </div>

                            <!-- Relational Graph Panel -->
                            <div class="card h-fit">
                                <div class="px-6 py-5 border-b border-gray-100">
                                    <h3 class="text-base font-semibold text-gray-900 m-0">Resumen de Vínculos</h3>
                                </div>
                                <div class="relation-list">
                                    <!-- Vehicle -->
                                    <div class="relation-item highlighted">
                                        <div class="relation-icon">
                                            <iconify-icon icon="lucide:car-front"></iconify-icon>
                                        </div>
                                        <div class="relation-info">
                                            <span class="relation-label">Vehículo Seleccionado</span>
                                            <span class="relation-value" x-text="selectedVehiculo.placaveh"></span>
                                        </div>
                                    </div>

                                    <div class="relation-connector"></div>

                                    <!-- Owner -->
                                    <div class="relation-item">
                                        <div class="relation-icon">
                                            <iconify-icon icon="lucide:briefcase"></iconify-icon>
                                        </div>
                                        <div class="relation-info">
                                            <span class="relation-label">Propietario</span>
                                            <span class="relation-value" x-text="(selectedVehiculo.propietario?.nomper || 'N/A') + ' ' + (selectedVehiculo.propietario?.apeper || '')"></span>
                                        </div>
                                    </div>

                                    <div class="relation-connector"></div>

                                    <!-- Driver -->
                                    <div class="relation-item">
                                        <div class="relation-icon">
                                            <iconify-icon icon="lucide:layout-dashboard"></iconify-icon>
                                        </div>
                                        <div class="relation-info">
                                            <span class="relation-label">Conductor</span>
                                            <span class="relation-value" x-text="(selectedVehiculo.conductor?.nomper || 'N/A') + ' ' + (selectedVehiculo.conductor?.apeper || '')"></span>
                                        </div>
                                    </div>

                                    <div class="relation-connector"></div>

                                    <!-- Company -->
                                    <div class="relation-item">
                                        <div class="relation-icon">
                                            <iconify-icon icon="lucide:building-2"></iconify-icon>
                                        </div>
                                        <div class="relation-info">
                                            <span class="relation-label">Empresa</span>
                                            <span class="relation-value" x-text="selectedVehiculo.empresa?.razsoem || 'Sin Empresa'"></span>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-6 pt-6 border-t border-gray-100">
                                        <button class="btn btn-outline w-full justify-center">
                                            <iconify-icon icon="lucide:edit"></iconify-icon> Editar vínculos
                                        </button>
                                        <button class="btn btn-outline-primary w-full justify-center mt-2">
                                            <iconify-icon icon="lucide:plus-circle"></iconify-icon> Crear vínculo
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Incluir scripts de Iconify (requeridos para íconos) -->
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
</x-app-layout>
