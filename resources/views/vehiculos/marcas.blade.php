@extends('layouts.app')

@section('content')
    <div id="marcas-wrapper" x-data="marcasUI()">
        <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>

        <style>
            #marcas-wrapper {
                --background: #f6f7f9;
                --foreground: #0f1724;
                --border: #00000014;
                --input: #ffffff;
                --primary: #0b3b5a;
                --primary-foreground: #ffffff;
                --muted: #f0f2f4;
                --muted-foreground: #9aa6b2;
                --success: #1ea45a;
                --card: #ffffff;
                --radius-md: 6px;
                --radius-lg: 8px;
                font-family: 'Inter', system-ui, sans-serif;
                background-color: var(--background);
                color: var(--foreground);
                min-height: calc(100vh - 65px);
            }

            #marcas-wrapper .main-content { padding: 40px; display: flex; flex-direction: column; gap: 24px; }
            #marcas-wrapper .page-header { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; }
            #marcas-wrapper .page-title { margin: 0; font-size: 24px; font-weight: 700; color: var(--foreground); }
            #marcas-wrapper .page-subtitle { margin: 0; font-size: 14px; color: #6b7280; margin-top: 4px; }
            
            #marcas-wrapper .content-grid { display: grid; grid-template-columns: minmax(0, 1.7fr) 380px; gap: 24px; align-items: start; }
            @media (max-width: 1024px) {
                #marcas-wrapper .content-grid { grid-template-columns: 1fr; }
            }

            #marcas-wrapper .card { background: var(--card); border-radius: var(--radius-lg); box-shadow: 0 12px 30px rgba(11, 59, 90, 0.08); border: 1px solid var(--border); overflow: hidden; }
            #marcas-wrapper .toolbar-card { padding: 20px; margin-bottom: 24px; }
            #marcas-wrapper .toolbar { display: flex; gap: 12px; align-items: center; }
            #marcas-wrapper .search-box { background: var(--input); border: 1px solid var(--border); border-radius: var(--radius-md); font-size: 14px; flex: 1; display: flex; align-items: center; gap: 10px; height: 42px; padding: 0 14px; color: var(--muted-foreground); }
            #marcas-wrapper .search-box input { border: none; outline: none; background: transparent; width: 100%; color: var(--foreground); padding: 0; }
            #marcas-wrapper .search-box input:focus { outline: none; box-shadow: none; }
            
            #marcas-wrapper .btn { height: 42px; padding: 0 16px; border-radius: var(--radius-md); border: none; display: inline-flex; align-items: center; justify-content: center; gap: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: 0.2s; }
            #marcas-wrapper .btn-primary { background: var(--primary); color: var(--primary-foreground); box-shadow: 0 10px 24px rgba(11, 59, 90, 0.16); }
            #marcas-wrapper .btn-secondary { background: var(--muted); color: var(--foreground); }
            
            #marcas-wrapper .table-card { padding: 0; overflow-x: auto; }
            #marcas-wrapper table { width: 100%; border-collapse: collapse; }
            #marcas-wrapper th, #marcas-wrapper td { text-align: left; padding: 16px 20px; font-size: 14px; }
            #marcas-wrapper th { color: #6b7280; font-weight: 600; border-bottom: 1px solid var(--border); background-color: #f9fafb; text-transform: uppercase; font-size: 12px;}
            #marcas-wrapper td { color: var(--foreground); font-weight: 500; border-bottom: 1px solid var(--border); }
            #marcas-wrapper tbody tr { cursor: pointer; transition: background-color 0.2s; }
            #marcas-wrapper tbody tr:hover { background-color: #f9fafb; }
            #marcas-wrapper tbody tr:last-child td { border-bottom: none; }

            #marcas-wrapper .actions { display: flex; justify-content: flex-end; gap: 8px; }
            #marcas-wrapper .btn-icon { width: 34px; height: 34px; border-radius: 4px; border: 1px solid var(--border); background: var(--card); color: var(--foreground); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; }
            #marcas-wrapper .btn-icon:hover { background: #e5e7eb; }

            #marcas-wrapper .detail-card { padding: 24px; display: flex; flex-direction: column; gap: 20px; }
            #marcas-wrapper .detail-title { margin: 0; font-size: 18px; font-weight: 700; color: var(--foreground); }
            #marcas-wrapper .form-grid { display: grid; grid-template-columns: 1fr; gap: 16px; }
            #marcas-wrapper .field { display: flex; flex-direction: column; gap: 8px; }
            #marcas-wrapper .field label { font-size: 13px; font-weight: 600; color: #6b7280; }
            #marcas-wrapper .input-box { background: var(--input); border: 1px solid var(--border); border-radius: var(--radius-md); font-size: 14px; height: 42px; display: flex; align-items: center; padding: 0 14px; color: var(--foreground); }
            #marcas-wrapper .readonly { background: var(--muted); color: var(--foreground); }
            #marcas-wrapper .footer-actions { display: flex; justify-content: flex-end; gap: 12px; padding-top: 16px; border-top: 1px solid var(--border); margin-top: 8px; }
        </style>

        <main class="main-content">
            <header class="page-header">
                <div>
                    <h1 class="page-title">Marcas de Vehículos</h1>
                    <p class="page-subtitle">Gestión del catálogo de marcas en el sistema</p>
                </div>
                @if(!auth()->user()->hasRole('Empresa'))
                    <button class="btn btn-primary" @click="openNewMarca()">
                        <iconify-icon icon="lucide:plus"></iconify-icon>
                        <span>Nueva marca</span>
                    </button>
                @endif
            </header>

            <div class="content-grid">
                <!-- GRID LEFT -->
                <section>
                    <div class="card toolbar-card">
                        <div class="toolbar">
                            <div class="search-box">
                                <iconify-icon icon="lucide:search"></iconify-icon>
                                <input type="text" x-model="searchQuery" @input="currentPage = 1" placeholder="Buscar por nombre de marca o ID...">
                            </div>
                        </div>
                    </div>

                    <div class="card table-card">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 80px">ID</th>
                                    <th>Nombre (Marca / Línea)</th>
                                    <th>Jerarquía / Marca Padre</th>
                                    <th>Vehículos en Sistema</th>
                                    <th style="text-align: right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-if="filteredMarcas.length === 0">
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 32px; color: #6b7280;">No se encontraron marcas.</td>
                                    </tr>
                                </template>
                                <template x-for="marca in paginatedMarcas" :key="marca.idmar">
                                    <tr @click="selectMarca(marca, 'view')" :style="selectedMarca?.idmar === marca.idmar ? 'background-color: #f0fdf4;' : ''">
                                        <td><strong style="font-family: monospace;" x-text="marca.idmar"></strong></td>
                                        <td>
                                            <span style="font-weight: 500;" x-text="marca.nommarlin"></span>
                                        </td>
                                        <td>
                                            <template x-if="!marca.depmar || marca.depmar == 0">
                                                <span style="background: #ede9fe; color: #5b21b6; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600;">MARCA RAÍZ</span>
                                            </template>
                                            <template x-if="marca.depmar && marca.depmar != 0">
                                                <div>
                                                    <span style="background: #e0f2fe; color: #0284c7; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600;">LÍNEA</span>
                                                    <span style="font-size: 13px; color: #6b7280; margin-left: 6px;" x-text="'de ' + getMarcaName(marca.depmar)"></span>
                                                </div>
                                            </template>
                                        </td>
                                        <td>
                                            <span style="font-weight: 600; color: var(--primary);" x-text="marca.vehiculos_count + ' registros'"></span>
                                        </td>
                                        <td>
                                            <div class="actions">
                                                <button class="btn-icon" title="Ver Detalle" @click.stop="selectMarca(marca, 'view')">
                                                    <iconify-icon icon="lucide:eye"></iconify-icon>
                                                </button>
                                                @if(!auth()->user()->hasRole('Empresa'))
                                                    <button class="btn-icon" title="Editar" @click.stop="selectMarca(marca, 'edit')">
                                                        <iconify-icon icon="lucide:edit-2"></iconify-icon>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>

                        <!-- Paginador Estético -->
                        <div style="padding: 16px 20px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: #fff;">
                            <div style="font-size: 13px; color: #6b7280;">
                                Mostrando <strong style="color: var(--foreground);" x-text="filteredMarcas.length > 0 ? ((currentPage - 1) * itemsPerPage) + 1 : 0"></strong> a 
                                <strong style="color: var(--foreground);" x-text="Math.min(currentPage * itemsPerPage, filteredMarcas.length)"></strong> 
                                de <strong style="color: var(--foreground);" x-text="filteredMarcas.length"></strong> registros
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <button class="btn btn-secondary" style="height: 32px; padding: 0 12px; font-size: 13px; background: var(--card); border: 1px solid var(--border);" @click="currentPage > 1 ? currentPage-- : null" :disabled="currentPage === 1" :style="currentPage === 1 ? 'opacity: 0.5; cursor: not-allowed;' : ''">
                                    <iconify-icon icon="lucide:chevron-left"></iconify-icon> Anterior
                                </button>
                                <button class="btn btn-secondary" style="height: 32px; padding: 0 12px; font-size: 13px; background: var(--card); border: 1px solid var(--border);" @click="currentPage < totalPages ? currentPage++ : null" :disabled="currentPage === totalPages" :style="currentPage === totalPages ? 'opacity: 0.5; cursor: not-allowed;' : ''">
                                    Siguiente <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- DETAIL RIGHT (ONLY VISIBLE WHEN SELECTED/NEW) -->
                <aside class="card detail-card" x-show="selectedMarca || isNew">
                    <h3 class="detail-title" x-text="isNew ? 'Nueva Marca' : 'Detalle de Marca'"></h3>
                    
                    <div class="form-grid">
                        <template x-if="!isNew">
                            <div class="field">
                                <label>Código P.K. (ID)</label>
                                <div class="input-box readonly" style="font-family: monospace;" x-text="form.idmar"></div>
                            </div>
                        </template>

                        <div class="field">
                            <label>Nombre de Marca o Línea <span style="color:red">*</span></label>
                            @if(auth()->user()->hasRole('Empresa'))
                                <div class="input-box readonly" x-text="form.nommarlin"></div>
                            @else
                                <div class="input-box readonly" x-text="form.nommarlin" x-show="!isEdit && !isNew"></div>
                                <input type="text" class="input-box" style="width: 100%; border: 1px solid var(--border);" x-model="form.nommarlin" placeholder="Ej. Chevrolet" x-show="isEdit || isNew">
                            @endif
                        </div>

                        <div class="field">
                            <label>Dependencia (ID Marca Padre)</label>
                            @if(auth()->user()->hasRole('Empresa'))
                                <div class="input-box readonly" x-text="form.depmar && form.depmar != 0 ? (form.depmar + ' - ' + getMarcaName(form.depmar)) : 'Es una Marca Principal Independiente'"></div>
                            @else
                                <div class="input-box readonly" x-text="form.depmar && form.depmar != 0 ? (form.depmar + ' - ' + getMarcaName(form.depmar)) : 'Es una Marca Principal Independiente'" x-show="!isEdit && !isNew"></div>
                                <select class="input-box" style="width: 100%; border: 1px solid var(--border);" x-model="form.depmar" x-show="isEdit || isNew">
                                    <option value="0">⭐ Es una Marca Principal Independiente</option>
                                    <template x-for="mRoot in marcas.filter(m => !m.depmar || m.depmar == 0)" :key="'root-'+mRoot.idmar">
                                        <option :value="mRoot.idmar" x-text="mRoot.idmar + ' - ' + mRoot.nommarlin"></option>
                                    </template>
                                </select>
                            @endif
                        </div>

                        <template x-if="!isNew">
                            <div class="field">
                                <label>Total Vehículos</label>
                                <div class="input-box readonly" x-text="(selectedMarca?.vehiculos_count || 0) + ' vehículos vinculados'"></div>
                            </div>
                        </template>
                    </div>

                    <div class="footer-actions">
                        <button class="btn btn-secondary" @click="closePanel()">Cerrar</button>
                        @if(!auth()->user()->hasRole('Empresa'))
                            <button class="btn btn-primary" x-text="isNew ? 'Registrar Marca' : 'Guardar Cambios'" x-show="isEdit || isNew"></button>
                        @endif
                    </div>
                </aside>

            </div>
        </main>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('marcasUI', () => ({
                marcas: @json($marcas),
                searchQuery: '',
                currentPage: 1,
                itemsPerPage: 10,
                selectedMarca: null,
                isNew: false,
                isEdit: false,
                form: {
                    idmar: '',
                    nommarlin: '',
                    depmar: ''
                },

                get filteredMarcas() {
                    if (this.searchQuery === '') return this.marcas;
                    let q = this.searchQuery.toLowerCase();
                    return this.marcas.filter(m => 
                        (m.nommarlin && m.nommarlin.toLowerCase().includes(q)) || 
                        (m.idmar && m.idmar.toString().includes(q))
                    );
                },

                get totalPages() {
                   return Math.ceil(this.filteredMarcas.length / this.itemsPerPage) || 1;
                },

                get paginatedMarcas() {
                    let start = (this.currentPage - 1) * this.itemsPerPage;
                    return this.filteredMarcas.slice(start, start + this.itemsPerPage);
                },

                getMarcaName(id) {
                    if (!id || id == 0) return 'Raíz';
                    const parent = this.marcas.find(m => m.idmar == id);
                    return parent ? parent.nommarlin : ('Desconocido ('+id+')');
                },

                selectMarca(marca, mode = 'view') {
                    this.isNew = false;
                    this.isEdit = (mode === 'edit');
                    this.selectedMarca = marca;
                    this.form = {
                        idmar: marca.idmar,
                        nommarlin: marca.nommarlin,
                        depmar: marca.depmar
                    };
                },

                openNewMarca() {
                    this.selectedMarca = null;
                    this.isNew = true;
                    this.isEdit = true;
                    this.form = {
                        idmar: '',
                        nommarlin: '',
                        depmar: '0'
                    };
                },

                closePanel() {
                    this.selectedMarca = null;
                    this.isNew = false;
                    this.isEdit = false;
                }
            }));
        });
    </script>
@endsection
