@extends('layouts.app')

@section('content')
@php
    $prefix = Auth::user()->hasRole('Administrador') ? 'admin' : 'digitador';
    
    // Agrupar parámetros por tipo para las pestañas
    $groupedParams = $diagnostico->parametros->groupBy(function($p) {
        return $p->parametro->tippar->nomtip;
    });

    // Determinar estado general mediante causales de rechazo
    $fallasTipoA = 0;
    $fallasTipoB = 0;

    foreach($diagnostico->parametros as $p) {
        $param = $p->parametro;
        $val = $p->valor;
        $esSeccionDefectos = str_contains(strtoupper($param->tippar->nomtip ?? ''), 'DEFECTOS');
        
        if ($param->control == 'number' && ($param->rini !== null && $param->rfin !== null)) {
            // Validación numérica por rango (se considera defecto Tipo A)
            if ($val < $param->rini || $val > $param->rfin) $fallasTipoA++;
        } elseif ($param->control == 'radio') {
            // Validación de radio buttons
            if ($esSeccionDefectos) {
                if (str_contains(strtolower($param->nompar), 'criterios')) {
                    if ($val == 'no') $fallasTipoA++;
                } else {
                    if ($val == 'si') $fallasTipoA++;
                }
            } else {
                if (in_array($val, ['no', 'no_funciona'])) $fallasTipoA++;
            }
        } elseif ($param->nompar == 'desc_inspeccion') {
            // Conteo de defectos visuales (Tipo A y Tipo B)
            $data = @json_decode($val, true);
            $lista = is_array($data) ? ($data['list'] ?? $data) : [];
            foreach ($lista as $def) {
                if (($def['tipo'] ?? '') == 'Tipo A') $fallasTipoA++;
                elseif (($def['tipo'] ?? '') == 'Tipo B') $fallasTipoB++;
            }
        } elseif (in_array($param->nompar, ['grupo_inspeccion', 'tipo_defecto'])) {
            // Por seguridad, si quedan restos antiguos, cuentan como A
            if (!empty($val)) $fallasTipoA++;
        }
    }

    $vehiculo = $diagnostico->vehiculo;
    $tipoServicio = $vehiculo->tipo_servicio; // 1=Particular, 2=Publico
    $tipoVehiculoStr = strtolower(optional(\App\Models\Valor::find($vehiculo->tipoveh))->nomval ?? '');
    if (empty($tipoVehiculoStr) && is_string($vehiculo->tipoveh)) {
        $tipoVehiculoStr = strtolower($vehiculo->tipoveh);
    }

    $causalesRechazo = [];
    
    // 1. si tiene almenos un defecto tipo A
    if ($fallasTipoA > 0) {
        $causalesRechazo[] = "Se encuentra al menos un defecto Tipo A ($fallasTipoA en total).";
    }
    
    if (str_contains($tipoVehiculoStr, 'motocicleta') || str_contains($tipoVehiculoStr, 'motocileta')) {
        // 4. si tipo de vehiculo es motocicletas y tiene 5 o mas fallas (Tipo B)
        if ($fallasTipoB >= 5) {
            $causalesRechazo[] = "La cantidad de defectos Tipo B es igual o superior a 5 para vehículos tipo motocicletas (Tiene $fallasTipoB).";
        }
    } elseif (str_contains($tipoVehiculoStr, 'motocarro')) {
        // 5. si tipo de vehiculo es motocarros y tiene 7 o mas fallas (Tipo B)
        if ($fallasTipoB >= 7) {
            $causalesRechazo[] = "La cantidad de defectos Tipo B es igual o superior a 7 para vehículos tipo motocarros (Tiene $fallasTipoB).";
        }
    } else {
        // 2. si tipo de servicio es particular y tiene 10 o mas fallas
        if ($tipoServicio == 1 && $fallasTipoB >= 10) {
            $causalesRechazo[] = "La cantidad de defectos Tipo B es igual o superior a 10 para vehículos particulares (Tiene $fallasTipoB).";
        } 
        // 3. si tipo de servicio es publico y tiene 5 o mas fallas
        elseif ($tipoServicio == 2 && $fallasTipoB >= 5) {
            $causalesRechazo[] = "La cantidad de defectos Tipo B es igual o superior a 5 para vehículos públicos (Tiene $fallasTipoB).";
        }
    }

    $allCumple = count($causalesRechazo) === 0;

    // Verificar campos requeridos
    $answeredParams = $diagnostico->parametros->filter(function($p) {
        return !is_null($p->valor) && $p->valor !== '';
    })->pluck('parametro.nompar')->toArray();

    $combuStr = strtoupper($diagnostico->vehiculo->combustible->nomval ?? '');
    $isDiesel = str_contains($combuStr, 'DIESEL');

    $requiredParamsDict = [
        'luz_izquierda' => 'Luz Izquierda',
        'luz_derecha' => 'Luz Derecha',
        'dilusion_gasolina' => 'Dilución Gasolina',
        'Criterios_de_validacion' => 'Criterios de Validación'
    ];

    if ($isDiesel) {
        $requiredParamsDict['temp_c'] = 'Temp C (V. Diesel)';
        $requiredParamsDict['rpm'] = 'RPM (V. Diesel)';
        $requiredParamsDict['ciclo1'] = 'Ciclo 1 (V. Diesel)';
        $requiredParamsDict['ciclo2'] = 'Ciclo 2 (V. Diesel)';
        $requiredParamsDict['ciclo3'] = 'Ciclo 3 (V. Diesel)';
        $requiredParamsDict['ciclo4'] = 'Ciclo 4 (V. Diesel)';
        $requiredParamsDict['resultado_diesel'] = 'Resultado Diesel';
    } else {
        $requiredParamsDict['co_ralenti'] = 'CO Ralenti';
        $requiredParamsDict['co_crucero'] = 'CO Crucero';
        $requiredParamsDict['hc_ralenti'] = 'HC Ralenti';
        $requiredParamsDict['hc_crucero'] = 'HC Crucero';
        $requiredParamsDict['o2_ralenti'] = 'O2 Ralenti';
        $requiredParamsDict['o2_crucero'] = 'O2 Crucero';
        $requiredParamsDict['co2_ralenti'] = 'CO2 Ralenti';
        $requiredParamsDict['co2_crucero'] = 'CO2 Crucero';
    }

    $missingFields = [];
    foreach($requiredParamsDict as $key => $label) {
        if(!in_array($key, $answeredParams)) {
            $missingFields[] = $label;
        }
    }

    $fotosCount = $diagnostico->fotos->count();
    if ($fotosCount < 2) {
        $missingFields[] = 'Evidencia Fotográfica (Se requieren al menos 2 fotos. Actual: ' . $fotosCount . ')';
    }
