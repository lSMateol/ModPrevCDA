@php
    $prefix = Auth::user()->hasRole('Administrador') ? 'admin' : 'digitador';
@endphp

<!-- Modal de Agendamiento con Diseño Premium -->
<div id="modal-agendar" class="fixed inset-0 bg-[#001c3b]/60 backdrop-blur-sm z-[100] flex items-center justify-center hidden p-4 overflow-y-auto transition-all duration-300">
    
    <div class="relative w-full max-w-lg transition-transform duration-300 scale-95" id="modal-content">
        <!-- Botón Cerrar Flotante Mejorado -->
        <button id="close-agendar" class="absolute -top-12 right-0 bg-[#ffba20] text-[#001834] px-4 py-2 rounded-full font-black text-[10px] uppercase tracking-[0.2em] shadow-2xl shadow-[#ffba20]/30 hover:bg-white hover:scale-105 active:scale-95 transition-all flex items-center gap-2 group">
            <span class="material-symbols-outlined text-sm transition-transform group-hover:rotate-90">close</span> 
            <span>Cerrar</span>
        </button>
        
        <!-- Tarjeta de Formulario -->
        <div class="bg-surface-container-lowest rounded-2xl p-8 shadow-[0_32px_64px_-12px_rgba(0,28,59,0.2)] border border-outline-variant/10 relative z-10 w-full">
            
            <!-- Encabezado del Modal -->
            <div class="mb-8 text-center">
                <h1 class="font-headline font-extrabold text-2xl text-on-surface tracking-tight mb-1">Asignación de Servicio</h1>
                <p class="text-on-surface-variant text-xs font-medium uppercase tracking-widest opacity-70">Módulo Preventivo de Inspección</p>
            </div>

            <form id="form-agendar" action="{{ route($prefix . '.diagnosticos.store') }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- Vehículo -->
                <div class="space-y-2">
                    <label class="font-label text-[0.65rem] uppercase tracking-wider text-on-surface-variant font-bold">Vehículo</label>
                    <div class="relative group">
                        <select name="idveh" id="idveh" required class="w-full bg-surface-container-high border-b-2 border-outline-variant/20 focus:border-primary-fixed-dim focus:ring-0 rounded-t-xl px-4 py-3 appearance-none text-on-surface font-semibold text-sm transition-all group-hover:bg-surface-container-highest cursor-pointer">
                            <option value="" disabled selected>Seleccione placa o interno</option>
                            <option value="">Cargando...</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant transition-transform group-hover:translate-y-[-40%]">expand_more</span>
                    </div>
                </div>

                <!-- Kilometraje -->
                <div class="space-y-2">
                    <label class="font-label text-[0.65rem] uppercase tracking-wider text-on-surface-variant font-bold">Kilometraje Actual</label>
                    <div class="relative group">
                        <input type="number" name="kilometraje" required class="w-full bg-surface-container-high border-b-2 border-outline-variant/20 focus:border-primary-fixed-dim focus:ring-0 rounded-t-xl px-4 py-3 text-on-surface font-semibold text-sm transition-all group-hover:bg-surface-container-highest" placeholder="Ingrese el recorrido actual">
                        <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant/50 text-base">speed</span>
                    </div>
                </div>

                <!-- Inspector -->
                <div class="space-y-2">
                    <label class="font-label text-[0.65rem] uppercase tracking-wider text-on-surface-variant font-bold">Inspector Asignado</label>
                    <div class="relative group">
                        <select name="idinsp" id="idinsp" required class="w-full bg-surface-container-high border-b-2 border-outline-variant/20 focus:border-primary-fixed-dim focus:ring-0 rounded-t-xl px-4 py-3 appearance-none text-on-surface font-semibold text-sm transition-all group-hover:bg-surface-container-highest cursor-pointer">
                             <option value="" disabled selected>Elegir personal técnico</option>
                             <option value="">Cargando...</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant">engineering</span>
                    </div>
                </div>

                <!-- Ingeniero -->
                <div class="space-y-2">
                    <label class="font-label text-[0.65rem] uppercase tracking-wider text-on-surface-variant font-bold">Ingeniero Responsable</label>
                    <div class="relative group">
                        <select name="iding" id="iding" required class="w-full bg-surface-container-high border-b-2 border-outline-variant/20 focus:border-primary-fixed-dim focus:ring-0 rounded-t-xl px-4 py-3 appearance-none text-on-surface font-semibold text-sm transition-all group-hover:bg-surface-container-highest cursor-pointer">
                            <option value="" disabled selected>Elegir director de turno</option>
                            <option value="">Cargando...</option>
                        </select>
                        <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant">supervisor_account</span>
                    </div>
                </div>

                <!-- Botón de Acción -->
                <div class="pt-6">
                    <button type="submit" class="w-full bg-gradient-to-r from-primary-fixed-dim to-[#ffc84d] text-on-primary-fixed py-4 px-6 rounded-xl font-black text-xs uppercase tracking-[0.15em] shadow-xl shadow-primary-fixed-dim/20 hover:shadow-primary-fixed-dim/40 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-3">
                        Asignar Servicio
                        <span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' 1;">assignment_turned_in</span>
                    </button>
                </div>
            </form>
            
            <!-- Indicador de Progreso -->
            <div class="mt-12 flex justify-center items-center gap-6 max-w-[240px] mx-auto">
                <div class="flex flex-col items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-primary-fixed-dim flex items-center justify-center text-on-primary-fixed font-black shadow-lg shadow-primary-fixed-dim/30 text-[10px]">1</div>
                    <span class="text-[0.6rem] font-black uppercase tracking-tighter text-on-surface">Datos</span>
                </div>
                <div class="flex-grow h-[3px] bg-outline-variant/20 rounded-full"></div>
                <div class="flex flex-col items-center gap-2 opacity-30">
                    <div class="w-8 h-8 rounded-full bg-surface-container-highest flex items-center justify-center text-on-surface-variant font-black text-[10px]">2</div>
                    <span class="text-[0.6rem] font-black uppercase tracking-tighter text-on-surface">Validar</span>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    /* Efecto de entrada al mostrar el modal */
    #modal-agendar:not(.hidden) #modal-content {
        transform: scale(1);
    }
</style>