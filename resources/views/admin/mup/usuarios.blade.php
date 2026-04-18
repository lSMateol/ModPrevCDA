@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/mup.css') }}">
<script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>

<div class="mup-container" x-data="{ 
    showCreateForm: true,
    selectedProfile: null,
    searchQuery: '',
    viewModal: false,
    editModal: false,
    deleteModal: false,
    selectedUser: null,
    showPass: false,
    showConfirmPass: false,
    // Validation for New User
    password: '',
    password_confirmation: '',
    get passwordsMatch() {
        if (!this.password || !this.password_confirmation) return true;
        return this.password === this.password_confirmation;
    },
    filterProfile(id) {
        this.selectedProfile = id;
    },
    openModal(action, user) {
        this.selectedUser = user;
        this.showPass = false;
        this.showConfirmPass = false;
        if (action === 'view') this.viewModal = true;
        if (action === 'edit') this.editModal = true;
        if (action === 'delete') this.deleteModal = true;
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
            <a href="{{ route('admin.mup.empresas') }}" class="mup-tab">Empresas</a>
            <a href="{{ route('admin.mup.usuarios') }}" class="mup-tab active">Usuario</a>
        </div>
    </header>

    <div class="mup-content-scroll">
        {{-- SECCIÓN ALERTA GLOBAL --}}
        @if(session('success') || session('error') || $errors->any())
        <div class="px-6 pt-4">
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4 rounded-r-md shadow-sm flex items-center gap-3">
                    <iconify-icon icon="lucide:check-circle" class="text-green-500 text-xl"></iconify-icon>
                    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4 rounded-r-md shadow-sm flex items-center gap-3">
                    <iconify-icon icon="lucide:alert-circle" class="text-red-500 text-xl"></iconify-icon>
                    <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-orange-50 border-l-4 border-orange-400 p-4 mb-4 rounded-r-md shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <iconify-icon icon="lucide:alert-triangle" class="text-orange-500 text-xl"></iconify-icon>
                        <p class="text-sm text-orange-700 font-bold">Por favor corrige los siguientes errores:</p>
                    </div>
                    <ul class="list-disc list-inside text-xs text-orange-600 space-y-1 ml-8">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
        @endif

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
                                        <button @click="openModal('view', {
                                            id: '{{ $user->id }}',
                                            name: '{{ $user->name }}',
                                            username: '{{ $user->username }}',
                                            email: '{{ $user->email }}',
                                            ndocper: '{{ $user->persona->ndocper ?? 0 }}',
                                            tdocper: '{{ $user->persona->tdocper ?? "" }}',
                                            telper: '{{ $user->persona->telper ?? "" }}',
                                            actper: '{{ $user->persona->actper ?? 1 }}',
                                            nompef: '{{ $user->persona->perfil->nompef ?? "Sin Rol" }}',
                                            idpef: '{{ $user->persona->idpef ?? "" }}',
                                            idemp: '{{ $user->idemp ?? "" }}',
                                            empresa: '{{ $user->empresa->razsoem ?? "Particular" }}'
                                        })" class="p-2 bg-blue-50 text-blue-600 rounded-md hover:bg-blue-100 transition"><iconify-icon icon="lucide:eye"></iconify-icon></button>
                                        
                                        <button @click="openModal('edit', {
                                            id: '{{ $user->id }}',
                                            name: '{{ $user->name }}',
                                            username: '{{ $user->username }}',
                                            email: '{{ $user->email }}',
                                            ndocper: '{{ $user->persona->ndocper ?? 0 }}',
                                            tdocper: '{{ $user->persona->tdocper ?? "" }}',
                                            telper: '{{ $user->persona->telper ?? "" }}',
                                            actper: '{{ $user->persona->actper ?? 1 }}',
                                            idpef: '{{ $user->persona->idpef ?? "" }}',
                                            idemp: '{{ $user->idemp ?? "" }}'
                                        })" class="p-2 bg-orange-50 text-orange-600 rounded-md hover:bg-orange-100 transition"><iconify-icon icon="lucide:pencil"></iconify-icon></button>
                                        
                                        <button @click="openModal('delete', {
                                            id: '{{ $user->id }}',
                                            name: '{{ $user->name }}'
                                        })" class="p-2 bg-red-50 text-red-600 rounded-md hover:bg-red-100 transition"><iconify-icon icon="lucide:trash-2"></iconify-icon></button>
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
                                    <div class="relative">
                                        <input :type="showPass ? 'text' : 'password'" name="password" x-model="password" class="mup-input pr-10" placeholder="Min. 6 caracteres" required>
                                        <button type="button" @click="showPass = !showPass" class="absolute right-3 top-2.5 text-gray-400 hover:text-[#0d3b5a] transition">
                                            <iconify-icon :icon="showPass ? 'lucide:eye-off' : 'lucide:eye'"></iconify-icon>
                                        </button>
                                    </div>
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Confirmar contraseña <span class="mup-required">*</span></label>
                                    <div class="relative">
                                        <input :type="showConfirmPass ? 'text' : 'password'" name="password_confirmation" x-model="password_confirmation" class="mup-input pr-10" required :class="!passwordsMatch ? 'border-red-500 ring-1 ring-red-500' : ''">
                                        <button type="button" @click="showConfirmPass = !showConfirmPass" class="absolute right-3 top-2.5 text-gray-400 hover:text-[#0d3b5a] transition">
                                            <iconify-icon :icon="showConfirmPass ? 'lucide:eye-off' : 'lucide:eye'"></iconify-icon>
                                        </button>
                                    </div>
                                    <template x-if="!passwordsMatch">
                                        <p class="text-[10px] text-red-500 mt-1 font-bold italic animate-pulse">Las contraseñas no coinciden</p>
                                    </template>
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
                                <button type="submit" class="mup-btn mup-btn-primary" :disabled="!passwordsMatch" :class="!passwordsMatch ? 'opacity-50 cursor-not-allowed' : ''">
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

    {{-- MODAL: Visualizar Usuario --}}
    <div x-show="viewModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden" @click.away="viewModal = false">
            <div class="p-6 border-b flex justify-between items-center bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                        <iconify-icon icon="lucide:user" class="text-xl"></iconify-icon>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800" x-text="'Detalle de: ' + selectedUser?.name"></h3>
                        <p class="text-xs text-gray-500" x-text="'ID de sistema: USR-' + String(selectedUser?.id).padStart(3, '0')"></p>
                    </div>
                </div>
                <button @click="viewModal = false" class="text-gray-400 hover:text-red-500 transition">
                    <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
                </button>
            </div>
            <div class="p-8 space-y-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Nombre Completo</span>
                        <p class="font-medium text-gray-800" x-text="selectedUser?.name"></p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Usuario (Alias)</span>
                        <p class="font-medium text-gray-800" x-text="selectedUser?.username"></p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Documento</span>
                        <p class="font-medium text-gray-800" x-text="selectedUser?.ndocper"></p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Correo Electrónico</span>
                        <p class="font-medium text-gray-800" x-text="selectedUser?.email"></p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Rol / Perfil</span>
                        <p class="font-medium text-blue-600" x-text="selectedUser?.nompef"></p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Empresa</span>
                        <p class="font-medium text-gray-800" x-text="selectedUser?.empresa"></p>
                    </div>
                </div>
                <div class="pt-4 flex justify-end">
                    <button @click="viewModal = false" class="mup-btn mup-btn-primary w-full sm:w-auto">Cerrar detalle</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: Editar Usuario --}}
    <div x-show="editModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden" @click.away="editModal = false">
            <div class="p-6 border-b flex justify-between items-center bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-600">
                        <iconify-icon icon="lucide:pencil" class="text-xl"></iconify-icon>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Modificar Usuario</h3>
                        <p class="text-xs text-gray-500">Actualiza la información de acceso y personal del usuario.</p>
                    </div>
                </div>
                <button @click="editModal = false" class="text-gray-400 hover:text-red-500 transition">
                    <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
                </button>
            </div>
            <form :action="'{{ route('admin.mup.usuarios.store') }}/' + selectedUser?.id" method="POST">
                @csrf
                @method('PUT')
                <div class="p-8 max-h-[70vh] overflow-y-auto">
                    <div class="grid grid-cols-2 gap-6">
                        <div class="mup-form-group span-2">
                            <label class="mup-label">Nombre completo <span class="mup-required">*</span></label>
                            <input type="text" name="nombre_completo" class="mup-input" required :value="selectedUser?.name">
                        </div>
                        <div class="mup-form-group">
                            <label class="mup-label">Tipo de documento <span class="mup-required">*</span></label>
                            <select name="tdocper" class="mup-input" required :value="selectedUser?.tdocper">
                                @foreach($tiposDoc as $tipo)
                                    <option value="{{ $tipo->idval }}">{{ $tipo->nomval }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mup-form-group">
                            <label class="mup-label">Número de documento <span class="mup-required">*</span></label>
                            <input type="number" name="ndocper" class="mup-input" required :value="selectedUser?.ndocper">
                        </div>
                        <div class="mup-form-group">
                            <label class="mup-label">Correo electrónico <span class="mup-required">*</span></label>
                            <input type="email" name="emaper" class="mup-input" required :value="selectedUser?.email">
                        </div>
                        <div class="mup-form-group">
                            <label class="mup-label">Teléfono</label>
                            <input type="text" name="telper" class="mup-input" :value="selectedUser?.telper">
                        </div>
                        <div class="mup-form-group">
                            <label class="mup-label">Nombre de usuario <span class="mup-required">*</span></label>
                            <input type="text" name="username" class="mup-input" required :value="selectedUser?.username">
                        </div>
                        <div class="mup-form-group">
                            <label class="mup-label">Estado <span class="mup-required">*</span></label>
                            <select name="actper" class="mup-input" :value="selectedUser?.actper">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                        <div class="mup-form-group">
                            <label class="mup-label">Nueva Contraseña (Opcional)</label>
                            <div class="relative">
                                <input :type="showPass ? 'text' : 'password'" name="password" x-model="password" class="mup-input pr-10" placeholder="********">
                                <button type="button" @click="showPass = !showPass" class="absolute right-3 top-2.5 text-gray-400 hover:text-[#0d3b5a] transition">
                                    <iconify-icon :icon="showPass ? 'lucide:eye-off' : 'lucide:eye'"></iconify-icon>
                                </button>
                            </div>
                        </div>
                        <div class="mup-form-group">
                            <label class="mup-label">Confirmar Contraseña</label>
                            <div class="relative">
                                <input :type="showConfirmPass ? 'text' : 'password'" name="password_confirmation" x-model="password_confirmation" class="mup-input pr-10" placeholder="********" :class="!passwordsMatch ? 'border-red-500 ring-1 ring-red-500' : ''">
                                <button type="button" @click="showConfirmPass = !showConfirmPass" class="absolute right-3 top-2.5 text-gray-400 hover:text-[#0d3b5a] transition">
                                    <iconify-icon :icon="showConfirmPass ? 'lucide:eye-off' : 'lucide:eye'"></iconify-icon>
                                </button>
                            </div>
                            <template x-if="!passwordsMatch">
                                <p class="text-[10px] text-red-500 mt-1 font-bold italic animate-pulse">Las contraseñas no coinciden</p>
                            </template>
                        </div>
                        <div class="mup-form-group">
                            <label class="mup-label">Rol / perfil <span class="mup-required">*</span></label>
                            <select name="idpef" class="mup-input" required :value="selectedUser?.idpef">
                                @foreach($perfiles as $perf)
                                    <option value="{{ $perf->idpef }}">{{ $perf->nompef }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mup-form-group">
                            <label class="mup-label">Empresa asociada</label>
                            <select name="idemp" class="mup-input" :value="selectedUser?.idemp">
                                <option value="">Particular (Sin empresa)</option>
                                @foreach($empresas as $emp)
                                    <option value="{{ $emp->idemp }}">{{ $emp->razsoem }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="p-6 border-t bg-gray-50 flex justify-end gap-3">
                    <button type="button" @click="editModal = false" class="mup-btn mup-btn-outline">Cancelar</button>
                    <button type="submit" class="mup-btn mup-btn-primary" :disabled="!passwordsMatch" :class="!passwordsMatch ? 'opacity-50 cursor-not-allowed' : ''">Actualizar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: Eliminar Usuario --}}
    <div x-show="deleteModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden" @click.away="deleteModal = false">
            <div class="p-8 text-center">
                <div class="w-16 h-16 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-4">
                    <iconify-icon icon="lucide:alert-triangle" class="text-3xl"></iconify-icon>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">¿Confirmar eliminación?</h3>
                <p class="text-sm text-gray-500 mb-6">
                    Estás a punto de eliminar permanentemente a <strong x-text="selectedUser?.name"></strong>. Esta acción no se puede deshacer y eliminará todos sus accesos vinculados.
                </p>
                <div class="flex flex-col gap-2">
                    <form :action="'{{ route('admin.mup.usuarios.store') }}/' + selectedUser?.id" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-bold transition">Eliminar permanentemente</button>
                    </form>
                    <button @click="deleteModal = false" class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg font-bold transition">Cancelar</button>
                </div>
            </div>
            <div class="bg-red-50 p-4 text-[10px] text-red-400 font-bold uppercase tracking-widest text-center border-t border-red-100">
                Atención: Borrado físico confirmado
            </div>
        </div>
    </div>
</div>
@endsection
