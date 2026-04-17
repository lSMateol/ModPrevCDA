<!-- Modal de Modificación de Asignación -->
<div id="modal-edit-asignacion" class="fixed inset-0 bg-[#001c3b]/60 backdrop-blur-sm z-[100] flex items-center justify-center hidden p-4 overflow-y-auto transition-all duration-300">
    <div class="relative w-full max-w-lg transition-transform duration-300 scale-95" id="modal-edit-content">
        <!-- Botón Cerrar -->
        <button id="close-edit-asignacion" class="absolute -top-12 right-0 bg-[#ffba20] text-[#001834] px-4 py-2 rounded-full font-black text-[10px] uppercase tracking-[0.2em] shadow-2xl transition-all flex items-center gap-2 group">
            <span class="material-symbols-outlined text-sm">close</span> Cerrar
        </button>
        
        <div class="bg-surface-container-lowest rounded-2xl p-8 shadow-2xl border border-outline-variant/10 relative z-10 w-full">
            <div class="mb-8 text-center">
                <h1 class="font-headline font-extrabold text-2xl text-[#001834] tracking-tight mb-1">Modificar Asignación</h1>
                <p class="text-on-surface-variant text-xs font-medium uppercase tracking-widest opacity-70">Ajuste técnico del servicio</p>
            </div>

            <form id="form-edit-asignacion" action="{{ route($prefix . '.diagnosticos.update-asignacion', $diagnostico->iddia) }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="space-y-4">
                    <!-- Kilometraje -->
                    <div class="space-y-2">
                        <label class="font-label text-[0.65rem] uppercase tracking-wider text-on-surface-variant font-bold">Kilometraje Actual</label>
                        <div class="relative">
                            <input type="number" name="kilomt" value="{{ $diagnostico->kilomt }}" required class="w-full bg-surface-container-high border-b-2 border-outline-variant/20 focus:border-[#ffba20] focus:ring-0 rounded-t-xl px-4 py-3 text-[#001834] font-semibold text-sm transition-all" placeholder="Kilometraje...">
                            <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant opacity-40">speed</span>
                        </div>
                    </div>

                    <!-- Inspector -->
                    <div class="space-y-2">
                        <label class="font-label text-[0.65rem] uppercase tracking-wider text-on-surface-variant font-bold">Inspector Asignado</label>
                        <div class="relative">
                            <select name="idinsp" id="edit_idinsp" required class="w-full bg-surface-container-high border-b-2 border-outline-variant/20 focus:border-[#ffba20] focus:ring-0 rounded-t-xl px-4 py-3 appearance-none text-[#001834] font-semibold text-sm">
                                <option value="{{ $diagnostico->idinsp }}" selected>{{ $diagnostico->inspector->nombre_completo ?? 'Actual' }}</option>
                                <!-- Se llena vía AJAX -->
                            </select>
                            <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant opacity-40">engineering</span>
                        </div>
                    </div>

                    <!-- Ingeniero -->
                    <div class="space-y-2">
                        <label class="font-label text-[0.65rem] uppercase tracking-wider text-on-surface-variant font-bold">Ingeniero Responsable</label>
                        <div class="relative">
                            <select name="iding" id="edit_iding" required class="w-full bg-surface-container-high border-b-2 border-outline-variant/20 focus:border-[#ffba20] focus:ring-0 rounded-t-xl px-4 py-3 appearance-none text-[#001834] font-semibold text-sm">
                                <option value="{{ $diagnostico->iding }}" selected>{{ $diagnostico->ingeniero->nombre_completo ?? 'Actual' }}</option>
                                <!-- Se llena vía AJAX -->
                            </select>
                            <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant opacity-40">supervisor_account</span>
                        </div>
                    </div>
                </div>

                <div class="pt-6">
                    <button type="submit" class="w-full bg-[#001834] text-white py-4 px-6 rounded-xl font-black text-xs uppercase tracking-[0.15em] shadow-xl hover:bg-[#ffba20] hover:text-[#001834] transition-all flex items-center justify-center gap-3">
                        Guardar Cambios
                        <span class="material-symbols-outlined text-lg">save</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('btn-edit-asignacion');
    const modal = document.getElementById('modal-edit-asignacion');
    const close = document.getElementById('close-edit-asignacion');
    const form = document.getElementById('form-edit-asignacion');
    const prefix = '{{ $prefix }}';

    if (btn && modal) {
        btn.addEventListener('click', async () => {
            // Cargar data si no está cargada
            if (document.getElementById('edit_idinsp').options.length <= 1) {
                try {
                    const res = await fetch(`/${prefix}/diagnosticos/data`);
                    const data = await res.json();
                    
                    const selInsp = document.getElementById('edit_idinsp');
                    const currentInsp = '{{ $diagnostico->idinsp }}';
                    data.inspectores.forEach(i => {
                        if (i.idper != currentInsp) {
                            const opt = document.createElement('option');
                            opt.value = i.idper;
                            opt.textContent = i.nomper + ' ' + i.apeper;
                            selInsp.appendChild(opt);
                        }
                    });

                    const selIng = document.getElementById('edit_iding');
                    const currentIng = '{{ $diagnostico->iding }}';
                    data.ingenieros.forEach(i => {
                        if (i.idper != currentIng) {
                            const opt = document.createElement('option');
                            opt.value = i.idper;
                            opt.textContent = i.nomper + ' ' + i.apeper;
                            selIng.appendChild(opt);
                        }
                    });
                } catch (e) { console.error(e); }
            }
            modal.classList.remove('hidden');
        });

        close.onclick = () => modal.classList.add('hidden');
        modal.onclick = (e) => { if (e.target === modal) modal.classList.add('hidden'); };

        form.onsubmit = async (e) => {
            e.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Guardando...';

            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (err) {
                console.error(err);
                alert('Error de conexión');
            } finally {
                submitBtn.disabled = false;
            }
        };
    }
});
</script>
