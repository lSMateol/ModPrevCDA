<aside class="w-64 bg-[#0a1d37] h-full flex flex-col text-gray-300 shadow-xl" x-data="{ openMenu: null }">
    <div class="p-6 border-b border-gray-700/50 flex items-center space-x-3">
        <div class="bg-orange-500 w-9 h-9 rounded-lg flex items-center justify-center shadow-lg shadow-orange-900/20">
            <i class="fa-solid fa-car-side text-white text-sm"></i>
        </div>
        <div>
            <h1 class="text-white font-bold text-[10px] leading-tight">CDA RASTRILLANTAS LTDA.</h1>
            <p class="text-[9px] text-gray-400 font-medium">CENTRO DE DIAGNÓSTICO AUTOMOTOR</p>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto py-4 custom-scrollbar">
        <div class="px-4 space-y-1">

            @php
                $prefix = Auth::check() && Auth::user()->hasRole('Administrador') ? 'admin' : (Auth::check() && Auth::user()->hasRole('Digitador') ? 'digitador' : 'empresa');
                $ultimoDiag = \App\Models\Diag::latest('iddia')->first();
                $currentId = Request::route('diagnostico') ?? ($ultimoDiag ? $ultimoDiag->iddia : null);
            @endphp

            {{-- 1. OPERACIÓN --}}
            @hasanyrole('Administrador|Digitador')
            <div class="space-y-1">
                <button @click="openMenu = (openMenu === 'operacion' ? null : 'operacion')" 
                        class="w-full flex items-center justify-between py-2 px-3 hover:bg-white/5 rounded-md group transition text-left">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-table-cells-large text-xs group-hover:text-white"></i>
                        <span class="text-[11px] font-bold uppercase tracking-wider group-hover:text-white">Operación</span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="openMenu === 'operacion' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openMenu === 'operacion'" x-collapse class="ml-5 pl-4 border-l border-gray-700/50 space-y-1 mt-1 bg-[#0f2a4a]/50 rounded-r-md relative">
                    <a href="{{ url($prefix . '/diagnosticos') }}" class="relative flex items-center py-2 text-[10px] text-gray-400 hover:text-white transition">
                        <span class="absolute -left-4 w-3 h-px bg-gray-700/50"></span>
                        <i class="fa-solid fa-table-cells text-[8px] mr-2"></i>
                        Diagnóstico
                    </a>
                    <a href="{{ $currentId ? url($prefix . '/diagnosticos/' . $currentId) : '#' }}" class="relative flex items-center py-2 text-[10px] {{ Request::is('*/diagnosticos/*') && !Request::is('*/edit') ? 'text-white font-bold bg-white/10 rounded-md px-2' : 'text-gray-400' }} hover:text-white transition">
                        <span class="absolute -left-4 w-3 h-px bg-gray-700/50"></span>
                        <i class="fa-solid fa-table-cells text-[8px] mr-2"></i>
                        Detalle Diagnóstico
                    </a>
                    <a href="{{ route($prefix . '.alertas') }}" class="relative flex items-center py-2 text-[10px] {{ Request::routeIs('*.alertas') ? 'text-white font-bold bg-white/10 rounded-md px-2' : 'text-gray-400' }} hover:text-white transition">
                        <span class="absolute -left-4 w-3 h-px bg-gray-700/50"></span>
                        <i class="fa-solid fa-table-cells text-[8px] mr-2"></i>
                        Alertas
                    </a>
                    <a href="{{ route($prefix . '.rechazados') }}" class="relative flex items-center py-2 text-[10px] {{ Request::routeIs('*.rechazados*') ? 'text-white font-bold bg-white/10 rounded-md px-2' : 'text-gray-400' }} hover:text-white transition">
                        <span class="absolute -left-4 w-3 h-px bg-gray-700/50"></span>
                        <i class="fa-solid fa-table-cells text-[8px] mr-2"></i>
                        Rechazados
                    </a>
                </div>
            </div>
            @endhasanyrole

            {{-- 2. GESTIÓN VEHICULAR --}}
            @if(auth()->user()->hasRole('Empresa'))
            <div class="space-y-1 mb-2">
                <a href="{{ url($prefix . '/vehiculos-empresa?view=perfil') }}" 
                   class="w-full flex items-center justify-between py-2 px-3 hover:bg-white/5 rounded-md group transition text-left {{ Request::query('view') == 'perfil' ? 'bg-white/10 text-white' : 'text-gray-400' }}">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-building-user text-xs group-hover:text-white {{ Request::query('view') == 'perfil' ? 'text-white' : '' }}"></i>
                        <span class="text-[11px] font-bold uppercase tracking-wider group-hover:text-white {{ Request::query('view') == 'perfil' ? 'text-white' : '' }}">Mi Perfil</span>
                    </div>
                </a>
            </div>
            @endif

            @hasanyrole('Administrador|Digitador|Empresa')
            <div class="space-y-1">
                <button @click="openMenu = (openMenu === 'gestion' ? null : 'gestion')" 
                        class="w-full flex items-center justify-between py-2 px-3 hover:bg-white/5 rounded-md group transition text-left">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-car text-xs group-hover:text-white"></i>
                        <span class="text-[11px] font-bold uppercase tracking-wider group-hover:text-white">Gestión Vehicular</span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="openMenu === 'gestion' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openMenu === 'gestion'" x-collapse class="ml-5 pl-4 border-l border-gray-700/50 space-y-1 mt-1 bg-[#0f2a4a]/50 rounded-r-md relative">
                    <a href="{{ url($prefix . '/vehiculos') }}" class="relative flex items-center py-2 text-[10px] {{ Request::is('*/vehiculos') && Request::query('view') != 'perfil' ? 'text-white font-bold bg-white/10 rounded-md px-2' : 'text-gray-400' }} hover:text-white transition">
                        <span class="absolute -left-4 w-3 h-px bg-gray-700/50"></span>
                        <i class="fa-solid fa-car-side text-[8px] mr-2"></i>
                        Vehículos
                    </a>
                    <a href="{{ url($prefix . '/vehiculos-empresa') }}" class="relative flex items-center py-2 text-[10px] {{ Request::is('*/vehiculos-empresa*') ? 'text-white font-bold bg-white/10 rounded-md px-2' : 'text-gray-400' }} hover:text-white transition">
                        <span class="absolute -left-4 w-3 h-px bg-gray-700/50"></span>
                        <i class="fa-solid fa-building text-[8px] mr-2"></i>
                        Vehículos Empresa
                    </a>
                    <a href="{{ url($prefix . '/historial') }}" class="relative flex items-center py-2 text-[10px] {{ Request::is('*/historial*') ? 'text-white font-bold bg-white/10 rounded-md px-2' : 'text-gray-400' }} hover:text-white transition">
                        <span class="absolute -left-4 w-3 h-px bg-gray-700/50"></span>
                        <i class="fa-solid fa-clock-rotate-left text-[8px] mr-2"></i>
                        Historial Mantenimiento
                    </a>
                    
                    @hasanyrole('Administrador|Digitador')
                        <a href="{{ url($prefix . '/marcas') }}" class="relative flex items-center py-2 text-[10px] {{ Request::is('*/marcas*') ? 'text-white font-bold bg-white/10 rounded-md px-2' : 'text-gray-400' }} hover:text-white transition">
                            <span class="absolute -left-4 w-3 h-px bg-gray-700/50"></span>
                            <i class="fa-solid fa-tags text-[8px] mr-2"></i>
                            Marca
                        </a>
                    @endhasanyrole
                </div>
            </div>
            @endhasanyrole

            {{-- 3. ENTIDADES --}}
            @role('Administrador')
            <div class="space-y-1">
                <button @click="openMenu = (openMenu === 'entidades' ? null : 'entidades')" 
                        class="w-full flex items-center justify-between py-2 px-3 hover:bg-white/5 rounded-md group transition text-left">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-building text-xs group-hover:text-white"></i>
                        <span class="text-[11px] font-bold uppercase tracking-wider group-hover:text-white">Entidades</span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="openMenu === 'entidades' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openMenu === 'entidades'" x-collapse class="ml-4 pl-4 border-l border-gray-700/50 space-y-0.5 mt-1 relative">
                    <a href="{{ route('admin.mup.usuarios.index') }}" class="relative flex items-center py-2 text-[10px] {{ Request::routeIs('admin.mup.*') ? 'text-white font-bold bg-white/10 rounded-md px-2' : 'text-gray-400' }} hover:text-white transition">
                        <span class="absolute -left-4 w-3 h-px bg-gray-700/50"></span>
                        <i class="fa-solid fa-building-user text-[8px] mr-2"></i>
                        UMP
                    </a>
                </div>
            </div>
            @endrole

            {{-- 4. CONFIGURACIÓN GLOBAL --}}
            @role('Administrador')
            <div class="space-y-1">
                <button @click="openMenu = (openMenu === 'config' ? null : 'config')"
                        class="w-full flex items-center justify-between py-2 px-3 hover:bg-white/5 rounded-md group transition text-left">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-gears text-xs group-hover:text-white"></i>
                        <span class="text-[11px] font-bold uppercase tracking-wider group-hover:text-white">Configuración Global</span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="openMenu === 'config' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openMenu === 'config'" x-collapse class="ml-5 pl-4 border-l border-gray-700/50 space-y-1 mt-1 bg-[#0f2a4a]/50 rounded-r-md relative">
                    <a href="#" class="relative flex items-center py-2 text-[10px] text-gray-400 hover:text-white transition">
                        <span class="absolute -left-4 w-3 h-px bg-gray-700/50"></span>
                        <i class="fa-solid fa-list text-[8px] mr-2"></i>
                        Tipo de Parámetro
                    </a>
                    <a href="#" class="relative flex items-center py-2 text-[10px] text-gray-400 hover:text-white transition">
                        <span class="absolute -left-4 w-3 h-px bg-gray-700/50"></span>
                        <i class="fa-solid fa-table text-[8px] mr-2"></i>
                        Parámetro
                    </a>
                    <a href="#" class="relative flex items-center py-2 text-[10px] text-gray-400 hover:text-white transition">
                        <span class="absolute -left-4 w-3 h-px bg-gray-700/50"></span>
                        <i class="fa-solid fa-globe text-[8px] mr-2"></i>
                        Dominio
                    </a>
                    <a href="#" class="relative flex items-center py-2 text-[10px] text-gray-400 hover:text-white transition">
                        <span class="absolute -left-4 w-3 h-px bg-gray-700/50"></span>
                        <i class="fa-solid fa-hashtag text-[8px] mr-2"></i>
                        Valor
                    </a>
                </div>
            </div>
            @endrole

        </div>
    </nav>

    {{-- BOTÓN DE SALIR --}}
    <div class="p-4 border-t border-gray-700/50">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center space-x-3 py-2 px-3 text-gray-400 hover:text-red-400 transition group">
                <i class="fa-solid fa-right-from-bracket text-xs group-hover:animate-pulse"></i>
                <span class="text-[11px] font-bold uppercase tracking-wider">Salir</span>
            </button>
        </form>
    </div>
</aside>