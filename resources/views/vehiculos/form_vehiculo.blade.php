@extends('layouts.app')

@section('content')
<div class="veh-form-wrapper" x-data="vehiculoFormInit()">

    <style>
        .veh-form-wrapper {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            padding: 24px 40px 48px;
            color: #0f1724;
            max-width: 1280px;
            margin: 0 auto;
        }

        /* Header */
        .veh-form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }
        .veh-form-header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .veh-form-header-left .back-btn {
            width: 40px; height: 40px;
            display: flex; align-items: center; justify-content: center;
            background: #ffffff; border: 1px solid #e2e8f0;
            border-radius: 8px; cursor: pointer; color: #0f1724;
            transition: 0.2s; text-decoration: none;
        }
        .veh-form-header-left .back-btn:hover { background: #f1f5f9; }
        .veh-form-header-left h1 {
            font-size: 24px; font-weight: 600; color: #0b3a5a; margin: 0 0 4px 0;
        }
        .veh-form-header-left p {
            font-size: 14px; color: #9aa6b2; margin: 0;
        }
        .veh-form-actions { display: flex; gap: 12px; }

        /* Buttons */
        .vbtn-form {
            display: inline-flex; align-items: center; justify-content: center;
            gap: 8px; padding: 10px 18px; border-radius: 8px;
            font-size: 14px; font-weight: 500; cursor: pointer;
            border: none; transition: 0.2s; white-space: nowrap;
            text-decoration: none;
        }
        .vbtn-form-primary { background: #0b3a5a; color: #fff; }
        .vbtn-form-primary:hover { background: #0a2e48; }
        .vbtn-form-secondary { background: #f1f5f9; color: #0f1724; border: 1px solid #e2e8f0; }
        .vbtn-form-secondary:hover { background: #e2e8f0; }

        /* Card */
        .veh-form-card {
            background: #ffffff; border: 1px solid rgba(0,0,0,0.08);
            border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            padding: 36px;
        }

        /* Form Grid */
        .veh-form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            column-gap: 24px;
            row-gap: 20px;
        }

        /* Section Title */
        .veh-section-title {
            grid-column: 1 / -1;
            font-size: 16px; font-weight: 600; color: #0b3a5a;
            margin: 0; padding-top: 28px;
            border-top: 1px solid rgba(0,0,0,0.08);
            display: flex; align-items: center; gap: 10px;
        }
        .veh-section-title:first-child { padding-top: 0; border-top: none; }
        .veh-section-title i { font-size: 16px; color: #6b7280; }

        /* Input group */
        .veh-field { display: flex; flex-direction: column; gap: 6px; }
        .veh-field.span-2 { grid-column: span 2; }
        .veh-field.span-3 { grid-column: 1 / -1; }

        .veh-field label {
            font-size: 13px; font-weight: 500; color: #0f1724;
        }
        .veh-field label .req { color: #e03e3e; }

        .veh-field input,
        .veh-field select {
            width: 100%; padding: 10px 12px;
            border: 1px solid #e2e8f0; border-radius: 8px;
            font-size: 14px; color: #0f1724;
            background: #ffffff; outline: none;
            transition: border-color 0.2s;
        }
        .veh-field input:focus,
        .veh-field select:focus { border-color: #0b3a5a; box-shadow: 0 0 0 3px rgba(11,58,90,0.08); }
        .veh-field input::placeholder { color: #94a3b8; }
        .veh-field input[readonly],
        .veh-field input[disabled] { background: #f8fafc; color: #9aa6b2; cursor: not-allowed; }

        /* Footer */
        .veh-form-footer {
            display: flex; justify-content: flex-end; align-items: center;
            gap: 16px; margin-top: 36px; padding-top: 24px;
            border-top: 1px solid rgba(0,0,0,0.08);
        }

        /* Success message */
        .veh-alert-success {
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            border: 1px solid #6ee7b7; border-radius: 8px;
            padding: 12px 20px; margin-bottom: 20px;
            font-size: 14px; color: #065f46;
            display: flex; align-items: center; gap: 10px;
        }

        /* Error messages */
        .veh-alert-error {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            border: 1px solid #fca5a5; border-radius: 8px;
            padding: 12px 20px; margin-bottom: 20px;
            font-size: 13px; color: #991b1b;
        }
        .veh-alert-error ul { margin: 6px 0 0 16px; }

        .veh-field .field-error {
            font-size: 12px; color: #e03e3e; margin-top: 2px;
        }

        @media (max-width: 900px) {
            .veh-form-grid { grid-template-columns: repeat(2, 1fr); }
            .veh-form-wrapper { padding: 16px 20px 32px; }
        }
        @media (max-width: 600px) {
            .veh-form-grid { grid-template-columns: 1fr; }
            .veh-field.span-2 { grid-column: span 1; }
            .veh-form-header { flex-direction: column; gap: 16px; align-items: flex-start; }
        }
    </style>

    {{-- Flash success --}}
    @if(session('success'))
    <div class="veh-alert-success">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Validation errors --}}
    @if($errors->any())
    <div class="veh-alert-error">
        <strong><i class="fa-solid fa-triangle-exclamation"></i> Corrige los siguientes errores:</strong>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Header --}}
    <div class="veh-form-header">
        <div class="veh-form-header-left">
            @php
                $user = auth()->user();
                $prefix = $user->hasRole('Administrador') ? 'admin' : ($user->hasRole('Digitador') ? 'digitador' : 'empresa');
            @endphp
            <a href="{{ route($prefix . '.vehiculos.index') }}" class="back-btn" title="Volver a vehículos">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1>{{ $modo === 'crear' ? 'Nuevo Vehículo' : 'Editar Vehículo' }}</h1>
                <p>{{ $modo === 'crear' ? 'Registro de un nuevo activo vehicular en el sistema' : 'Modificar los datos del vehículo ' . ($vehiculo->placaveh ?? '') }}</p>
            </div>
        </div>
        <div class="veh-form-actions">
            <a href="{{ route($prefix . '.vehiculos.index') }}" class="vbtn-form vbtn-form-secondary">Cancelar</a>
            <button type="submit" form="vehiculo-form" class="vbtn-form vbtn-form-primary">
                <i class="fa-solid fa-floppy-disk"></i>
                {{ $modo === 'crear' ? 'Guardar vehículo' : 'Actualizar vehículo' }}
            </button>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="veh-form-card">
        <form
            id="vehiculo-form"
            method="POST"
            action="{{ $modo === 'crear'
                ? route($prefix . '.vehiculos.store')
                : route($prefix . '.vehiculos.update', $vehiculo->idveh) }}"
        >
            @csrf
            @if($modo === 'editar')
                @method('PUT')
            @endif

            <div class="veh-form-grid">

                {{-- ═══════ SECCIÓN 1: INFORMACIÓN BÁSICA ═══════ --}}
                <div class="veh-section-title">
                    <i class="fa-solid fa-circle-info"></i> Información Básica
                </div>

                <div class="veh-field">
                    <label>No. interno</label>
                    <input type="text" name="nordveh" value="{{ old('nordveh', $vehiculo->nordveh ?? ($modo === 'crear' ? 'Autogenerado' : '')) }}" readonly style="background-color: #f8fafc; color: #9aa6b2; cursor: not-allowed;" />
                    @error('nordveh') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="veh-field">
                    <label>No. placa <span class="req">*</span></label>
                    <input type="text" name="placaveh" value="{{ old('placaveh', $vehiculo->placaveh ?? '') }}" placeholder="Ej: ABC123" maxlength="6" required />
                    @error('placaveh') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="veh-field">
                    <label>Tipo de servicio <span class="req">*</span></label>
                    <select name="tipo_servicio" required>
                        <option value="" disabled {{ old('tipo_servicio', $vehiculo->tipo_servicio ?? '') == '' ? 'selected' : '' }}>Seleccionar servicio</option>
                        <option value="1" {{ old('tipo_servicio', $vehiculo->tipo_servicio ?? '') == 1 ? 'selected' : '' }}>Particular</option>
                        <option value="2" {{ old('tipo_servicio', $vehiculo->tipo_servicio ?? '') == 2 ? 'selected' : '' }}>Público</option>
                    </select>
                    @error('tipo_servicio') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="veh-field" style="position: relative;" @click.away="showMarcaList = false">
                    <label>Línea (Marca) <span class="req">*</span></label>
                    <input type="hidden" name="linveh" :value="selectedMarcaId">
                    <input type="text" x-model="searchMarca" @focus="showMarcaList = true" placeholder="Escriba para buscar o seleccionar..." autocomplete="off" required>
                    
                    <div x-show="showMarcaList" style="position: absolute; top: 100%; left: 0; right: 0; max-height: 200px; overflow-y: auto; background: white; border: 1px solid #e2e8f0; border-radius: 8px; z-index: 10; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);" x-cloak>
                        <template x-for="m in filteredMarcas" :key="m.idmar">
                            <div @click="selectMarca(m)" x-text="m.nommarlin" style="padding: 10px 12px; cursor: pointer; border-bottom: 1px solid #f1f5f9;" onmouseover="this.style.backgroundColor='#f8fafc'" onmouseout="this.style.backgroundColor='white'"></div>
                        </template>
                        <div x-show="filteredMarcas.length === 0" style="padding: 10px 12px; color: #9aa6b2;">No se encontraron marcas</div>
                    </div>
                    @error('linveh') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="veh-field">
                    <label>Modelo (Año) <span class="req">*</span></label>
                    <input type="number" name="modveh" value="{{ old('modveh', $vehiculo->modveh ?? '') }}" placeholder="Ej: 2024" min="1950" max="2035" required />
                    @error('modveh') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="veh-field">
                    <label>Color del vehículo <span class="req">*</span></label>
                    <input type="text" name="colveh" value="{{ old('colveh', $vehiculo->colveh ?? '') }}" placeholder="Ej: Blanco" maxlength="20" required />
                    @error('colveh') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                {{-- ═══════ SECCIÓN 2: ESPECIFICACIONES TÉCNICAS ═══════ --}}
                <div class="veh-section-title">
                    <i class="fa-solid fa-wrench"></i> Especificaciones Técnicas
                </div>

                <div class="veh-field">
                    <label>Clase de vehículo <span class="req">*</span></label>
                    <select name="clveh" required>
                        <option value="">Seleccionar clase</option>
                        @foreach($clases as $c)
                            <option value="{{ $c->idval }}" {{ old('clveh', $vehiculo->clveh ?? '') == $c->idval ? 'selected' : '' }}>
                                {{ $c->nomval }}
                            </option>
                        @endforeach
                    </select>
                    @error('clveh') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="veh-field">
                    <label>Tipo de motor <span class="req">*</span></label>
                    <select name="tmotveh" style="background-color: #f8fafc; color: #9aa6b2; pointer-events: none;" tabindex="-1" readonly required>
                        @foreach($tiposMotor as $tm)
                            @if(stripos($tm->nomval, '4 T') !== false || stripos($tm->nomval, '4T') !== false)
                                <option value="{{ $tm->idval }}" selected>{{ $tm->nomval }}</option>
                            @endif
                        @endforeach
                    </select>
                    @error('tmotveh') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="veh-field">
                    <label>Tipo de combustible <span class="req">*</span></label>
                    <select name="combuveh" style="background-color: #f8fafc; color: #9aa6b2; pointer-events: none;" tabindex="-1" readonly required>
                        @foreach($combustibles as $cb)
                            @if(stripos($cb->nomval, 'DIESEL') !== false)
                                <option value="{{ $cb->idval }}" selected>{{ $cb->nomval }}</option>
                            @endif
                        @endforeach
                    </select>
                    @error('combuveh') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="veh-field">
                    <label>Número de sillas <span class="req">*</span></label>
                    <input type="number" name="capveh" value="{{ old('capveh', $vehiculo->capveh ?? '') }}" placeholder="Ej: 5" min="1" required />
                    @error('capveh') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="veh-field">
                    <label>Cilindraje (cc) <span class="req">*</span></label>
                    <input type="number" name="cilveh" value="{{ old('cilveh', $vehiculo->cilveh ?? '') }}" placeholder="Ej: 1600" min="0" required />
                    @error('cilveh') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="veh-field">
                    <label>Categoría de carga <span class="req">*</span></label>
                    <select name="crgveh" required>
                        <option value="">Seleccionar carga</option>
                        @foreach($cargas as $cr)
                            <option value="{{ $cr->idval }}" {{ old('crgveh', $vehiculo->crgveh ?? '') == $cr->idval ? 'selected' : '' }}>
                                {{ $cr->nomval }}
                            </option>
                        @endforeach
                    </select>
                    @error('crgveh') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="veh-field">
                    <label>No. motor <span class="req">*</span></label>
                    <input type="text" name="nmotveh" value="{{ old('nmotveh', $vehiculo->nmotveh ?? '') }}" placeholder="Número de motor" maxlength="30" required />
                    @error('nmotveh') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="veh-field">
                    <label>No. chasis <span class="req">*</span></label>
                    <input type="text" name="nchaveh" value="{{ old('nchaveh', $vehiculo->nchaveh ?? '') }}" placeholder="Número de chasis" maxlength="30" required />
                    @error('nchaveh') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="veh-field">
                    <label>Blindaje <span class="req">*</span></label>
                    <select name="blinveh" required>
                        <option value="">Seleccionar</option>
                        <option value="2" {{ old('blinveh', $vehiculo->blinveh ?? '') == 2 ? 'selected' : '' }}>No</option>
                        <option value="1" {{ old('blinveh', $vehiculo->blinveh ?? '') == 1 ? 'selected' : '' }}>Sí</option>
                    </select>
                    @error('blinveh') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                {{-- ═══════ SECCIÓN 3: DOCUMENTACIÓN Y SEGUROS ═══════ --}}
                <div class="veh-section-title">
                    <i class="fa-solid fa-file-lines"></i> Documentación y Seguros
                </div>

                <div class="veh-field">
                    <label>No. licencia de tránsito <span class="req">*</span></label>
                    <input type="number" name="lictraveh" value="{{ old('lictraveh', $vehiculo->lictraveh ?? '') }}" placeholder="Solo números" required />
                    @error('lictraveh') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="veh-field">
                    <label>Fecha de matrícula <span class="req">*</span></label>
                    <input type="date" name="fmatv" value="{{ old('fmatv', $vehiculo->fmatv ?? '') }}" required />
                    @error('fmatv') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="veh-field">
                    <label>Venc. tarjeta de operación</label>
                    <input type="date" name="fecvenr" value="{{ old('fecvenr', $vehiculo->fecvenr ?? '') }}" />
                    @error('fecvenr') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                {{-- SOAT --}}
                <div class="veh-field">
                    <label>Número de SOAT <span class="req">*</span></label>
                    <input type="number" name="soat" value="{{ old('soat', $vehiculo->soat ?? '') }}" placeholder="Solo números" required />
                    @error('soat') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="veh-field">
                    <label>Vencimiento SOAT <span class="req">*</span></label>
                    <input type="date" name="fecvens" value="{{ old('fecvens', $vehiculo->fecvens ?? '') }}" required />
                    @error('fecvens') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                {{-- Tecnomecánica --}}
                <div class="veh-field">
                    <label>Certificado Tecnomecánica <span class="req">*</span></label>
                    <input type="number" name="tecmecveh" value="{{ old('tecmecveh', $vehiculo->tecmecveh ?? '') }}" placeholder="Solo números" required />
                    @error('tecmecveh') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="veh-field">
                    <label>Vencimiento Tecnomecánica <span class="req">*</span></label>
                    <input type="date" name="fecvent" value="{{ old('fecvent', $vehiculo->fecvent ?? '') }}" required />
                    @error('fecvent') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                {{-- Extracontractual --}}
                <div class="veh-field">
                    <label>Póliza Extracontractual <span class="req">*</span></label>
                    <input type="number" name="extcontveh" value="{{ old('extcontveh', $vehiculo->extcontveh ?? '') }}" placeholder="Solo números" required />
                    @error('extcontveh') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="veh-field">
                    <label>Vencimiento Extracontractual <span class="req">*</span></label>
                    <input type="date" name="fecvene" value="{{ old('fecvene', $vehiculo->fecvene ?? '') }}" required />
                    @error('fecvene') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                {{-- ═══════ SECCIÓN 4: VINCULACIÓN INICIAL ═══════ --}}
                <div class="veh-section-title">
                    <i class="fa-solid fa-users"></i> Vinculación
                </div>

                <div class="veh-field">
                    <label>Propietario <span class="req">*</span></label>
                    <select name="prop" required>
                        <option value="">Seleccionar</option>
                        @foreach($propietarios as $p)
                            <option value="{{ $p->idper }}" {{ old('prop', $vehiculo->prop ?? '') == $p->idper ? 'selected' : '' }}>
                                {{ $p->nomper }} {{ $p->apeper ?? '' }} — {{ $p->ndocper ?? '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('prop') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="veh-field">
                    <label>Conductor asignado <span class="req">*</span></label>
                    <select name="cond" required>
                        <option value="">Seleccionar</option>
                        @foreach($conductores as $p)
                            <option value="{{ $p->idper }}" {{ old('cond', $vehiculo->cond ?? '') == $p->idper ? 'selected' : '' }}>
                                {{ $p->nomper }} {{ $p->apeper ?? '' }} — {{ $p->ndocper ?? '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('cond') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="veh-field">
                    <label>Empresa asociada</label>
                    <select name="idemp">
                        <option value="">Sin empresa / Independiente</option>
                        @foreach($empresas as $e)
                            <option value="{{ $e->idemp }}" {{ old('idemp', $vehiculo->idemp ?? '') == $e->idemp ? 'selected' : '' }}>
                                {{ $e->razsoem }}
                            </option>
                        @endforeach
                    </select>
                    @error('idemp') <span class="field-error">{{ $message }}</span> @enderror
                </div>

            </div>

            {{-- Footer --}}
            <div class="veh-form-footer">
                <a href="{{ route($prefix . '.vehiculos.index') }}" class="vbtn-form vbtn-form-secondary">Descartar</a>
                <button type="submit" class="vbtn-form vbtn-form-primary">
                    <i class="fa-solid fa-check"></i>
                    {{ $modo === 'crear' ? 'Crear vehículo' : 'Guardar cambios' }}
                </button>
            </div>

        </form>
    </div>

</div>

<script>
    function vehiculoFormInit() {
        return {
            marcas: @json($marcas),
            propietarios: @json($propietarios),
            conductores: @json($conductores),
            
            // Marca (Línea)
            searchMarca: '{{ $vehiculo->marca->nommarlin ?? '' }}',
            selectedMarcaId: '{{ old('linveh', $vehiculo->linveh ?? '') }}',
            showMarcaList: false,
            
            get filteredMarcas() {
                if (!this.searchMarca) return this.marcas;
                return this.marcas.filter(m => m.nommarlin.toLowerCase().includes(this.searchMarca.toLowerCase()));
            },
            
            selectMarca(m) {
                this.selectedMarcaId = m.idmar;
                this.searchMarca = m.nommarlin;
                this.showMarcaList = false;
            }
        }
    }
</script>
@endsection
