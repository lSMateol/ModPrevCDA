@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/mup.css') }}">
<script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>

<div class="mup-container" x-data="{ 
    showCreateForm: true,
    selectedProfile: null,
    searchQuery: '',
    filterProfile(id) {
        this.selectedProfile = id;
    }
}">
    <header class="mup-topbar">
        <div class="mup-page-title">
            <h1>MUP - Módulo de Usuarios y Perfiles</h1>
            <p>Gestión de entidades, perfiles del sistema y permisos administrativos</p>
        </div>
        <div class="mup-tabs">
            <a href="{{ route('admin.mup.conductores') }}" class="mup-tab">Conductor</a>
            <a href="#" class="mup-tab">Propietario</a>
            <a href="#" class="mup-tab">Empresas</a>
            <a href="{{ route('admin.mup.usuarios') }}" class="mup-tab active">Usuario</a>
        </div>
    </header>

    <div class="mup-content-scroll">
        <div class="stack-layout">
            {{-- SECCIÓN: Listado de Usuarios --}}
            <section class="mup-card">
                <div class="mup-card-header-plain">
                    <div>
                        <div class="mup-card-title text-gray-800">Listado de usuarios del sistema</div>
                        <div class="mup-card-subtitle">Filtra por rol, estado o documento y exporta el listado correspondiente.</div>
                    </div>
                    <div class="flex items-center gap-4 flex-wrap">
                        <div class="export-group">
                            <button class="export-btn csv"><iconify-icon icon="lucide:file-text"></iconify-icon> CSV</button>
                            <button class="export-btn excel"><iconify-icon icon="lucide:file-spreadsheet"></iconify-icon> Excel</button>
                            <button class="export-btn pdf"><iconify-icon icon="lucide:file"></iconify-icon> PDF</button>
                        </div>
                        <div class="relative">
                            <input type="text" x-model="searchQuery" placeholder="Buscar por nombre, rol o documento..." class="pl-10 pr-4 py-2 border rounded-md text-sm w-80 bg-gray-50">
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
                                <th>Rol</th>
                                <th>Estado</th>
                                <th class="text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usuarios as $user)
                            <tr x-show="(searchQuery === '' || '{{ strtolower($user->name) }}'.includes(searchQuery.toLowerCase()))">
                                <td>USR-{{ str_pad($user->id, 3, '0', STR_PAD_LEFT) }}</td>
                                <td><strong>{{ $user->name }}</strong></td>
                                <td>{{ number_format($user->persona->ndocper ?? 0, 0, ',', '.') }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->persona->perfil->nompef ?? 'Sin Rol' }}</td>
                                <td>
                                    <span class="mup-state-badge {{ ($user->persona->actper ?? 1) ? 'mup-state-active' : 'mup-state-inactive' }}">
                                        <div class="w-2 h-2 rounded-full bg-current"></div>
                                        {{ ($user->persona->actper ?? 1) ? 'Activo' : 'Inactivo' }}
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
                                <td colspan="7" class="text-center py-10 text-gray-500">No hay usuarios registrados.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                {{-- CARD: Nuevo Usuario --}}
                <section class="mup-card" x-show="showCreateForm">
                    <div class="mup-card-header-soft">
                        <div class="flex items-center gap-3">
                            <iconify-icon icon="lucide:user-round-plus" class="text-2xl text-[#0d3b5a]"></iconify-icon>
                            <div>
                                <div class="mup-card-title">Nuevo usuario</div>
                                <div class="mup-card-subtitle">Crea un nuevo usuario en el sistema y asígnale su rol operativo.</div>
                            </div>
                        </div>
                        <button @click="showCreateForm = false" class="text-gray-400 hover:text-red-500 transition">
                            <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
                        </button>
                    </div>
                    <div class="mup-card-body">
                        <form action="{{ route('admin.mup.usuarios.store') }}" method="POST">
                            @csrf
                            <div class="text-[13px] font-bold text-[#0d3b5a] mb-4 uppercase tracking-wider">Información personal</div>
                            <div class="border-b mb-6"></div>
                            
                            <div class="mup-form-grid">
                                <div class="mup-form-group span-2">
                                    <label class="mup-label">Nombre completo <span class="mup-required">*</span></label>
                                    <input type="text" name="nombre_completo" class="mup-input" placeholder="Ej. Juan Pérez" required value="{{ old('nombre_completo') }}">
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
                                    <label class="mup-label">Número de documento <span class="mup-required">*</span></label>
                                    <input type="number" name="ndocper" class="mup-input" placeholder="Ej. 12345678" required value="{{ old('ndocper') }}">
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Correo electrónico <span class="mup-required">*</span></label>
                                    <input type="email" name="emaper" class="mup-input" placeholder="Ej. correo@ejemplo.com" required value="{{ old('emaper') }}">
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Teléfono</label>
                                    <input type="text" name="telper" class="mup-input" placeholder="Ej. 300 123 4567" value="{{ old('telper') }}">
                                </div>
                            </div>

                            <div class="text-[13px] font-bold text-[#0d3b5a] mt-8 mb-4 uppercase tracking-wider">Acceso al sistema</div>
                            <div class="border-b mb-6"></div>

                            <div class="mup-form-grid">
                                <div class="mup-form-group span-2">
                                    <label class="mup-label">Nombre de usuario <span class="mup-required">*</span></label>
                                    <input type="text" name="username" class="mup-input" placeholder="Ej. juan.perez" required value="{{ old('username') }}">
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Contraseña <span class="mup-required">*</span></label>
                                    <input type="password" name="password" class="mup-input" placeholder="Min. 6 caracteres" required>
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Confirmar contraseña <span class="mup-required">*</span></label>
                                    <input type="password" name="password_confirmation" class="mup-input" required>
                                </div>
                            </div>

                            <div class="text-[13px] font-bold text-[#0d3b5a] mt-8 mb-4 uppercase tracking-wider">Rol y asignación</div>
                            <div class="border-b mb-6"></div>

                            <div class="mup-form-grid">
                                <div class="mup-form-group">
                                    <label class="mup-label">Rol / perfil <span class="mup-required">*</span></label>
                                    <select name="idpef" class="mup-input" required>
                                        @foreach($perfiles as $perf)
                                            <option value="{{ $perf->idpef }}">{{ $perf->nompef }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Empresa asociada</label>
                                    <select name="idemp" class="mup-input">
                                        <option value="">Seleccionar empresa...</option>
                                        @foreach($empresas as $emp)
                                            <option value="{{ $emp->idemp }}">{{ $emp->razsoem }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4 p-3 bg-amber-50 rounded-md flex items-center gap-3 text-amber-800 text-xs">
                                <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                                <span>Este formulario aplica para perfiles distintos a conductor, propietario y empresas.</span>
                            </div>

                            <div class="mt-8 flex justify-end gap-3 pt-6 border-t">
                                <button type="reset" class="mup-btn mup-btn-outline">Cancelar</button>
                                <button type="submit" class="mup-btn mup-btn-primary">
                                    <iconify-icon icon="lucide:save"></iconify-icon>
                                    Guardar usuario
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                {{-- CARD: Permisos (Sidebar format) --}}
                <section class="mup-card h-full">
                    <div class="mup-card-body pt-8">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <div class="mup-card-title text-lg">Permisos del perfil: Administrador</div>
                                <div class="mup-card-subtitle">Visible para superadministradores. Define qué puede hacer este perfil en cada módulo.</div>
                            </div>
                            <button class="text-gray-400"><iconify-icon icon="lucide:x" class="text-xl"></iconify-icon></button>
                        </div>

                        <div class="flex gap-6 border-b mb-6 text-sm font-medium">
                            <div class="text-[#0d3b5a] border-b-2 border-[#0d3b5a] pb-2 cursor-pointer">Módulos</div>
                            <div class="text-gray-400 pb-2 cursor-pointer">Resumen</div>
                        </div>

                        <div class="space-y-4">
                            @php
                                $modulosPerm = ['Dashboard', 'Vehículos', 'Actores', 'Diagnóstico', 'Detalle diagnóstico', 'Rechazados', 'Historial', 'Alertas'];
                            @endphp
                            <div class="grid grid-cols-5 text-[11px] font-bold text-gray-400 uppercase mb-2">
                                <div class="col-span-1">Módulo</div>
                                <div class="text-center">Ver</div>
                                <div class="text-center">Crear</div>
                                <div class="text-center">Edit</div>
                                <div class="text-center">Elim</div>
                            </div>
                            @foreach($modulosPerm as $mod)
                            <div class="grid grid-cols-5 py-3 border-t items-center">
                                <div class="text-sm font-medium">{{ $mod }}</div>
                                <div class="flex justify-center"><iconify-icon icon="lucide:check" class="text-white bg-[#0d3b5a] rounded p-0.5 text-[10px]"></iconify-icon></div>
                                <div class="text-center">
                                    @if(in_array($mod, ['Vehículos', 'Actores', 'Diagnóstico', 'Detalle diagnóstico', 'Alertas']))
                                        <iconify-icon icon="lucide:check" class="text-white bg-[#0d3b5a] rounded p-0.5 text-[10px]"></iconify-icon>
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </div>
                                <div class="text-center">
                                    @if(in_array($mod, ['Vehículos', 'Actores', 'Diagnóstico', 'Detalle diagnóstico']))
                                        <iconify-icon icon="lucide:check" class="text-white bg-[#0d3b5a] rounded p-0.5 text-[10px]"></iconify-icon>
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </div>
                                <div class="text-center text-gray-300">
                                    @if(in_array($mod, ['Vehículos', 'Actores', 'Diagnóstico', 'Detalle diagnóstico']))
                                        <div class="w-4 h-4 rounded border border-gray-200 mx-auto"></div>
                                    @else
                                        -
                                    @endif
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

            {{-- SECCIÓN: Consulta dinámica de perfiles --}}
            <section class="mup-card">
                <div class="mup-card-header-plain">
                    <div>
                        <div class="mup-card-title">Consulta dinámica de perfiles</div>
                        <div class="mup-card-subtitle">Visualiza, filtra y exporta todos los roles activos del sistema. Se elimina el rol de auditor por empresa.</div>
                    </div>
                    <a href="{{ route('admin.mup.perfil.nuevo') }}" class="mup-btn mup-btn-primary h-10">
                        <iconify-icon icon="lucide:plus"></iconify-icon>
                        Nuevo perfil
                    </a>
                </div>
                <div class="mup-card-body">
                    <div class="flex gap-3 mb-6 flex-wrap">
                        <button class="px-4 py-2 rounded-full text-sm font-bold bg-[#0d3b5a] text-white">Todos</button>
                        <button class="px-4 py-2 rounded-full text-sm font-bold bg-[#f1f7fb] text-[#0d3b5a]">Administrativos</button>
                        <button class="px-4 py-2 rounded-full text-sm font-bold bg-[#f1f7fb] text-[#0d3b5a]">Operativos</button>
                        <button class="px-4 py-2 rounded-full text-sm font-bold bg-[#f1f7fb] text-[#0d3b5a]">Externos</button>
                        <button class="px-4 py-2 rounded-full text-sm font-bold bg-[#f1f7fb] text-[#0d3b5a]">Activos</button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach($perfiles as $perf)
                        <div class="bg-gray-50 border rounded-xl p-5 flex flex-col gap-4">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="font-bold text-gray-800">{{ $perf->nompef }}</div>
                                    <div class="text-xs text-gray-500 mt-1 line-clamp-2">
                                        {{ $perf->des_pef ?? 'Perfil configurado para la operación del CDA.' }}
                                    </div>
                                </div>
                                <div class="text-lg font-bold text-[#0d3b5a]">{{ $perf->personas_count }}</div>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="bg-green-50 text-green-600 px-3 py-1 rounded-full font-bold">Activo</span>
                                <span class="text-gray-400 font-bold">8 módulos</span>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-8 flex justify-between items-center text-xs text-gray-400 border-t pt-4">
                        <div>Consulta dinámica con todos los perfiles visibles del sistema</div>
                        <div>Exporta el resultado filtrado en CSV, Excel o PDF</div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
