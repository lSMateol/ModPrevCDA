@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/mup.css') }}">
<script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>

<div class="mup-container" x-data="{ 
    marcarTodos: function() {
        const checkboxes = document.querySelectorAll('input[type=checkbox]');
        checkboxes.forEach(c => c.checked = true);
    }
}">
    <header class="mup-topbar">
        <div class="mup-page-title">
            <h1>
                <a href="{{ route('admin.mup.conductores') }}" class="flex items-center justify-center bg-gray-100 p-2 rounded-md mr-3 text-sm hover:bg-gray-200 transition">
                    <iconify-icon icon="lucide:arrow-left"></iconify-icon>
                </a>
                Nuevo Perfil del Sistema
            </h1>
            <p>Crea un nuevo rol y define sus alcances, permisos y accesos en el sistema del CDA.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.mup.conductores') }}" class="mup-btn mup-btn-outline">Cancelar</a>
            <button type="button" @click="document.getElementById('form-perfil').submit()" class="mup-btn mup-btn-primary">
                <iconify-icon icon="lucide:save"></iconify-icon>
                Guardar perfil
            </button>
        </div>
    </header>

    <div class="mup-content-scroll">
        <div class="max-w-4xl mx-auto space-y-6">
            <form id="form-perfil" action="{{ route('admin.mup.perfil.store') }}" method="POST">
                @csrf
                {{-- INFO CARD --}}
                <section class="mup-card">
                    <div class="mup-card-header-soft">
                        <div class="flex items-center gap-3">
                            <iconify-icon icon="lucide:shield" class="text-2xl text-[#0d3b5a]"></iconify-icon>
                            <div>
                                <div class="mup-card-title">Información del perfil</div>
                                <div class="mup-card-subtitle">Datos básicos que identificarán al rol dentro del sistema MUP.</div>
                            </div>
                        </div>
                    </div>
                    <div class="mup-card-body">
                        <div class="mup-form-grid">
                            <div class="mup-form-group">
                                <label class="mup-label">Nombre del perfil <span class="mup-required">*</span></label>
                                <input type="text" name="nompef" class="mup-input" placeholder="Ej. Supervisor de patio" required value="{{ old('nompef') }}">
                            </div>
                            <div class="mup-form-group">
                                <label class="mup-label">Alcance / Tipo <span class="mup-required">*</span></label>
                                <select name="tipo_pef" class="mup-input" required>
                                    <option value="Operativo">Operativo</option>
                                    <option value="Administrativo">Administrativo</option>
                                    <option value="Gerencial">Gerencial</option>
                                    <option value="Externo">Externo</option>
                                </select>
                            </div>
                            <div class="mup-form-group span-2">
                                <label class="mup-label">Descripción del rol</label>
                                <textarea name="des_pef" class="mup-input py-3 h-24" placeholder="Escribe brevemente qué funciones generales tendrá este perfil en el sistema...">{{ old('des_pef') }}</textarea>
                            </div>
                            <div class="mup-form-group">
                                <label class="mup-label">Estado inicial</label>
                                <select name="activo" class="mup-input">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- PERMISSIONS CARD --}}
                <section class="mup-card">
                    <div class="mup-card-body pt-7">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <div class="mup-card-title text-lg">Configuración de permisos</div>
                                <div class="mup-card-subtitle">Define qué podrá ver, crear, editar o eliminar este perfil en cada módulo del sistema.</div>
                            </div>
                            <button type="button" @click="marcarTodos" class="mup-btn mup-btn-outline h-9 px-4 text-xs font-bold">
                                Marcar todos
                            </button>
                        </div>

                        <div class="flex gap-5 border-b mb-4 text-sm font-medium">
                            <div class="text-[#0d3b5a] border-b-2 border-[#0d3b5a] pb-2 cursor-pointer">Módulos principales</div>
                            <div class="text-gray-400 pb-2 cursor-pointer">Reportes y exportaciones</div>
                            <div class="text-gray-400 pb-2 cursor-pointer">Configuración global</div>
                        </div>

                        <div class="mup-table-wrap">
                            <div class="grid grid-cols-5 text-[11px] font-bold text-gray-400 uppercase py-3">
                                <div class="col-span-1">Módulo</div>
                                <div class="text-center">Ver</div>
                                <div class="text-center">Crear</div>
                                <div class="text-center">Editar</div>
                                <div class="text-center">Eliminar</div>
                            </div>

                            @foreach($modulos as $modulo)
                            <div class="grid grid-cols-5 py-4 border-t items-center">
                                <div class="text-sm font-medium text-gray-800">{{ $modulo->nompag }}</div>
                                <div class="flex justify-center">
                                    <input type="checkbox" name="permisos[{{ $modulo->nompag }}][ver]" class="w-4 h-4 rounded border-gray-300 text-[#0d3b5a] focus:ring-[#0d3b5a]">
                                </div>
                                <div class="flex justify-center">
                                    <input type="checkbox" name="permisos[{{ $modulo->nompag }}][crear]" class="w-4 h-4 rounded border-gray-300 text-[#0d3b5a] focus:ring-[#0d3b5a]">
                                </div>
                                <div class="flex justify-center">
                                    <input type="checkbox" name="permisos[{{ $modulo->nompag }}][editar]" class="w-4 h-4 rounded border-gray-300 text-[#0d3b5a] focus:ring-[#0d3b5a]">
                                </div>
                                <div class="flex justify-center">
                                    <input type="checkbox" name="permisos[{{ $modulo->nompag }}][eliminar]" class="w-4 h-4 rounded border-gray-300 text-[#0d3b5a] focus:ring-[#0d3b5a]">
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="mt-8 pt-6 border-t flex flex-wrap gap-5 text-xs text-gray-400">
                            <div class="flex items-center gap-2">
                                <div class="w-3.5 h-3.5 rounded bg-green-500"></div>
                                <span>Permitido (Tilde para habilitar)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3.5 h-3.5 rounded border border-gray-200"></div>
                                <span>No permitido</span>
                            </div>
                            <div>- No aplica en este módulo</div>
                        </div>
                    </div>
                </section>
            </form>
        </div>
    </div>
</div>
@endsection
