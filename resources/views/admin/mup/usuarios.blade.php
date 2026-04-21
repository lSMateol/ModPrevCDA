@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/mup.css') }}">
<script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>

<div class="mup-container" x-data="mupEditor()">
    <header class="mup-topbar">
        <div class="mup-page-title">
            <h1>MUP - Módulo de Usuarios y Perfiles</h1>
            <p>Gestión de entidades, perfiles del sistema y permisos administrativos</p>
        </div>
        <div class="mup-tabs">
            <a href="{{ route('admin.mup.conductores.index') }}" class="mup-tab">Conductor</a>
            <a href="{{ route('admin.mup.propietarios.index') }}" class="mup-tab">Propietario</a>
            <a href="{{ route('admin.mup.empresas.index') }}" class="mup-tab">Empresas</a>
            <a href="{{ route('admin.mup.usuarios.index') }}" class="mup-tab active">Usuario</a>
        </div>
    </header>

    <script>
        function mupEditor() {
            return {
                searchQuery: '',
                viewModal: false,
                deleteModal: false,
                selectedUser: null,
                showPass: false,
                showConfirmPass: false,

                // Drawers
                createDrawer: false,
                editDrawer: false,

                // Validation
                password: '',
                password_confirmation: '',

                get passwordsMatch() {
                    if (!this.password || !this.password_confirmation) return true;
                    return this.password === this.password_confirmation;
                },

                openCreate() {
                    this.password = '';
                    this.password_confirmation = '';
                    this.showPass = false;
                    this.showConfirmPass = false;
                    this.createDrawer = true;
                },

                openEdit(user) {
                    this.selectedUser = user;
                    this.password = '';
                    this.password_confirmation = '';
                    this.showPass = false;
                    this.showConfirmPass = false;
                    this.editDrawer = true;
                },

                openView(user) {
                    this.selectedUser = user;
                    this.viewModal = true;
                },

                openDelete(user) {
                    this.selectedUser = user;
                    this.deleteModal = true;
                },

                getInitials(name) {
                    if (!name) return '??';
                    const parts = name.trim().split(' ');
                    if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
                    return name.substring(0, 2).toUpperCase();
                }
            };
        }
    </script>

    <div class="mup-content-scroll">
        {{-- ALERTAS GLOBALES --}}
        @if(session('success') || session('error') || $errors->any())
        <div class="px-2 pt-4">
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

        {{-- SECCIÓN: Listado de Usuarios --}}
        <section class="mup-card">
            {{-- TOOLBAR CONSOLIDADA --}}
            <div class="mup-card-header-plain" style="flex-wrap: wrap;">
                <div>
                    <div class="mup-card-title text-gray-800">Listado de usuarios del sistema</div>
                    <div class="mup-card-subtitle">Filtra por rol, estado o documento y exporta el listado correspondiente.</div>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="export-group">
                        <button class="export-btn csv"><iconify-icon icon="lucide:file-text"></iconify-icon> CSV</button>
                        <button class="export-btn excel"><iconify-icon icon="lucide:file-spreadsheet"></iconify-icon> Excel</button>
                        <button class="export-btn pdf"><iconify-icon icon="lucide:file"></iconify-icon> PDF</button>
                    </div>
                    <div class="relative">
                        <input type="text" x-model="searchQuery" placeholder="Buscar por nombre, rol o documento..." class="pl-10 pr-4 py-2 border rounded-md text-sm w-72 bg-gray-50">
                        <div class="absolute left-3 top-2.5 text-gray-400">
                            <iconify-icon icon="lucide:search"></iconify-icon>
                        </div>
                    </div>
                    <button @click="openCreate()" class="mup-btn mup-btn-primary h-10">
                        <iconify-icon icon="lucide:plus"></iconify-icon>
                        Nuevo usuario
                    </button>
                </div>
            </div>

            {{-- TABLA PREMIUM --}}
            <div class="mup-table-wrap">
                <table class="mup-data-table">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Documento</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $user)
                        <tr x-show="searchQuery === '' || @js(strtolower($user->name . ' ' . ($user->persona->ndocper ?? '') . ' ' . ($user->persona->perfil->nompef ?? ''))).includes(searchQuery.toLowerCase())"
                            class="hover:bg-blue-50/30 transition-colors">
                            <td>
                                <div class="mup-user-identity">
                                    <div class="mup-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', $user->name)[1] ?? $user->name, 0, 1)) }}</div>
                                    <div>
                                        <div class="font-semibold text-gray-800">{{ $user->name }}</div>
                                        <div class="text-xs text-gray-400">USR-{{ str_pad($user->id, 3, '0', STR_PAD_LEFT) }} · {{ $user->username }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-sm font-medium text-gray-600">{{ number_format($user->persona->ndocper ?? 0, 0, ',', '.') }}</span>
                            </td>
                            <td>
                                <span class="text-sm text-gray-600">{{ $user->email }}</span>
                            </td>
                            <td>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-[#0d3b5a] border border-blue-100">
                                    <iconify-icon icon="lucide:shield" style="font-size: 12px;"></iconify-icon>
                                    {{ $user->persona->perfil->nompef ?? 'Sin Rol' }}
                                </span>
                            </td>
                            <td>
                                <span class="mup-state-badge {{ ($user->persona->actper ?? 1) ? 'mup-state-active' : 'mup-state-inactive' }}">
                                    <div class="w-2 h-2 rounded-full bg-current"></div>
                                    {{ ($user->persona->actper ?? 1) ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-right">
                                @php
                                    $viewPayload = [
                                        'id' => $user->id,
                                        'name' => $user->name,
                                        'username' => $user->username,
                                        'email' => $user->email,
                                        'ndocper' => $user->persona->ndocper ?? 0,
                                        'tdocper' => $user->persona->tdocper ?? '',
                                        'telper' => $user->persona->telper ?? '',
                                        'actper' => $user->persona->actper ?? 1,
                                        'nompef' => $user->persona->perfil->nompef ?? 'Sin Rol',
                                        'idpef' => $user->persona->idpef ?? '',
                                        'idemp' => $user->idemp ?? '',
                                        'empresa' => $user->empresa->razsoem ?? 'Particular',
                                    ];
                                    $editPayload = [
                                        'id' => $user->id,
                                        'name' => $user->name,
                                        'username' => $user->username,
                                        'email' => $user->email,
                                        'ndocper' => $user->persona->ndocper ?? 0,
                                        'tdocper' => $user->persona->tdocper ?? '',
                                        'telper' => $user->persona->telper ?? '',
                                        'actper' => $user->persona->actper ?? 1,
                                        'idpef' => $user->persona->idpef ?? '',
                                        'idemp' => $user->idemp ?? '',
                                    ];
                                    $deletePayload = [
                                        'id' => $user->id,
                                        'name' => $user->name,
                                    ];
                                @endphp
                                <div class="flex justify-end gap-2">
                                    <button @click='openView(@json($viewPayload))' class="p-2 bg-blue-50 text-blue-600 rounded-md hover:bg-blue-100 transition" title="Ver detalle">
                                        <iconify-icon icon="lucide:eye"></iconify-icon>
                                    </button>
                                    
                                    <button @click='openEdit(@json($editPayload))' class="p-2 bg-orange-50 text-orange-600 rounded-md hover:bg-orange-100 transition" title="Editar">
                                        <iconify-icon icon="lucide:pencil"></iconify-icon>
                                    </button>
                                    
                                    <button @click='openDelete(@json($deletePayload))' class="p-2 bg-red-50 text-red-600 rounded-md hover:bg-red-100 transition" title="Eliminar">
                                        <iconify-icon icon="lucide:trash-2"></iconify-icon>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-16">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-14 h-14 rounded-full bg-gray-50 flex items-center justify-center">
                                        <iconify-icon icon="lucide:users" class="text-2xl text-gray-300"></iconify-icon>
                                    </div>
                                    <p class="text-gray-400 font-medium">No hay usuarios registrados.</p>
                                    <button @click="openCreate()" class="mup-btn mup-btn-primary h-9 text-sm mt-1">
                                        <iconify-icon icon="lucide:plus"></iconify-icon> Crear primer usuario
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- FOOTER DE TABLA --}}
            <div class="px-6 py-4 border-t flex justify-between items-center text-xs text-gray-400">
                <span>{{ count($usuarios) }} usuario(s) registrado(s)</span>
                <span>Última actualización: {{ now()->format('d/m/Y H:i') }}</span>
            </div>
        </section>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- DRAWER: Crear Nuevo Usuario                        --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div x-show="createDrawer" class="mup-drawer-overlay" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="mup-drawer" @click.away="createDrawer = false">
            <form action="{{ route('admin.mup.usuarios.store') }}" method="POST" class="flex flex-col h-full">
                @csrf
                {{-- HEADER --}}
                <div class="mup-drawer-header">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#0d3b5a] text-white flex items-center justify-center">
                            <iconify-icon icon="lucide:user-round-plus" class="text-xl"></iconify-icon>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-[15px]">Nuevo usuario</h3>
                            <p class="text-xs text-gray-400">Crea un nuevo usuario y asígnale su rol operativo.</p>
                        </div>
                    </div>
                    <button type="button" @click="createDrawer = false" class="text-gray-400 hover:text-red-500 transition">
                        <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
                    </button>
                </div>

                {{-- BODY --}}
                <div class="mup-drawer-body">
                    {{-- Bloque: Info Personal --}}
                    <div class="text-[11px] font-bold text-[#0d3b5a] mb-3 uppercase tracking-widest flex items-center gap-2">
                        <iconify-icon icon="lucide:id-card" class="text-sm"></iconify-icon>
                        Información personal
                    </div>
                    <div class="space-y-4 mb-8">
                        <div class="mup-form-group">
                            <label class="mup-label">Nombre completo <span class="mup-required">*</span></label>
                            <input type="text" name="nombre_completo" class="mup-input" placeholder="Ej. Juan Pérez" required value="{{ old('nombre_completo') }}">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="mup-form-group">
                                <label class="mup-label">Tipo doc. <span class="mup-required">*</span></label>
                                <select name="tdocper" class="mup-input" required>
                                    @foreach($tiposDoc as $tipo)
                                        <option value="{{ $tipo->idval }}">{{ $tipo->nomval }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mup-form-group">
                                <label class="mup-label">Nro. documento <span class="mup-required">*</span></label>
                                <input type="number" name="ndocper" class="mup-input" placeholder="12345678" required value="{{ old('ndocper') }}">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="mup-form-group">
                                <label class="mup-label">Correo <span class="mup-required">*</span></label>
                                <input type="email" name="emaper" class="mup-input" placeholder="correo@ejemplo.com" required value="{{ old('emaper') }}">
                            </div>
                            <div class="mup-form-group">
                                <label class="mup-label">Teléfono</label>
                                <input type="text" name="telper" class="mup-input" placeholder="300 123 4567" value="{{ old('telper') }}">
                            </div>
                        </div>
                    </div>

                    {{-- Bloque: Acceso --}}
                    <div class="text-[11px] font-bold text-[#0d3b5a] mb-3 uppercase tracking-widest flex items-center gap-2">
                        <iconify-icon icon="lucide:lock" class="text-sm"></iconify-icon>
                        Acceso al sistema
                    </div>
                    <div class="space-y-4 mb-8">
                        <div class="mup-form-group">
                            <label class="mup-label">Nombre de usuario <span class="mup-required">*</span></label>
                            <input type="text" name="username" class="mup-input" placeholder="Ej. juan.perez" required value="{{ old('username') }}">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="mup-form-group">
                                <label class="mup-label">Contraseña <span class="mup-required">*</span></label>
                                <div class="relative">
                                    <input :type="showPass ? 'text' : 'password'" name="password" x-model="password" class="mup-input pr-10" placeholder="Min. 6 caracteres" required>
                                    <button type="button" @click="showPass = !showPass" class="absolute right-3 top-3 text-gray-400 hover:text-[#0d3b5a] transition">
                                        <iconify-icon :icon="showPass ? 'lucide:eye-off' : 'lucide:eye'"></iconify-icon>
                                    </button>
                                </div>
                            </div>
                            <div class="mup-form-group">
                                <label class="mup-label">Confirmar <span class="mup-required">*</span></label>
                                <div class="relative">
                                    <input :type="showConfirmPass ? 'text' : 'password'" name="password_confirmation" x-model="password_confirmation" class="mup-input pr-10" required :class="!passwordsMatch ? 'border-red-500 ring-1 ring-red-500' : ''">
                                    <button type="button" @click="showConfirmPass = !showConfirmPass" class="absolute right-3 top-3 text-gray-400 hover:text-[#0d3b5a] transition">
                                        <iconify-icon :icon="showConfirmPass ? 'lucide:eye-off' : 'lucide:eye'"></iconify-icon>
                                    </button>
                                </div>
                                <template x-if="!passwordsMatch">
                                    <p class="text-[10px] text-red-500 mt-1 font-bold italic animate-pulse">Las contraseñas no coinciden</p>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Bloque: Rol --}}
                    <div class="text-[11px] font-bold text-[#0d3b5a] mb-3 uppercase tracking-widest flex items-center gap-2">
                        <iconify-icon icon="lucide:shield" class="text-sm"></iconify-icon>
                        Rol y asignación
                    </div>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
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
                                    <option value="">Sin empresa</option>
                                    @foreach($empresas as $emp)
                                        <option value="{{ $emp->idemp }}">{{ $emp->razsoem }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="p-3 bg-amber-50 rounded-lg flex items-center gap-3 text-amber-700 text-xs">
                            <iconify-icon icon="lucide:info" class="text-amber-500"></iconify-icon>
                            <span>Este formulario aplica para perfiles distintos a conductor, propietario y empresas.</span>
                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="mup-drawer-footer">
                    <button type="button" @click="createDrawer = false" class="mup-btn mup-btn-outline">Cancelar</button>
                    <button type="submit" class="mup-btn mup-btn-primary" :disabled="!passwordsMatch" :class="!passwordsMatch ? 'opacity-50 cursor-not-allowed' : ''">
                        <iconify-icon icon="lucide:save"></iconify-icon>
                        Guardar usuario
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- DRAWER: Editar Usuario                             --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div x-show="editDrawer" class="mup-drawer-overlay" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="mup-drawer" @click.away="editDrawer = false">
            <form :action="'{{ url('admin/entidades/mup/usuarios') }}/' + selectedUser?.id" method="POST" class="flex flex-col h-full">
                @csrf
                @method('PUT')
                {{-- HEADER --}}
                <div class="mup-drawer-header">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-orange-500 text-white flex items-center justify-center">
                            <iconify-icon icon="lucide:pencil" class="text-xl"></iconify-icon>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-[15px]">Modificar usuario</h3>
                            <p class="text-xs text-gray-400">Actualiza la información de acceso y personal.</p>
                        </div>
                    </div>
                    <button type="button" @click="editDrawer = false" class="text-gray-400 hover:text-red-500 transition">
                        <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
                    </button>
                </div>

                {{-- BODY --}}
                <div class="mup-drawer-body">
                    {{-- Bloque: Info Personal --}}
                    <div class="text-[11px] font-bold text-[#0d3b5a] mb-3 uppercase tracking-widest flex items-center gap-2">
                        <iconify-icon icon="lucide:id-card" class="text-sm"></iconify-icon>
                        Información personal
                    </div>
                    <div class="space-y-4 mb-8">
                        <div class="mup-form-group">
                            <label class="mup-label">Nombre completo <span class="mup-required">*</span></label>
                            <input type="text" name="nombre_completo" class="mup-input" required :value="selectedUser?.name">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="mup-form-group">
                                <label class="mup-label">Tipo doc. <span class="mup-required">*</span></label>
                                <select name="tdocper" class="mup-input" required :value="selectedUser?.tdocper">
                                    @foreach($tiposDoc as $tipo)
                                        <option value="{{ $tipo->idval }}">{{ $tipo->nomval }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mup-form-group">
                                <label class="mup-label">Nro. documento <span class="mup-required">*</span></label>
                                <input type="number" name="ndocper" class="mup-input" required :value="selectedUser?.ndocper">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="mup-form-group">
                                <label class="mup-label">Correo <span class="mup-required">*</span></label>
                                <input type="email" name="emaper" class="mup-input" required :value="selectedUser?.email">
                            </div>
                            <div class="mup-form-group">
                                <label class="mup-label">Teléfono</label>
                                <input type="text" name="telper" class="mup-input" :value="selectedUser?.telper">
                            </div>
                        </div>
                    </div>

                    {{-- Bloque: Acceso --}}
                    <div class="text-[11px] font-bold text-[#0d3b5a] mb-3 uppercase tracking-widest flex items-center gap-2">
                        <iconify-icon icon="lucide:lock" class="text-sm"></iconify-icon>
                        Acceso al sistema
                    </div>
                    <div class="space-y-4 mb-8">
                        <div class="grid grid-cols-2 gap-3">
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
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="mup-form-group">
                                <label class="mup-label">Nueva contraseña</label>
                                <div class="relative">
                                    <input :type="showPass ? 'text' : 'password'" name="password" x-model="password" class="mup-input pr-10" placeholder="Dejar vacío si no cambia">
                                    <button type="button" @click="showPass = !showPass" class="absolute right-3 top-3 text-gray-400 hover:text-[#0d3b5a] transition">
                                        <iconify-icon :icon="showPass ? 'lucide:eye-off' : 'lucide:eye'"></iconify-icon>
                                    </button>
                                </div>
                            </div>
                            <div class="mup-form-group">
                                <label class="mup-label">Confirmar</label>
                                <div class="relative">
                                    <input :type="showConfirmPass ? 'text' : 'password'" name="password_confirmation" x-model="password_confirmation" class="mup-input pr-10" placeholder="********" :class="!passwordsMatch ? 'border-red-500 ring-1 ring-red-500' : ''">
                                    <button type="button" @click="showConfirmPass = !showConfirmPass" class="absolute right-3 top-3 text-gray-400 hover:text-[#0d3b5a] transition">
                                        <iconify-icon :icon="showConfirmPass ? 'lucide:eye-off' : 'lucide:eye'"></iconify-icon>
                                    </button>
                                </div>
                                <template x-if="!passwordsMatch">
                                    <p class="text-[10px] text-red-500 mt-1 font-bold italic animate-pulse">Las contraseñas no coinciden</p>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Bloque: Rol --}}
                    <div class="text-[11px] font-bold text-[#0d3b5a] mb-3 uppercase tracking-widest flex items-center gap-2">
                        <iconify-icon icon="lucide:shield" class="text-sm"></iconify-icon>
                        Rol y asignación
                    </div>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
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
                                    <option value="">Sin empresa</option>
                                    @foreach($empresas as $emp)
                                        <option value="{{ $emp->idemp }}">{{ $emp->razsoem }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="mup-drawer-footer">
                    <button type="button" @click="editDrawer = false" class="mup-btn mup-btn-outline">Cancelar</button>
                    <button type="submit" class="mup-btn mup-btn-primary" :disabled="!passwordsMatch" :class="!passwordsMatch ? 'opacity-50 cursor-not-allowed' : ''">
                        <iconify-icon icon="lucide:save"></iconify-icon>
                        Actualizar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- MODAL: Visualizar Usuario                          --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div x-show="viewModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden" @click.away="viewModal = false">
            <div class="p-6 border-b flex justify-between items-center bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="mup-avatar" style="width:44px;height:44px;font-size:16px;" x-text="getInitials(selectedUser?.name)"></div>
                    <div>
                        <h3 class="font-bold text-gray-800" x-text="selectedUser?.name"></h3>
                        <p class="text-xs text-gray-500" x-text="'USR-' + String(selectedUser?.id).padStart(3, '0') + ' · ' + selectedUser?.nompef"></p>
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

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- MODAL: Eliminar Usuario                            --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div x-show="deleteModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
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
                    <form :action="'{{ url('admin/entidades/mup/usuarios') }}/' + selectedUser?.id" method="POST">
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
