@extends('layouts.app')

@section('content')
    @php
        $rolePrefix = auth()->user()->hasRole('Administrador') ? 'admin.' : (auth()->user()->hasRole('Digitador') ? 'digitador.' : 'empresa.');
    @endphp
    <div id="historial-wrapper" x-data="historialApp()">
        <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>

        <style>
            #historial-wrapper {
                --background: #f4f6f8;
                --foreground: #111827;
                --primary: #0b1f38;
                --border: #e5e7eb;
                --card-bg: #ffffff;
                --success-bg: #d1fae5;
                --success-text: #065f46;
                --warning-bg: #fef3c7;
                --warning-text: #92400e;
                --danger-bg: #fee2e2;
                --danger-text: #991b1b;
                --muted-bg: #f3f4f6;
                --muted-text: #4b5563;
                font-family: 'Inter', system-ui, sans-serif;
                background-color: var(--background);
                color: var(--foreground);
                min-height: calc(100vh - 65px);
            }

            #historial-wrapper .main-content { padding: 32px 40px; display: flex; flex-direction: column; gap: 24px; }
            #historial-wrapper .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 16px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; transition: 0.2s; border: 1px solid transparent; height: 40px; }
            #historial-wrapper .btn-primary { background-color: var(--primary); color: #ffffff; }
            #historial-wrapper .btn-primary:hover { opacity: 0.9; }
            #historial-wrapper .btn-secondary { background-color: #10b981; color: #ffffff; }
            #historial-wrapper .btn-secondary:hover { opacity: 0.9; }
            #historial-wrapper .btn-outline { background-color: transparent; border-color: #d1d5db; color: #374151; }
            #historial-wrapper .btn-outline:hover { background-color: #f9fafb; }
            #historial-wrapper .card { background: var(--card-bg); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid var(--border); overflow: hidden; }
            #historial-wrapper .p-6 { padding: 24px; }
            #historial-wrapper .page-header { display: flex; justify-content: space-between; align-items: flex-start; }
            #historial-wrapper .page-title { font-size: 24px; font-weight: 700; margin: 0 0 6px 0; }
            #historial-wrapper .page-subtitle { font-size: 14px; color: #6b7280; margin: 0; }
            #historial-wrapper .header-actions { display: flex; gap: 12px; }
            #historial-wrapper .filters-grid { display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end; }
            #historial-wrapper .input-group { display: flex; flex-direction: column; gap: 8px; flex: 1; min-width: 150px; }
            #historial-wrapper .input-label { font-size: 13px; font-weight: 500; color: #374151; }
            #historial-wrapper .input-control { height: 40px; width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0 12px; font-size: 14px; background-color: #ffffff; outline: none; }
            #historial-wrapper .search-box { position: relative; width: 100%; }
            #historial-wrapper .search-box .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6b7280; font-size: 16px; }
            #historial-wrapper .search-box input { padding-left: 36px; }
            #historial-wrapper .table-container { overflow-x: auto; }
            #historial-wrapper .data-table { width: 100%; border-collapse: collapse; text-align: left; }
            #historial-wrapper .data-table th { padding: 14px 24px; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; border-bottom: 1px solid var(--border); background-color: #f9fafb; }
            #historial-wrapper .data-table td { padding: 16px 24px; font-size: 14px; color: #374151; border-bottom: 1px solid var(--border); vertical-align: middle; }
            #historial-wrapper .data-table tr { transition: background-color 0.2s; }
            #historial-wrapper .data-table tr:hover { background-color: #f9fafb; }
            #historial-wrapper .data-table tr:last-child td { border-bottom: none; }
            #historial-wrapper .two-line-cell { display: flex; flex-direction: column; gap: 4px; }
            #historial-wrapper .two-line-cell .main-text { font-weight: 600; color: #111827; font-size: 14px; }
            #historial-wrapper .two-line-cell .sub-text { font-size: 12px; color: #6b7280; }
            #historial-wrapper .status-badge { display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; border: 1px solid transparent; text-transform: uppercase; }
            #historial-wrapper .status-badge.success { background-color: #ecfdf5; color: #10b981; border-color: #d1fae5; }
            #historial-wrapper .status-badge.danger { background-color: #fef2f2; color: #ef4444; border-color: #fee2e2; }
            #historial-wrapper .status-badge.warning { background-color: #fffbeb; color: #f59e0b; border-color: #fef3c7; }
            #historial-wrapper .actions-cell { display: flex; justify-content: flex-end; gap: 4px; }
            #historial-wrapper .icon-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; border: none; background: transparent; color: #6b7280; cursor: pointer; transition: 0.2s; }
            #historial-wrapper .icon-btn:hover { background-color: #f3f4f6; color: #111827; }
            #historial-wrapper .show-more-bar { padding: 12px; text-align: center; border-top: 1px solid #e5e7eb; background: #f9fafb; cursor: pointer; font-size: 13px; font-weight: 500; color: #4b5563; transition: 0.2s; }
            #historial-wrapper .show-more-bar:hover { background: #f3f4f6; color: #111827; }
            #historial-wrapper .company-tag { display: inline-block; font-size: 11px; font-weight: 600; color: #0b3a5a; background-color: #e0f2fe; padding: 2px 6px; border-radius: 4px; margin-bottom: 4px; }
            
            @media (max-width: 640px) {
                #historial-wrapper .main-content { padding: 16px 20px; }
                #historial-wrapper .page-header { flex-direction: column; gap: 16px; align-items: stretch; }
                #historial-wrapper .header-actions { flex-direction: column; width: 100%; }
                #historial-wrapper .header-actions .btn { width: 100%; justify-content: center; }
                #historial-wrapper .filters-grid { flex-direction: column; align-items: stretch; }
                #historial-wrapper .input-group { width: 100%; flex: none !important; }
            }
        </style>

        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Historial de Mantenimiento</h1>
                    <p class="page-subtitle">Registro y seguimiento de mantenimientos preventivos realizados dentro de las instalaciones</p>
                </div>
                <div class="header-actions">
                    <button @click="exportarReporte()" class="btn btn-secondary">
                        <iconify-icon icon="lucide:file-text"></iconify-icon> Reporte de Mantenimientos
                    </button>
                    @hasanyrole('Administrador|Digitador')
                    <a href="{{ route($rolePrefix . 'diagnosticos.create') }}" class="btn btn-primary">
                        <iconify-icon icon="lucide:plus"></iconify-icon> Nuevo Agendamiento
                    </a>
                    @endhasanyrole
                </div>
            </div>

            <!-- FILTERS -->
            <div class="card p-6" style="margin-bottom: 24px;">
                <div class="filters-grid">
                    <div class="input-group" style="flex: 2;">
                        <label class="input-label">Buscar Vehículo</label>
                        <div class="search-box">
                            <iconify-icon icon="lucide:search" class="search-icon"></iconify-icon>
                            <input type="text" x-model="search" class="input-control" placeholder="Placa o modelo..." />
                        </div>
                    </div>

                    @if(!auth()->user()->hasRole('Empresa'))
                    <div class="input-group" style="flex: 1.5;">
                        <label class="input-label">Empresa</label>
                        <select x-model="empresaFilter" class="input-control" style="appearance: none; background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%236B7280%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22%3E%3Cpath d=%22m6 9 6 6 6-6%22/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 12px center;">
                            <option value="">Todas las empresas</option>
                            <template x-for="empresa in empresas" :key="empresa.idemp">
                                <option :value="empresa.idemp" x-text="empresa.razsoem"></option>
                            </template>
                        </select>
                    </div>
                    @endif

                    <div class="input-group" style="flex: 1.5;">
                        <label class="input-label">Estado</label>
                        <select x-model="estadoFilter" class="input-control" style="appearance: none; background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%236B7280%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22%3E%3Cpath d=%22m6 9 6 6 6-6%22/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 12px center;">
                            <option value="">Todos los estados</option>
                            <option value="aprobado">Aprobado</option>
                            <option value="no_aprobado">No Aprobado</option>
                            <option value="pendiente">Pendiente</option>
                        </select>
                    </div>
                    
                    <div class="input-group" style="flex: 1;">
                        <label class="input-label">Fecha Inicio</label>
                        <input type="date" x-model="fechaInicio" class="input-control" />
                    </div>
                    
                    <div class="input-group" style="flex: 1;">
                        <label class="input-label">Fecha Fin</label>
                        <input type="date" x-model="fechaFin" class="input-control" />
                    </div>

                    <div x-show="isFiltering" style="display: none;">
                        <button type="button" @click="limpiarFiltros()" class="btn btn-outline" style="height: 40px; color: #ef4444; border-color: #fca5a5;">
                            <iconify-icon icon="lucide:x"></iconify-icon> Limpiar
                        </button>
                    </div>
                </div>
            </div>
            


            <!-- DATA TABLE -->
            <div class="card">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--border)">
                    <div style="display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 15px; color: var(--primary);">
                        <iconify-icon icon="lucide:clipboard-list" style="font-size: 18px"></iconify-icon>
                        Registros de Mantenimiento (<span x-text="filteredDiagnosticos.length"></span>)
                    </div>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Vehículo</th>
                                <th>Estado / Diagnóstico</th>
                                @if(!auth()->user()->hasRole('Empresa'))
                                    <th class="text-right">Acciones</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="displayedDiagnosticos.length === 0">
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 32px; color: #6b7280;">No hay historiales de mantenimiento que coincidan con los filtros.</td>
                                </tr>
                            </template>
                            
                            <template x-for="diag in displayedDiagnosticos" :key="diag.iddia">
                                <tr>
                                    <td>
                                        <div class="two-line-cell">
                                            <span class="main-text" x-text="formatDate(diag.fecdia)"></span>
                                            <span class="sub-text" x-text="formatTime(diag.fecdia)"></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="two-line-cell">
                                            <span class="main-text" style="font-family: monospace" x-text="diag.vehiculo?.placaveh || 'N/A'"></span>
                                            <span class="sub-text">
                                                <span x-text="diag.vehiculo?.marca?.nommarlin || 'N/A'"></span>
                                                <span x-text="diag.vehiculo?.modveh ? ' - ' + diag.vehiculo.modveh : ''"></span>
                                            </span>
                                            <template x-if="diag.vehiculo?.empresa">
                                                <div><span class="company-tag" x-text="diag.vehiculo.empresa.razsoem"></span></div>
                                            </template>
                                        </div>
                                    </td>
                                    <td>
                                        <template x-if="diag.aprobado == 1">
                                            <span class="status-badge success">APROBADO</span>
                                        </template>
                                        <template x-if="diag.aprobado == 0 && diag.aprobado !== null">
                                            <span class="status-badge danger">NO APROBADO</span>
                                        </template>
                                        <template x-if="diag.aprobado === null || diag.aprobado === ''">
                                            <span class="status-badge" style="background-color: #f3f4f6; color: #6b7280;">PENDIENTE</span>
                                        </template>
                                    </td>
                                    @if(!auth()->user()->hasRole('Empresa'))
                                        <td>
                                            <div class="actions-cell">
                                                @hasanyrole('Administrador|Digitador')
                                                    <a :href="'/' + '{{ str_replace('.', '', $rolePrefix) }}' + '/diagnosticos/' + diag.iddia + '/edit'" class="icon-btn" title="Editar">
                                                        <iconify-icon icon="lucide:edit-2"></iconify-icon>
                                                    </a>
                                                    <a :href="'/' + '{{ str_replace('.', '', $rolePrefix) }}' + '/diagnosticos/' + diag.iddia" class="icon-btn" title="Ver Detalles">
                                                        <iconify-icon icon="lucide:eye"></iconify-icon>
                                                    </a>
                                                @endhasanyrole
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                
                {{-- Pagination --}}
                <div class="flex flex-col sm:flex-row justify-between items-center px-6 py-4 bg-white border-t border-slate-200 gap-4">
                    <div style="color: #64748b; font-size: 14px;">
                        Mostrando <span style="font-weight: 500; color: #0f172a;" x-text="(currentPage - 1) * perPage + (totalItems > 0 ? 1 : 0)"></span> a <span style="font-weight: 500; color: #0f172a;" x-text="Math.min(currentPage * perPage, totalItems)"></span> de <span style="font-weight: 500; color: #0f172a;" x-text="totalItems"></span> resultados
                    </div>
                    <div class="inline-flex border border-slate-200 rounded-md overflow-x-auto shadow-sm max-w-full" x-show="totalPages > 1" x-cloak>
                        <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1" 
                                :style="currentPage === 1 ? 'padding: 8px 12px; background: #f8fafc; border-right: 1px solid #e2e8f0; color: #cbd5e1; cursor: not-allowed;' : 'padding: 8px 12px; background: #fff; border-right: 1px solid #e2e8f0; color: #64748b; cursor: pointer;'">
                            <i class="fa-solid fa-chevron-left" style="font-size: 12px;"></i>
                        </button>
                        <template x-for="(page, index) in paginationArray" :key="index">
                            <button @click="page !== '...' ? goToPage(page) : null" 
                                    :disabled="page === '...'"
                                    x-text="page" 
                                    :style="page === currentPage ? 'padding: 8px 14px; background: #f1f5f9; border-right: 1px solid #e2e8f0; font-size: 14px; font-weight: 600; color: #0f172a; cursor: default;' : (page === '...' ? 'padding: 8px 14px; background: #fff; border-right: 1px solid #e2e8f0; font-size: 14px; color: #94a3b8; cursor: default;' : 'padding: 8px 14px; background: #fff; border-right: 1px solid #e2e8f0; font-size: 14px; color: #64748b; cursor: pointer;')">
                            </button>
                        </template>
                        <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages" 
                                :style="currentPage === totalPages ? 'padding: 8px 12px; background: #f8fafc; color: #cbd5e1; cursor: not-allowed;' : 'padding: 8px 12px; background: #fff; color: #64748b; cursor: pointer;'">
                            <i class="fa-solid fa-chevron-right" style="font-size: 12px;"></i>
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function historialApp() {
            return {
                diagnosticos: @json($diagnosticos),
                empresas: @json($empresasFiltro),
                search: '',
                estadoFilter: '',
                empresaFilter: '',
                fechaInicio: '',
                fechaFin: '',
                currentPage: 1,
                perPage: 15,

                init() {
                    this.$watch('search', () => { this.currentPage = 1; });
                    this.$watch('estadoFilter', () => { this.currentPage = 1; });
                    this.$watch('empresaFilter', () => { this.currentPage = 1; });
                    this.$watch('fechaInicio', () => { this.currentPage = 1; });
                    this.$watch('fechaFin', () => { this.currentPage = 1; });
                },

                get isFiltering() {
                    return this.search !== '' || this.estadoFilter !== '' || this.empresaFilter !== '' || this.fechaInicio !== '' || this.fechaFin !== '';
                },

                limpiarFiltros() {
                    this.search = '';
                    this.estadoFilter = '';
                    this.empresaFilter = '';
                    this.fechaInicio = '';
                    this.fechaFin = '';
                },

                get filteredDiagnosticos() {
                    return this.diagnosticos.filter(d => {
                        let match = true;
                        
                        // Buscador
                        if (this.search) {
                            const term = this.search.toLowerCase();
                            const v = d.vehiculo;
                            const pMatch = v && v.placaveh && v.placaveh.toLowerCase().includes(term);
                            const mMatch = v && v.marca && v.marca.nommarlin && v.marca.nommarlin.toLowerCase().includes(term);
                            if (!pMatch && !mMatch) match = false;
                        }

                        // Estado
                        if (this.estadoFilter) {
                            if (this.estadoFilter === 'aprobado' && d.aprobado !== 1) match = false;
                            if (this.estadoFilter === 'no_aprobado' && d.aprobado !== 0) match = false;
                            if (this.estadoFilter === 'pendiente' && d.aprobado !== null) match = false;
                        }

                        // Empresa
                        if (this.empresaFilter) {
                            if (!d.vehiculo || String(d.vehiculo.idemp) !== String(this.empresaFilter)) match = false;
                        }

                        // Fechas
                        if (this.fechaInicio) {
                            if (new Date(d.fecdia) < new Date(this.fechaInicio + 'T00:00:00')) match = false;
                        }
                        if (this.fechaFin) {
                            if (new Date(d.fecdia) > new Date(this.fechaFin + 'T23:59:59')) match = false;
                        }

                        return match;
                    });
                },

                get totalItems() {
                    return this.filteredDiagnosticos.length;
                },

                get totalPages() {
                    return Math.ceil(this.totalItems / this.perPage) || 1;
                },

                get paginationArray() {
                    let current = this.currentPage;
                    let last = this.totalPages;
                    let delta = 2;
                    let left = current - delta;
                    let right = current + delta + 1;
                    let range = [];
                    let rangeWithDots = [];
                    let l;

                    for (let i = 1; i <= last; i++) {
                        if (i === 1 || i === last || (i >= left && i < right)) {
                            range.push(i);
                        }
                    }

                    for (let i of range) {
                        if (l) {
                            if (i - l === 2) {
                                rangeWithDots.push(l + 1);
                            } else if (i - l !== 1) {
                                rangeWithDots.push('...');
                            }
                        }
                        rangeWithDots.push(i);
                        l = i;
                    }

                    return rangeWithDots;
                },

                goToPage(page) {
                    if (page >= 1 && page <= this.totalPages) {
                        this.currentPage = page;
                    }
                },

                get displayedDiagnosticos() {
                    const start = (this.currentPage - 1) * this.perPage;
                    const end = start + this.perPage;
                    return this.filteredDiagnosticos.slice(start, end);
                },

                exportarReporte() {
                    let url = '{{ route($rolePrefix . "historial.reporte") }}?';
                    if (this.empresaFilter) url += 'empresa=' + this.empresaFilter + '&';
                    if (this.estadoFilter) url += 'estado=' + this.estadoFilter + '&';
                    if (this.fechaInicio) url += 'fecha_inicio=' + this.fechaInicio + '&';
                    if (this.fechaFin) url += 'fecha_fin=' + this.fechaFin + '&';
                    window.open(url, '_blank');
                },

                formatDate(dateString) {
                    if (!dateString) return 'N/A';
                    const date = new Date(dateString);
                    const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                    return `${String(date.getDate()).padStart(2, '0')} ${months[date.getMonth()]} ${date.getFullYear()}`;
                },

                formatTime(dateString) {
                    if (!dateString) return '';
                    const date = new Date(dateString);
                    return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                }
            }
        }
    </script>
@endsection
