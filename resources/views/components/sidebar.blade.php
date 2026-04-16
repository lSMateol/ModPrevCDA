<aside class="w-64 bg-[#0a1d37] h-full flex flex-col text-gray-300 shadow-xl" x-data="{ openMenu: 'operacion' }">
    <div class="p-6 border-b border-gray-700/50 flex items-center space-x-3">
        <div class="bg-orange-500 w-9 h-9 rounded-lg flex items-center justify-center shadow-lg shadow-orange-900/20">
            <i class="fa-solid fa-square-full text-white text-[10px]"></i>
        </div>
        <div>
            <h1 class="text-white font-bold text-[10px] leading-tight">CDA RASTRILLANTAS LTDA.</h1>
            <p class="text-[9px] text-gray-400 font-medium">CENTRO DE DIAGNÓSTICO AUTOMOTOR</p>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto py-4 custom-scrollbar">
        <div class="px-4 space-y-1">

            @php
                $prefix = Auth::user()->hasRole('Administrador') ? 'admin' : 'digitador';
                $ultimoDiag = \App\Models\Diag::latest('iddia')->first();
                $currentId = Request::route('diagnostico') ?? ($ultimoDiag ? $ultimoDiag->iddia : null);
            @endphp

            {{-- 1. OPERACIÓN --}}
            @hasanyrole('Administrador|Digitador')
            <div class="space-y-1">
                <button @click="openMenu = (openMenu === 'operacion' ? null : 'operacion')" 
                        class="w-full flex items-center justify-between py-2.5 px-3 hover:bg-white/5 rounded-md group transition text-left">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-table-cells text-[11px] group-hover:text-white"></i>
                        <span class="text-[10px] font-bold uppercase tracking-wider group-hover:text-white">Operación</span>
                    </div>
                    <span class="text-[9px] font-bold" x-text="openMenu === 'operacion' ? 'v' : '>'"></span>
                </button>
                <div x-show="openMenu === 'operacion'" x-collapse class="ml-4 pl-4 border-l border-gray-700/50 space-y-0.5 mt-1 relative">
                    <div class="absolute left-0 top-0 bottom-0 w-px bg-gray-700/50"></div>
                    
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
                    <a href="#" class="relative flex items-center py-2 text-[10px] text-gray-400 hover:text-white transition">
                        <span class="absolute -left-4 w-3 h-px bg-gray-700/50"></span>
                        <i class="fa-solid fa-table-cells text-[8px] mr-2"></i>
                        Rechazados
                    </a>
                </div>
            </div>
            @endhasanyrole

            {{-- 2. GESTIÓN VEHICULAR --}}
            @hasanyrole('Administrador|Digitador|Empresa')
            <div class="space-y-1">
                <button @click="openMenu = (openMenu === 'gestion' ? null : 'gestion')" 
                        class="w-full flex items-center justify-between py-2.5 px-3 hover:bg-white/5 rounded-md group transition text-left">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-car text-[11px] group-hover:text-white"></i>
                        <span class="text-[10px] font-bold uppercase tracking-wider group-hover:text-white">Gestión Vehicular</span>
                    </div>
                    <span class="text-[9px] font-bold" x-text="openMenu === 'gestion' ? 'v' : '>'"></span>
                </button>
                <div x-show="openMenu === 'gestion'" x-collapse class="ml-4 pl-4 border-l border-gray-700/50 space-y-0.5 mt-1 relative">
                    <a href="#" class="relative flex items-center py-2 text-[10px] text-gray-400 hover:text-white transition">
                        <span class="absolute -left-4 w-3 h-px bg-gray-700/50"></span>
                        <i class="fa-solid fa-table-cells text-[8px] mr-2"></i>
                        Vehículos
                    </a>
                    <a href="#" class="relative flex items-center py-2 text-[10px] text-gray-400 hover:text-white transition">
                        <span class="absolute -left-4 w-3 h-px bg-gray-700/50"></span>
                        <i class="fa-solid fa-table-cells text-[8px] mr-2"></i>
                        Vehículos Empresa
                    </a>
                    <a href="#" class="relative flex items-center py-2 text-[10px] text-gray-400 hover:text-white transition">
                        <span class="absolute -left-4 w-3 h-px bg-gray-700/50"></span>
                        <i class="fa-solid fa-table-cells text-[8px] mr-2"></i>
                        Historial Mantenimiento
                    </a>
                    @hasanyrole('Administrador|Digitador')
                    <a href="#" class="relative flex items-center py-2 text-[10px] text-gray-400 hover:text-white transition">
                        <span class="absolute -left-4 w-3 h-px bg-gray-700/50"></span>
                        <i class="fa-solid fa-table-cells text-[8px] mr-2"></i>
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
                        class="w-full flex items-center justify-between py-2.5 px-3 hover:bg-white/5 rounded-md group transition text-left">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-table-cells text-[11px] group-hover:text-white"></i>
                        <span class="text-[10px] font-bold uppercase tracking-wider group-hover:text-white">Entidades</span>
                    </div>
                    <span class="text-[9px] font-bold" x-text="openMenu === 'entidades' ? 'v' : '>'"></span>
                </button>
                <div x-show="openMenu === 'entidades'" x-collapse class="ml-4 pl-4 border-l border-gray-700/50 space-y-0.5 mt-1 relative">
                    <a href="{{ route('admin.mup.usuarios') }}" class="relative flex items-center py-2 text-[10px] {{ Request::routeIs('admin.mup.*') ? 'text-white font-bold bg-white/10 rounded-md px-2' : 'text-gray-400' }} hover:text-white transition">
                        <span class="absolute -left-4 w-3 h-px bg-gray-700/50"></span>
                        <i class="fa-solid fa-table-cells text-[8px] mr-2"></i>
                        MUP
                    </a>
                </div>
            </div>
            @endrole

            {{-- 4. CONFIGURACIÓN GLOBAL --}}
            @role('Administrador')
            <div class="space-y-1">
                <button @click="openMenu = (openMenu === 'config' ? null : 'config')" 
                        class="w-full flex items-center justify-between py-2.5 px-3 hover:bg-white/5 rounded-md group transition text-left">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-gear text-[11px] group-hover:text-white"></i>
                        <span class="text-[10px] font-bold uppercase tracking-wider group-hover:text-white">Configuración Global</span>
                    </div>
                    <span class="text-[9px] font-bold" x-text="openMenu === 'config' ? 'v' : '>'"></span>
                </button>
                <div x-show="openMenu === 'config'" x-collapse class="ml-4 pl-4 border-l border-gray-700/50 space-y-0.5 mt-1 relative">
                    <a href="#" class="relative flex items-center py-2 text-[10px] text-gray-400 hover:text-white transition">
                        <span class="absolute -left-4 w-3 h-px bg-gray-700/50"></span>
                        <i class="fa-solid fa-table-cells text-[8px] mr-2"></i>
                        Tipo de Parametro
                    </a>
                    <a href="#" class="relative flex items-center py-2 text-[10px] text-gray-400 hover:text-white transition">
                        <span class="absolute -left-4 w-3 h-px bg-gray-700/50"></span>
                        <i class="fa-solid fa-table-cells text-[8px] mr-2"></i>
                        Parametro
                    </a>
                    <a href="#" class="relative flex items-center py-2 text-[10px] text-gray-400 hover:text-white transition">
                        <span class="absolute -left-4 w-3 h-px bg-gray-700/50"></span>
                        <i class="fa-solid fa-table-cells text-[8px] mr-2"></i>
                        Dominio
                    </a>
                    <a href="#" class="relative flex items-center py-2 text-[10px] text-gray-400 hover:text-white transition">
                        <span class="absolute -left-4 w-3 h-px bg-gray-700/50"></span>
                        <i class="fa-solid fa-table-cells text-[8px] mr-2"></i>
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
            <button type="submit" class="w-full flex items-center space-x-3 py-2 px-3 text-gray-400 hover:text-red-400 transition group text-left">
                <i class="fa-solid fa-arrow-right-from-bracket text-[10px] group-hover:animate-pulse"></i>
                <span class="text-[10px] font-bold uppercase tracking-wider">Salir</span>
            </button>
        </form>
    </div>
</aside>