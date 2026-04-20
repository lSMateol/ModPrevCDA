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
            <a href="{{ route('admin.mup.conductores.index') }}" class="mup-tab">Conductor</a>
            <a href="{{ route('admin.mup.propietarios.index') }}" class="mup-tab active">Propietario</a>
            <a href="{{ route('admin.mup.empresas.index') }}" class="mup-tab">Empresas</a>
            <a href="{{ route('admin.mup.usuarios.index') }}" class="mup-tab">Usuario</a>
        </div>
    </header>

    <div class="mup-content-scroll">
        <div class="stack-layout">
            {{-- SECCIÓN: Listado de Propietarios --}}
            <section class="mup-card">
                <div class="mup-card-header-plain">
                    <div>
                        <div class="mup-card-title text-gray-800">Listado de propietarios</div>
                        <div class="mup-card-subtitle">Consulta, edita y exporta el listado de propietarios registrados en el sistema.</div>
                    </div>
                    <div class="flex items-center gap-4 flex-wrap">
                        <div class="export-group">
                            <button class="export-btn csv"><iconify-icon icon="lucide:file-text"></iconify-icon> CSV</button>
                            <button class="export-btn excel"><iconify-icon icon="lucide:file-spreadsheet"></iconify-icon> Excel</button>
                            <button class="export-btn pdf"><iconify-icon icon="lucide:file"></iconify-icon> PDF</button>
                        </div>
                        <div class="relative">
                            <input type="text" x-model="searchQuery" placeholder="Buscar por nombre o documento..." class="pl-10 pr-4 py-2 border rounded-md text-sm w-80 bg-gray-50">
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
                                <th>Nombre</th>
                                <th>Documento</th>
                                <th>Correo</th>
                                <th>Estado</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($propietarios as $prop)
                            <tr x-show="(searchQuery === '' || '{{ strtolower($prop->nomper.' '.$prop->apeper) }}'.includes(searchQuery.toLowerCase()))">
                                <td>PRO-{{ str_pad($prop->idper, 3, '0', STR_PAD_LEFT) }}</td>
                                <td><strong>{{ $prop->nomper }} {{ $prop->apeper }}</strong></td>
                                <td>{{ number_format($prop->ndocper, 0, ',', '.') }}</td>
                                <td>{{ $prop->emaper }}</td>
                                <td>
                                    <span class="mup-state-badge {{ ($prop->actper) ? 'mup-state-active' : 'mup-state-inactive' }}">
                                        <div class="w-2 h-2 rounded-full bg-current"></div>
                                        {{ ($prop->actper) ? 'Activo' : 'Inactivo' }}
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
                                <td colspan="6" class="text-center py-10 text-gray-500">No hay propietarios registrados.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                {{-- CARD: Nuevo Propietario --}}
                <section class="mup-card" x-show="showCreateForm">
                    <div class="mup-card-header-soft">
                        <div class="flex items-center gap-3">
                            <iconify-icon icon="lucide:user-round-plus" class="text-2xl text-[#0d3b5a]"></iconify-icon>
                            <div>
                                <div class="mup-card-title">Nuevo propietario</div>
                                <div class="mup-card-subtitle">Registra un nuevo propietario con información personal y datos operativos.</div>
                            </div>
                        </div>
                        <button @click="showCreateForm = false" class="text-gray-400 hover:text-red-500 transition">
                            <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
                        </button>
                    </div>
                    <div class="mup-card-body">
                        <form action="{{ route('admin.mup.propietarios.store') }}" method="POST">
                            @csrf
                            <div class="text-[13px] font-bold text-[#0d3b5a] mb-4 uppercase tracking-wider">Información personal</div>
                            <div class="border-b mb-6"></div>
                            
                            <div class="mup-form-grid">
                                <div class="mup-form-group span-2">
                                    <label class="mup-label">Nombre completo <span class="mup-required">*</span></label>
                                    <input type="text" name="nombre_completo" class="mup-input" placeholder="Ej. Carlos Martínez" required value="{{ old('nombre_completo') }}">
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Tipo de documento <span class="mup-required">*</span></label>
                                    <select name="tdocper" class="mup-input" required>
                                        @foreach($tiposDoc as $tipo)
                                            <option value="{{ $tipo->idval }}">{{ $tipo->nomval }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">No. Documento <span class="mup-required">*</span></label>
                                    <input type="number" name="ndocper" class="mup-input" placeholder="Ej. 79123456" required value="{{ old('ndocper') }}">
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Dirección</label>
                                    <input type="text" name="dirper" class="mup-input" placeholder="Ej. Calle 123" value="{{ old('dirper') }}">
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Ciudad</label>
                                    <input type="text" name="ciuper" class="mup-input" placeholder="Ej. Bogotá" value="{{ old('ciuper') }}">
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Teléfono</label>
                                    <input type="text" name="telper" class="mup-input" placeholder="Ej. 300 123 4567" value="{{ old('telper') }}">
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">E-mail <span class="mup-required">*</span></label>
                                    <input type="email" name="emaper" class="mup-input" placeholder="Ej. correo@ejemplo.com" required value="{{ old('emaper') }}">
                                </div>
                            </div>

                            <div class="text-[13px] font-bold text-[#0d3b5a] mt-8 mb-4 uppercase tracking-wider">Datos operativos</div>
                            <div class="border-b mb-6"></div>

                            <div class="mup-form-grid">
                                <div class="mup-form-group">
                                    <label class="mup-label">Categoría del pase <span class="mup-required">*</span></label>
                                    <select name="catcon" class="mup-input" required>
                                        @foreach($categorias as $cat)
                                            <option value="{{ $cat->idval }}">{{ $cat->nomval }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">No. licencia de tránsito <span class="mup-required">*</span></label>
                                    <input type="text" name="nliccon" class="mup-input" placeholder="Ej. 12345678" required value="{{ old('nliccon') }}">
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Fecha vencimiento licencia <span class="mup-required">*</span></label>
                                    <input type="date" name="fvencon" class="mup-input" required value="{{ old('fvencon') }}">
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Activo (SI/NO) <span class="mup-required">*</span></label>
                                    <select name="actper" class="mup-input">
                                        <option value="1">SI</option>
                                        <option value="0">NO</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4 p-3 bg-blue-50 rounded-md flex items-center gap-3 text-blue-800 text-xs">
                                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                <span>Ficha de propietario organizada por bloques para facilitar el registro operativo.</span>
                            </div>

                            <div class="mt-8 flex justify-end gap-3 pt-6 border-t">
                                <button type="reset" class="mup-btn mup-btn-outline">Cancelar</button>
                                <button type="submit" class="mup-btn mup-btn-primary">
                                    <iconify-icon icon="lucide:save"></iconify-icon>
                                    Guardar propietario
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                {{-- CARD: Permisos (Sidebar format) --}}
                <section class="mup-card h-full" id="permissions-card">
                    <div class="mup-card-body pt-8">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <div class="mup-card-title text-lg">Permisos del perfil: Propietarios</div>
                                <div class="mup-card-subtitle">Configuración específica para el perfil propietario dentro del sistema del CDA.</div>
                            </div>
                            <button type="button" @click="marcarTodos" class="px-3 py-1 bg-gray-100 text-[#0d3b5a] rounded text-[10px] font-bold">Marcar todos</button>
                        </div>

                        <div class="flex gap-6 border-b mb-6 text-sm font-medium">
                            <div class="text-[#0d3b5a] border-b-2 border-[#0d3b5a] pb-2 cursor-pointer">Módulos</div>
                            <div class="text-gray-400 pb-2 cursor-pointer">Resumen</div>
                        </div>

                        <div class="space-y-4">
                            @php
                                $modulosProp = ['Dashboard', 'Agenda de revisión', 'Historial de vehículos', 'Documentos asociados', 'Alertas de vencimiento', 'Actualización de datos'];
                            @endphp
                            <div class="grid grid-cols-5 text-[11px] font-bold text-gray-400 uppercase mb-2">
                                <div class="col-span-1">Módulo</div>
                                <div class="text-center">Ver</div>
                                <div class="text-center">Crear</div>
                                <div class="text-center">Edit</div>
                                <div class="text-center">Elim</div>
                            </div>
                            @foreach($modulosProp as $mod)
                            <div class="grid grid-cols-5 py-3 border-t items-center">
                                <div class="text-sm font-medium">{{ $mod }}</div>
                                <div class="flex justify-center"><input type="checkbox" checked class="rounded border-gray-300"></div>
                                <div class="text-center">
                                    @if(in_array($mod, ['Documentos asociados', 'Actualización de datos']))
                                        <input type="checkbox" checked class="rounded border-gray-300">
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </div>
                                <div class="text-center">
                                    @if(in_array($mod, ['Actualización de datos']))
                                        <input type="checkbox" checked class="rounded border-gray-300">
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </div>
                                <div class="text-center text-gray-300">-</div>
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
