@extends('layouts.app')

@section('content')
<div class="p-6 md:p-8 max-w-7xl mx-auto" x-data="catalogos()">
    
    <!-- HEADER -->
    <div class="mb-8">
        <h1 class="text-3xl font-black text-[#001834] font-headline tracking-tight">Catálogos del Sistema</h1>
        <p class="text-sm text-on-surface-variant font-body mt-2">Administra los dominios, valores y parámetros globales de la aplicación.</p>
    </div>

    @if (session('success'))
    <div class="mb-6 bg-emerald-50 text-emerald-700 p-4 rounded-xl border border-emerald-100 flex items-center gap-3">
        <span class="material-symbols-outlined">check_circle</span>
        <span class="text-sm font-semibold">{{ session('success') }}</span>
    </div>
    @endif

    @if ($errors->any())
    <div class="mb-6 bg-red-50 text-red-700 p-4 rounded-xl border border-red-100 flex items-start gap-3">
        <span class="material-symbols-outlined mt-0.5">error</span>
        <div>
            <span class="text-sm font-bold">Por favor corrige los siguientes errores:</span>
            <ul class="list-disc list-inside text-sm mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <!-- TABS -->
    <div class="flex items-center gap-8 border-b border-outline-variant/20 mb-8 overflow-x-auto">
        <a href="{{ route('admin.catalogos.index', ['tab' => 'dominios']) }}" 
           class="pb-4 flex items-center gap-2 text-sm font-black tracking-widest uppercase transition-colors whitespace-nowrap {{ $tab == 'dominios' ? 'text-[#001834] border-b-2 border-primary-fixed' : 'text-on-surface-variant/50 hover:text-on-surface-variant' }}">
            <span class="material-symbols-outlined text-lg">category</span>
            Gestión de Dominios
        </a>
        <a href="{{ route('admin.catalogos.index', ['tab' => 'parametros']) }}" 
           class="pb-4 flex items-center gap-2 text-sm font-black tracking-widest uppercase transition-colors whitespace-nowrap {{ $tab == 'parametros' ? 'text-[#001834] border-b-2 border-primary-fixed' : 'text-on-surface-variant/50 hover:text-on-surface-variant' }}">
            <span class="material-symbols-outlined text-lg">tune</span>
            Gestión de Parámetros
        </a>
    </div>

    <!-- MAIN CONTENT TWO COLUMNS -->
    <div class="flex flex-col lg:flex-row gap-8 items-start">
        
        <!-- LEFT PANEL -->
        <div class="w-full lg:w-1/3 flex flex-col gap-4">
            
            @if($tab == 'dominios')
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <h2 class="text-xl font-black text-[#001834] font-headline">Lista de Dominios</h2>
                        <p class="text-xs text-on-surface-variant">Catálogos globales</p>
                    </div>
                    <button @click="openModalDominio()" class="bg-primary-fixed hover:bg-primary-fixed-dim text-[#001834] px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest flex items-center gap-2 transition-all shadow-sm">
                        <span class="material-symbols-outlined text-sm">add</span> Nuevo
                    </button>
                </div>

                <div class="flex flex-col gap-3">
                    @forelse($dominios as $dom)
                        <div class="relative group">
                            <a href="{{ route('admin.catalogos.index', ['tab' => 'dominios', 'dominio_id' => $dom->iddom]) }}" 
                               class="flex items-center gap-4 p-4 rounded-2xl border transition-all {{ ($dominioSeleccionado && $dominioSeleccionado->iddom == $dom->iddom) ? 'bg-white border-primary-fixed shadow-md' : 'bg-surface-container-lowest border-transparent hover:border-outline-variant/30 hover:shadow-sm' }}">
                                <div class="bg-surface-container-low p-2 rounded-lg text-on-surface-variant shrink-0">
                                    <span class="material-symbols-outlined">folder</span>
                                </div>
                                <div class="flex-grow min-w-0">
                                    <h3 class="font-bold text-[#001834] text-sm truncate">{{ $dom->nomdom }}</h3>
                                    <p class="text-[0.65rem] text-on-surface-variant uppercase tracking-widest font-semibold mt-0.5">{{ $dom->valores_count }} valores</p>
                                </div>
                            </a>
                            <!-- Actions for Dominio -->
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click.prevent="editDominio({{ $dom->iddom }}, '{{ addslashes($dom->nomdom) }}')" class="p-1.5 text-on-surface-variant hover:text-[#001834] bg-white rounded-lg shadow-sm border border-outline-variant/20 transition-colors">
                                    <span class="material-symbols-outlined text-sm">edit</span>
                                </button>
                                <button @click.prevent="deleteDominio({{ $dom->iddom }})" class="p-1.5 text-red-400 hover:text-red-700 bg-white rounded-lg shadow-sm border border-red-100 transition-colors">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center p-8 bg-surface-container-lowest rounded-3xl border border-dashed border-outline-variant/30">
                            <span class="material-symbols-outlined text-4xl text-on-surface-variant/30 mb-2">folder_off</span>
                            <p class="text-sm text-on-surface-variant font-medium">No hay dominios creados.</p>
                        </div>
                    @endforelse
                </div>

            @else
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <h2 class="text-xl font-black text-[#001834] font-headline">Tipos de Parámetro</h2>
                        <p class="text-xs text-on-surface-variant">Agrupaciones</p>
                    </div>
                    <button @click="openModalTippar()" class="bg-primary-fixed hover:bg-primary-fixed-dim text-[#001834] px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest flex items-center gap-2 transition-all shadow-sm">
                        <span class="material-symbols-outlined text-sm">add</span> Nuevo
                    </button>
                </div>

                <div class="flex flex-col gap-3">
                    @forelse($tipospar as $tip)
                        <div class="relative group">
                            <a href="{{ route('admin.catalogos.index', ['tab' => 'parametros', 'tippar_id' => $tip->idtip]) }}" 
                               class="flex items-center gap-4 p-4 rounded-2xl border transition-all {{ ($tipparSeleccionado && $tipparSeleccionado->idtip == $tip->idtip) ? 'bg-white border-primary-fixed shadow-md' : 'bg-surface-container-lowest border-transparent hover:border-outline-variant/30 hover:shadow-sm' }}">
                                <div class="bg-surface-container-low p-2 rounded-lg text-on-surface-variant shrink-0">
                                    <span class="material-symbols-outlined">folder</span>
                                </div>
                                <div class="flex-grow min-w-0">
                                    <h3 class="font-bold text-[#001834] text-sm truncate">{{ $tip->nomtip }}</h3>
                                    <p class="text-[0.65rem] text-on-surface-variant uppercase tracking-widest font-semibold mt-0.5">{{ $tip->params_count }} parámetros</p>
                                </div>
                            </a>
                            <!-- Actions for Tippar -->
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click.prevent="editTippar({{ $tip->idtip }}, '{{ addslashes($tip->nomtip) }}', '{{ addslashes($tip->tittip) }}', {{ $tip->idpef }}, '{{ addslashes($tip->icotip) }}', {{ $tip->acttip }})" class="p-1.5 text-on-surface-variant hover:text-[#001834] bg-white rounded-lg shadow-sm border border-outline-variant/20 transition-colors">
                                    <span class="material-symbols-outlined text-sm">edit</span>
                                </button>
                                <button @click.prevent="deleteTippar({{ $tip->idtip }})" class="p-1.5 text-red-400 hover:text-red-700 bg-white rounded-lg shadow-sm border border-red-100 transition-colors">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center p-8 bg-surface-container-lowest rounded-3xl border border-dashed border-outline-variant/30">
                            <span class="material-symbols-outlined text-4xl text-on-surface-variant/30 mb-2">list_off</span>
                            <p class="text-sm text-on-surface-variant font-medium">No hay tipos de parámetro.</p>
                        </div>
                    @endforelse
                </div>
            @endif
        </div>

        <!-- RIGHT PANEL -->
        <div class="w-full lg:w-2/3 bg-white rounded-3xl shadow-sm border border-outline-variant/10 p-6 md:p-8 min-h-[500px]">
            
            @if($tab == 'dominios' && $dominioSeleccionado)
                <!-- Valores del Dominio -->
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-6 mb-8">
                    <div>
                        <span class="bg-[#001834] text-white text-[0.6rem] font-black uppercase tracking-widest px-3 py-1 rounded-full mb-3 inline-block">Vista Detallada</span>
                        <h2 class="text-2xl font-black text-[#001834] font-headline">Valores: {{ $dominioSeleccionado->nomdom }}</h2>
                    </div>
                    <button @click="openModalValor({{ $dominioSeleccionado->iddom }})" class="bg-white border-2 border-primary-fixed hover:bg-primary-fixed/10 text-[#001834] px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest flex items-center justify-center gap-2 transition-colors shrink-0">
                        <span class="material-symbols-outlined text-sm">playlist_add</span> Añadir Valor
                    </button>
                </div>

                @if($dominioSeleccionado->valores->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-container-lowest">
                                    <th class="py-3 px-4 text-[0.65rem] font-black text-on-surface-variant uppercase tracking-widest rounded-l-xl">ID</th>
                                    <th class="py-3 px-4 text-[0.65rem] font-black text-on-surface-variant uppercase tracking-widest">Nombre del Valor</th>
                                    <th class="py-3 px-4 text-[0.65rem] font-black text-on-surface-variant uppercase tracking-widest">Parámetro</th>
                                    <th class="py-3 px-4 text-[0.65rem] font-black text-on-surface-variant uppercase tracking-widest">Estado</th>
                                    <th class="py-3 px-4 text-[0.65rem] font-black text-on-surface-variant uppercase tracking-widest text-right rounded-r-xl">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/10">
                                @foreach($dominioSeleccionado->valores as $val)
                                <tr class="hover:bg-surface-container-lowest/50 transition-colors">
                                    <td class="py-4 px-4 text-sm font-mono text-on-surface-variant">{{ str_pad($val->idval, 3, '0', STR_PAD_LEFT) }}</td>
                                    <td class="py-4 px-4 text-sm font-bold text-[#001834]">{{ $val->nomval }}</td>
                                    <td class="py-4 px-4 text-sm text-on-surface-variant">{{ $val->parval ?: '-' }}</td>
                                    <td class="py-4 px-4 text-sm">
                                        @if($val->actval)
                                            <span class="bg-emerald-50 text-emerald-700 text-xs px-2 py-1 rounded font-bold">Activo</span>
                                        @else
                                            <span class="bg-red-50 text-red-700 text-xs px-2 py-1 rounded font-bold">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 flex justify-end gap-2">
                                        <button @click="editValor({{ $val->idval }}, {{ $val->iddom }}, '{{ addslashes($val->nomval) }}', '{{ addslashes($val->parval) }}', {{ $val->actval }})" class="p-1.5 text-on-surface-variant hover:text-primary-fixed transition-colors">
                                            <span class="material-symbols-outlined text-sm">edit</span>
                                        </button>
                                        <button @click="deleteValor({{ $val->idval }})" class="p-1.5 text-red-300 hover:text-red-700 transition-colors">
                                            <span class="material-symbols-outlined text-sm">delete</span>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-16 text-center">
                        <div class="bg-surface-container-lowest p-6 rounded-full mb-4">
                            <span class="material-symbols-outlined text-4xl text-on-surface-variant/50">menu_book</span>
                        </div>
                        <p class="text-sm font-bold text-[#001834] mb-1">Aún no hay valores</p>
                        <p class="text-xs text-on-surface-variant">Usa el botón superior para añadir nuevos registros a esta categoría.</p>
                    </div>
                @endif

            @elseif($tab == 'parametros' && $tipparSeleccionado)
                <!-- Params del Tippar -->
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-6 mb-8">
                    <div>
                        <span class="bg-[#001834] text-white text-[0.6rem] font-black uppercase tracking-widest px-3 py-1 rounded-full mb-3 inline-block">Vista Detallada</span>
                        <h2 class="text-2xl font-black text-[#001834] font-headline">Parámetros: {{ $tipparSeleccionado->nomtip }}</h2>
                    </div>
                    <button @click="openModalParam({{ $tipparSeleccionado->idtip }})" class="bg-white border-2 border-primary-fixed hover:bg-primary-fixed/10 text-[#001834] px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest flex items-center justify-center gap-2 transition-colors shrink-0">
                        <span class="material-symbols-outlined text-sm">add_box</span> Añadir Param
                    </button>
                </div>

                @if($tipparSeleccionado->params->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-container-lowest">
                                    <th class="py-3 px-3 text-[0.65rem] font-black text-on-surface-variant uppercase tracking-widest rounded-l-xl">ID</th>
                                    <th class="py-3 px-3 text-[0.65rem] font-black text-on-surface-variant uppercase tracking-widest">Nombre</th>
                                    <th class="py-3 px-3 text-[0.65rem] font-black text-on-surface-variant uppercase tracking-widest">Control</th>
                                    <th class="py-3 px-3 text-[0.65rem] font-black text-on-surface-variant uppercase tracking-widest">Campo</th>
                                    <th class="py-3 px-3 text-[0.65rem] font-black text-on-surface-variant uppercase tracking-widest text-right rounded-r-xl">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/10">
                                @foreach($tipparSeleccionado->params as $par)
                                <tr class="hover:bg-surface-container-lowest/50 transition-colors">
                                    <td class="py-3 px-3 text-sm font-mono text-on-surface-variant">{{ $par->idpar }}</td>
                                    <td class="py-3 px-3 text-sm font-bold text-[#001834]">
                                        {{ $par->nompar }}
                                        @if($par->rini || $par->rfin)
                                            <div class="text-[0.65rem] font-normal text-on-surface-variant">Rango: {{ $par->rini }} - {{ $par->rfin }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-sm text-on-surface-variant">{{ $par->control }}</td>
                                    <td class="py-3 px-3 text-sm text-on-surface-variant">{{ $par->nomcampo }}</td>
                                    <td class="py-3 px-3 flex justify-end gap-1">
                                        <button @click="editParam({{ $par->idpar }}, {{ $par->idtip }}, '{{ addslashes($par->nompar) }}', '{{ addslashes($par->nomcampo) }}', '{{ addslashes($par->control) }}', '{{ $par->rini }}', '{{ $par->rfin }}', '{{ addslashes($par->unipar) }}', '{{ $par->colum }}', {{ $par->can }}, {{ $par->actpar }})" class="p-1.5 text-on-surface-variant hover:text-primary-fixed transition-colors">
                                            <span class="material-symbols-outlined text-sm">edit</span>
                                        </button>
                                        <button @click="deleteParam({{ $par->idpar }})" class="p-1.5 text-red-300 hover:text-red-700 transition-colors">
                                            <span class="material-symbols-outlined text-sm">delete</span>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-16 text-center">
                        <div class="bg-surface-container-lowest p-6 rounded-full mb-4">
                            <span class="material-symbols-outlined text-4xl text-on-surface-variant/50">data_object</span>
                        </div>
                        <p class="text-sm font-bold text-[#001834] mb-1">Aún no hay parámetros</p>
                        <p class="text-xs text-on-surface-variant">Usa el botón superior para añadir nuevos parámetros a esta categoría.</p>
                    </div>
                @endif
                
            @else
                <div class="flex items-center justify-center h-full text-center py-20">
                    <div>
                        <span class="material-symbols-outlined text-5xl text-outline-variant/30 mb-4 block">touch_app</span>
                        <p class="text-lg font-bold text-[#001834]">Selecciona un elemento</p>
                        <p class="text-sm text-on-surface-variant mt-1">Elige un elemento del panel izquierdo para ver sus detalles.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODALS -->
    <!-- ============================================== -->

    <!-- Modal Dominio -->
    <div x-show="showDominioModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="display: none;"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div @click.away="showDominioModal = false" class="bg-white rounded-3xl p-6 md:p-8 w-full max-w-md shadow-2xl relative"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100">
            
            <h3 class="text-xl font-black text-[#001834] mb-6 font-headline" x-text="dominioForm.iddom ? 'Editar Dominio' : 'Nuevo Dominio'"></h3>
            
            <form :action="dominioForm.iddom ? '{{ url('admin/catalogos/dominio') }}/' + dominioForm.iddom : '{{ route('admin.catalogos.dominio.store') }}'" method="POST">
                @csrf
                <template x-if="dominioForm.iddom"><input type="hidden" name="_method" value="PUT"></template>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-black text-on-surface-variant uppercase tracking-widest mb-2">Nombre del Dominio</label>
                        <input type="text" name="nomdom" x-model="dominioForm.nomdom" required
                               class="w-full bg-surface-container-lowest border border-outline-variant/30 text-[#001834] text-sm rounded-xl focus:ring-primary-fixed focus:border-primary-fixed block p-3 transition-colors">
                    </div>
                </div>
                
                <div class="mt-8 flex justify-end gap-3">
                    <button type="button" @click="showDominioModal = false" class="px-5 py-2.5 text-sm font-bold text-on-surface-variant hover:text-[#001834] transition-colors">Cancelar</button>
                    <button type="submit" class="bg-primary-fixed hover:bg-primary-fixed-dim text-[#001834] px-6 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest transition-colors shadow-sm">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Valor -->
    <div x-show="showValorModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="display: none;"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div @click.away="showValorModal = false" class="bg-white rounded-3xl p-6 md:p-8 w-full max-w-lg shadow-2xl relative"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100">
            
            <h3 class="text-xl font-black text-[#001834] mb-6 font-headline" x-text="valorForm.idval ? 'Editar Valor' : 'Nuevo Valor'"></h3>
            
            <form :action="valorForm.idval ? '{{ url('admin/catalogos/valor') }}/' + valorForm.idval : '{{ route('admin.catalogos.valor.store') }}'" method="POST">
                @csrf
                <template x-if="valorForm.idval"><input type="hidden" name="_method" value="PUT"></template>
                <input type="hidden" name="iddom" x-model="valorForm.iddom">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-black text-on-surface-variant uppercase tracking-widest mb-2">Nombre del Valor</label>
                        <input type="text" name="nomval" x-model="valorForm.nomval" required
                               class="w-full bg-surface-container-lowest border border-outline-variant/30 text-[#001834] text-sm rounded-xl focus:ring-primary-fixed focus:border-primary-fixed block p-3 transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-on-surface-variant uppercase tracking-widest mb-2">Parámetro (opcional)</label>
                        <input type="text" name="parval" x-model="valorForm.parval" placeholder="Ej: AUTO, DIE, etc."
                               class="w-full bg-surface-container-lowest border border-outline-variant/30 text-[#001834] text-sm rounded-xl focus:ring-primary-fixed focus:border-primary-fixed block p-3 transition-colors">
                    </div>
                    <div class="flex items-center gap-3 pt-2">
                        <input type="checkbox" name="actval" id="actval" value="1" x-model="valorForm.actval" class="w-4 h-4 text-primary-fixed bg-surface-container-lowest border-outline-variant/30 rounded focus:ring-primary-fixed">
                        <label for="actval" class="text-sm font-bold text-[#001834]">Registro Activo</label>
                    </div>
                </div>
                
                <div class="mt-8 flex justify-end gap-3">
                    <button type="button" @click="showValorModal = false" class="px-5 py-2.5 text-sm font-bold text-on-surface-variant hover:text-[#001834] transition-colors">Cancelar</button>
                    <button type="submit" class="bg-primary-fixed hover:bg-primary-fixed-dim text-[#001834] px-6 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest transition-colors shadow-sm">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tippar -->
    <div x-show="showTipparModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="display: none;"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div @click.away="showTipparModal = false" class="bg-white rounded-3xl p-6 md:p-8 w-full max-w-lg shadow-2xl relative overflow-y-auto max-h-[90vh]"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100">
            
            <h3 class="text-xl font-black text-[#001834] mb-6 font-headline" x-text="tipparForm.idtip ? 'Editar Tipo de Parámetro' : 'Nuevo Tipo'"></h3>
            
            <form :action="tipparForm.idtip ? '{{ url('admin/catalogos/tippar') }}/' + tipparForm.idtip : '{{ route('admin.catalogos.tippar.store') }}'" method="POST">
                @csrf
                <template x-if="tipparForm.idtip"><input type="hidden" name="_method" value="PUT"></template>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-black text-on-surface-variant uppercase tracking-widest mb-2">Nombre Corto</label>
                        <input type="text" name="nomtip" x-model="tipparForm.nomtip" required
                               class="w-full bg-surface-container-lowest border border-outline-variant/30 text-[#001834] text-sm rounded-xl focus:ring-primary-fixed focus:border-primary-fixed block p-3 transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-on-surface-variant uppercase tracking-widest mb-2">Título Completo</label>
                        <input type="text" name="tittip" x-model="tipparForm.tittip" required
                               class="w-full bg-surface-container-lowest border border-outline-variant/30 text-[#001834] text-sm rounded-xl focus:ring-primary-fixed focus:border-primary-fixed block p-3 transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-on-surface-variant uppercase tracking-widest mb-2">Perfil Asignado</label>
                        <select name="idpef" x-model="tipparForm.idpef" required class="w-full bg-surface-container-lowest border border-outline-variant/30 text-[#001834] text-sm rounded-xl focus:ring-primary-fixed focus:border-primary-fixed block p-3">
                            <option value="">Seleccione un perfil</option>
                            @foreach($perfiles as $pef)
                                <option value="{{ $pef->idpef }}">{{ $pef->nompef }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-on-surface-variant uppercase tracking-widest mb-2">Ícono (opcional)</label>
                        <input type="text" name="icotip" x-model="tipparForm.icotip" placeholder="Ej: tune, build, text_fields"
                               class="w-full bg-surface-container-lowest border border-outline-variant/30 text-[#001834] text-sm rounded-xl focus:ring-primary-fixed focus:border-primary-fixed block p-3 transition-colors">
                    </div>
                    <div class="flex items-center gap-3 pt-2">
                        <input type="checkbox" name="acttip" id="acttip" value="1" x-model="tipparForm.acttip" class="w-4 h-4 text-primary-fixed bg-surface-container-lowest border-outline-variant/30 rounded focus:ring-primary-fixed">
                        <label for="acttip" class="text-sm font-bold text-[#001834]">Registro Activo</label>
                    </div>
                </div>
                
                <div class="mt-8 flex justify-end gap-3">
                    <button type="button" @click="showTipparModal = false" class="px-5 py-2.5 text-sm font-bold text-on-surface-variant hover:text-[#001834] transition-colors">Cancelar</button>
                    <button type="submit" class="bg-primary-fixed hover:bg-primary-fixed-dim text-[#001834] px-6 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest transition-colors shadow-sm">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Param -->
    <div x-show="showParamModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="display: none;"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div @click.away="showParamModal = false" class="bg-white rounded-3xl p-6 md:p-8 w-full max-w-2xl shadow-2xl relative overflow-y-auto max-h-[90vh]"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100">
            
            <h3 class="text-xl font-black text-[#001834] mb-6 font-headline" x-text="paramForm.idpar ? 'Editar Parámetro' : 'Nuevo Parámetro'"></h3>
            
            <form :action="paramForm.idpar ? '{{ url('admin/catalogos/param') }}/' + paramForm.idpar : '{{ route('admin.catalogos.param.store') }}'" method="POST">
                @csrf
                <template x-if="paramForm.idpar"><input type="hidden" name="_method" value="PUT"></template>
                <input type="hidden" name="idtip" x-model="paramForm.idtip">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-black text-on-surface-variant uppercase tracking-widest mb-2">Nombre del Parámetro</label>
                        <input type="text" name="nompar" x-model="paramForm.nompar" required
                               class="w-full bg-surface-container-lowest border border-outline-variant/30 text-[#001834] text-sm rounded-xl focus:ring-primary-fixed focus:border-primary-fixed block p-3 transition-colors">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-black text-on-surface-variant uppercase tracking-widest mb-2">Nombre del Campo (BD)</label>
                        <input type="text" name="nomcampo" x-model="paramForm.nomcampo" required maxlength="30"
                               class="w-full bg-surface-container-lowest border border-outline-variant/30 text-[#001834] text-sm rounded-xl focus:ring-primary-fixed focus:border-primary-fixed block p-3 transition-colors">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-black text-on-surface-variant uppercase tracking-widest mb-2">Tipo de Control</label>
                        <select name="control" x-model="paramForm.control" required class="w-full bg-surface-container-lowest border border-outline-variant/30 text-[#001834] text-sm rounded-xl focus:ring-primary-fixed focus:border-primary-fixed block p-3">
                            <option value="text">Texto</option>
                            <option value="number">Número</option>
                            <option value="radio">Opciones (Radio)</option>
                            <option value="checkbox">Casilla</option>
                            <option value="textarea">Área de texto</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-on-surface-variant uppercase tracking-widest mb-2">Rango Inicial</label>
                        <input type="number" step="0.01" name="rini" x-model="paramForm.rini"
                               class="w-full bg-surface-container-lowest border border-outline-variant/30 text-[#001834] text-sm rounded-xl focus:ring-primary-fixed focus:border-primary-fixed block p-3 transition-colors">
                    </div>

                    <div>
                        <label class="block text-xs font-black text-on-surface-variant uppercase tracking-widest mb-2">Rango Final</label>
                        <input type="number" step="0.01" name="rfin" x-model="paramForm.rfin"
                               class="w-full bg-surface-container-lowest border border-outline-variant/30 text-[#001834] text-sm rounded-xl focus:ring-primary-fixed focus:border-primary-fixed block p-3 transition-colors">
                    </div>

                    <div>
                        <label class="block text-xs font-black text-on-surface-variant uppercase tracking-widest mb-2">Unidad</label>
                        <input type="text" name="unipar" x-model="paramForm.unipar" placeholder="Ej: mm, RPM, °C"
                               class="w-full bg-surface-container-lowest border border-outline-variant/30 text-[#001834] text-sm rounded-xl focus:ring-primary-fixed focus:border-primary-fixed block p-3 transition-colors">
                    </div>

                    <div>
                        <label class="block text-xs font-black text-on-surface-variant uppercase tracking-widest mb-2">Columna / Cantidad</label>
                        <div class="flex gap-2">
                            <input type="number" name="colum" x-model="paramForm.colum" placeholder="Col" class="w-1/2 bg-surface-container-lowest border border-outline-variant/30 text-[#001834] text-sm rounded-xl p-3">
                            <input type="number" name="can" x-model="paramForm.can" placeholder="Cant" required class="w-1/2 bg-surface-container-lowest border border-outline-variant/30 text-[#001834] text-sm rounded-xl p-3">
                        </div>
                    </div>

                    <div class="md:col-span-2 flex items-center gap-3 pt-2">
                        <input type="checkbox" name="actpar" id="actpar" value="1" x-model="paramForm.actpar" class="w-4 h-4 text-primary-fixed bg-surface-container-lowest border-outline-variant/30 rounded focus:ring-primary-fixed">
                        <label for="actpar" class="text-sm font-bold text-[#001834]">Registro Activo</label>
                    </div>
                </div>
                
                <div class="mt-8 flex justify-end gap-3 border-t border-outline-variant/10 pt-4">
                    <button type="button" @click="showParamModal = false" class="px-5 py-2.5 text-sm font-bold text-on-surface-variant hover:text-[#001834] transition-colors">Cancelar</button>
                    <button type="submit" class="bg-primary-fixed hover:bg-primary-fixed-dim text-[#001834] px-6 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest transition-colors shadow-sm">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" style="display: none;"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div @click.away="showDeleteModal = false" class="bg-white rounded-3xl p-6 w-full max-w-sm shadow-2xl relative text-center"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            
            <div class="w-16 h-16 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-3xl">warning</span>
            </div>
            
            <h3 class="text-xl font-black text-[#001834] mb-2 font-headline">¿Eliminar registro?</h3>
            <p class="text-sm text-on-surface-variant mb-6">Esta acción no se puede deshacer y podría afectar la integridad de los datos relacionados.</p>
            
            <form :action="deleteActionUrl" method="POST" class="flex gap-3 w-full">
                @csrf
                <input type="hidden" name="_method" value="DELETE">
                <button type="button" @click="showDeleteModal = false" class="flex-1 px-5 py-3 rounded-xl text-sm font-bold text-on-surface-variant bg-surface-container-lowest hover:bg-surface-container-low transition-colors">Cancelar</button>
                <button type="submit" class="flex-1 px-5 py-3 rounded-xl text-sm font-black uppercase tracking-widest text-white bg-red-600 hover:bg-red-700 transition-colors shadow-sm">
                    Eliminar
                </button>
            </form>
        </div>
    </div>

</div>

<script>
    function catalogos() {
        return {
            showDominioModal: false,
            showValorModal: false,
            showTipparModal: false,
            showParamModal: false,
            showDeleteModal: false,
            deleteActionUrl: '',
            
            dominioForm: { iddom: null, nomdom: '' },
            valorForm: { idval: null, iddom: null, nomval: '', parval: '', actval: true },
            tipparForm: { idtip: null, nomtip: '', tittip: '', idpef: '', icotip: '', acttip: true },
            paramForm: { idpar: null, idtip: null, nompar: '', nomcampo: '', control: 'text', rini: '', rfin: '', unipar: '', colum: '', can: 1, actpar: true },

            openModalDominio() {
                this.dominioForm = { iddom: null, nomdom: '' };
                this.showDominioModal = true;
            },
            editDominio(id, nombre) {
                this.dominioForm = { iddom: id, nomdom: nombre };
                this.showDominioModal = true;
            },
            deleteDominio(id) {
                this.deleteActionUrl = '{{ url("admin/catalogos/dominio") }}/' + id;
                this.showDeleteModal = true;
            },

            openModalValor(dominio_id) {
                this.valorForm = { idval: null, iddom: dominio_id, nomval: '', parval: '', actval: true };
                this.showValorModal = true;
            },
            editValor(id, dominio_id, nombre, param, act) {
                this.valorForm = { idval: id, iddom: dominio_id, nomval: nombre, parval: param, actval: !!act };
                this.showValorModal = true;
            },
            deleteValor(id) {
                this.deleteActionUrl = '{{ url("admin/catalogos/valor") }}/' + id;
                this.showDeleteModal = true;
            },

            openModalTippar() {
                this.tipparForm = { idtip: null, nomtip: '', tittip: '', idpef: '', icotip: '', acttip: true };
                this.showTipparModal = true;
            },
            editTippar(id, nom, tit, pef, ico, act) {
                this.tipparForm = { idtip: id, nomtip: nom, tittip: tit, idpef: pef, icotip: ico, acttip: !!act };
                this.showTipparModal = true;
            },
            deleteTippar(id) {
                this.deleteActionUrl = '{{ url("admin/catalogos/tippar") }}/' + id;
                this.showDeleteModal = true;
            },

            openModalParam(tippar_id) {
                this.paramForm = { idpar: null, idtip: tippar_id, nompar: '', nomcampo: '', control: 'text', rini: '', rfin: '', unipar: '', colum: '', can: 1, actpar: true };
                this.showParamModal = true;
            },
            editParam(id, tip, nom, campo, ctl, ri, rf, uni, col, can, act) {
                this.paramForm = { idpar: id, idtip: tip, nompar: nom, nomcampo: campo, control: ctl, rini: ri, rfin: rf, unipar: uni, colum: col, can: can, actpar: !!act };
                this.showParamModal = true;
            },
            deleteParam(id) {
                this.deleteActionUrl = '{{ url("admin/catalogos/param") }}/' + id;
                this.showDeleteModal = true;
            }
        }
    }
</script>
@endsection
