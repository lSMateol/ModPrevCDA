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

    <form method="POST" action="{{ route($prefix . '.diagnosticos.update', $diagnostico->iddia) }}" class="space-y-10">
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
            </div>

            @if(str_contains(strtoupper($tipo), 'VISUAL'))
                <!-- Especial para Inspección Visual (Multifila) -->
                @php
                    $descInspeccion = $paramValues['desc_inspeccion'] ?? '';
                    $dataDecoded = @json_decode($descInspeccion, true);
                    $listaDefectos = is_array($dataDecoded) ? ($dataDecoded['list'] ?? $dataDecoded) : []; // Retrocompatibilidad
                    $generalObs = is_array($dataDecoded) ? ($dataDecoded['obs'] ?? '') : '';
                    if(!is_array($listaDefectos)) $listaDefectos = [];
                @endphp
                <div class="bg-surface-container-low p-6 rounded-2xl border border-outline-variant/10">
                    <div id="wrapper-defectos-visuales" class="space-y-4">
                        @forelse($listaDefectos as $index => $item)
                        <div class="defecto-row grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-white rounded-xl shadow-sm relative">
                            <div class="space-y-1">
                                <label class="text-[0.6rem] font-black uppercase opacity-60">Categoría</label>
                                <select name="visual_defecto[]" class="w-full bg-surface-container-high border-none rounded-lg p-2.5 text-xs font-bold">
                                    @foreach(['SPLINDERS', 'BUJES DE LOS MUELLES', 'TERMINALES BRAZO LARGO Y CORTO (2)', 'PASAMANOS SUELTOS', 'LLANTA DE REPUESTO', 'LATONERIA Y PINTURA', 'POLARIZADOS', 'ASIENTOS MAL ANCLADOS', 'TAPICERIA', 'COJINERIA', 'EXTINTOR', 'INEXISTENCIA CINTAS RETROREFLECTIVAS', 'LLANTAS LISAS', 'BUJES BARRA ESTABILIZADORA', 'GOTEO CAJA DE DIRECCION, TRANSMISION Y MOTOR', 'MAL FUNCIONAMIENTO LUCES TRASERAS', 'BOTIQUIN', 'MAL FUNCIONAMIENTO DISPOSITIVO DE VELOCIDAD', 'INEXISTENCIA DE CALCOMANIAS'] as $opc)
                                        <option value="{{ $opc }}" {{ ($item['grupo'] ?? '') == $opc ? 'selected' : '' }}>{{ $opc }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[0.6rem] font-black uppercase opacity-60">Tipo</label>
                                <select name="visual_tipo[]" class="w-full bg-surface-container-high border-none rounded-lg p-2.5 text-xs font-bold">
                                    <option value="Tipo A" {{ ($item['tipo'] ?? '') == 'Tipo A' ? 'selected' : '' }}>Tipo A</option>
                                    <option value="Tipo B" {{ ($item['tipo'] ?? '') == 'Tipo B' ? 'selected' : '' }}>Tipo B</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[0.6rem] font-black uppercase opacity-60">Descripción del Hallazgo</label>
                                <input type="text" name="visual_obs[]" value="{{ $item['obs'] ?? ($item['desc'] ?? '') }}" placeholder="Especifique..." class="w-full bg-surface-container-high border-none rounded-lg p-2.5 text-xs font-bold">
                            </div>
                            <button type="button" onclick="this.parentElement.remove()" class="absolute -top-2 -right-2 bg-red-100 text-red-600 rounded-full p-1 hover:bg-red-600 hover:text-white transition-all shadow-sm">
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
                        @empty
                            <p class="text-xs font-bold text-on-surface-variant opacity-40 italic text-center py-4" id="empty-defectos-msg">No se han registrado defectos del listado.</p>
                        @endforelse
                    </div>
                    
                    <div class="mt-6 pt-6 border-t border-dashed border-outline-variant/20">
                        <div class="flex flex-col md:flex-row gap-6 items-start">
                            <button type="button" id="add-defecto-visual" class="flex items-center gap-2 text-[#001834] font-black text-[0.65rem] uppercase tracking-widest bg-primary-fixed-dim/20 px-4 py-2 rounded-lg hover:bg-primary-fixed-dim transition-all">
                                <span class="material-symbols-outlined text-sm">add</span> Añadir del Listado
                            </button>
                            
                            <div class="flex-1 w-full space-y-2">
                                <label class="text-[0.7rem] font-black uppercase tracking-widest text-on-surface-variant opacity-80">Observaciones Generales / Otros Hallazgos</label>
                                <textarea id="visual_obs_general" rows="3" placeholder="Otros defectos no incluidos en la lista o comentarios adicionales..." class="w-full bg-surface-container-high border-none rounded-xl focus:ring-2 focus:ring-primary-fixed-dim p-4 text-sm font-semibold text-[#001834] transition-all">{{ $generalObs }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Campo oculto para cumplir con el validador actual -->
                    <input type="hidden" name="desc_inspeccion" id="desc_inspeccion_json" value="{{ $descInspeccion }}">
                </div>
            @else
                <!-- Parámetros en Grid -->
                <div class="grid grid-cols-1 {{ $params->count() > 3 ? 'md:grid-cols-2' : '' }} gap-4">
                    @foreach($params as $param)
                    @if(!in_array($param->nompar, ['grupo_inspeccion', 'tipo_defecto', 'desc_inspeccion']))
                    <div class="flex flex-col gap-2 {{ $param->control == 'textarea' ? 'col-span-full' : '' }}">
                        @if($param->control == 'radio')
                            <div class="flex flex-col gap-3 p-4 {{ $errors->has($param->nompar) ? 'bg-red-50 border-red-200' : 'bg-surface-container-low border-transparent' }} rounded-2xl border hover:border-[#001834]/5 transition-all">
                                <label class="text-[0.65rem] font-black uppercase tracking-widest {{ $errors->has($param->nompar) ? 'text-red-700' : 'text-on-surface-variant' }} opacity-60 leading-tight">
                                    {{ str_replace('_', ' ', $param->nompar) }}
                                </label>
                                <div class="flex flex-wrap gap-5">
                                    @php
                                        $opciones = ($param->nompar == 'luz_izquierda' || $param->nompar == 'luz_derecha' || str_contains(strtolower($param->nompar), 'funciona')) 
                                            ? ['funciona','no_funciona'] 
                                            : ['si','no','na'];
                                    @endphp
                                    @foreach($opciones as $opc)
                                    <label class="flex items-center gap-2 cursor-pointer group">
                                        <input type="radio" name="{{ $param->nompar }}" value="{{ $opc }}" 
                                            {{ old($param->nompar, $paramValues[$param->nompar] ?? '') == $opc ? 'checked' : '' }} 
                                            class="w-5 h-5 text-[#ffba20] border-2 border-outline-variant/30 focus:ring-offset-0 focus:ring-0 cursor-pointer checked:border-[#ffba20] bg-white transition-all">
                                        <span class="text-[0.65rem] font-black uppercase tracking-tighter text-on-surface group-hover:text-[#ffba20] transition-colors">
                                            {{ $opc == 'na' ? 'N/A' : ($opc == 'no_funciona' ? 'No funciona' : $opc) }}
                                        </span>
                                    </label>
                                    @endforeach
                                </div>
                                @error($param->nompar)
                                    <span class="text-[10px] font-bold text-red-600 uppercase tracking-tighter">{{ $message }}</span>
                                @enderror
                            </div>
                        @elseif($param->control == 'number')
                            <div class="space-y-1">
                                <label class="text-[0.65rem] font-black uppercase tracking-widest {{ $errors->has($param->nompar) ? 'text-red-700' : 'text-on-surface-variant' }} opacity-60 px-1">{{ str_replace('_', ' ', $param->nompar) }}</label>
                                <input type="number" step="any" name="{{ $param->nompar }}" 
                                    value="{{ old($param->nompar, $paramValues[$param->nompar] ?? '') }}" 
                                    placeholder="Escribe aquí..."
                                    class="w-full {{ $errors->has($param->nompar) ? 'bg-red-50 border-red-300 ring-1 ring-red-300' : 'bg-surface-container-high border-none' }} rounded-xl focus:ring-2 focus:ring-primary-fixed-dim p-4 text-sm font-bold text-[#001834] transition-all">
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
                </div>
            @endif
        </section>
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
    <div class="defecto-row grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-white rounded-xl shadow-sm relative">
        <div class="space-y-1">
            <label class="text-[0.6rem] font-black uppercase opacity-60">Categoría</label>
            <select name="visual_defecto[]" class="w-full bg-surface-container-high border-none rounded-lg p-2.5 text-xs font-bold">
                @foreach(['SPLINDERS', 'BUJES DE LOS MUELLES', 'TERMINALES BRAZO LARGO Y CORTO (2)', 'PASAMANOS SUELTOS', 'LLANTA DE REPUESTO', 'LATONERIA Y PINTURA', 'POLARIZADOS', 'ASIENTOS MAL ANCLADOS', 'TAPICERIA', 'COJINERIA', 'EXTINTOR', 'INEXISTENCIA CINTAS RETROREFLECTIVAS', 'LLANTAS LISAS', 'BUJES BARRA ESTABILIZADORA', 'GOTEO CAJA DE DIRECCION, TRANSMISION Y MOTOR', 'MAL FUNCIONAMIENTO LUCES TRASERAS', 'BOTIQUIN', 'MAL FUNCIONAMIENTO DISPOSITIVO DE VELOCIDAD', 'INEXISTENCIA DE CALCOMANIAS'] as $opc)
                    <option value="{{ $opc }}">{{ $opc }}</option>
                @endforeach
            </select>
        </div>
        <div class="space-y-1">
            <label class="text-[0.6rem] font-black uppercase opacity-60">Tipo</label>
            <select name="visual_tipo[]" class="w-full bg-surface-container-high border-none rounded-lg p-2.5 text-xs font-bold">
                <option value="Tipo A">Tipo A</option>
                <option value="Tipo B">Tipo B</option>
            </select>
        </div>
        <div class="space-y-1">
            <label class="text-[0.6rem] font-black uppercase opacity-60">Observaciones</label>
            <input type="text" name="visual_obs[]" placeholder="Describa el hallazgo..." class="w-full bg-surface-container-high border-none rounded-lg p-2.5 text-xs font-bold">
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
    const form = document.querySelector('form');
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
            const grupo = row.querySelector('[name="visual_defecto[]"]').value;
            const tipo = row.querySelector('[name="visual_tipo[]"]').value;
            const obs = row.querySelector('[name="visual_obs[]"]').value;
            
            if(grupo) {
                list.push({ grupo, tipo, obs });
            }
        });

        const obs_general = document.getElementById('visual_obs_general').value;
        const finalData = {
            list: list,
            obs: obs_general
        };

        if(hiddenJson) {
            hiddenJson.value = JSON.stringify(finalData);
        }
    });
});
</script>
@endsection