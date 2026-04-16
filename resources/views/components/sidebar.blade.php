<aside class="w-64 bg-[#0a1d37] h-full flex flex-col text-gray-300 shadow-xl" x-data="{ openMenu: 'operacion' }">
    <div class="p-6 border-b border-gray-700/50 flex items-center space-x-3">
        <div class="bg-orange-500 w-9 h-9 rounded-lg flex items-center justify-center shadow-lg shadow-orange-900/20">
            <i class="fa-solid fa-car-side text-white text-sm"></i>
        </div>
        <div>
            <h1 class="text-white font-bold text-[10px] leading-tight">CDA RASTRILLANTAS LTDA.</h1>
            <p class="text-[9px] text-gray-400 font-medium">CENTRO DE DIAGNÓSTICO</p>
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
                        class="w-full flex items-center justify-between py-2 px-3 hover:bg-white/5 rounded-md group transition text-left">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-table-cells-large text-xs group-hover:text-white"></i>
                        <span class="text-[11px] font-bold uppercase tracking-wider group-hover:text-white">Operación</span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="openMenu === 'operacion' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openMenu === 'operacion'" x-collapse class="pl-9 space-y-1 mt-1 bg-[#0f2a4a]/50 rounded-md">
                    <a href="{{ url($prefix . '/diagnosticos') }}" class="block text-[10px] py-2 text-gray-400 hover:text-white transition">Diagnóstico</a>
                    <a href="{{ $currentId ? url($prefix . '/diagnosticos/' . $currentId) : '#' }}" class="block text-[10px] py-2 {{ Request::is('*/diagnosticos/*') && !Request::is('*/edit') ? 'text-white font-bold' : 'text-gray-400' }} hover:text-white transition">Detalle Diagnóstico</a>
                    <a href="{{ route($prefix . '.alertas') }}" class="block text-[10px] py-2 {{ Request::routeIs('*.alertas') ? 'text-white font-bold' : 'text-gray-400' }} hover:text-white transition">Alertas</a>
                    <a href="#" class="block text-[10px] py-2 text-gray-400 hover:text-white transition">Rechazados</a>
                </div>
            </div>
            @endhasanyrole

            {{-- 2. GESTIÓN VEHICULAR --}}
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
                
                <div x-show="openMenu === 'gestion'" x-collapse class="pl-9 space-y-1 mt-1 bg-[#0f2a4a]/50 rounded-md">
                    <a href="#" class="block text-[10px] py-2 text-gray-400 hover:text-white transition">Vehículos</a>
                    <a href="#" class="block text-[10px] py-2 text-gray-400 hover:text-white transition">Empresas</a>
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