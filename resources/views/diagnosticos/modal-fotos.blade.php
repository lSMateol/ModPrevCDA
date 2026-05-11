<!-- Modal para Tomar/Cargar Fotos -->
<div id="modal-fotos" class="fixed inset-0 z-[60] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div class="fixed inset-0 transition-opacity bg-[#001834]/80 backdrop-blur-sm" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Contenido del Modal -->
        <div class="inline-block align-bottom bg-surface-container-lowest rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-white/10">
            <div class="px-8 pt-8 pb-6 bg-[#001834] relative">
                <div class="absolute top-0 right-0 p-6">
                    <button id="close-fotos" class="text-white/40 hover:text-white transition-colors cursor-pointer">
                        <span class="material-symbols-outlined text-2xl font-bold">close</span>
                    </button>
                </div>
                <div class="flex items-center gap-4">
                    <div class="bg-primary-fixed-dim/20 p-3 rounded-2xl">
                        <span class="material-symbols-outlined text-primary-fixed-dim text-2xl" style="font-variation-settings: 'FILL' 1;">photo_camera</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-white leading-tight">Captura de Evidencia</h3>
                        <p class="text-white/40 text-[0.65rem] uppercase tracking-[0.2em] font-black mt-1">Diagnóstico ID-#<span id="foto-diag-id">0</span></p>
                    </div>
                </div>
            </div>

            <div class="p-8 space-y-8">
                <!-- Contenedor de Captura -->
                <div id="capture-container" class="space-y-6">
                    <!-- Vista previa de cámara / área de drop -->
                    <div class="relative group">
                        <div id="camera-preview" class="w-full aspect-video bg-surface-container-low rounded-2xl border-2 border-dashed border-outline-variant/30 overflow-hidden flex flex-col items-center justify-center gap-4 group-hover:border-primary-fixed-dim/50 transition-all cursor-pointer">
                            <video id="video" class="hidden w-full h-full object-cover" autoplay playsinline></video>
                            <canvas id="canvas" class="hidden"></canvas>
                            
                            <div id="upload-placeholder" class="text-center p-8">
                                <span class="material-symbols-outlined text-4xl text-[#001834] opacity-20 mb-4 block">cloud_upload</span>
                                <p class="text-[0.65rem] font-black uppercase tracking-widest text-[#001834] opacity-60">Toque para usar cámara o subir archivos</p>
                                <p class="text-[0.55rem] font-bold text-on-surface-variant/40 mt-2 uppercase tracking-tighter">Máximo 2 fotografías (Formato WebP automático)</p>
                            </div>
                        </div>
                        <input type="file" id="file-input" accept="image/*" multiple class="hidden">
                    </div>

                    <!-- Controles de Cámara -->
                    <div id="camera-controls" class="hidden flex justify-center gap-4">
                        <button id="snap" class="bg-[#001834] text-white px-8 py-3 rounded-xl font-black text-xs uppercase tracking-widest flex items-center gap-2 hover:scale-[1.02] active:scale-[0.98] transition-all">
                            <span class="material-symbols-outlined text-lg">radio_button_checked</span>
                            Capturar Foto
                        </button>
                        <button id="stop-camera" class="bg-error/10 text-error px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest flex items-center gap-2 hover:bg-error/20 transition-all">
                            Finalizar Cámara
                        </button>
                    </div>

                    <!-- Lista de Fotos Capturadas -->
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <h4 class="text-[0.65rem] font-black uppercase tracking-[0.2em] text-on-surface-variant opacity-60">Fotos Seleccionadas (<span id="photo-count">0</span>/2)</h4>
                        </div>
                        <div id="photo-list" class="grid grid-cols-2 gap-4">
                            <!-- Aquí se insertan las fotos -->
                        </div>
                    </div>
                </div>

                <!-- Botones finales -->
                <div class="pt-6 border-t border-outline-variant/10">
                    <button id="btn-save-fotos" disabled class="w-full bg-gradient-to-r from-primary-fixed-dim to-[#ffc84d] text-on-primary-fixed py-5 rounded-2xl font-black uppercase tracking-[0.2em] shadow-xl shadow-primary-fixed-dim/30 hover:scale-[1.01] active:scale-[0.98] disabled:opacity-30 disabled:grayscale disabled:scale-100 transition-all flex items-center justify-center gap-3">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">cloud_upload</span>
                        <span>Guardar Evidencias</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="photo-item-template">
    <div class="group relative rounded-xl overflow-hidden aspect-video bg-black/5 border border-outline-variant/20 shadow-sm">
        <img src="" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <button class="absolute top-2 right-2 p-1 bg-red-500 text-white rounded-lg opacity-0 group-hover:opacity-100 transition-all hover:bg-red-600 shadow-lg remove-photo">
            <span class="material-symbols-outlined text-sm">delete</span>
        </button>
        <div class="absolute bottom-2 left-2 flex items-center gap-1.5">
            <span class="px-1.5 py-0.5 bg-white/20 backdrop-blur-md rounded text-[0.5rem] font-black text-white uppercase tracking-tighter border border-white/10">WEBP</span>
        </div>
    </div>
</template>
