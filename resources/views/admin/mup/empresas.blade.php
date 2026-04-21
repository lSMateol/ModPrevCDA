@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/mup.css') }}">
<script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>

<div class="mup-container" x-data="empresaManager()">
    <header class="mup-topbar">
        <div class="mup-page-title">
            <h1>MUP - Módulo de Usuarios y Perfiles</h1>
            <p>Gestión de entidades, perfiles del sistema y permisos administrativos</p>
        </div>
        <div class="mup-tabs">
            <a href="{{ route('admin.mup.conductores.index') }}" class="mup-tab">Conductor</a>
            <a href="{{ route('admin.mup.propietarios.index') }}" class="mup-tab">Propietario</a>
            <a href="{{ route('admin.mup.empresas.index') }}" class="mup-tab active">Empresas</a>
            <a href="{{ route('admin.mup.usuarios.index') }}" class="mup-tab">Usuario</a>
        </div>
    </header>

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

        {{-- SECCIÓN: Directorio Empresarial --}}
        <section class="mup-card">
            {{-- TOOLBAR CONSOLIDADA --}}
            <div class="mup-card-header-plain" style="flex-wrap: wrap;">
                <div>
                    <div class="mup-card-title text-gray-800">Directorio Empresarial</div>
                    <div class="mup-card-subtitle">Administración de NITs, cuentas de acceso y estados de facturación.</div>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="export-group">
                        <button class="export-btn csv"><iconify-icon icon="lucide:file-text"></iconify-icon> CSV</button>
                        <button class="export-btn excel"><iconify-icon icon="lucide:file-spreadsheet"></iconify-icon> Excel</button>
                        <button class="export-btn pdf"><iconify-icon icon="lucide:file"></iconify-icon> PDF</button>
                    </div>
                    <div class="relative">
                        <input type="text" x-model="search" placeholder="Buscar por nombre, NIT o gerente..." class="pl-10 pr-4 py-2 border rounded-md text-sm w-72 bg-gray-50">
                        <div class="absolute left-3 top-2.5 text-gray-400">
                            <iconify-icon icon="lucide:search"></iconify-icon>
                        </div>
                    </div>
                    <button @click="openCreate()" class="mup-btn mup-btn-primary h-10">
                        <iconify-icon icon="lucide:plus"></iconify-icon>
                        Nueva empresa
                    </button>
                </div>
            </div>

            {{-- TABLA PREMIUM --}}
            <div class="mup-table-wrap">
                <table class="mup-data-table">
                    <thead>
                        <tr>
                            <th>Empresa</th>
                            <th>NIT Fiscal</th>
                            <th>Email Corporativo</th>
                            <th class="text-center">Estado</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="e in filteredEmpresas()" :key="e.idemp">
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <td>
                                    <div class="mup-user-identity">
                                        <div class="mup-avatar" style="background: #0d3b5a; color: white;" x-text="e.abremp ? e.abremp.substring(0,2) : e.razsoem.substring(0,2).toUpperCase()"></div>
                                        <div>
                                            <div class="font-semibold text-gray-800" x-text="e.razsoem"></div>
                                            <div class="text-xs text-gray-400" x-text="'EMP-' + e.idemp + ' · Gerente: ' + e.nomger"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="font-mono text-sm text-[#0d3b5a] font-bold" x-text="e.nonitem"></div>
                                    <div class="text-xs text-gray-400" x-text="e.direm || 'Sin dirección'"></div>
                                </td>
                                <td>
                                    <div class="text-sm text-gray-600" x-text="e.emaem"></div>
                                    <div class="text-xs text-gray-400 flex items-center gap-1">
                                        <iconify-icon icon="lucide:phone" style="font-size: 10px;"></iconify-icon>
                                        <span x-text="e.telem"></span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="mup-state-badge mup-state-active">
                                        <div class="w-2 h-2 rounded-full bg-current"></div>
                                        Activo
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <button @click="openView(e)" class="p-2 bg-blue-50 text-blue-600 rounded-md hover:bg-blue-100 transition" title="Ver detalle">
                                            <iconify-icon icon="lucide:eye"></iconify-icon>
                                        </button>
                                        <button @click="openEdit(e)" class="p-2 bg-orange-50 text-orange-600 rounded-md hover:bg-orange-100 transition" title="Editar">
                                            <iconify-icon icon="lucide:pencil"></iconify-icon>
                                        </button>
                                        <button @click="openDelete(e)" class="p-2 bg-red-50 text-red-600 rounded-md hover:bg-red-100 transition" title="Eliminar">
                                            <iconify-icon icon="lucide:trash-2"></iconify-icon>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- FOOTER DE TABLA --}}
            <div class="px-6 py-4 border-t flex justify-between items-center text-xs text-gray-400">
                <span x-text="filteredEmpresas().length + ' empresa(s) registrada(s)'"></span>
                <span>Última actualización: {{ now()->format('d/m/Y H:i') }}</span>
            </div>
        </section>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- DRAWER: Registrar Nueva Empresa                    --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div x-show="createDrawer" class="mup-drawer-overlay" x-cloak
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="mup-drawer" @click.away="createDrawer = false">
            <form action="{{ route('admin.mup.empresas.store') }}" method="POST" class="flex flex-col h-full">
                @csrf
                {{-- HEADER --}}
                <div class="mup-drawer-header">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#0d3b5a] text-white flex items-center justify-center">
                            <iconify-icon icon="lucide:building-2" class="text-xl"></iconify-icon>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-[15px]">Alta Corporativa</h3>
                            <p class="text-xs text-gray-400">Registro de entidades aliadas y credenciales de acceso.</p>
                        </div>
                    </div>
                    <button type="button" @click="createDrawer = false" class="text-gray-400 hover:text-red-500 transition">
                        <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
                    </button>
                </div>

                {{-- BODY --}}
                <div class="mup-drawer-body">
                    {{-- Bloque 1: Información Corporativa --}}
                    <div class="text-[11px] font-bold text-[#0d3b5a] mb-3 uppercase tracking-widest flex items-center gap-2">
                        <iconify-icon icon="lucide:building" class="text-sm"></iconify-icon>
                        Información corporativa
                    </div>
                    <div class="space-y-4 mb-8">
                        <div class="mup-form-group">
                            <label class="mup-label">Razón Social <span class="mup-required">*</span></label>
                            <input type="text" name="razsoem" class="mup-input" placeholder="Ej. Transportes Unidos S.A." required>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="mup-form-group">
                                <label class="mup-label">NIT <span class="mup-required">*</span></label>
                                <input type="text" name="nonitem" class="mup-input" placeholder="900.123.456-7" required>
                            </div>
                            <div class="mup-form-group">
                                <label class="mup-label">Abreviatura</label>
                                <input type="text" name="abremp" class="mup-input" placeholder="Ej. TUSA">
                            </div>
                        </div>
                        <div class="mup-form-group">
                            <label class="mup-label">Dirección</label>
                            <input type="text" name="direm" class="mup-input" placeholder="Dirección principal">
                        </div>
                    </div>

                    {{-- Bloque 2: Contacto Corporativo --}}
                    <div class="text-[11px] font-bold text-[#0d3b5a] mb-3 uppercase tracking-widest flex items-center gap-2">
                        <iconify-icon icon="lucide:contact" class="text-sm"></iconify-icon>
                        Contacto corporativo
                    </div>
                    <div class="space-y-4 mb-8">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="mup-form-group">
                                <label class="mup-label">Nombre del Gerente <span class="mup-required">*</span></label>
                                <input type="text" name="nomger" class="mup-input" placeholder="Ej. Luis M. Restrepo" required>
                            </div>
                            <div class="mup-form-group">
                                <label class="mup-label">Teléfono <span class="mup-required">*</span></label>
                                <input type="text" name="telem" class="mup-input" placeholder="604 123 4567" required>
                            </div>
                        </div>
                        <div class="mup-form-group">
                            <label class="mup-label">Email de Contacto <span class="mup-required">*</span></label>
                            <input type="email" name="emaem" class="mup-input" placeholder="contacto@empresa.com" required>
                        </div>
                    </div>

                    {{-- Bloque 3: Acceso al Sistema --}}
                    <div class="text-[11px] font-bold text-[#0d3b5a] mb-3 uppercase tracking-widest flex items-center gap-2">
                        <iconify-icon icon="lucide:lock" class="text-sm"></iconify-icon>
                        Acceso al sistema
                    </div>
                    <div class="space-y-4">
                        <div class="mup-form-group">
                            <label class="mup-label">Nombre de Usuario <span class="mup-required">*</span></label>
                            <input type="text" name="username" class="mup-input" placeholder="Ej. transportes.unidos" required>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="mup-form-group">
                                <label class="mup-label">Contraseña <span class="mup-required">*</span></label>
                                <div class="relative">
                                    <input :type="showPass ? 'text' : 'password'" name="password" class="mup-input pr-10" required>
                                    <button type="button" @click="showPass = !showPass" class="absolute right-3 top-3 text-gray-400 hover:text-[#0d3b5a] transition">
                                        <iconify-icon :icon="showPass ? 'lucide:eye-off' : 'lucide:eye'"></iconify-icon>
                                    </button>
                                </div>
                            </div>
                            <div class="mup-form-group">
                                <label class="mup-label">Confirmar <span class="mup-required">*</span></label>
                                <div class="relative">
                                    <input :type="showConfirmPass ? 'text' : 'password'" name="password_confirmation" class="mup-input pr-10" required>
                                    <button type="button" @click="showConfirmPass = !showConfirmPass" class="absolute right-3 top-3 text-gray-400 hover:text-[#0d3b5a] transition">
                                        <iconify-icon :icon="showConfirmPass ? 'lucide:eye-off' : 'lucide:eye'"></iconify-icon>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 bg-amber-50 rounded-lg flex items-center gap-3 text-amber-700 text-xs">
                            <iconify-icon icon="lucide:info" class="text-amber-500"></iconify-icon>
                            <span>Al registrar una empresa, se creará automáticamente un usuario con perfil corporativo.</span>
                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="mup-drawer-footer">
                    <button type="button" @click="createDrawer = false" class="mup-btn mup-btn-outline">Cancelar</button>
                    <button type="submit" class="mup-btn mup-btn-primary">
                        <iconify-icon icon="lucide:save"></iconify-icon>
                        Registrar empresa
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- DRAWER: Editar Empresa                             --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div x-show="editDrawer" class="mup-drawer-overlay" x-cloak
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="mup-drawer" @click.away="editDrawer = false">
            <form :action="'{{ url('admin/entidades/mup/empresas') }}/' + currentEmp.idemp" method="POST" class="flex flex-col h-full">
                @csrf
                @method('PUT')
                {{-- HEADER --}}
                <div class="mup-drawer-header">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-orange-500 text-white flex items-center justify-center">
                            <iconify-icon icon="lucide:pencil" class="text-xl"></iconify-icon>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-[15px]" x-text="'Editar: ' + currentEmp.razsoem"></h3>
                            <p class="text-xs text-gray-400">Actualiza la información corporativa de esta empresa.</p>
                        </div>
                    </div>
                    <button type="button" @click="editDrawer = false" class="text-gray-400 hover:text-red-500 transition">
                        <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
                    </button>
                </div>

                {{-- BODY --}}
                <div class="mup-drawer-body">
                    {{-- Bloque 1: Información --}}
                    <div class="text-[11px] font-bold text-[#0d3b5a] mb-3 uppercase tracking-widest flex items-center gap-2">
                        <iconify-icon icon="lucide:building" class="text-sm"></iconify-icon>
                        Información corporativa
                    </div>
                    <div class="space-y-4 mb-8">
                        <div class="mup-form-group">
                            <label class="mup-label">Razón Social <span class="mup-required">*</span></label>
                            <input type="text" name="razsoem" x-model="currentEmp.razsoem" class="mup-input" required>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="mup-form-group">
                                <label class="mup-label">NIT <span class="mup-required">*</span></label>
                                <input type="text" name="nonitem" x-model="currentEmp.nonitem" class="mup-input" required>
                            </div>
                            <div class="mup-form-group">
                                <label class="mup-label">Abreviatura</label>
                                <input type="text" name="abremp" x-model="currentEmp.abremp" class="mup-input">
                            </div>
                        </div>
                        <div class="mup-form-group">
                            <label class="mup-label">Dirección</label>
                            <input type="text" name="direm" x-model="currentEmp.direm" class="mup-input">
                        </div>
                    </div>

                    {{-- Bloque 2: Contacto --}}
                    <div class="text-[11px] font-bold text-[#0d3b5a] mb-3 uppercase tracking-widest flex items-center gap-2">
                        <iconify-icon icon="lucide:contact" class="text-sm"></iconify-icon>
                        Contacto corporativo
                    </div>
                    <div class="space-y-4 mb-8">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="mup-form-group">
                                <label class="mup-label">Nombre del Gerente <span class="mup-required">*</span></label>
                                <input type="text" name="nomger" x-model="currentEmp.nomger" class="mup-input" required>
                            </div>
                            <div class="mup-form-group">
                                <label class="mup-label">Teléfono <span class="mup-required">*</span></label>
                                <input type="text" name="telem" x-model="currentEmp.telem" class="mup-input" required>
                            </div>
                        </div>
                        <div class="mup-form-group">
                            <label class="mup-label">Email Corporativo <span class="mup-required">*</span></label>
                            <input type="email" name="emaem" x-model="currentEmp.emaem" class="mup-input" required>
                        </div>
                    </div>

                    {{-- Bloque 3: Acceso al Sistema --}}
                    <div class="text-[11px] font-bold text-[#0d3b5a] mb-3 uppercase tracking-widest flex items-center gap-2">
                        <iconify-icon icon="lucide:lock" class="text-sm"></iconify-icon>
                        Acceso al sistema
                    </div>
                    <div class="space-y-4">
                        <div class="mup-form-group">
                            <label class="mup-label">Nombre de Usuario</label>
                            <input type="text" name="username" class="mup-input" placeholder="Dejar vacío si no cambia">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="mup-form-group">
                                <label class="mup-label">Nueva Contraseña</label>
                                <div class="relative">
                                    <input :type="showPass ? 'text' : 'password'" name="password" class="mup-input pr-10" placeholder="Dejar vacío si no cambia">
                                    <button type="button" @click="showPass = !showPass" class="absolute right-3 top-3 text-gray-400 hover:text-[#0d3b5a] transition">
                                        <iconify-icon :icon="showPass ? 'lucide:eye-off' : 'lucide:eye'"></iconify-icon>
                                    </button>
                                </div>
                            </div>
                            <div class="mup-form-group">
                                <label class="mup-label">Confirmar</label>
                                <div class="relative">
                                    <input :type="showConfirmPass ? 'text' : 'password'" name="password_confirmation" class="mup-input pr-10" placeholder="********">
                                    <button type="button" @click="showConfirmPass = !showConfirmPass" class="absolute right-3 top-3 text-gray-400 hover:text-[#0d3b5a] transition">
                                        <iconify-icon :icon="showConfirmPass ? 'lucide:eye-off' : 'lucide:eye'"></iconify-icon>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-lg flex items-center gap-3 text-blue-700 text-xs">
                            <iconify-icon icon="lucide:info" class="text-blue-500"></iconify-icon>
                            <span>Los campos de acceso son opcionales. Solo se actualizarán si se completan.</span>
                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="mup-drawer-footer">
                    <button type="button" @click="editDrawer = false" class="mup-btn mup-btn-outline">Cancelar</button>
                    <button type="submit" class="mup-btn mup-btn-primary">
                        <iconify-icon icon="lucide:save"></iconify-icon>
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- MODAL: Eliminar Empresa                            --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div x-show="deleteModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden" @click.away="deleteModal = false">
            <div class="p-8 text-center">
                <div class="w-16 h-16 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-4">
                    <iconify-icon icon="lucide:alert-triangle" class="text-3xl"></iconify-icon>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">¿Eliminar Empresa?</h3>
                <p class="text-sm text-gray-500 mb-6">
                    Esta acción eliminará permanentemente a <strong x-text="currentEmp.razsoem"></strong> y revocará todos los accesos vinculados.
                </p>
                <div class="flex flex-col gap-2">
                    <form :action="'{{ url('admin/entidades/mup/empresas') }}/' + currentEmp.idemp" method="POST">
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

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- MODAL: Ver Detalle Empresa                         --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div x-show="viewModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden" @click.away="viewModal = false">
            <div class="p-6 border-b flex justify-between items-center bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="mup-avatar" style="width:44px;height:44px;font-size:16px;background:#0d3b5a;color:white;" x-text="currentEmp.abremp ? currentEmp.abremp.substring(0,2) : (currentEmp.razsoem || '').substring(0,2).toUpperCase()"></div>
                    <div>
                        <h3 class="font-bold text-gray-800" x-text="currentEmp.razsoem"></h3>
                        <p class="text-xs text-gray-500" x-text="'EMP-' + currentEmp.idemp + ' · NIT: ' + currentEmp.nonitem"></p>
                    </div>
                </div>
                <button @click="viewModal = false" class="text-gray-400 hover:text-red-500 transition">
                    <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
                </button>
            </div>
            <div class="p-8 space-y-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Razón Social</span>
                        <p class="font-medium text-gray-800" x-text="currentEmp.razsoem"></p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">NIT Fiscal</span>
                        <p class="font-medium text-gray-800 font-mono" x-text="currentEmp.nonitem"></p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Gerente</span>
                        <p class="font-medium text-gray-800" x-text="currentEmp.nomger"></p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Email Corporativo</span>
                        <p class="font-medium text-gray-800" x-text="currentEmp.emaem"></p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Teléfono</span>
                        <p class="font-medium text-gray-800" x-text="currentEmp.telem"></p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Dirección</span>
                        <p class="font-medium text-gray-800" x-text="currentEmp.direm || 'Sin dirección'"></p>
                    </div>
                </div>
                <div class="pt-4 flex justify-end">
                    <button @click="viewModal = false" class="mup-btn mup-btn-primary w-full sm:w-auto">Cerrar detalle</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function empresaManager() {
    return {
        search: '',
        createDrawer: false,
        editDrawer: false,
        viewModal: false,
        deleteModal: false,
        currentEmp: {},
        showPass: false,
        showConfirmPass: false,
        empresas: @json($empresas),

        filteredEmpresas() {
            if (!this.search) return this.empresas;
            const q = this.search.toLowerCase();
            const safe = (v) => (v ?? '').toString().toLowerCase();
            return this.empresas.filter(e => 
                safe(e.razsoem).includes(q) || 
                safe(e.nonitem).includes(q) ||
                safe(e.nomger).includes(q) ||
                safe(e.emaem).includes(q)
            );
        },

        openCreate() {
            this.showPass = false;
            this.showConfirmPass = false;
            this.createDrawer = true;
        },

        openView(e) {
            this.currentEmp = { ...e };
            this.viewModal = true;
        },

        openEdit(e) {
            this.currentEmp = { ...e };
            this.editDrawer = true;
        },

        openDelete(e) {
            this.currentEmp = e;
            this.deleteModal = true;
        }
    }
}
</script>
@endsection
