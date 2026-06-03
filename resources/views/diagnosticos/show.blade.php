@extends('layouts.app')

@section('content')
@php
    $prefix = Auth::user()->hasRole('Administrador') ? 'admin' : 'digitador';
    
    // Obtener parámetros activos de forma limpia y centralizada
    $activeParams = $diagnostico->getActiveParameters();
    $activeParamIds = $activeParams->pluck('idpar')->toArray();
    $savedParamValues = $diagnostico->parametros->keyBy('idpar');

    // Construir una colección de diapar virtuales/reales para la vista
    $activeDiapars = $activeParams->map(function($param) use ($savedParamValues, $diagnostico) {
        $diapar = $savedParamValues->get($param->idpar);
        if (!$diapar) {
            $diapar = new \App\Models\Diapar();
            $diapar->idpar = $param->idpar;
            $diapar->iddia = $diagnostico->iddia;
            $diapar->valor = null;
        }
        $diapar->setRelation('parametro', $param);
        return $diapar;
    });

    // Agrupar los parámetros activos por el tipo (tippar)
    $groupedParams = $activeDiapars->groupBy(function($dp) {
        return $dp->parametro->tippar->nomtip;
    });

    $fallasTipoA = 0;
    $fallasTipoB = 0;
    $fallasTecnicas = 0;

    foreach($activeDiapars as $p) {
        $param = $p->parametro;
        $val = $p->valor;
        $nomTip = strtoupper($param->tippar->nomtip ?? '');

        if ($param->nompar == 'desc_inspeccion') {
            // Conteo de defectos visuales (Único origen permitido para A y B)
            $data = @json_decode($val, true);
            $lista = is_array($data) ? ($data['list'] ?? $data) : [];
            foreach ($lista as $def) {
                if (($def['tipo'] ?? '') == 'Tipo A') $fallasTipoA++;
                elseif (($def['tipo'] ?? '') == 'Tipo B') $fallasTipoB++;
            }
        } else {
            // Validación de otros parámetros activos (Gases, Luces, etc.)
            $failed = false;
            if ($param->control == 'number' && ($param->rini !== null && $param->rfin !== null)) {
                if ($val !== null && $val !== '') {
                    if ($val < $param->rini || $val > $param->rfin) $failed = true;
                }
            } elseif ($param->control == 'radio') {
                if ($param->nompar == 'dilusion_gasolina') {
                    if (strtolower($val) == 'no') $failed = true;
                } elseif (str_contains($nomTip, 'DEFECTOS') && !str_contains($nomTip, 'VISUAL')) {
                    // Sección "Defectos" (No visual)
                    if (str_contains(strtolower($param->nompar), 'criterios')) {
                        if (!in_array(strtolower($val), ['si', 'na'])) $failed = true;
                    } else {
                        if (strtolower($val) == 'si') $failed = true;
                    }
                } else {
                    if (in_array($val, ['no', 'no_funciona'])) $failed = true;
                }
            }
            if ($failed) $fallasTecnicas++;
        }
    }

    $vehiculo = $diagnostico->vehiculo;
    $tipoServicio = $vehiculo->tipo_servicio; // 1=Particular, 2=Publico
    $tipoVehiculoStr = strtolower(optional(\App\Models\Valor::find($vehiculo->tipoveh))->nomval ?? '');
    if (empty($tipoVehiculoStr) && is_string($vehiculo->tipoveh)) {
        $tipoVehiculoStr = strtolower($vehiculo->tipoveh);
    }

    $causalesRechazo = [];
    
    // 1. si tiene almenos un defecto tipo A o fallas técnicas
    if ($fallasTipoA > 0 || $fallasTecnicas > 0) {
        if ($fallasTipoA > 0) $causalesRechazo[] = "Se encuentra al menos un defecto Tipo A ($fallasTipoA en total).";
        if ($fallasTecnicas > 0) $causalesRechazo[] = "Se detectaron fallas técnicas en parámetros obligatorios ($fallasTecnicas en total).";
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

    // Obtener campos faltantes/incompletos de forma centralizada
    $missingFields = $diagnostico->getMissingFields();
    $fotosCount = $diagnostico->fotos->count();
@endphp

<div class="px-4 sm:px-6 lg:px-10 pb-20 max-w-[1400px] mx-auto" x-data="{ activeTab: '{{ $groupedParams->keys()->first() }}' }">
    <!-- Main Header -->
    <header class="flex flex-col md:flex-row justify-between items-start gap-4 md:gap-6 mb-8 mt-2 md:mt-0">
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
                                        <p class="text-sm font-medium text-on-surface-variant opacity-80">{{ $def['obs'] ?? ($def['desc'] ?? '') }}</p>
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
                                    
                                    // Ocultar parámetros opcionales si no aplican (ej: exploradoras = na)
                                    if ($val === 'na') continue;

                                    $cumple = true;
                                    $esSeccionDefectos = str_contains(strtoupper($tipo), 'DEFECTOS');

                                    if ($param->control == 'number' && ($param->rini !== null && $param->rfin !== null)) {
                                        $cumple = ($val >= $param->rini && $val <= $param->rfin);
                                    } elseif ($param->control == 'radio') {
                                        if ($param->nompar == 'dilusion_gasolina') {
                                            $cumple = in_array(strtolower($val), ['si', 'na']);
                                        } elseif ($esSeccionDefectos) {
                                            if (str_contains(strtolower($param->nompar), 'criterios')) {
                                                $cumple = in_array(strtolower($val), ['si', 'na']);
                                            } else {
                                                $cumple = in_array(strtolower($val), ['no', 'na']);
                                            }
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
                                            <div class="flex flex-col gap-1">
                                                <div class="inline-flex items-center gap-2 bg-red-50 text-red-700 px-3 py-1.5 rounded-xl border border-red-100 text-[0.55rem] md:text-[0.6rem] font-black uppercase tracking-widest">
                                                    <span class="material-symbols-outlined text-xs">cancel</span> No Cumple
                                                </div>
                                                @if($esSeccionDefectos)
                                                    <span class="text-[9px] font-black text-red-800 uppercase px-1">Falla Técnica</span>
                                                @endif
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
                                if ($val === null || $val === '' || $val < $param->rini || $val > $param->rfin) $sectionCumple = false;
                            } elseif ($param->control == 'radio') {
                                if ($val === null || $val === '') {
                                    $sectionCumple = false;
                                } elseif ($param->nompar == 'dilusion_gasolina') {
                                    if (strtolower($val) == 'no') $sectionCumple = false;
                                } elseif (str_contains(strtoupper($tipo), 'DEFECTOS')) {
                                    if (str_contains(strtolower($param->nompar), 'criterios')) {
                                        if (!in_array(strtolower($val), ['si', 'na'])) $sectionCumple = false;
                                    } else {
                                        if (!in_array(strtolower($val), ['no', 'na'])) $sectionCumple = false;
                                    }
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
    <div class="mt-12 space-y-4" x-data="{ editingStatus: false }">
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
                <div class="mt-4 flex flex-wrap gap-3">
                    <a href="{{ route($prefix . '.diagnosticos.edit', $diagnostico->iddia) }}" class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-700 transition-all shadow-sm">
                        <span class="material-symbols-outlined text-sm">edit</span> Completar Información
                    </a>
                    @if($fotosCount < 2)
                        <button type="button" class="inline-flex items-center gap-2 bg-[#ffba20] text-[#001834] px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest hover:scale-105 active:scale-95 transition-all shadow-sm btn-foto" data-id="{{ $diagnostico->iddia }}">
                            <span class="material-symbols-outlined text-sm">photo_camera</span> Cargar Fotos
                        </button>
                    @endif
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
            @if($diagnostico->aprobado != 0)
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
                $canExport = !is_null($diagnostico->aprobado);
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

@include('diagnosticos.modal-fotos')
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const routeFotosBase = document.querySelector('meta[name="url-prefix"]').content;
        const modalFotos = document.getElementById('modal-fotos');
        const closeFotos = document.getElementById('close-fotos');
        const fotoDiagId = document.getElementById('foto-diag-id');
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const snap = document.getElementById('snap');
        const stopCameraBtn = document.getElementById('stop-camera');
        const cameraPreview = document.getElementById('camera-preview');
        const fileInput = document.getElementById('file-input');
        const photoList = document.getElementById('photo-list');
        const photoCount = document.getElementById('photo-count');
        const btnSaveFotos = document.getElementById('btn-save-fotos');
        const template = document.getElementById('photo-item-template');

        let stream = null;
        let existingPhotos = []; 
        let newPhotos = [];      
        let idsAEliminar = [];   

        document.querySelectorAll('.btn-foto').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.getAttribute('data-id');
                fotoDiagId.innerText = id;
                modalFotos.classList.remove('hidden');
                
                try {
                    const res = await fetch(`${routeFotosBase}/diagnosticos/${id}/fotos`);
                    existingPhotos = await res.json();
                    newPhotos = [];
                    idsAEliminar = [];
                    updatePhotoUI();
                } catch (err) {
                    console.error("Error cargando fotos:", err);
                    resetFotos();
                }
            });
        });

        function resetFotos() {
            stopCamera();
            existingPhotos = [];
            newPhotos = [];
            idsAEliminar = [];
            updatePhotoUI();
        }

        cameraPreview.addEventListener('click', () => {
            if (!stream) startCamera();
            else fileInput.click();
        });

        async function startCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: "environment" },
                    audio: false 
                });
                video.srcObject = stream;
                video.classList.remove('hidden');
                document.getElementById('upload-placeholder').classList.add('hidden');
                document.getElementById('camera-controls').classList.remove('hidden');
            } catch (err) {
                fileInput.click();
            }
        }

        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
            video.classList.add('hidden');
            document.getElementById('camera-controls').classList.add('hidden');
            if (existingPhotos.length === 0 && newPhotos.length === 0) {
                document.getElementById('upload-placeholder').classList.remove('hidden');
            }
        }

        if(stopCameraBtn) stopCameraBtn.onclick = (e) => { e.stopPropagation(); stopCamera(); };

        if(snap) snap.onclick = (e) => {
            e.stopPropagation();
            if ((existingPhotos.length + newPhotos.length) >= 2) return alert('Máximo 2 fotos permitidas.');
            
            const MAX_WIDTH = 1024;
            let width = video.videoWidth;
            let height = video.videoHeight;
            
            if (width > MAX_WIDTH) {
                height = Math.round((height * MAX_WIDTH) / width);
                width = MAX_WIDTH;
            }

            canvas.width = width;
            canvas.height = height;
            canvas.getContext('2d').drawImage(video, 0, 0, width, height);
            
            canvas.toBlob((blob) => {
                newPhotos.push(blob);
                updatePhotoUI();
            }, 'image/webp', 0.6);
        };

        if(fileInput) fileInput.onchange = (e) => {
            Array.from(e.target.files).forEach(file => {
                if ((existingPhotos.length + newPhotos.length) >= 2) return;
                const reader = new FileReader();
                reader.onload = (ev) => {
                    const img = new Image();
                    img.onload = () => {
                        const MAX_WIDTH = 1024;
                        let width = img.width;
                        let height = img.height;
                        
                        if (width > MAX_WIDTH) {
                            height = Math.round((height * MAX_WIDTH) / width);
                            width = MAX_WIDTH;
                        }

                        const tempCanvas = document.createElement('canvas');
                        tempCanvas.width = width; 
                        tempCanvas.height = height;
                        tempCanvas.getContext('2d').drawImage(img, 0, 0, width, height);
                        tempCanvas.toBlob((blob) => {
                            newPhotos.push(blob);
                            updatePhotoUI();
                        }, 'image/webp', 0.6);
                    };
                    img.src = ev.target.result;
                };
                reader.readAsDataURL(file);
            });
            fileInput.value = '';
        };

        function updatePhotoUI() {
            const total = existingPhotos.length + newPhotos.length;
            photoCount.innerText = total;
            btnSaveFotos.disabled = (total === 0 && idsAEliminar.length === 0);
            
            if (total >= 2) stopCamera();
            if (total > 0) document.getElementById('upload-placeholder').classList.add('hidden');
            else if (!stream) document.getElementById('upload-placeholder').classList.remove('hidden');

            photoList.innerHTML = '';
            
            existingPhotos.forEach((foto, i) => {
                const clone = template.content.cloneNode(true);
                clone.querySelector('img').src = foto.url;
                clone.querySelector('.remove-photo').onclick = () => {
                    idsAEliminar.push(foto.id);
                    existingPhotos.splice(i, 1);
                    updatePhotoUI();
                };
                photoList.appendChild(clone);
            });

            newPhotos.forEach((blob, i) => {
                const url = URL.createObjectURL(blob);
                const clone = template.content.cloneNode(true);
                clone.querySelector('img').src = url;
                clone.querySelector('.remove-photo').onclick = () => {
                    newPhotos.splice(i, 1);
                    updatePhotoUI();
                };
                photoList.appendChild(clone);
            });
        }

        if(btnSaveFotos) btnSaveFotos.onclick = async () => {
            const id = fotoDiagId.innerText;
            if (!id) return alert('ID de diagnóstico no encontrado.');

            const formData = new FormData();
            newPhotos.forEach((blob, i) => {
                formData.append(`fotos[]`, blob, `evid_${id}_new_${i}.webp`);
            });
            formData.append('ids_a_eliminar', JSON.stringify(idsAEliminar));

            const btnText = document.getElementById('btn-save-fotos-text');
            btnSaveFotos.disabled = true;
            const originalText = btnText ? btnText.innerText : 'Guardar Evidencias';
            if (btnText) btnText.innerText = 'Guardando...';

            try {
                const res = await fetch(`${routeFotosBase}/diagnosticos/${id}/fotos`, {
                    method: 'POST',
                    body: formData,
                    headers: { 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (res.ok) {
                    alert('Evidencias guardadas correctamente.');
                    window.location.reload();
                } else {
                    const data = await res.json();
                    alert('Error al guardar: ' + (data.message || 'Error desconocido'));
                }
            } catch (err) {
                console.error(err);
                alert('Error al guardar: ' + err.message);
            } finally {
                if (btnText) btnText.innerText = originalText;
                btnSaveFotos.disabled = false;
            }
        };

        if(closeFotos) closeFotos.onclick = () => { modalFotos.classList.add('hidden'); stopCamera(); };
        if(modalFotos) modalFotos.onclick = (e) => { if (e.target === modalFotos) { modalFotos.classList.add('hidden'); stopCamera(); } };
    });
</script>
@endpush