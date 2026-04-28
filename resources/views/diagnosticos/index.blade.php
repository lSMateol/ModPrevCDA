@extends('layouts.app')

@section('content')
<div class="px-10 pb-20 max-w-7xl mx-auto">
    <!-- Top Bar Interno (Título y Acción) -->
    <!-- Top Bar Interno (Título y Acción) -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 mb-10">
        <div>
            <h2 class="font-headline font-black text-[#002D54] text-2xl md:text-3xl tracking-tight">Lista de Diagnósticos</h2>
            <p class="text-on-surface-variant font-body text-sm mt-1">Control de flota y reportes técnicos preventivos</p>
        </div>
        <div class="flex items-center w-full sm:w-auto">
            <button id="btn-agendar" class="w-full sm:w-auto bg-gradient-to-r from-primary-fixed-dim to-[#ffc84d] text-on-primary-fixed px-8 py-4 sm:py-3 rounded-xl font-black text-xs uppercase tracking-widest shadow-lg shadow-primary-fixed-dim/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-3">
                <span class="material-symbols-outlined text-lg">add</span>
                <span>Nuevo Diagnóstico</span>
            </button>
        </div>
    </div>
    
    @if(session('error'))
        <div class="mb-8 bg-red-50 border-l-4 border-red-500 p-4 rounded-xl flex items-center gap-3 animate-in fade-in duration-500">
            <span class="material-symbols-outlined text-red-600">error</span>
            <p class="text-sm font-bold text-red-800">{{ session('error') }}</p>
        </div>
    @endif

    @if(session('success'))
        <div class="mb-8 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-xl flex items-center gap-3 animate-in fade-in duration-500">
            <span class="material-symbols-outlined text-emerald-600">check_circle</span>
            <p class="text-sm font-bold text-emerald-800">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Sección de Métricas (Bento Grid Style) -->
    <section class="grid grid-cols-12 gap-6 mt-4">
        <div class="col-span-12 lg:col-span-8 bg-surface-container-lowest p-6 md:p-8 rounded-2xl shadow-sm border-b-4 border-primary-fixed-dim flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 group hover:shadow-md transition-all">
            <div>
                <h3 class="font-body uppercase tracking-[0.2em] text-[0.6rem] text-on-surface-variant font-black mb-1 md:mb-2 opacity-60">Métricas de Hoy</h3>
                <p class="font-headline font-extrabold text-2xl md:text-3xl lg:text-4xl text-[#001834]">Rendimiento Diario</p>
            </div>
            <div class="flex gap-8 md:gap-12 w-full sm:w-auto justify-around sm:justify-end">
                <div class="text-center group-hover:scale-110 transition-transform">
                    <p class="text-3xl md:text-4xl font-black text-[#001834]">{{ $completados }}</p>
                    <p class="text-[0.6rem] uppercase font-black tracking-tighter text-on-surface-variant">Completados</p>
                </div>
                <div class="text-center group-hover:scale-110 transition-transform">
                    <p class="text-3xl md:text-4xl font-black text-primary-fixed-dim">{{ $pendientes }}</p>
                    <p class="text-[0.6rem] uppercase font-black tracking-tighter text-on-surface-variant">Pendientes</p>
                </div>
            </div>
        </div>
        <div class="col-span-12 lg:col-span-4 bg-[#001834] p-6 md:p-8 rounded-2xl flex flex-col justify-center shadow-lg shadow-[#001834]/20">
            <p class="text-white/60 text-[0.65rem] font-black uppercase tracking-[0.2em]">Estado General</p>
            <div class="mt-2 flex items-end gap-2">
                <span class="text-primary-fixed-dim text-4xl md:text-5xl font-black">{{ $efectividad }}%</span>
                <span class="text-[#f7fafc] text-sm mb-1.5 md:mb-2 opacity-80 font-medium tracking-tight">Efectividad</span>
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

        <form id="filter-form" method="GET" action="{{ route($prefix . '.diagnosticos.index') }}" class="w-full">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 items-end">
                <div class="flex flex-col gap-2">
                    <label class="font-body uppercase text-[0.6rem] font-black tracking-widest text-on-surface-variant opacity-70 px-1">Fecha</label>
                    <input type="date" name="fecha" onchange="this.form.submit()" value="{{ request('fecha') }}" class="w-full bg-surface-container-lowest border-2 border-transparent focus:border-primary-fixed-dim focus:ring-0 rounded-xl p-3 text-sm font-semibold transition-all">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-body uppercase text-[0.6rem] font-black tracking-widest text-on-surface-variant opacity-70 px-1">Empresa</label>
                    <select name="empresa_id" onchange="this.form.submit()" class="w-full bg-surface-container-lowest border-2 border-transparent focus:border-primary-fixed-dim focus:ring-0 rounded-xl p-3 text-sm font-semibold transition-all appearance-none cursor-pointer">
                        <option value="">Todas las Empresas</option>
                        @foreach($empresas as $emp)
                            <option value="{{ $emp->idemp }}" {{ request('empresa_id') == $emp->idemp ? 'selected' : '' }}>{{ $emp->razsoem }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-body uppercase text-[0.6rem] font-black tracking-widest text-on-surface-variant opacity-70 px-1">Placa de Vehículo</label>
                    <input type="text" name="placa" oninput="debounceSubmit()" value="{{ request('placa') }}" placeholder="Ej. KVM-091" class="w-full bg-surface-container-lowest border-2 border-transparent focus:border-primary-fixed-dim focus:ring-0 rounded-xl p-3 text-sm font-semibold transition-all">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-body uppercase text-[0.6rem] font-black tracking-widest text-on-surface-variant opacity-70 px-1">Estado</label>
                    <select name="aprobado" onchange="this.form.submit()" class="w-full bg-surface-container-lowest border-2 border-transparent focus:border-primary-fixed-dim focus:ring-0 rounded-xl p-3 text-sm font-semibold transition-all cursor-pointer">
                        <option value="">Todos los Estados</option>
                        <option value="1" {{ request('aprobado') == '1' ? 'selected' : '' }}>Aprobado</option>
                        <option value="0" {{ request('aprobado') == '0' ? 'selected' : '' }}>No Aprobado</option>
                    </select>
                </div>
                <div class="flex flex-col gap-3 sm:col-span-2 lg:col-span-1">
                    <div class="w-full flex items-center justify-center bg-[#001834]/5 text-[#001834]/40 font-black text-[10px] h-[46px] uppercase tracking-widest rounded-xl border border-dashed border-[#001834]/10">
                        Auto-Filtro Activo
                    </div>
                    <a href="{{ route($prefix . '.diagnosticos.index') }}" class="text-[9px] font-black text-[#001834] uppercase tracking-widest text-center hover:underline opacity-60 hover:opacity-100 transition-opacity">
                        Limpiar Filtros
                    </a>
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
            <div class="bg-surface-container-lowest rounded-2xl p-5 md:p-6 shadow-sm border border-transparent hover:border-primary-fixed-dim/20 hover:shadow-xl hover:translate-y-[-1px] transition-all duration-300 relative overflow-hidden group">
                <div class="flex flex-col lg:flex-row items-center justify-between gap-6">
                    
                    <!-- Lado Izquierdo: Fecha y Placa -->
                    <div class="flex flex-col sm:flex-row items-center gap-6 w-full lg:w-auto">
                        <div class="flex-shrink-0 text-center sm:text-left min-w-[100px]">
                            <p class="text-[0.6rem] font-black text-on-surface-variant uppercase tracking-[0.15em] opacity-40">Fecha</p>
                            <p class="font-black text-sm text-[#001834]">{{ \Carbon\Carbon::parse($diag->fecdia)->format('d M, Y') }}</p>
                        </div>

                        <div class="flex-shrink-0 bg-surface-container-low px-5 py-3 rounded-xl border border-outline-variant/10 shadow-inner min-w-[140px] text-center">
                            <p class="text-[0.55rem] font-black text-on-surface-variant uppercase tracking-widest opacity-40 mb-0.5">Vehículo</p>
                            <p class="font-black text-lg tracking-tighter text-[#001834]">{{ $diag->vehiculo->placaveh ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Centro: Empresa (Con más presencia) -->
                    <div class="flex-grow text-center lg:text-left lg:px-10 border-t lg:border-t-0 lg:border-x border-gray-100 py-4 lg:py-0 w-full lg:w-auto">
                        <p class="text-[0.6rem] font-black text-on-surface-variant uppercase tracking-[0.15em] opacity-40 mb-1">Empresa / Propietario</p>
                        <div class="flex flex-col gap-1">
                            <span class="font-extrabold text-[0.95rem] text-[#001834] leading-tight truncate">{{ $diag->vehiculo->empresa->razsoem ?? 'Sin empresa vinculada' }}</span>
                            <span class="text-[10px] font-black text-secondary tracking-widest uppercase opacity-60">Expediente ID-#{{ $diag->iddia }}</span>
                        </div>
                    </div>

                    <!-- Lado Derecho: Estado y Acciones -->
                    <div class="flex flex-col sm:flex-row items-center gap-4 md:gap-8 w-full lg:w-auto">
                        <!-- Badge de Estado -->
                        <div class="flex flex-row items-center gap-3">
                            @if($diag->dpiddia)
                                <span class="bg-[#002D54] text-white px-3 py-2 rounded-lg text-[0.55rem] font-black uppercase tracking-tighter shadow-sm flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[12px]">history</span> RE-INSP
                                </span>
                            @endif

                            @if(is_null($diag->aprobado))
                                <span class="bg-gray-50 text-gray-400 px-5 py-2.5 rounded-full text-[0.65rem] font-black border border-gray-100 uppercase tracking-widest">Pendiente</span>
                            @elseif($diag->aprobado)
                                <span class="bg-emerald-50 text-emerald-700 px-5 py-2.5 rounded-full text-[0.65rem] font-black border border-emerald-100 uppercase tracking-widest">Aprobado</span>
                            @else
                                <span class="bg-red-50 text-red-700 px-5 py-2.5 rounded-full text-[0.65rem] font-black border border-red-100 uppercase tracking-widest">No Aprobado</span>
                            @endif
                        </div>

                        <!-- Botones de Acción -->
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route($prefix . '.diagnosticos.show', $diag->iddia) }}" class="p-3 text-[#001834] bg-surface-container-low hover:bg-[#001834] hover:text-white rounded-xl transition-all duration-300" title="Ver Detalle">
                                <span class="material-symbols-outlined text-lg">visibility</span>
                            </a>
                            
                            @php
                                $canExport = !is_null($diag->aprobado);
                            @endphp
                            
                            <a href="{{ $canExport ? route($prefix . '.diagnosticos.export', $diag->iddia) : 'javascript:void(0)' }}" 
                               target="{{ $canExport ? '_blank' : '_self' }}" 
                               class="p-3 {{ $canExport ? 'text-[#002D54] bg-surface-container-low hover:bg-[#002D54] hover:text-white' : 'text-gray-300 bg-gray-50 cursor-not-allowed opacity-50' }} rounded-xl transition-all duration-300" 
                               onclick="{{ !$canExport ? "alert('Terminar proceso para exportar')" : '' }}"
                               title="Exportar">
                                <span class="material-symbols-outlined text-lg">description</span>
                            </a>

                            <a href="{{ route($prefix . '.diagnosticos.edit', $diag->iddia) }}" class="p-3 text-[#001834] bg-surface-container-low hover:bg-[#001834] hover:text-white rounded-xl transition-all duration-300" title="Modificar">
                                <span class="material-symbols-outlined text-lg">edit_note</span>
                            </a>
                            <button class="p-3 text-primary-fixed-dim bg-primary-fixed-dim/10 hover:bg-primary-fixed-dim hover:text-on-primary-fixed rounded-xl transition-all duration-300 btn-foto" data-id="{{ $diag->iddia }}" title="Tomar Foto">
                                <span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' 1;">photo_camera</span>
                            </button>
                            <form action="{{ route($prefix . '.diagnosticos.destroy', $diag->iddia) }}" method="POST" onsubmit="return confirm('¿Eliminar registro?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-3 text-error bg-error/10 hover:bg-error hover:text-white rounded-xl transition-all duration-300" title="Eliminar">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </form>
                        </div>
                    </div>
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
@include('diagnosticos.modal-fotos')
@endsection


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Prefijo global
        const prefix = '{{ Auth::user()->hasRole("Administrador") ? "admin" : "digitador" }}';

        // ==========================================
        // LÓGICA DE AGENDAR
        // ==========================================
        const btnAgendar = document.getElementById('btn-agendar');
        const modalAgendar = document.getElementById('modal-agendar');
        const closeAgendar = document.getElementById('close-agendar');
        const formAgendar = document.getElementById('form-agendar');

        if (btnAgendar && modalAgendar) {
            btnAgendar.addEventListener('click', async () => {
                try {
                    document.getElementById('idveh').innerHTML = '<option value="">Cargando vehículos...</option>';
                    document.getElementById('idinsp').innerHTML = '<option value="">Cargando inspectores...</option>';
                    document.getElementById('iding').innerHTML = '<option value="">Cargando ingenieros...</option>';
                    
                    const res = await fetch(`/${prefix}/diagnosticos/data`, {
                        method: 'GET',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });
                    
                    const data = await res.json();
                    allVehicles = data.vehiculos;
                    
                    const selectVehiculo = document.getElementById('idveh');
                    selectVehiculo.innerHTML = '<option value="">Seleccione vehículo</option>';
                    allVehicles.forEach(v => {
                        selectVehiculo.innerHTML += `<option value="${v.idveh}">${v.placaveh} - ${v.empresa?.razsoem || 'Sin empresa'}</option>`;
                    });

                    const selectInsp = document.getElementById('idinsp');
                    selectInsp.innerHTML = '<option value="">Seleccione inspector</option>';
                    data.inspectores.forEach(i => {
                        selectInsp.innerHTML += `<option value="${i.idper}">${i.nomper} ${i.apeper}</option>`;
                    });
                    
                    const selectIng = document.getElementById('iding');
                    selectIng.innerHTML = '<option value="">Seleccione ingeniero</option>';
                    data.ingenieros.forEach(i => {
                        selectIng.innerHTML += `<option value="${i.idper}">${i.nomper} ${i.apeper}</option>`;
                    });
                    
                    modalAgendar.classList.remove('hidden');
                } catch (error) {
                    console.error('Error al cargar datos:', error);
                }
            });

            const selectVehiculo = document.getElementById('idveh');
            if (selectVehiculo) {
                selectVehiculo.addEventListener('change', (e) => {
                    const veh = allVehicles.find(v => v.idveh == e.target.value);
                    const display = document.getElementById('combu_display');
                    if (veh && display) {
                        display.value = veh.combustible?.nomval || 'NO DEFINIDO';
                    }
                });
            }

            if (closeAgendar) closeAgendar.onclick = () => modalAgendar.classList.add('hidden');
            modalAgendar.onclick = (e) => { if (e.target === modalAgendar) modalAgendar.classList.add('hidden'); };

            if (formAgendar) {
                formAgendar.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const formData = new FormData(formAgendar);
                    const submitBtn = formAgendar.querySelector('button[type="submit"]');
                    submitBtn.disabled = true;
                    
                    try {
                        const res = await fetch(formAgendar.action, {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        });
                        if (res.ok) window.location.reload();
                        else {
                            const data = await res.json();
                            if (data.duplicate) {
                                if (confirm(data.message + "\n\n¿Desea ir a EDITAR el diagnóstico existente para corregir valores?")) {
                                    window.location.href = `/${prefix}/diagnosticos/${data.iddia}/edit`;
                                }
                            } else {
                                alert(data.message || 'Error al guardar');
                            }
                        }
                    } catch (error) {
                        console.error(error);
                    } finally {
                        submitBtn.disabled = false;
                    }
                });
            }
        }

        // ==========================================
        // LÓGICA DE CAPTURA DE FOTOS (WebP)
        // ==========================================
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
        let existingPhotos = []; // { id, url }
        let newPhotos = [];      // Blobs
        let idsAEliminar = [];   // IDs de fotos existentes a borrar

        document.querySelectorAll('.btn-foto').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.getAttribute('data-id');
                fotoDiagId.innerText = id;
                modalFotos.classList.remove('hidden');
                
                // Cargar fotos existentes
                try {
                    const res = await fetch(`/${prefix}/diagnosticos/${id}/fotos`);
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

        stopCameraBtn.onclick = (e) => { e.stopPropagation(); stopCamera(); };

        snap.onclick = (e) => {
            e.stopPropagation();
            if ((existingPhotos.length + newPhotos.length) >= 2) return alert("Máximo 2 fotos");
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            canvas.toBlob((blob) => {
                newPhotos.push(blob);
                updatePhotoUI();
            }, 'image/webp', 0.8);
        };

        fileInput.onchange = (e) => {
            Array.from(e.target.files).forEach(file => {
                if ((existingPhotos.length + newPhotos.length) >= 2) return;
                const reader = new FileReader();
                reader.onload = (ev) => {
                    const img = new Image();
                    img.onload = () => {
                        const tempCanvas = document.createElement('canvas');
                        tempCanvas.width = img.width; tempCanvas.height = img.height;
                        tempCanvas.getContext('2d').drawImage(img, 0, 0);
                        tempCanvas.toBlob((blob) => {
                            newPhotos.push(blob);
                            updatePhotoUI();
                        }, 'image/webp', 0.8);
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
            
            // Renderizar existentes
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

            // Renderizar nuevas
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

        btnSaveFotos.onclick = async () => {
            const id = fotoDiagId.innerText;
            const formData = new FormData();
            
            newPhotos.forEach((blob, i) => formData.append(`fotos[]`, blob, `evid_${id}_new_${i}.webp`));
            formData.append('ids_a_eliminar', JSON.stringify(idsAEliminar));

            btnSaveFotos.disabled = true;
            btnSaveFotos.innerText = 'Sincronizando...';

            try {
                const res = await fetch(`/${prefix}/diagnosticos/${id}/fotos`, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                if (res.ok) {
                    alert('Evidencias actualizadas');
                    modalFotos.classList.add('hidden');
                } else alert('Error al guardar');
            } catch (err) {
                alert('Error de red');
            } finally {
                btnSaveFotos.innerText = 'Guardar Evidencias';
                btnSaveFotos.disabled = false;
            }
        };

        closeFotos.onclick = () => { modalFotos.classList.add('hidden'); stopCamera(); };
        modalFotos.onclick = (e) => { if (e.target === modalFotos) { modalFotos.classList.add('hidden'); stopCamera(); } };
    });

    let filterTimeout = null;
    function debounceSubmit() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(() => {
            document.getElementById('filter-form').submit();
        }, 600);
    }
</script>
@endpush