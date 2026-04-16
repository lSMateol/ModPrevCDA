@extends('layouts.app')

@section('content')
<div class="px-10 pb-20 max-w-7xl mx-auto">
    <!-- Top Bar Interno (Título y Acción) -->
    <div class="flex justify-between items-center mb-10">
        <div>
            <h2 class="font-headline font-black text-[#002D54] text-3xl tracking-tight">Lista de Diagnósticos</h2>
            <p class="text-on-surface-variant font-body text-sm mt-1">Control de flota y reportes técnicos preventivos</p>
        </div>
        <div class="flex items-center gap-4">
            <button id="btn-agendar" class="bg-gradient-to-r from-primary-fixed-dim to-[#ffc84d] text-on-primary-fixed px-8 py-3 rounded-xl font-black text-xs uppercase tracking-widest shadow-lg shadow-primary-fixed-dim/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center gap-3">
                <span class="material-symbols-outlined text-lg">add</span>
                <span>Nuevo Diagnóstico</span>
            </button>
        </div>
    </div>

    <!-- Sección de Métricas (Bento Grid Style) -->
    <section class="grid grid-cols-12 gap-6 mt-4">
        <div class="col-span-12 md:col-span-8 bg-surface-container-lowest p-8 rounded-2xl shadow-sm border-b-4 border-primary-fixed-dim flex justify-between items-center group hover:shadow-md transition-all">
            <div>
                <h3 class="font-body uppercase tracking-[0.2em] text-[0.6rem] text-on-surface-variant font-black mb-2 opacity-60">Métricas de Hoy</h3>
                <p class="font-headline font-extrabold text-4xl text-[#001834]">Rendimiento Diario</p>
            </div>
            <div class="flex gap-12">
                <div class="text-center group-hover:scale-110 transition-transform">
                    <p class="text-4xl font-black text-[#001834]">{{ $completados }}</p>
                    <p class="text-[0.6rem] uppercase font-black tracking-tighter text-on-surface-variant">Completados</p>
                </div>
                <div class="text-center group-hover:scale-110 transition-transform">
                    <p class="text-4xl font-black text-primary-fixed-dim">{{ $pendientes }}</p>
                    <p class="text-[0.6rem] uppercase font-black tracking-tighter text-on-surface-variant">Pendientes</p>
                </div>
            </div>
        </div>
        <div class="col-span-12 md:col-span-4 bg-[#001834] p-8 rounded-2xl flex flex-col justify-center shadow-lg shadow-[#001834]/20">
            <p class="text-white/60 text-[0.65rem] font-black uppercase tracking-[0.2em]">Estado General</p>
            <div class="mt-2 flex items-end gap-2">
                <span class="text-primary-fixed-dim text-5xl font-black">{{ $efectividad }}%</span>
                <span class="text-[#f7fafc] text-sm mb-2 opacity-80 font-medium">Efectividad</span>
            </div>
        </div>
    </section>

    <!-- Filtros de Búsqueda Mejorados -->
    <section class="mt-12 bg-surface-container-low p-8 rounded-2xl border border-outline-variant/5">
        <h3 class="font-headline font-bold text-[#001834] text-lg mb-8 flex items-center gap-3">
            <span class="material-symbols-outlined text-[#ffba20] bg-[#001834] p-1.5 rounded-lg text-sm">filter_list</span>
            Filtros de Búsqueda
        </h3>
        
        @php
            $prefix = Auth::user()->hasRole('Administrador') ? 'admin' : 'digitador';
        @endphp

        <form method="GET" action="{{ route($prefix . '.diagnosticos.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                <div class="flex flex-col gap-2">
                    <label class="font-body uppercase text-[0.6rem] font-black tracking-widest text-on-surface-variant opacity-70 px-1">Fecha</label>
                    <input type="date" name="fecha" value="{{ request('fecha') }}" class="bg-surface-container-lowest border-2 border-transparent focus:border-primary-fixed-dim focus:ring-0 rounded-xl p-3 text-sm font-semibold transition-all">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-body uppercase text-[0.6rem] font-black tracking-widest text-on-surface-variant opacity-70 px-1">Empresa</label>
                    <select name="empresa_id" class="bg-surface-container-lowest border-2 border-transparent focus:border-primary-fixed-dim focus:ring-0 rounded-xl p-3 text-sm font-semibold transition-all appearance-none cursor-pointer">
                        <option value="">Todas las Empresas</option>
                        @foreach($empresas as $emp)
                            <option value="{{ $emp->idemp }}" {{ request('empresa_id') == $emp->idemp ? 'selected' : '' }}>{{ $emp->razsoem }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-body uppercase text-[0.6rem] font-black tracking-widest text-on-surface-variant opacity-70 px-1">Placa de Vehículo</label>
                    <input type="text" name="placa" value="{{ request('placa') }}" placeholder="Ej. KVM-091" class="bg-surface-container-lowest border-2 border-transparent focus:border-primary-fixed-dim focus:ring-0 rounded-xl p-3 text-sm font-semibold transition-all">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-body uppercase text-[0.6rem] font-black tracking-widest text-on-surface-variant opacity-70 px-1">Estado</label>
                    <select name="aprobado" class="bg-surface-container-lowest border-2 border-transparent focus:border-primary-fixed-dim focus:ring-0 rounded-xl p-3 text-sm font-semibold transition-all cursor-pointer">
                        <option value="">Todos los Estados</option>
                        <option value="1" {{ request('aprobado') == '1' ? 'selected' : '' }}>Aprobado</option>
                        <option value="0" {{ request('aprobado') == '0' ? 'selected' : '' }}>No Aprobado</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-[#001834] text-white font-black text-xs h-[46px] uppercase tracking-widest rounded-xl hover:bg-tertiary-container hover:shadow-lg transition-all active:scale-[0.98]">
                        Aplicar Filtros
                    </button>
                </div>
            </div>
        </form>
    </section>

    <!-- Listado de Resultados -->
    <section class="mt-12">
        <div class="flex justify-between items-center mb-8 px-2">
            <h3 class="font-headline font-extrabold text-2xl text-[#001834]">Registros Recientes</h3>
            <p class="text-xs font-body font-bold text-on-surface-variant opacity-60">
                Mostrando <span class="text-[#001834] font-black">{{ $diagnosticos->count() }}</span> de <span class="text-[#001834] font-black">{{ $diagnosticos->total() }}</span> registros
            </p>
        </div>

        <div class="space-y-4">
            @forelse($diagnosticos as $diag)
            <div class="bg-surface-container-lowest rounded-2xl p-6 shadow-sm border border-transparent hover:border-primary-fixed-dim/20 flex flex-col md:flex-row items-center gap-8 hover:shadow-xl hover:translate-y-[-2px] transition-all duration-300">
                <!-- Fecha -->
                <div class="flex-shrink-0 text-center md:text-left min-w-[100px]">
                    <p class="text-[0.6rem] font-black text-on-surface-variant uppercase tracking-[0.15em] opacity-50 mb-1">Fecha</p>
                    <p class="font-black text-sm text-[#001834]">{{ \Carbon\Carbon::parse($diag->fecdia)->format('d M, Y') }}</p>
                </div>
                
                <!-- Placa -->
                <div class="flex-shrink-0 bg-surface-container-low px-5 py-3 rounded-xl border border-outline-variant/10 shadow-inner">
                    <p class="text-[0.55rem] font-black text-on-surface-variant uppercase tracking-widest opacity-50 mb-0.5">Placa</p>
                    <p class="font-black text-xl tracking-tighter text-[#001834]">{{ $diag->vehiculo->placaveh ?? 'N/A' }}</p>
                </div>
                
                <!-- Info -->
                <div class="flex-grow">
                    <p class="text-[0.6rem] font-black text-on-surface-variant uppercase tracking-[0.15em] opacity-50 mb-1">Empresa / Mantenimiento</p>
                    <div class="flex items-center gap-3">
                        <span class="font-extrabold text-[0.95rem] text-[#001834]">{{ $diag->vehiculo->empresa->razsoem ?? 'Sin empresa' }}</span>
                        <span class="w-1 h-1 rounded-full bg-outline-variant/50"></span>
                        <span class="text-xs font-black text-secondary tracking-widest uppercase opacity-70">ID-#{{ $diag->iddia }}</span>
                    </div>
                </div>
                
                <!-- Estado Status -->
                <div class="flex-shrink-0">
                    @if($diag->aprobado)
                        <span class="bg-emerald-50 text-emerald-700 px-5 py-2 rounded-full text-[0.65rem] font-black border border-emerald-100 uppercase tracking-widest">Aprobado</span>
                    @else
                        <span class="bg-red-50 text-red-700 px-5 py-2 rounded-full text-[0.65rem] font-black border border-red-100 uppercase tracking-widest">No Aprobado</span>
                    @endif
                </div>
                
                <!-- Acciones -->
                <div class="flex gap-2">
                    <a href="{{ route($prefix . '.diagnosticos.show', $diag->iddia) }}" class="p-3 text-[#001834] bg-surface-container-low hover:bg-[#001834] hover:text-white rounded-xl transition-all duration-300" title="Ver Detalle">
                        <span class="material-symbols-outlined text-lg">visibility</span>
                    </a>
                    <a href="{{ route($prefix . '.diagnosticos.edit', $diag->iddia) }}" class="p-3 text-[#001834] bg-surface-container-low hover:bg-[#001834] hover:text-white rounded-xl transition-all duration-300" title="Modificar">
                        <span class="material-symbols-outlined text-lg">edit_note</span>
                    </a>
                    <button class="p-3 text-primary-fixed-dim bg-primary-fixed-dim/10 hover:bg-primary-fixed-dim hover:text-on-primary-fixed rounded-xl transition-all duration-300 btn-foto" data-id="{{ $diag->iddia }}" title="Tomar Foto">
                        <span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' 1;">photo_camera</span>
                    </button>
                    <form action="{{ route($prefix . '.diagnosticos.destroy', $diag->iddia) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este diagnóstico?')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-3 text-error bg-error/10 hover:bg-error hover:text-white rounded-xl transition-all duration-300" title="Eliminar">
                            <span class="material-symbols-outlined text-lg">delete</span>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="bg-surface-container-low rounded-2xl py-20 text-center text-on-surface-variant flex flex-col items-center gap-4">
                <span class="material-symbols-outlined text-6xl opacity-20">error_outline</span>
                <p class="font-bold tracking-widest uppercase text-xs opacity-50">No se encontraron diagnósticos registrados</p>
            </div>
            @endforelse
        </div>

        <!-- Paginación con Estilo Premium -->
        <div class="mt-12">
            {{ $diagnosticos->onEachSide(1)->links() }}
        </div>
    </section>
</div>

@include('diagnosticos.modal-agendar')
@endsection


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnAgendar = document.getElementById('btn-agendar');
        const modalAgendar = document.getElementById('modal-agendar');
        const closeAgendar = document.getElementById('close-agendar');
        const formAgendar = document.getElementById('form-agendar');

        if (!btnAgendar || !modalAgendar) {
            console.error('No se encontraron los elementos del modal');
            return;
        }

        // Obtener el prefijo según el rol del usuario
        const prefix = '{{ Auth::user()->hasRole("Administrador") ? "admin" : "digitador" }}';

        // Abrir modal y cargar datos
        btnAgendar.addEventListener('click', async () => {
            try {
                // Mostrar loading en los selects
                document.getElementById('idveh').innerHTML = '<option value="">Cargando vehículos...</option>';
                document.getElementById('idinsp').innerHTML = '<option value="">Cargando inspectores...</option>';
                document.getElementById('iding').innerHTML = '<option value="">Cargando ingenieros...</option>';
                
                const res = await fetch(`/${prefix}/diagnosticos/data`, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                const data = await res.json();
                
                // Llenar vehículos
                const selectVehiculo = document.getElementById('idveh');
                selectVehiculo.innerHTML = '<option value="">Seleccione vehículo</option>';
                if (data.vehiculos && data.vehiculos.length) {
                    data.vehiculos.forEach(v => {
                        selectVehiculo.innerHTML += `<option value="${v.idveh}">${v.placaveh} - ${v.empresa?.razsoem || 'Sin empresa'}</option>`;
                    });
                } else {
                    selectVehiculo.innerHTML += '<option disabled>No hay vehículos registrados</option>';
                }
                
                // Llenar inspectores
                const selectInsp = document.getElementById('idinsp');
                selectInsp.innerHTML = '<option value="">Seleccione inspector</option>';
                if (data.inspectores && data.inspectores.length) {
                    data.inspectores.forEach(i => {
                        selectInsp.innerHTML += `<option value="${i.idper}">${i.nomper} ${i.apeper}</option>`;
                    });
                } else {
                    selectInsp.innerHTML += '<option disabled>No hay inspectores disponibles</option>';
                }
                
                // Llenar ingenieros
                const selectIng = document.getElementById('iding');
                selectIng.innerHTML = '<option value="">Seleccione ingeniero</option>';
                if (data.ingenieros && data.ingenieros.length) {
                    data.ingenieros.forEach(i => {
                        selectIng.innerHTML += `<option value="${i.idper}">${i.nomper} ${i.apeper}</option>`;
                    });
                } else {
                    selectIng.innerHTML += '<option disabled>No hay ingenieros disponibles</option>';
                }
                
                modalAgendar.classList.remove('hidden');
            } catch (error) {
                console.error('Error al cargar datos:', error);
                alert('No se pudieron cargar los datos. Revisa la consola para más detalles.');
            }
        });

        // Cerrar modal con la X
        if (closeAgendar) {
            closeAgendar.addEventListener('click', () => {
                modalAgendar.classList.add('hidden');
            });
        }
        
        // Cerrar modal haciendo clic fuera del contenido
        modalAgendar.addEventListener('click', (e) => {
            if (e.target === modalAgendar) {
                modalAgendar.classList.add('hidden');
            }
        });

        // Enviar formulario del modal
        if (formAgendar) {
            formAgendar.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(formAgendar);
                const submitBtn = formAgendar.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerText;
                submitBtn.innerText = 'Guardando...';
                submitBtn.disabled = true;
                
                try {
                    const res = await fetch(formAgendar.action, {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    });
                    
                    if (res.ok) {
                        window.location.href = `/${prefix}/diagnosticos`;
                    } else {
                        const errorData = await res.json().catch(() => ({}));
                        alert(errorData.message || 'Error al agendar el servicio. Verifica los datos.');
                        submitBtn.innerText = originalText;
                        submitBtn.disabled = false;
                    }
                } catch (error) {
                    console.error('Error en la petición:', error);
                    alert('Error de conexión. Intenta nuevamente.');
                    submitBtn.innerText = originalText;
                    submitBtn.disabled = false;
                }
            });
        }
    });
</script>
@endpush