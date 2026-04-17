@extends('layouts.app')

@section('content')
<div class="px-8 py-6 space-y-8">
    <!-- Top Bar Interno -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="font-headline font-black text-3xl text-[#001834] tracking-tight">Panel de Alertas</h1>
            <p class="text-sm text-gray-500 font-medium">Control preventivo de vencimientos documentales</p>
        </div>
        <div class="flex items-center gap-3 bg-white px-4 py-2 rounded-2xl shadow-sm border border-gray-100">
            <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Navegación Activa:</span>
            <span class="text-[10px] font-black uppercase tracking-widest text-[#ffba20]">Alertas Vehiculares</span>
        </div>
    </div>

    <!-- Summary Bento Grid -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Critical -->
        <div class="bg-white p-6 rounded-2xl border-l-4 border-red-500 shadow-sm flex flex-col justify-between h-36">
            <div class="flex justify-between items-start">
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Vencimientos Críticos</span>
                <span class="material-symbols-outlined text-red-500" style="font-variation-settings: 'FILL' 1;">error</span>
            </div>
            <div class="text-4xl font-headline font-black text-[#001834]">{{ $metricas['criticos'] }} <span class="text-xs font-bold text-red-500 uppercase tracking-widest ml-2">Vehículos</span></div>
        </div>

        <!-- Warning -->
        <div class="bg-white p-6 rounded-2xl border-l-4 border-[#ffba20] shadow-sm flex flex-col justify-between h-36">
            <div class="flex justify-between items-start">
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Advertencias (Plazo Medio)</span>
                <span class="material-symbols-outlined text-[#ffba20]" style="font-variation-settings: 'FILL' 1;">warning</span>
            </div>
            <div class="text-4xl font-headline font-black text-[#001834]">{{ $metricas['advertencias'] }} <span class="text-xs font-bold text-[#ffba20] uppercase tracking-widest ml-2">Próximos</span></div>
        </div>

        <!-- Active -->
        <div class="bg-white p-6 rounded-2xl border-l-4 border-blue-500 shadow-sm flex flex-col justify-between h-36">
            <div class="flex justify-between items-start">
                <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Documentos Activos</span>
                <span class="material-symbols-outlined text-blue-500" style="font-variation-settings: 'FILL' 1;">check_circle</span>
            </div>
            <div class="text-4xl font-headline font-black text-[#001834]">{{ $metricas['al_dia'] }} <span class="text-xs font-bold text-blue-500 uppercase tracking-widest ml-2">Al día</span></div>
        </div>
    </section>

    <!-- Search Filters -->
    <section class="bg-gray-50 p-8 rounded-3xl border border-gray-100 space-y-6">
        <form action="{{ route(Auth::user()->hasRole('Administrador') ? 'admin.alertas' : 'digitador.alertas') }}" method="GET">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-[#001834] rounded-xl flex items-center justify-center text-white shadow-lg shadow-[#001834]/10">
                    <span class="material-symbols-outlined text-xl">tune</span>
                </div>
                <h2 class="font-headline text-xl font-black text-[#001834] tracking-tight">Filtros de Precisión</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Vencimiento Desde</label>
                    <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}" class="w-full bg-white border-2 border-transparent focus:border-[#ffba20] focus:ring-0 rounded-2xl py-4 px-4 text-sm font-bold shadow-sm transition-all">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Vencimiento Hasta</label>
                    <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" class="w-full bg-white border-2 border-transparent focus:border-[#ffba20] focus:ring-0 rounded-2xl py-4 px-4 text-sm font-bold shadow-sm transition-all">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Flota de Empresa</label>
                    <select name="empresa_id" class="w-full bg-white border-2 border-transparent focus:border-[#ffba20] focus:ring-0 rounded-2xl py-4 px-6 text-sm font-bold shadow-sm transition-all">
                        <option value="">Todas las Empresas</option>
                        @foreach($empresas as $emp)
                            <option value="{{ $emp->idemp }}" {{ request('empresa_id') == $emp->idemp ? 'selected' : '' }}>{{ $emp->nomemp }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Placa Vehicular</label>
                    <input type="text" name="placa" value="{{ request('placa') }}" class="w-full bg-white border-2 border-transparent focus:border-[#ffba20] focus:ring-0 rounded-2xl py-4 px-4 text-sm font-bold shadow-sm transition-all" placeholder="ej. KVM-091">
                </div>
                <div class="md:col-span-4 flex justify-end gap-3">
                    <a href="{{ route(Auth::user()->hasRole('Administrador') ? 'admin.alertas' : 'digitador.alertas') }}" class="bg-gray-200 text-gray-700 px-8 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-gray-300 transition-all text-center">
                        Limpiar Filtros
                    </a>
                    <button type="submit" class="bg-[#001834] text-white px-10 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-[#ffba20] hover:text-[#001834] transition-all shadow-lg">
                        Aplicar Filtros
                    </button>
                </div>
            </div>
        </form>
    </section>

    <!-- Alerts List -->
    <section class="space-y-6">
        <div class="flex justify-between items-end">
            <div>
                <h2 class="font-headline text-2xl font-black text-[#001834] tracking-tight">Alertas Detectadas</h2>
                <p class="text-sm text-gray-500 font-medium mt-1">Se requiere acción inmediata en los documentos marcados</p>
            </div>
            <button class="bg-[#001834] text-white px-8 py-4 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] hover:bg-[#002d54] transition-all shadow-xl shadow-[#001834]/10">
                Exportar Reporte
            </button>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            @foreach($alertas as $alerta)
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 hover:shadow-xl hover:shadow-gray-100 transition-all duration-500 group relative overflow-hidden">
                <div class="flex justify-between items-start mb-8">
                    <div class="flex items-center gap-6">
                        <div class="w-20 h-20 bg-gray-50 rounded-3xl flex items-center justify-center group-hover:bg-[#001834] transition-colors duration-500 shadow-inner">
                            <span class="material-symbols-outlined text-4xl text-[#001834] group-hover:text-[#ffba20] transition-colors duration-500">
                                @php
                                    $tipo = strtolower($alerta['vehiculo']->tipoveh);
                                    if(str_contains($tipo, 'camion') || str_contains($tipo, 'tracto')) echo 'local_shipping';
                                    elseif(str_contains($tipo, 'bus')) echo 'directions_bus';
                                    else echo 'directions_car';
                                @endphp
                            </span>
                        </div>
                        <div>
                            <h3 class="font-headline text-3xl font-black text-[#001834] tracking-tighter">{{ $alerta['vehiculo']->placaveh }}</h3>
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-gray-400 mt-1">
                                {{ $alerta['vehiculo']->marca->nommar ?? 'Marca Desconocida' }} • {{ $alerta['vehiculo']->empresa->nomemp ?? 'Empresa General' }}
                            </p>
                        </div>
                    </div>
                    @if($alerta['prioridad'] === 'alta')
                        <span class="bg-red-50 text-red-600 px-5 py-2 rounded-full text-[10px] font-black uppercase tracking-widest border border-red-100">Acción Crítica</span>
                    @else
                        <span class="bg-[#ffba20]/10 text-[#5e4200] px-5 py-2 rounded-full text-[10px] font-black uppercase tracking-widest border border-[#ffba20]/20">Advertencia</span>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-6">
                    @foreach($alerta['documentos'] as $tipo => $doc)
                    <div class="p-5 rounded-3xl transition-all duration-300 {{ $doc['estado'] === 'vencido' ? 'bg-red-50/50 border-l-4 border-red-500' : 'bg-gray-50 border-l-4 border-[#ffba20]' }}">
                        <p class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-2">{{ $tipo === 'soat' ? 'SOAT' : 'Tecnomecánica' }}</p>
                        <p class="text-lg font-black {{ $doc['estado'] === 'vencido' ? 'text-red-600' : 'text-[#001834]' }}">
                            {{ $doc['fecha']->format('M d, Y') }}
                        </p>
                        <p class="text-[10px] font-bold mt-1 {{ $doc['estado'] === 'vencido' ? 'text-red-400' : 'text-[#ffba20]' }}">
                            {{ $doc['estado'] === 'vencido' ? 'Vencido hace ' . abs($doc['dias']) . ' días' : 'Vence en ' . $doc['dias'] . ' días' }}
                        </p>
                    </div>
                    @endforeach

                    <!-- Otros documentos estáticos para completar el diseño -->
                    <div class="p-5 bg-gray-50 rounded-3xl border-l-4 border-blue-500">
                        <p class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-2">Responsabilidad Contractual</p>
                        <p class="text-lg font-black text-[#001834] opacity-50">Vigente</p>
                        <span class="inline-block mt-2 bg-blue-100 text-blue-600 px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest">ACTIVO</span>
                    </div>
                    <div class="p-5 bg-gray-50 rounded-3xl border-l-4 border-blue-500">
                        <p class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-2">Licencia de Tránsito</p>
                        <p class="text-lg font-black text-[#001834] opacity-50">Permanente</p>
                        <span class="inline-block mt-2 bg-blue-100 text-blue-600 px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest">OK</span>
                    </div>
                </div>
            </div>
            @endforeach

            @if($alertas->isEmpty())
            <div class="col-span-full py-20 text-center space-y-4">
                <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center mx-auto text-blue-500">
                    <span class="material-symbols-outlined text-5xl" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                </div>
                <h3 class="font-headline text-2xl font-black text-[#001834]">¡Todo en orden!</h3>
                <p class="text-gray-500 max-w-sm mx-auto">No se han detectado alertas críticas en la flota actualmente. Todos los documentos están vigentes.</p>
            </div>
            @endif
        </div>
    </section>
</div>
@endsection
