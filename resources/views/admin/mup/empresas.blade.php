@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/mup.css') }}">
<script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>

<div class="mup-container" x-data="{ 
    showCreateForm: true,
    searchQuery: '',
    marcarTodos: function() {
        const checkboxes = document.querySelectorAll('#permissions-card input[type=checkbox]');
        checkboxes.forEach(c => c.checked = true);
    }
}">
    <header class="mup-topbar">
        <div class="mup-page-title">
            <h1>MUP - Módulo de Usuarios y Perfiles</h1>
            <p>Gestión de entidades, perfiles del sistema y permisos administrativos</p>
        </div>
        <div class="mup-tabs">
            <a href="{{ route('admin.mup.conductores') }}" class="mup-tab">Conductor</a>
            <a href="{{ route('admin.mup.propietarios') }}" class="mup-tab">Propietario</a>
            <a href="{{ route('admin.mup.empresas') }}" class="mup-tab active">Empresas</a>
            <a href="{{ route('admin.mup.usuarios') }}" class="mup-tab">Usuario</a>
        </div>
    </header>

    <div class="mup-content-scroll">
        <div class="stack-layout">
            {{-- SECCIÓN: Listado de Empresas --}}
            <section class="mup-card">
                <div class="mup-card-header-plain">
                    <div>
                        <div class="mup-card-title text-gray-800">Listado de empresas</div>
                        <div class="mup-card-subtitle">Consulta, edita y exporta el listado de empresas registradas en el sistema.</div>
                    </div>
                    <div class="top-list-toolbar">
                        <div class="export-group">
                            <button class="export-btn csv"><iconify-icon icon="lucide:file-text"></iconify-icon> CSV</button>
                            <button class="export-btn excel"><iconify-icon icon="lucide:file-spreadsheet"></iconify-icon> Excel</button>
                            <button class="export-btn pdf"><iconify-icon icon="lucide:file"></iconify-icon> PDF</button>
                        </div>
                        <div class="relative">
                            <input type="text" x-model="searchQuery" placeholder="Buscar por nombre, NIT o correo..." class="pl-10 pr-4 py-2 border rounded-md text-sm w-80 bg-gray-50">
                            <div class="absolute left-3 top-2.5 text-gray-400">
                                <iconify-icon icon="lucide:search"></iconify-icon>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mup-table-wrap">
                    <table class="mup-data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre de empresa</th>
                                <th>NIT</th>
                                <th>Correo Corporativo</th>
                                <th>Estado</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($empresas as $emp)
                            <tr x-show="(searchQuery === '' || '{{ strtolower($emp->razsoem) }}'.includes(searchQuery.toLowerCase()) || '{{ $emp->nonitem }}'.includes(searchQuery))">
                                <td>EMP-{{ str_pad($emp->idemp, 3, '0', STR_PAD_LEFT) }}</td>
                                <td><strong>{{ $emp->razsoem }}</strong></td>
                                <td>{{ $emp->nonitem }}</td>
                                <td>{{ $emp->emaem }}</td>
                                <td>
                                    <span class="mup-state-badge mup-state-active">
                                        <div class="w-2 h-2 rounded-full bg-current"></div>
                                        Activo
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <button class="p-2 bg-blue-50 text-blue-600 rounded-md"><iconify-icon icon="lucide:eye"></iconify-icon></button>
                                        <button class="p-2 bg-orange-50 text-orange-600 rounded-md"><iconify-icon icon="lucide:pencil"></iconify-icon></button>
                                        <button class="p-2 bg-red-50 text-red-600 rounded-md"><iconify-icon icon="lucide:trash-2"></iconify-icon></button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 text-gray-500">No hay empresas registradas.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                {{-- CARD: Nueva Empresa --}}
                <section class="mup-card" x-show="showCreateForm">
                    <div class="mup-card-header-soft">
                        <div class="flex items-center gap-3">
                            <iconify-icon icon="lucide:building-2" class="text-2xl text-[#0d3b5a]"></iconify-icon>
                            <div>
                                <div class="mup-card-title">Nueva empresa</div>
                                <div class="mup-card-subtitle">Registra una ficha corporativa clara, ordenada y fácil de entender, con acceso al sistema.</div>
                            </div>
                        </div>
                        <button @click="showCreateForm = false" class="text-gray-400 hover:text-red-500 transition">
                            <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
                        </button>
                    </div>
                    <div class="mup-card-body">
                        <form action="{{ route('admin.mup.empresas.store') }}" method="POST">
                            @csrf
                            <div class="text-[13px] font-bold text-[#0d3b5a] mb-4 uppercase tracking-wider">Información corporativa</div>
                            <div class="border-b mb-6"></div>
                            
                            <div class="mup-form-grid">
                                <div class="mup-form-group span-2">
                                    <label class="mup-label">Nombre de empresa <span class="mup-required">*</span></label>
                                    <input type="text" name="razsoem" class="mup-input" placeholder="Ej. Transportes Unidos S.A." required value="{{ old('razsoem') }}">
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">NIT <span class="mup-required">*</span></label>
                                    <input type="text" name="nonitem" class="mup-input" placeholder="Ej. 900.123.456-7" required value="{{ old('nonitem') }}">
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Abreviatura</label>
                                    <input type="text" name="abremp" class="mup-input" placeholder="Ej. TUSA" value="{{ old('abremp') }}">
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Dirección</label>
                                    <input type="text" name="direm" class="mup-input" placeholder="Ej. Cra. 45 # 12-34" value="{{ old('direm') }}">
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Ciudad</label>
                                    <input type="text" name="ciudeem" class="mup-input" placeholder="Ej. Medellín" value="{{ old('ciudeem') }}">
                                </div>
                            </div>

                            <div class="text-[13px] font-bold text-[#0d3b5a] mt-8 mb-4 uppercase tracking-wider">Contacto corporativo</div>
                            <div class="border-b mb-6"></div>

                            <div class="mup-form-grid">
                                <div class="mup-form-group">
                                    <label class="mup-label">Nombre Gerente <span class="mup-required">*</span></label>
                                    <input type="text" name="nomger" class="mup-input" placeholder="Ej. Luis Miguel Restrepo" required value="{{ old('nomger') }}">
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Teléfono <span class="mup-required">*</span></label>
                                    <input type="text" name="telem" class="mup-input" placeholder="Ej. 604 123 4567" required value="{{ old('telem') }}">
                                </div>
                                <div class="mup-form-group span-2">
                                    <label class="mup-label">Email de contacto <span class="mup-required">*</span></label>
                                    <input type="email" name="emaem" class="mup-input" placeholder="Ej. contacto@empresa.com" required value="{{ old('emaem') }}">
                                </div>
                            </div>

                            <div class="text-[13px] font-bold text-[#0d3b5a] mt-8 mb-4 uppercase tracking-wider">Acceso al sistema</div>
                            <div class="border-b mb-6"></div>

                            <div class="mup-form-grid">
                                <div class="mup-form-group span-2">
                                    <label class="mup-label">Nombre de usuario <span class="mup-required">*</span></label>
                                    <input type="text" name="username" class="mup-input" placeholder="Ej. transportes.unidos" required value="{{ old('username') }}">
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Contraseña <span class="mup-required">*</span></label>
                                    <input type="password" name="password" class="mup-input" placeholder="********" required>
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Confirmar contraseña <span class="mup-required">*</span></label>
                                    <input type="password" name="password_confirmation" class="mup-input" placeholder="********" required>
                                </div>
                            </div>

                            <div class="mt-4 p-3 bg-blue-50 rounded-md flex items-center gap-3 text-blue-800 text-xs text-balance">
                                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                <span>Ficha corporativa diseñada para ser concisa, mantener la jerarquía visual del CDA y habilitar el acceso seguro al sistema.</span>
                            </div>

                            <div class="mt-8 flex justify-end gap-3 pt-6 border-t">
                                <button type="reset" class="mup-btn mup-btn-outline">Cancelar</button>
                                <button type="submit" class="mup-btn mup-btn-primary">
                                    <iconify-icon icon="lucide:save"></iconify-icon>
                                    Guardar empresa
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                {{-- CARD: Permisos (Sidebar format) --}}
                <section class="mup-card h-full" id="permissions-card">
                    <div class="mup-card-body pt-8">
                        <div class="flex justify-between items-start mb-6 text-balance">
                            <div>
                                <div class="mup-card-title text-lg">Permisos del perfil: Empresas</div>
                                <div class="mup-card-subtitle">Configuración de accesos a módulos corporativos y facturación para empresas aliadas.</div>
                            </div>
                            <button type="button" @click="marcarTodos" class="px-3 py-1 bg-gray-100 text-[#0d3b5a] rounded text-[10px] font-bold">Marcar todos</button>
                        </div>

                        <div class="flex gap-6 border-b mb-6 text-sm font-medium">
                            <div class="text-[#0d3b5a] border-b-2 border-[#0d3b5a] pb-2 cursor-pointer">Módulos</div>
                            <div class="text-gray-400 pb-2 cursor-pointer">Resumen</div>
                        </div>

                        <div class="space-y-4">
                            @php
                                $modulosEmp = [
                                    ['name' => 'Dashboard Corporativo', 'v' => true, 'c' => false, 'e' => false, 'd' => false],
                                    ['name' => 'Flota de vehículos', 'v' => true, 'c' => true, 'e' => true, 'd' => false],
                                    ['name' => 'Historial de servicios', 'v' => true, 'c' => false, 'e' => false, 'd' => false],
                                    ['name' => 'Facturación', 'v' => true, 'c' => false, 'e' => false, 'd' => false],
                                    ['name' => 'Reportes y analítica', 'v' => true, 'c' => true, 'e' => false, 'd' => false],
                                    ['name' => 'Actualización de datos', 'v' => true, 'c' => true, 'e' => true, 'd' => false],
                                ];
                            @endphp
                            <div class="grid grid-cols-5 text-[11px] font-bold text-gray-400 uppercase mb-2">
                                <div class="col-span-1">Módulo</div>
                                <div class="text-center">Ver</div>
                                <div class="text-center">Crear</div>
                                <div class="text-center">Edit</div>
                                <div class="text-center">Elim</div>
                            </div>
                            @foreach($modulosEmp as $mod)
                            <div class="grid grid-cols-5 py-3 border-t items-center">
                                <div class="text-xs font-medium">{{ $mod['name'] }}</div>
                                <div class="flex justify-center"><input type="checkbox" {{ $mod['v'] ? 'checked' : '' }} class="rounded border-gray-300"></div>
                                <div class="text-center">
                                    @if($mod['c']) <input type="checkbox" checked class="rounded border-gray-300"> @else <span class="text-gray-300">-</span> @endif
                                </div>
                                <div class="text-center">
                                    @if($mod['e']) <input type="checkbox" checked class="rounded border-gray-300"> @else <span class="text-gray-300">-</span> @endif
                                </div>
                                <div class="text-center">
                                    @if($mod['d']) <input type="checkbox" checked class="rounded border-gray-300"> @else <span class="text-gray-300">-</span> @endif
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="mt-8 flex justify-end gap-3 pt-6 border-t">
                            <button class="mup-btn mup-btn-outline">Cancelar</button>
                            <button class="mup-btn mup-btn-primary">Guardar cambios</button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