@endphp

<div class="px-6 pb-20 max-w-[1400px] mx-auto" x-data="{ activeTab: '{{ $groupedParams->keys()->first() }}' }">
    <!-- Main Header -->
    <header class="flex flex-col md:flex-row justify-between items-start gap-4 md:gap-6 mb-8 mt-2 md:mt-0 px-2 sm:px-0">
        <div class="w-full md:w-auto">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl md:text-3xl font-black text-[#002D54] tracking-tight">Detalle Diagnóstico</h1>
                @if($diagnostico->dpiddia)
                    <span class="bg-[#002D54] text-white px-3 py-1.5 rounded-lg text-[0.55rem] font-black uppercase tracking-tighter shadow-sm flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[12px]">history</span>
                        RE-INSP
                    </span>
                @endif
            </div>
            <p class="text-on-surface-variant font-body text-xs md:text-sm mt-1.5 opacity-60 flex items-center gap-2">
                <span class="material-symbols-outlined text-sm md:text-base text-emerald-600">check_circle</span>
                Hallazgos y especificaciones técnicas.
            </p>
        </div>
        <div class="flex flex-row items-center gap-2 w-full md:w-auto mt-2 md:mt-0">
            <button id="btn-edit-asignacion" class="flex-1 md:flex-none justify-center bg-surface-container-high text-[#001834] px-4 md:px-6 py-3 rounded-xl font-bold text-[9px] md:text-xs uppercase tracking-widest hover:bg-[#ffba20] transition-all flex items-center gap-2 border border-outline-variant/10 group whitespace-nowrap">
                <span class="material-symbols-outlined text-lg transition-transform group-hover:rotate-180 duration-500">settings</span>
                <span class="hidden sm:inline">Modificar asignación</span>
                <span class="sm:hidden">Modificar</span>
            </button>
            <button class="bg-surface-container-lowest text-on-surface-variant px-4 md:px-6 py-3 rounded-xl font-bold text-[9px] md:text-xs uppercase tracking-widest hover:bg-gray-100 transition-all border border-outline-variant/10" onclick="window.location.href='{{ route($prefix . '.diagnosticos.index') }}'">
                Volver
            </button>
        </div>
    </header>

    @include('diagnosticos.modal-edit-asignacion')

    <!-- Info Cards Bar -->
    <!-- Info Cards Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 md:gap-6 mb-10">
        <!-- Placa -->
        <div class="bg-surface-container-lowest p-5 md:p-6 rounded-2xl shadow-sm border-l-4 border-[#001834] flex flex-col justify-center">
            <p class="text-[0.6rem] font-black text-on-surface-variant uppercase tracking-[0.2em] mb-1 md:mb-2 opacity-50">Placa</p>
            <p class="text-2xl md:text-3xl font-black text-[#001834] uppercase tracking-tighter">{{ $diagnostico->vehiculo->placaveh }}</p>
        </div>
        
        <!-- Fecha -->
        <div class="bg-surface-container-lowest p-5 md:p-6 rounded-2xl shadow-sm border-l-4 border-outline-variant/30">
            <p class="text-[0.6rem] font-black text-on-surface-variant uppercase tracking-[0.2em] mb-1 md:mb-2 opacity-50">Fecha y Hora</p>
            <p class="text-sm md:text-lg font-bold text-[#001834]">{{ \Carbon\Carbon::parse($diagnostico->fecdia)->translatedFormat('d M Y, H:i') }}</p>
        </div>
        
        <!-- Inspector -->
        <div class="bg-surface-container-lowest p-5 md:p-6 rounded-2xl shadow-sm border-l-4 border-outline-variant/30">
            <p class="text-[0.6rem] font-black text-on-surface-variant uppercase tracking-[0.2em] mb-1 md:mb-2 opacity-50">Inspector</p>
            <p class="text-sm md:text-lg font-bold text-[#001834]">{{ $diagnostico->inspector->nomper ?? 'N/A' }} {{ $diagnostico->inspector->apeper ?? '' }}</p>
        </div>
        
        <!-- Ingeniero -->
        <div class="bg-surface-container-lowest p-5 md:p-6 rounded-2xl shadow-sm border-l-4 border-outline-variant/30">
            <p class="text-[0.6rem] font-black text-on-surface-variant uppercase tracking-[0.2em] mb-1 md:mb-2 opacity-50">Ing. Autorizador</p>
            <p class="text-sm md:text-lg font-bold text-[#001834]">{{ $diagnostico->ingeniero->nomper ?? 'N/A' }} {{ $diagnostico->ingeniero->apeper ?? '' }}</p>
        </div>
        
        <!-- Estado -->
        <div class="{{ $allCumple ? 'bg-emerald-50 border-emerald-100' : 'bg-red-50 border-red-100' }} p-5 md:p-6 rounded-2xl shadow-sm border flex items-center gap-4 sm:col-span-2 lg:col-span-1">
            <span class="material-symbols-outlined {{ $allCumple ? 'text-emerald-700 bg-emerald-100' : 'text-red-700 bg-red-100' }} p-2 rounded-xl text-lg flex-shrink-0">
                {{ $allCumple ? 'verified' : 'shield_with_heart' }}
            </span>
            <div>
                <p class="text-[0.6rem] font-black {{ $allCumple ? 'text-emerald-700' : 'text-red-700' }} uppercase tracking-widest opacity-70">Sugerencia</p>
                <p class="text-sm font-black {{ $allCumple ? 'text-emerald-900' : 'text-red-900' }} leading-tight">
                    {{ $allCumple ? 'Aprobado' : 'Rechazo' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-12 gap-8">
        
        <!-- Technical Details (Left) -->
        <div class="col-span-12 lg:col-span-8 bg-surface-container-lowest rounded-3xl overflow-hidden shadow-sm border border-outline-variant/10">
            <!-- Pestañas -->
            <div class="flex border-b border-outline-variant/10 bg-surface-container-low/30 overflow-x-auto no-scrollbar scroll-smooth">
                @foreach($groupedParams as $tipo => $params)
                <button 
                    @click="activeTab = '{{ $tipo }}'"
                    :class="activeTab === '{{ $tipo }}' ? 'border-[#001834] text-[#001834] bg-white opacity-100' : 'border-transparent text-on-surface-variant opacity-40 hover:opacity-80 font-bold'"
                    class="px-6 md:px-8 py-4 md:py-5 font-black text-xs md:text-sm uppercase tracking-[0.1em] border-b-4 transition-all whitespace-nowrap flex-shrink-0">
                    {{ $tipo }}
                </button>
                @endforeach
            </div>

            <!-- Contenido de Tablas -->
            <!-- Contenido de Tablas -->
            <div class="p-4 md:p-8 overflow-x-auto">
                @foreach($groupedParams as $tipo => $params)
                <div x-show="activeTab === '{{ $tipo }}'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" class="min-w-[600px] md:min-w-0">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left border-b border-outline-variant/20">
                                <th class="pb-4 font-black text-[0.6rem] md:text-[0.65rem] uppercase tracking-widest text-on-surface-variant opacity-50">Parámetro</th>
                                <th class="pb-4 font-black text-[0.6rem] md:text-[0.65rem] uppercase tracking-widest text-on-surface-variant opacity-50">Valor</th>
                                <th class="pb-4 font-black text-[0.6rem] md:text-[0.65rem] uppercase tracking-widest text-on-surface-variant opacity-50 hidden sm:table-cell">Rango</th>
                                <th class="pb-4 font-black text-[0.6rem] md:text-[0.65rem] uppercase tracking-widest text-on-surface-variant opacity-50">Resultado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/10 font-body">
                            @if(str_contains(strtoupper($tipo), 'VISUAL'))
                                @php
                                    $data = @json_decode($params->firstWhere('parametro.nompar', 'desc_inspeccion')->valor ?? '', true);
                                    $lista = is_array($data) ? ($data['list'] ?? $data) : [];
                                    $obsG = is_array($data) ? ($data['obs'] ?? '') : '';
                                    if(!is_array($lista)) $lista = [];
                                @endphp
                                @forelse($lista as $def)
                                <tr class="group hover:bg-surface-container-low/30 transition-colors">
                                    <td class="py-4 md:py-5 pr-4">
                                        <p class="font-bold text-sm text-[#001834]">{{ $def['grupo'] ?? '-' }}</p>
                                        <p class="text-[0.65rem] font-medium text-on-surface-variant opacity-60">{{ $def['obs'] ?? ($def['desc'] ?? '') }}</p>
                                    </td>
                                    <td class="py-4 md:py-5 font-black text-sm text-[#001834]/80">
                                        {{ $def['tipo'] ?? '-' }}
                                    </td>
                                    <td class="py-4 md:py-5 font-bold text-xs text-on-surface-variant opacity-60 hidden sm:table-cell">-</td>
                                    <td class="py-4 md:py-5">
                                        <div class="inline-flex items-center gap-2 bg-red-50 text-red-700 px-3 py-1.5 rounded-xl border border-red-100 text-[0.55rem] md:text-[0.6rem] font-black uppercase tracking-widest">
                                            <span class="material-symbols-outlined text-xs">warning</span> Hallazgo
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                    @if(empty($obsG))
                                    <tr>
                                        <td colspan="4" class="py-10 text-center text-sm font-bold text-on-surface-variant opacity-40 italic">
                                            No se reportaron defectos.
                                        </td>
                                    </tr>
                                    @endif
                                @endforelse
                                
                                @if(!empty($obsG))
                                <tr>
                                    <td colspan="4" class="py-6 border-t border-dashed border-outline-variant/30">
                                        <p class="text-[0.65rem] font-black uppercase tracking-[0.1em] text-on-surface-variant opacity-60 mb-2">Otros Hallazgos</p>
                                        <div class="bg-surface-container-low p-4 rounded-2xl text-sm font-semibold text-[#001834] leading-relaxed">
                                            {{ $obsG }}
                                        </div>
                                    </td>
                                </tr>
                                @endif
                            @else
                                @foreach($params as $p)
                                @php
                                    $param = $p->parametro;
                                    $val = $p->valor;
                                    $cumple = true;
                                    $esSeccionDefectos = str_contains(strtoupper($tipo), 'DEFECTOS');

                                    if ($param->control == 'number' && ($param->rini !== null && $param->rfin !== null)) {
                                        $cumple = ($val >= $param->rini && $val <= $param->rfin);
                                    } elseif ($param->control == 'radio') {
                                        if ($esSeccionDefectos) {
                                            // Lógica específica solicitada: (Dilusion_gasolina NO/NA) AND (Criterios_de_validacion SI)
                                            $valDilusion = $params->firstWhere('parametro.nompar', 'dilusion_gasolina')->valor ?? '';
                                            $valCriterios = $params->firstWhere('parametro.nompar', 'Criterios_de_validacion')->valor ?? '';
                                            $dilusionOk = in_array(strtolower($valDilusion), ['no', 'na']);
                                            $criteriosOk = strtolower($valCriterios) == 'si';
                                            $cumple = $dilusionOk && $criteriosOk;
                                        } else {
                                            $cumple = !in_array($val, ['no', 'no_funciona']);
                                        }
                                    } elseif (in_array($param->nompar, ['grupo_inspeccion', 'tipo_defecto'])) {
                                        $cumple = empty($val);
                                    }
                                    $rango = ($param->control == 'number' && ($param->rini !== null && $param->rfin !== null)) 
                                        ? $param->rini . '-' . $param->rfin
                                        : ($param->control == 'radio' ? 'Cualit.' : 'N/A');
                                @endphp
                                <tr class="group hover:bg-surface-container-low/30 transition-colors {{ !$cumple ? 'bg-red-50/50' : '' }}">
                                    <td class="py-4 md:py-5 pr-4">
                                        <p class="font-bold text-sm {{ !$cumple ? 'text-red-700' : 'text-[#001834]' }}">{{ str_replace('_', ' ', $param->nompar) }}</p>
                                    </td>
                                    <td class="py-4 md:py-5 font-black text-sm {{ !$cumple ? 'text-red-900' : 'text-[#001834]/80' }}">
                                        {{ $p->valor }} {{ $param->unipar }}
                                    </td>
                                    <td class="py-4 md:py-5 font-bold text-[10px] md:text-xs text-on-surface-variant opacity-60 hidden sm:table-cell">
                                        {{ $rango }}
                                    </td>
                                    <td class="py-4 md:py-5">
                                        @if($cumple)
                                            <div class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-xl border border-emerald-100 text-[0.55rem] md:text-[0.6rem] font-black uppercase tracking-widest">
                                                <span class="material-symbols-outlined text-xs">check_circle</span> Cumple
                                            </div>
                                        @else
                                            <div class="inline-flex items-center gap-2 bg-red-50 text-red-700 px-3 py-1.5 rounded-xl border border-red-100 text-[0.55rem] md:text-[0.6rem] font-black uppercase tracking-widest">
                                                <span class="material-symbols-outlined text-xs">cancel</span> Error
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Right Panels -->
        <div class="col-span-12 lg:col-span-4 space-y-8">
            <!-- Compliance Summary Card -->
            <div class="bg-surface-container-lowest p-8 rounded-3xl shadow-sm border border-outline-variant/10">
                <h3 class="font-headline font-black text-[#001834] text-lg mb-6 flex items-center gap-3">
                    <span class="material-symbols-outlined text-sm bg-primary-fixed-dim p-1.5 rounded-lg text-[#001834]">analytics</span>
                    Resumen de Cumplimiento
                </h3>
                <div class="space-y-4">
                    @foreach($groupedParams as $tipo => $params)
                    @php
                        $sectionCumple = true;
                        foreach($params as $p) {
                            $param = $p->parametro;
                            $val = $p->valor;
                            if ($param->control == 'number' && ($param->rini !== null && $param->rfin !== null)) {
                                if ($val < $param->rini || $val > $param->rfin) $sectionCumple = false;
                            } elseif ($param->control == 'radio') {
                                if (str_contains(strtoupper($tipo), 'DEFECTOS')) {
                                    $valDilusion = $params->firstWhere('parametro.nompar', 'dilusion_gasolina')->valor ?? '';
                                    $valCriterios = $params->firstWhere('parametro.nompar', 'Criterios_de_validacion')->valor ?? '';
                                    
                                    $dilusionOk = in_array(strtolower($valDilusion), ['no', 'na']);
                                    $criteriosOk = strtolower($valCriterios) == 'si';
                                    
                                    if (!($dilusionOk && $criteriosOk)) $sectionCumple = false;
                                    break;
                                } else {
                                    if (in_array($val, ['no', 'no_funciona'])) $sectionCumple = false;
                                }
                            } elseif (in_array($param->nompar, ['grupo_inspeccion', 'tipo_defecto'])) {
                                if (!empty($val)) $sectionCumple = false;
                            }
                        }
                    @endphp
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-sm text-on-surface-variant">{{ $tipo }}</span>
                        @if($sectionCumple)
                            <span class="text-emerald-600 font-black text-[0.65rem] uppercase tracking-widest flex items-center gap-1">
                                <span class="material-symbols-outlined text-[10px]">done_all</span> Cumple
                            </span>
                        @else
                            <span class="text-red-600 font-black text-[0.65rem] uppercase tracking-widest flex items-center gap-1">
                                <span class="material-symbols-outlined text-[10px]">close</span> No Cumple
                            </span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Evidencia Fotográfica -->
            <div class="bg-surface-container-lowest p-8 rounded-3xl shadow-sm border border-outline-variant/10">
                <h3 class="font-headline font-black text-[#001834] text-lg mb-6 flex items-center gap-3">
                    <span class="material-symbols-outlined text-sm bg-primary-fixed-dim p-1.5 rounded-lg text-[#001834]">photo_library</span>
                    Evidencia Fotográfica
                </h3>
                
                @if($diagnostico->fotos->count() > 0)
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($diagnostico->fotos as $foto)
                            <div class="group relative aspect-video rounded-2xl overflow-hidden bg-surface-container-low border border-outline-variant/10 hover:shadow-xl transition-all duration-500">
                                <img 
                                    src="{{ route('storage.fallback', ['path' => $foto->rutafoto]) }}" 
                                    alt="Evidencia" 
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-[#001834]/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
                                    <span class="text-white text-[10px] font-black uppercase tracking-widest">Vista Ampliada</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-surface-container-low/30 rounded-2xl p-6 border border-dashed border-outline-variant/30 flex flex-col items-center text-center">
                        <span class="material-symbols-outlined text-on-surface-variant/20 text-4xl mb-2">no_photography</span>
                        <p class="text-xs font-bold text-on-surface-variant opacity-40 uppercase tracking-widest">Sin evidencias registradas</p>
                    </div>
                @endif
            </div>

            <!-- Observations Card -->
            <div class="bg-surface-container-lowest p-8 rounded-3xl shadow-sm border border-outline-variant/10">
                <h3 class="font-headline font-black text-[#001834] text-lg mb-4 flex items-center gap-3">
                    <span class="material-symbols-outlined text-sm bg-primary-fixed-dim p-1.5 rounded-lg text-[#001834]">rate_review</span>
                    Observaciones Generales y Causal de Rechazo
                </h3>
                @if(!$allCumple)
                    <div class="space-y-3">
                        <p class="text-sm font-black text-red-700 uppercase tracking-widest">CAUSAL DE RECHAZO:</p>
                        <ul class="list-disc list-inside text-sm text-red-600 font-bold space-y-2 pl-2">
                            @foreach($causalesRechazo as $causal)
                                <li>{{ $causal }}</li>
                            @endforeach
                        </ul>
                        <p class="text-sm font-semibold leading-relaxed text-on-surface-variant mt-4 pt-4 border-t border-red-100">
                            Se recomienda revisión mecánica inmediata del vehículo {{ $diagnostico->vehiculo->placaveh }}.
                        </p>
                    </div>
                @else
                    <p class="text-sm font-semibold leading-relaxed text-emerald-700 bg-emerald-50 p-4 rounded-xl border border-emerald-100">
                        El vehículo cumple satisfactoriamente con todos los parámetros técnicos evaluados y no presenta ninguna de las causales de rechazo establecidas en esta inspección.
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Bottom Actions -->
    <div class="mt-12 space-y-4 px-2 sm:px-0" x-data="{ editingStatus: false }">
        @if(count($missingFields) > 0)
            <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-2xl shadow-sm mb-6">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-red-600">warning</span>
                    <h3 class="text-sm font-black text-red-800 uppercase tracking-widest">Información Incompleta</h3>
                </div>
                <p class="text-xs text-red-600 mt-2 font-bold">No se puede aprobar o rechazar el diagnóstico porque faltan los siguientes campos obligatorios:</p>
                <ul class="list-disc list-inside text-xs text-red-600 mt-2 font-bold opacity-80">
                    @foreach($missingFields as $field)
                        <li>{{ $field }}</li>
                    @endforeach
                </ul>
                <div class="mt-4">
                    <a href="{{ route($prefix . '.diagnosticos.edit', $diagnostico->iddia) }}" class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-700 transition-all shadow-sm">
                        <span class="material-symbols-outlined text-sm">edit</span> Completar Información
                    </a>
                </div>
            </div>
        @else
            <!-- Main Decision Buttons (Priority on Mobile) -->
            <div x-show="editingStatus || {{ $diagnostico->aprobado === null ? 'true' : ($diagnostico->aprobado == 0 ? 'true' : 'false') }}" class="flex gap-3 w-full">
                <form action="{{ route($prefix . '.diagnosticos.reject', $diagnostico->iddia) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full bg-[#ba1a1a] text-white py-4 rounded-2xl font-black text-[10px] md:text-xs uppercase tracking-widest shadow-lg shadow-red-200 flex items-center justify-center gap-2 hover:bg-red-700 transition-all">
                        RECHAZAR <span class="material-symbols-outlined text-sm">cancel</span>
                    </button>
                </form>

                <form action="{{ route($prefix . '.diagnosticos.approve', $diagnostico->iddia) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full bg-emerald-600 text-white py-4 rounded-2xl font-black text-[10px] md:text-xs uppercase tracking-widest shadow-lg shadow-emerald-200 flex items-center justify-center gap-2 hover:bg-emerald-700 transition-all">
                        APROBAR <span class="material-symbols-outlined text-sm">verified</span>
                    </button>
                </form>
            </div>
        @endif

        <!-- Status & Secondary Actions Container -->
        <div class="pt-2 border-t border-gray-100/50">
            @if($diagnostico->aprobado != 0 && !($diagnostico->rechazo && $diagnostico->rechazo->estadorec == 'Reasignado'))
                <div x-show="!editingStatus" class="flex items-center justify-between bg-emerald-50 p-1 pr-4 rounded-2xl border border-emerald-100 mb-2">
                    <div class="bg-emerald-100 text-emerald-700 px-5 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">check_circle</span> FINALIZADO
                    </div>
                    <button @click="editingStatus = true" class="text-[#001834] font-black text-[10px] uppercase tracking-widest border-b-2 border-[#001834]/20 hover:border-[#001834] transition-all">
                        CAMBIAR ESTADO
                    </button>
                </div>
            @endif

            @php
                $canExport = !is_null($diagnostico->aprobado) && !($diagnostico->rechazo && $diagnostico->rechazo->estadorec == 'Reasignado');
            @endphp
            <a href="{{ $canExport ? route($prefix . '.diagnosticos.export', $diagnostico->iddia) : 'javascript:void(0)' }}" 
               target="{{ $canExport ? '_blank' : '_self' }}" 
               onclick="{{ !$canExport ? "alert('Debe terminar el proceso para exportar')" : '' }}"
               class="{{ $canExport ? 'bg-white text-on-surface-variant hover:bg-[#001834] hover:text-white' : 'bg-gray-100 text-gray-400 cursor-not-allowed' }} w-full justify-center px-6 py-4 rounded-2xl border border-outline-variant/10 font-black text-[10px] uppercase tracking-widest flex items-center gap-2 transition-all shadow-sm">
                <span class="material-symbols-outlined text-sm">picture_as_pdf</span> EXPORTAR REPORTE PDF
            </a>
        </div>
    </div>
</div>
@endsection