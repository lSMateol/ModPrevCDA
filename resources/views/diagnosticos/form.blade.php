@extends('layouts.app')

@section('content')
@php
    $prefix = Auth::user()->hasRole('Administrador') ? 'admin' : 'digitador';
@endphp

<main class="pt-24 px-4 pb-12 space-y-10 max-w-2xl mx-auto w-full">
    <!-- Encabezado del Formulario -->
    <header class="space-y-4">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-black tracking-tight text-[#001834]">Detalle de Diagnóstico</h1>
            <span class="material-symbols-outlined text-[#001834] opacity-30">more_vert</span>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <div class="inline-flex items-center gap-2 bg-[#001834] text-primary-fixed-dim px-4 py-1.5 rounded-xl shadow-lg shadow-[#001834]/10">
                <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">directions_car</span>
                <span class="font-black tracking-widest uppercase text-xs">{{ $diagnostico->vehiculo->placaveh }}</span>
            </div>
            <div class="inline-flex items-center gap-2 px-3 py-1 text-on-surface-variant/60 font-body">
                <span class="material-symbols-outlined text-sm">calendar_today</span>
                <span class="font-bold text-xs uppercase tracking-tighter" id="current-date-display">
                    {{ \Carbon\Carbon::parse($diagnostico->fecdia)->translatedFormat('d F, Y') }}
                </span>
            </div>
        </div>
    </header>

    <form id="diagnostico-form" method="POST" action="{{ route($prefix . '.diagnosticos.update', $diagnostico->iddia) }}" class="space-y-10">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-2xl shadow-sm animate-pulse">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-red-600">error</span>
                    <h3 class="text-sm font-black text-red-800 uppercase tracking-widest">Errores de Validación</h3>
                </div>
                <p class="text-xs text-red-600 mt-2 font-bold">Por favor verifica los campos marcados en rojo. Asegúrate de que los valores numéricos estén dentro de los rangos permitidos.</p>
            </div>
        @endif

        @foreach($parametrosPorTipo as $tipo => $params)
        @php
            $t = strtoupper($tipo);
            $formType = $diagnostico->tipo_formulario ?? '';
            $showSection = true;

            if ($formType == 'diesel_basico' && str_contains($t, 'GASES')) $showSection = false;
            elseif ($formType == 'otto_sin_gases' && (str_contains($t, 'GASES') || str_contains($t, 'OTTO') || str_contains($t, 'CICLO OTTO'))) $showSection = false;
        @endphp

        @if($showSection)
        <section class="space-y-6">
            <!-- Título de Sección -->
            <div class="flex items-center gap-3 pb-3 border-b-2 border-outline-variant/10">
                <span class="material-symbols-outlined text-primary-fixed-dim bg-[#001834] p-2 rounded-xl text-lg shadow-md shadow-[#001834]/20">
                    @switch(strtoupper($tipo))
                        @case('LUCES')
                        @case('LUCES BAJAS') lightbulb @break
                        @case('MOTOR DIESEL')
                        @case('V. DIESEL') precision_manufacturing @break
                        @case('DEFECTOS') warning @break
                        @case('INSPECCION VISUAL')
                        @case('DEFECTOS INSPECCION VISUAL Y SENSORIAL') visibility @break
                        @case('EMISIONES AUDIBLES') volume_up @break
                        @case('EMISIÓN DE GASES') co2 @break
                        @case('V. CICLO OTTO') settings_input_component @break
                        @default build @break
                    @endswitch
                </span>
                <h2 class="font-headline font-black text-sm uppercase tracking-[0.2em] text-on-surface-variant">{{ $tipo }}</h2>
                
                @if(str_contains(strtoupper($tipo), 'DIESEL') || str_contains(strtoupper($tipo), 'OTTO') || str_contains(strtoupper($tipo), 'GASES'))
                    <button type="button" onclick="fillSimulacion('{{ strtoupper($tipo) }}')" class="ml-auto flex items-center gap-2 bg-[#ffba20] text-[#001834] px-4 py-2 rounded-xl font-black text-[10px] uppercase tracking-widest hover:scale-105 active:scale-95 transition-all shadow-lg shadow-[#ffba20]/20">
                        <span class="material-symbols-outlined text-xs">auto_fix_high</span>
                        Simular Aprobado
                    </button>
                @endif
            </div>

            @if(str_contains(strtoupper($tipo), 'VISUAL'))
                <!-- Especial para Inspección Visual (Multifila) -->
                @php
                    $descInspeccion = old('desc_inspeccion', $paramValues['desc_inspeccion'] ?? '');
                    $dataDecoded = @json_decode($descInspeccion, true);
                    $listaDefectos = is_array($dataDecoded) ? ($dataDecoded['list'] ?? $dataDecoded) : []; // Retrocompatibilidad
                    $generalObs = is_array($dataDecoded) ? ($dataDecoded['obs'] ?? '') : '';
                    if(!is_array($listaDefectos)) $listaDefectos = [];
                @endphp
                <div class="bg-surface-container-low p-6 rounded-2xl border border-outline-variant/10">
                    <div id="wrapper-defectos-visuales" class="space-y-4">
                        @forelse($listaDefectos as $index => $item)
                        <div class="defecto-row grid grid-cols-1 md:grid-cols-12 gap-4 p-4 bg-white rounded-xl shadow-sm relative items-start">
                            <input type="hidden" name="visual_defecto[]" value="{{ $item['grupo'] ?? 'N/A' }}">
                            <div class="space-y-1 md:col-span-3">
                                <label class="text-[0.6rem] font-black uppercase opacity-60">Tipo</label>
                                <select name="visual_tipo[]" class="w-full bg-surface-container-high border-none rounded-lg p-2.5 text-xs font-bold">
                                    <option value="Tipo A" {{ ($item['tipo'] ?? '') == 'Tipo A' ? 'selected' : '' }}>Tipo A</option>
                                    <option value="Tipo B" {{ ($item['tipo'] ?? '') == 'Tipo B' ? 'selected' : '' }}>Tipo B</option>
                                </select>
                            </div>
                            <div class="space-y-1 md:col-span-9">
                                <label class="text-[0.6rem] font-black uppercase opacity-60">Descripción del Hallazgo</label>
                                <textarea name="visual_obs[]" rows="2" placeholder="Especifique..." class="w-full bg-surface-container-high border-none rounded-lg p-2.5 text-xs font-bold resize-none">{{ $item['obs'] ?? ($item['desc'] ?? '') }}</textarea>
                            </div>
                            <button type="button" onclick="this.parentElement.remove()" class="absolute -top-2 -right-2 bg-red-100 text-red-600 rounded-full p-1 hover:bg-red-600 hover:text-white transition-all shadow-sm">
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
                        @empty
                            <p class="text-xs font-bold text-on-surface-variant opacity-40 italic text-center py-4" id="empty-defectos-msg">No se han registrado defectos del listado.</p>
                        @endforelse
                    </div>
                    
                    <div class="mt-6 pt-6 border-t border-dashed border-outline-variant/20 space-y-4">
                        <button type="button" id="add-defecto-visual" class="flex w-fit items-center gap-2 text-[#001834] font-black text-[0.65rem] uppercase tracking-widest bg-primary-fixed-dim/20 px-4 py-2 rounded-lg hover:bg-primary-fixed-dim transition-all">
                            <span class="material-symbols-outlined text-sm">add</span> Añadir Falla
                        </button>
                        
                        <div class="w-full space-y-2">
                            <label class="text-[0.7rem] font-black uppercase tracking-widest text-on-surface-variant opacity-80">Observaciones Generales / Otros Hallazgos</label>
                            <textarea id="visual_obs_general" rows="3" placeholder="Otros defectos no incluidos en la lista o comentarios adicionales..." class="w-full bg-surface-container-high border-none rounded-xl focus:ring-2 focus:ring-primary-fixed-dim p-4 text-sm font-semibold text-[#001834] transition-all">{{ $generalObs }}</textarea>
                        </div>
                    </div>

                    <!-- Campo oculto para cumplir con el validador actual -->
                    <input type="hidden" name="desc_inspeccion" id="desc_inspeccion_json" value="{{ old('desc_inspeccion', $descInspeccion) }}">
                </div>
            @else
                <!-- Parámetros en Grid -->
                <div class="grid grid-cols-1 {{ $params->count() > 3 ? 'md:grid-cols-2' : '' }} gap-4">
                    @foreach($params as $param)
                    @if(!in_array($param->nompar, ['grupo_inspeccion', 'tipo_defecto', 'desc_inspeccion']))
                    <div class="flex flex-col gap-2 {{ $param->control == 'textarea' ? 'col-span-full' : '' }}">
                        @if($param->control == 'radio')
                            <div class="flex flex-col gap-3 p-4 {{ $errors->has($param->nompar) ? 'bg-red-50 border-red-200' : 'bg-surface-container-low border-transparent' }} rounded-2xl border hover:border-[#001834]/5 transition-all {{ $param->nompar == 'exploradoras' ? 'hidden exploradoras-input' : '' }}">
                                <label class="text-[0.65rem] font-black uppercase tracking-widest {{ $errors->has($param->nompar) ? 'text-red-700' : 'text-on-surface-variant' }} opacity-60 leading-tight">
                                    {{ str_replace('_', ' ', $param->nompar) }}
                                </label>
                                <div class="flex flex-wrap gap-5">
                                    @php
                                        $esLuces = str_contains(strtoupper($tipo), 'LUCES');
                                        $opciones = $esLuces ? ['funciona','no_funciona'] : ['si','no','na'];
                                        
                                        $currentVal = old($param->nompar, $paramValues[$param->nompar] ?? '');
                                        
                                        // Default to funciona if it's a mandatory field and empty
                                        if ($currentVal === '' && in_array($param->nompar, ['reversa', 'frenos', 'direccionales'])) {
                                            $currentVal = 'funciona';
                                        }
                                        
                                        // Default to NO or NA for defects if empty
                                        if ($currentVal === '' && !$esLuces) {
                                            if (in_array($param->nompar, ['dilusion_gasolina', 'Criterios_de_validacion'])) {
                                                $currentVal = 'na';
                                            } else {
                                                $currentVal = str_contains(strtolower($param->nompar), 'criterios') ? 'si' : 'no';
                                            }
                                        }
                                    @endphp
                                    @foreach($opciones as $opc)
                                    <label class="flex items-center gap-2 cursor-pointer group">
                                        <input type="radio" name="{{ $param->nompar }}" value="{{ $opc }}" 
                                            {{ $currentVal === $opc ? 'checked' : '' }} 
                                            class="w-5 h-5 text-[#ffba20] border-2 border-outline-variant/30 focus:ring-offset-0 focus:ring-0 cursor-pointer checked:border-[#ffba20] bg-white transition-all">
                                        <span class="text-[0.65rem] font-black uppercase tracking-tighter text-on-surface group-hover:text-[#ffba20] transition-colors">
                                            {{ $opc == 'na' ? 'N/A' : ($opc == 'no_funciona' ? 'No funciona' : $opc) }}
                                        </span>
                                    </label>
                                    @endforeach
                                    
                                    @if($param->nompar == 'exploradoras')
                                        <label class="hidden">
                                            <input type="radio" name="exploradoras" value="na" id="exploradoras_na_radio" {{ $currentVal === 'na' || $currentVal === '' ? 'checked' : '' }}>
                                        </label>
                                    @endif
                                </div>
                                @error($param->nompar)
                                    <span class="text-[10px] font-bold text-red-600 uppercase tracking-tighter">{{ $message }}</span>
                                @enderror
                            </div>
                        @elseif($param->control == 'number')
                            <div class="space-y-1">
                                <div class="flex items-center justify-between px-1">
                                    <label class="text-[0.65rem] font-black uppercase tracking-widest {{ $errors->has($param->nompar) ? 'text-red-700' : 'text-on-surface-variant' }} opacity-60">{{ str_replace('_', ' ', $param->nompar) }}</label>
                                    @if($param->rini !== null || $param->rfin !== null)
                                        <span class="text-[9px] font-black opacity-30 tracking-tighter">[{{ $param->rini }} - {{ $param->rfin }}]{{ $param->unipar }}</span>
                                    @endif
                                </div>
                                <input type="number" step="any" name="{{ $param->nompar }}" 
                                    value="{{ old($param->nompar, $paramValues[$param->nompar] ?? '') }}" 
                                    placeholder="---"
                                    data-min="{{ $param->rini }}"
                                    data-max="{{ $param->rfin }}"
                                    data-mantiene="{{ $param->se_mantiene }}"
                                    class="dynamic-param-input w-full {{ $errors->has($param->nompar) ? 'bg-red-50 border-red-300 ring-1 ring-red-300' : 'bg-surface-container-high border-none' }} rounded-xl focus:ring-2 focus:ring-primary-fixed-dim p-4 text-sm font-bold text-[#001834] transition-all">
                                @error($param->nompar)
                                    <span class="text-[10px] font-bold text-red-600 uppercase tracking-tighter px-1">{{ $message }}</span>
                                @enderror
                            </div>
                        @elseif($param->control == 'textarea')
                            <div class="space-y-1">
                                <label class="text-[0.65rem] font-black uppercase tracking-widest text-on-surface-variant opacity-60 px-1">{{ str_replace('_', ' ', $param->nompar) }}</label>
                                <textarea name="{{ $param->nompar }}" rows="4" 
                                    placeholder="Detalles o descripción técnica..."
                                    class="w-full bg-surface-container-high border-none rounded-2xl focus:ring-2 focus:ring-primary-fixed-dim p-4 text-sm font-semibold text-[#001834] transition-all">{{ old($param->nompar, $paramValues[$param->nompar] ?? '') }}</textarea>
                                @error($param->nompar)
                                    <span class="text-[10px] font-bold text-red-600 uppercase tracking-tighter px-1">{{ $message }}</span>
                                @enderror
                            </div>
                        @else
                            <div class="space-y-1">
                                <label class="text-[0.65rem] font-black uppercase tracking-widest text-on-surface-variant opacity-60 px-1">{{ str_replace('_', ' ', $param->nompar) }}</label>
                                <input type="text" name="{{ $param->nompar }}" 
                                    value="{{ old($param->nompar, $paramValues[$param->nompar] ?? '') }}" 
                                    class="w-full bg-surface-container-high border-none rounded-xl focus:ring-2 focus:ring-primary-fixed-dim p-4 text-sm font-bold text-[#001834] transition-all">
                                @error($param->nompar)
                                    <span class="text-[10px] font-bold text-red-600 uppercase tracking-tighter px-1">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif
                    </div>
                    @endif
                    @endforeach
                    @if(str_contains(strtoupper($tipo), 'LUCES'))
                    <!-- Lógica Especial: Luces Exploradoras y Comentarios -->
                    <div class="col-span-full bg-surface-container-low p-6 rounded-2xl border border-outline-variant/10 mt-4 space-y-6">
                        <div class="flex items-center justify-between pb-4 border-b border-outline-variant/5">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-primary-fixed-dim">wb_iridescent</span>
                                <label for="tiene_exploradoras" class="text-xs font-black uppercase tracking-widest text-on-surface-variant">¿Tiene luces exploradoras?</label>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="tiene_exploradoras" class="sr-only peer" {{ (old('exploradoras', $paramValues['exploradoras'] ?? '') != 'na' && !empty($paramValues['exploradoras'])) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-outline-variant/30 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#ffba20]"></div>
                            </label>
                        </div>
                        
                        <div id="exploradoras_container" class="hidden animate-in fade-in slide-in-from-top-2 duration-300">
                            <div class="bg-white p-4 rounded-xl border border-outline-variant/10">
                                <p class="text-[0.6rem] font-black uppercase opacity-60 mb-3 tracking-widest">Estado de Exploradoras</p>
                                <div class="flex gap-8">
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="radio" name="_exploradora_ui" value="funciona" class="w-5 h-5 text-[#ffba20] border-2 border-outline-variant/30 focus:ring-offset-0 focus:ring-0" {{ (old('exploradoras', $paramValues['exploradoras'] ?? '') == 'funciona') ? 'checked' : '' }}>
                                        <span class="text-xs font-bold uppercase tracking-tight text-on-surface group-hover:text-[#ffba20] transition-colors">Funciona Correctamente</span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="radio" name="_exploradora_ui" value="no_funciona" class="w-5 h-5 text-red-500 border-2 border-outline-variant/30 focus:ring-offset-0 focus:ring-0" {{ (old('exploradoras', $paramValues['exploradoras'] ?? '') == 'no_funciona') ? 'checked' : '' }}>
                                        <span class="text-xs font-bold uppercase tracking-tight text-on-surface group-hover:text-red-500 transition-colors">No Funciona</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2 pt-2">
                            <div class="flex items-center gap-2 text-on-surface-variant opacity-60">
                                <span class="material-symbols-outlined text-sm">chat_bubble</span>
                                <label class="text-[0.65rem] font-black uppercase tracking-widest">Comentarios generales de luces</label>
                            </div>
                            <textarea id="comentarios_luces" rows="3" placeholder="Ej: Faro cristal roto, intensidad baja en direccional trasera..." class="w-full bg-white border border-outline-variant/10 rounded-xl focus:ring-2 focus:ring-primary-fixed-dim p-4 text-sm font-semibold text-[#001834] transition-all"></textarea>
                            <p class="text-[9px] font-bold text-on-surface-variant/40 uppercase tracking-tight italic">* Estos comentarios se guardarán automáticamente como defectos en la inspección visual.</p>
                        </div>
                    </div>
                    @endif
                </div>
            @endif
        </section>
        @endif
        @endforeach

        <!-- Botón de Acción -->
        <div class="pt-10 pb-20">
            <button type="submit" class="w-full bg-gradient-to-r from-[#221500] to-[#3c2900] text-[#ffba20] py-5 rounded-2xl font-black uppercase tracking-[0.2em] shadow-2xl shadow-[#221500]/40 hover:scale-[1.01] active:scale-[0.98] transition-all flex items-center justify-center gap-3">
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">save</span>
                Guardar Diagnóstico
            </button>
        </div>
    </form>
</main>

<template id="tpl-defecto-row">
    <div class="defecto-row grid grid-cols-1 md:grid-cols-12 gap-4 p-4 bg-white rounded-xl shadow-sm relative items-start">
        <input type="hidden" name="visual_defecto[]" value="N/A">
        <div class="space-y-1 md:col-span-3">
            <label class="text-[0.6rem] font-black uppercase opacity-60">Tipo</label>
            <select name="visual_tipo[]" class="w-full bg-surface-container-high border-none rounded-lg p-2.5 text-xs font-bold">
                <option value="Tipo A">Tipo A</option>
                <option value="Tipo B">Tipo B</option>
            </select>
        </div>
        <div class="space-y-1 md:col-span-9">
            <label class="text-[0.6rem] font-black uppercase opacity-60">Observaciones</label>
            <textarea name="visual_obs[]" rows="2" placeholder="Describa el hallazgo..." class="w-full bg-surface-container-high border-none rounded-lg p-2.5 text-xs font-bold resize-none"></textarea>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="absolute -top-2 -right-2 bg-red-100 text-red-600 rounded-full p-1 hover:bg-red-600 hover:text-white transition-all shadow-sm">
            <span class="material-symbols-outlined text-sm">close</span>
        </button>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnAdd = document.getElementById('add-defecto-visual');
    const wrapper = document.getElementById('wrapper-defectos-visuales');
    const tpl = document.getElementById('tpl-defecto-row');
    const form = document.getElementById('diagnostico-form');
    const hiddenJson = document.getElementById('desc_inspeccion_json');

    if(btnAdd && wrapper && tpl) {
        btnAdd.addEventListener('click', function() {
            const emptyMsg = document.getElementById('empty-defectos-msg');
            if(emptyMsg) emptyMsg.remove();
            
            const clone = tpl.content.cloneNode(true);
            wrapper.appendChild(clone);
        });
    }

    form.addEventListener('submit', function(e) {
        const rows = document.querySelectorAll('.defecto-row');
        const list = [];
        
        rows.forEach(row => {
            const grupoEl = row.querySelector('[name="visual_defecto[]"]');
            const grupo = grupoEl ? grupoEl.value : 'N/A';
            const tipoEl = row.querySelector('[name="visual_tipo[]"]');
            const tipo = tipoEl ? tipoEl.value : '';
            const obsEl = row.querySelector('[name="visual_obs[]"]');
            const obs = obsEl ? obsEl.value : '';
            
            if(tipo || obs) {
                list.push({ grupo, tipo, obs });
            }
        });

        // Lógica de Comentarios de Luces -> Inspección Visual Tipo A
        const comentariosLuces = document.getElementById('comentarios_luces') ? document.getElementById('comentarios_luces').value.trim() : '';
        if (comentariosLuces) {
            list.push({
                grupo: 'Luces',
                tipo: 'Tipo A',
                obs: 'HALLAZGO EN LUCES: ' + comentariosLuces
            });
        }

        const obs_general = document.getElementById('visual_obs_general') ? document.getElementById('visual_obs_general').value.trim() : '';
        
        // Manejo de Exploradoras
        const chkExploradoras = document.getElementById('tiene_exploradoras');
        const radioNa = document.getElementById('exploradoras_na_radio');
        const radioExp = document.querySelectorAll('input[name="_exploradora_ui"]');
        const radioTarget = document.querySelectorAll('input[name="exploradoras"]');

        if (chkExploradoras && !chkExploradoras.checked) {
            // Si no tiene, forzamos NA en el parámetro real
            if (radioNa) radioNa.checked = true;
        } else {
            // Si tiene, sincronizamos el valor de la UI con el parámetro real
            const selectedUi = document.querySelector('input[name="_exploradora_ui"]:checked');
            if (selectedUi) {
                const targetValue = selectedUi.value;
                radioTarget.forEach(r => {
                    if (r.value === targetValue) r.checked = true;
                });
            }
        }

        const finalData = {
            list: list,
            obs: obs_general
        };

        if(hiddenJson) {
            hiddenJson.value = JSON.stringify(finalData);
        }
    });

    const chkExploradorasEvent = document.getElementById('tiene_exploradoras');
    if (chkExploradorasEvent) {
        const syncContainer = () => {
            const container = document.getElementById('exploradoras_container');
            if (chkExploradorasEvent.checked) {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        };
        chkExploradorasEvent.addEventListener('change', syncContainer);
        syncContainer(); // Estado inicial
    }

    // Auto-expandir textareas de observaciones en inspección visual
    function autoExpand(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    }

    document.addEventListener('input', function(e) {
        if (e.target && e.target.name === 'visual_obs[]') {
            autoExpand(e.target);
        }
    });

    document.querySelectorAll('textarea[name="visual_obs[]"]').forEach(function(textarea) {
        autoExpand(textarea);
    });
});

function fillSimulacion(tipo) {
    const setVal = (name, val) => {
        const el = document.querySelector(`input[name="${name}"]`);
        if(el) {
            el.value = val;
            el.setAttribute('value', val);
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }
    };

    if (tipo.includes('DIESEL')) {
        const temp = (Math.random() * (80 - 71) + 71).toFixed(2);
        const rpm = (Math.random() * (4200 - 3500) + 3500).toFixed(0);
        const c1 = (Math.random() * (4.00 - 3.00) + 3.00).toFixed(2);
        const c2 = (Math.random() * (2.99 - 2.80) + 2.80).toFixed(2);
        const c3 = (Math.random() * (2.79 - 2.50) + 2.50).toFixed(2);
        const c4 = (Math.random() * (2.49 - 2.30) + 2.30).toFixed(2);
        const promedio = ((parseFloat(c1) + parseFloat(c2) + parseFloat(c3) + parseFloat(c4)) / 4).toFixed(2);

        setVal('temp_c', temp);
        setVal('rpm', rpm);
        setVal('ciclo1', c1);
        setVal('ciclo2', c2);
        setVal('ciclo3', c3);
        setVal('ciclo4', c4);
        setVal('resultado_diesel', promedio);
        console.log("Valores Diesel generados cumpliendo restricciones estrictas.");
    } 
    
    if (tipo.includes('OTTO') || tipo.includes('GASES')) {
        setVal('temperatura_gases', '0');
        setVal('rpm_gases', (Math.random() * (1200 - 800) + 800).toFixed(0));
        setVal('co_ralenti', (Math.random() * (0.80 - 0.10) + 0.10).toFixed(2));
        setVal('co_crucero', (Math.random() * (0.80 - 0.10) + 0.10).toFixed(2));
        setVal('co2_ralenti', (Math.random() * (11 - 10) + 10).toFixed(2));
        setVal('co2_crucero', (Math.random() * (11 - 10) + 10).toFixed(2));
        setVal('o2_ralenti', (Math.random() * (5 - 0.1) + 0.1).toFixed(2));
        setVal('o2_crucero', (Math.random() * (5 - 0.1) + 0.1).toFixed(2));
        setVal('hc_ralenti', (Math.random() * (160 - 10) + 10).toFixed(0));
        setVal('hc_crucero', (Math.random() * (160 - 10) + 10).toFixed(0));
        setVal('no_ralenti', '0');
        setVal('no_crucero', '0');
        console.log("Valores Gasolina/Otto generados.");
    }
}
    // Validación en tiempo real para parámetros numéricos
    document.querySelectorAll('.dynamic-param-input').forEach(input => {
        input.addEventListener('input', function() {
            const val = parseFloat(this.value);
            const min = parseFloat(this.dataset.min);
            const max = parseFloat(this.dataset.max);
            const mantiene = this.dataset.mantiene === "1";

            if (!isNaN(val) && !isNaN(min) && !isNaN(max)) {
                if (val < min || val > max) {
                    this.classList.add('ring-2', 'ring-red-500', 'bg-red-50');
                    if (mantiene) {
                        this.setCustomValidity(`Valor fuera de rango permitido (${min} - ${max})`);
                    }
                } else {
                    this.classList.remove('ring-2', 'ring-red-500', 'bg-red-50');
                    this.classList.add('ring-2', 'ring-emerald-500', 'bg-emerald-50');
                    this.setCustomValidity('');
                }
            } else {
                this.classList.remove('ring-2', 'ring-red-500', 'bg-red-50', 'ring-emerald-50', 'bg-emerald-50');
            }
        });
        // Disparar validación inicial para valores existentes
        if(input.value) input.dispatchEvent(new Event('input'));
    });
</script>
@endsection