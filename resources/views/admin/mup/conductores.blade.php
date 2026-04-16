@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/mup.css') }}">
<script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>

<div class="mup-container">
    <header class="mup-topbar">
        <div class="mup-page-title">
            <h1>MUP - Módulo de Usuarios y Perfiles</h1>
            <p>Gestión de entidades, perfiles del sistema y permisos administrativos</p>
        </div>
        <div class="mup-tabs">
            <a href="{{ route('admin.mup.conductores') }}" class="mup-tab active">Conductor</a>
            <a href="#" class="mup-tab">Propietario</a>
            <a href="#" class="mup-tab">Empresas</a>
            <a href="#" class="mup-tab">Usuario</a>
        </div>
    </header>

    <div class="mup-content-scroll">
        <div class="space-y-6">
            {{-- SECCIÓN: Listado de Conductores --}}
            <section class="mup-card">
                <div class="mup-card-header-plain">
                    <div>
                        <div class="mup-card-title">Listado de conductores</div>
                        <div class="mup-card-subtitle">Consulta, edita y exporta el listado de conductores registrados en el sistema.</div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex border rounded-md p-1 bg-white">
                            <button class="px-3 py-1 text-xs font-bold text-blue-600 bg-blue-50 rounded">CSV</button>
                            <button class="px-3 py-1 text-xs font-bold text-green-600 bg-green-50 rounded mx-1">Excel</button>
                            <button class="px-3 py-1 text-xs font-bold text-red-600 bg-red-50 rounded">PDF</button>
                        </div>
                        <div class="relative">
                            <input type="text" placeholder="Buscar..." class="pl-10 pr-4 py-2 border rounded-md text-sm w-64 bg-gray-50">
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
                            @forelse($conductores as $con)
                            <tr>
                                <td>CON-{{ str_pad($con->idper, 3, '0', STR_PAD_LEFT) }}</td>
                                <td><strong>{{ $con->nomper }} {{ $con->apeper }}</strong></td>
                                <td>{{ number_format($con->ndocper, 0, ',', '.') }}</td>
                                <td>{{ $con->emaper }}</td>
                                <td>
                                    <span class="mup-state-badge {{ $con->actper ? 'mup-state-active' : 'mup-state-inactive' }}">
                                        {{ $con->actper ? 'Activo' : 'Inactivo' }}
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
                                <td colspan="6" class="text-center py-10 text-gray-500">No hay conductores registrados.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="mup-management-row">
                {{-- FORMULARIO: Nuevo Conductor --}}
                <section class="mup-card">
                    <div class="mup-card-header-soft">
                        <div class="flex items-center gap-3">
                            <iconify-icon icon="lucide:user-round-plus" class="text-2xl text-[#0d3b5a]"></iconify-icon>
                            <div>
                                <div class="mup-card-title">Nuevo conductor</div>
                                <div class="mup-card-subtitle">Registra un nuevo conductor con su información personal y datos operativos.</div>
                            </div>
                        </div>
                    </div>
                    <div class="mup-card-body">
                        <form action="{{ route('admin.mup.conductores.store') }}" method="POST">
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

                            <div class="text-[13px] font-bold text-[#0d3b5a] mt-8 mb-4 uppercase tracking-wider">Datos operativos</div>
                            <div class="border-b mb-6"></div>

                            <div class="mup-form-grid">
                                <div class="mup-form-group">
                                    <label class="mup-label">Categoría del pase <span class="mup-required">*</span></label>
                                    <select name="catcon" class="mup-input" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($categorias as $cat)
                                            <option value="{{ $cat->idval }}">{{ $cat->nomval }}</option>
                                        @endforeach
                                        {{-- Fallback si no hay categorías sembradas --}}
                                        @if($categorias->isEmpty())
                                            <option value="1">C2</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">No. licencia de tránsito <span class="mup-required">*</span></label>
                                    <input type="text" name="nliccon" class="mup-input" placeholder="Ej. 1234567890" required value="{{ old('nliccon') }}">
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Fecha de vencimiento <span class="mup-required">*</span></label>
                                    <input type="date" name="fvencon" class="mup-input" required value="{{ old('fvencon') }}">
                                </div>
                                <div class="mup-form-group">
                                    <label class="mup-label">Activo (SI/NO) <span class="mup-required">*</span></label>
                                    <select name="actper" class="mup-input" required>
                                        <option value="1" {{ old('actper') == '1' ? 'selected' : '' }}>SI</option>
                                        <option value="0" {{ old('actper') == '0' ? 'selected' : '' }}>NO</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-8 flex justify-end gap-3 pt-6 border-t">
                                <button type="reset" class="mup-btn mup-btn-outline">Cancelar</button>
                                <button type="submit" class="mup-btn mup-btn-primary">
                                    <iconify-icon icon="lucide:save"></iconify-icon>
                                    Guardar conductor
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                {{-- CARD: Permisos --}}
                <section class="mup-card">
                    <div class="mup-card-body">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <div class="mup-card-title text-lg">Permisos del perfil: Conductores</div>
                                <div class="mup-card-subtitle">Configuración específica para el perfil conductor dentro del flujo operativo del CDA.</div>
                            </div>
                        </div>

                        <div class="flex gap-6 border-b mb-6 text-sm font-medium">
                            <div class="text-[#0d3b5a] border-b-2 border-[#0d3b5a] pb-2 cursor-pointer">Módulos</div>
                            <div class="text-gray-400 pb-2 cursor-pointer">Resumen</div>
                        </div>

                        <div class="space-y-4">
                            @php
                                $modulos = ['Dashboard', 'Agenda de revisión', 'Historial de vehículos', 'Documentos asociados', 'Alertas de vencimiento', 'Actualización de datos'];
                            @endphp
                            <div class="grid grid-cols-5 text-[11px] font-bold text-gray-400 uppercase mb-2">
                                <div class="col-span-1">Módulo</div>
                                <div class="text-center">Ver</div>
                                <div class="text-center">Crear</div>
                                <div class="text-center">Edit</div>
                                <div class="text-center">Elim</div>
                            </div>
                            @foreach($modulos as $mod)
                            <div class="grid grid-cols-5 py-3 border-t items-center">
                                <div class="text-sm font-medium">{{ $mod }}</div>
                                <div class="flex justify-center"><iconify-icon icon="lucide:check" class="text-white bg-[#0d3b5a] rounded p-0.5 text-[10px]"></iconify-icon></div>
                                <div class="text-center text-gray-300">-</div>
                                <div class="text-center text-gray-300">-</div>
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
