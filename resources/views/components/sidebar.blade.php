@php
    $prefix = Auth::check() && Auth::user()->hasRole('Administrador') ? 'admin' : (Auth::check() && Auth::user()->hasRole('Digitador') ? 'digitador' : 'empresa');
    $ultimoDiag = \App\Models\Diag::latest('iddia')->first();
    $currentId = Request::route('diagnostico') ?? ($ultimoDiag ? $ultimoDiag->iddia : null);
@endphp

{{-- Overlay para móvil --}}
<div x-show="mobileMenuOpen" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="mobileMenuOpen = false"
     class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm lg:hidden">
</div>

<aside :class="{
        'w-64': !sidebarCollapsed,
        'w-20': sidebarCollapsed,
        'translate-x-0': mobileMenuOpen,
        '-translate-x-full lg:translate-x-0': !mobileMenuOpen
    }"
    class="fixed inset-y-0 left-0 z-50 bg-[#0a1d37] h-full flex flex-col text-gray-300 shadow-2xl transition-all duration-300 ease-in-out lg:static lg:inset-0"
    x-data="{ openMenu: null }">
    
    {{-- HEADER --}}
    <div class="p-4 sm:p-6 border-b border-gray-700/50 flex items-center justify-between relative h-[88px] shrink-0">
        <div class="flex items-center space-x-3 min-w-0" x-show="!sidebarCollapsed || mobileMenuOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
            <div class="bg-orange-500 w-9 h-9 rounded-lg flex items-center justify-center shadow-lg shadow-orange-900/20 shrink-0">
                <i class="fa-solid fa-car-side text-white text-sm"></i>
            </div>
            <div class="truncate">
                <h1 class="text-white font-bold text-[10px] leading-tight truncate">CDA RASTRILLANTAS LTDA.</h1>
                <p class="text-[9px] text-gray-400 font-medium truncate">CENTRO DE DIAGNÓSTICO</p>
            </div>
        </div>

        {{-- Isotipo para modo colapsado --}}
        <div x-show="sidebarCollapsed && !mobileMenuOpen" class="w-full flex justify-center" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-50" x-transition:enter-end="opacity-100 scale-100">
            <div class="bg-orange-500 w-10 h-10 rounded-xl flex items-center justify-center shadow-lg shadow-orange-900/20">
                <i class="fa-solid fa-car-side text-white text-base"></i>
            </div>
        </div>

        {{-- Botón Toggle Desktop (Flotante) --}}
        <button @click="sidebarCollapsed = !sidebarCollapsed" 
                class="hidden lg:flex absolute -right-3 top-1/2 -translate-y-1/2 w-6 h-6 bg-[#0a1d37] border border-gray-700/50 rounded-full items-center justify-center text-gray-400 hover:text-white shadow-md hover:shadow-orange-500/10 transition-all duration-300 z-[60]"
                title="Alternar menú">
            <i class="fa-solid text-[9px]" :class="sidebarCollapsed ? 'fa-chevron-right' : 'fa-chevron-left'"></i>
        </button>

        {{-- Botón Cerrar Móvil --}}
        <button @click="mobileMenuOpen = false" class="lg:hidden p-2 hover:bg-white/10 rounded-md">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>

    {{-- NAVEGACIÓN --}}
    <nav class="flex-1 overflow-y-auto py-4 custom-scrollbar">
        <div class="px-3 sm:px-4 space-y-1">

            {{-- 1. OPERACIÓN --}}
            @hasanyrole('Administrador|Digitador')
            <div class="space-y-1">
                <button @click="sidebarCollapsed ? (sidebarCollapsed = false, openMenu = 'operacion') : (openMenu = (openMenu === 'operacion' ? null : 'operacion'))" 
                        class="w-full flex items-center justify-between py-2.5 px-3 hover:bg-white/5 rounded-md group transition-all duration-200 text-left"
                        :class="openMenu === 'operacion' ? 'bg-white/5 text-white' : ''"
                        title="Operación">
                    <div class="flex items-center space-x-3 min-w-0">
                        <i class="fa-solid fa-table-cells-large text-sm group-hover:text-white shrink-0" :class="openMenu === 'operacion' ? 'text-white' : ''"></i>
                        <span x-show="!sidebarCollapsed || mobileMenuOpen" class="text-[11px] font-bold uppercase tracking-wider group-hover:text-white truncate" x-transition>Operación</span>
                    </div>
                    <i x-show="!sidebarCollapsed || mobileMenuOpen" class="fa-solid fa-chevron-down text-[10px] transition-transform shrink-0" :class="openMenu === 'operacion' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="(openMenu === 'operacion') && (!sidebarCollapsed || mobileMenuOpen)" x-collapse class="ml-5 pl-4 border-l border-gray-700/50 space-y-1 mt-1 bg-[#0f2a4a]/50 rounded-r-md relative py-1">
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
                   class="w-full flex items-center justify-between py-2.5 px-3 hover:bg-white/5 rounded-md group transition text-left {{ Request::query('view') == 'perfil' ? 'bg-white/10 text-white' : 'text-gray-400' }}"
                   title="Mi Perfil">
                    <div class="flex items-center space-x-3 min-w-0">
                        <i class="fa-solid fa-building-user text-sm group-hover:text-white {{ Request::query('view') == 'perfil' ? 'text-white' : '' }} shrink-0"></i>
                        <span x-show="!sidebarCollapsed || mobileMenuOpen" class="text-[11px] font-bold uppercase tracking-wider group-hover:text-white {{ Request::query('view') == 'perfil' ? 'text-white' : '' }} truncate">Mi Perfil</span>
                    </div>
                </a>
            </div>
            @endif

            @hasanyrole('Administrador|Digitador|Empresa')
            <div class="space-y-1">
                <button @click="sidebarCollapsed ? (sidebarCollapsed = false, openMenu = 'gestion') : (openMenu = (openMenu === 'gestion' ? null : 'gestion'))" 
                        class="w-full flex items-center justify-between py-2.5 px-3 hover:bg-white/5 rounded-md group transition-all duration-200 text-left"
                        :class="openMenu === 'gestion' ? 'bg-white/5 text-white' : ''"
                        title="Gestión Vehicular">
                    <div class="flex items-center space-x-3 min-w-0">
                        <i class="fa-solid fa-car text-sm group-hover:text-white shrink-0" :class="openMenu === 'gestion' ? 'text-white' : ''"></i>
                        <span x-show="!sidebarCollapsed || mobileMenuOpen" class="text-[11px] font-bold uppercase tracking-wider group-hover:text-white truncate">Gestión Vehicular</span>
                    </div>
                    <i x-show="!sidebarCollapsed || mobileMenuOpen" class="fa-solid fa-chevron-down text-[10px] transition-transform shrink-0" :class="openMenu === 'gestion' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="(openMenu === 'gestion') && (!sidebarCollapsed || mobileMenuOpen)" x-collapse class="ml-5 pl-4 border-l border-gray-700/50 space-y-1 mt-1 bg-[#0f2a4a]/50 rounded-r-md relative py-1">
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
            @hasanyrole('Administrador|Digitador')
            <div class="space-y-1">
                <button @click="sidebarCollapsed ? (sidebarCollapsed = false, openMenu = 'entidades') : (openMenu = (openMenu === 'entidades' ? null : 'entidades'))" 
                        class="w-full flex items-center justify-between py-2.5 px-3 hover:bg-white/5 rounded-md group transition-all duration-200 text-left"
                        :class="openMenu === 'entidades' ? 'bg-white/5 text-white' : ''"
                        title="Entidades">
                    <div class="flex items-center space-x-3 min-w-0">
                        <i class="fa-solid fa-building text-sm group-hover:text-white shrink-0" :class="openMenu === 'entidades' ? 'text-white' : ''"></i>
                        <span x-show="!sidebarCollapsed || mobileMenuOpen" class="text-[11px] font-bold uppercase tracking-wider group-hover:text-white truncate">Entidades</span>
                    </div>
                    <i x-show="!sidebarCollapsed || mobileMenuOpen" class="fa-solid fa-chevron-down text-[10px] transition-transform shrink-0" :class="openMenu === 'entidades' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="(openMenu === 'entidades') && (!sidebarCollapsed || mobileMenuOpen)" x-collapse class="ml-4 pl-4 border-l border-gray-700/50 space-y-0.5 mt-1 relative py-1">
                    @php
                        $mupRoute = auth()->user()->hasRole('Administrador')
                            ? route('admin.mup.usuarios.index')
                            : route('digitador.mup.usuarios.index');
                        $mupActive = Request::routeIs('admin.mup.*') || Request::routeIs('digitador.mup.*');
                    @endphp
                    <a href="{{ $mupRoute }}" class="relative flex items-center py-2 text-[10px] {{ $mupActive ? 'text-white font-bold bg-white/10 rounded-md px-2' : 'text-gray-400' }} hover:text-white transition">
                        <span class="absolute -left-4 w-3 h-px bg-gray-700/50"></span>
                        <i class="fa-solid fa-building-user text-[8px] mr-2"></i>
                        MUP
                    </a>
                </div>
            </div>
            @endhasanyrole

            {{-- 4. CONFIGURACIÓN GLOBAL --}}
            @role('Administrador')
            <div class="space-y-1">
                <button @click="sidebarCollapsed ? (sidebarCollapsed = false, openMenu = 'config') : (openMenu = (openMenu === 'config' ? null : 'config'))"
                        class="w-full flex items-center justify-between py-2.5 px-3 hover:bg-white/5 rounded-md group transition-all duration-200 text-left"
                        :class="openMenu === 'config' ? 'bg-white/5 text-white' : ''"
                        title="Configuración Global">
                    <div class="flex items-center space-x-3 min-w-0">
                        <i class="fa-solid fa-gears text-sm group-hover:text-white shrink-0" :class="openMenu === 'config' ? 'text-white' : ''"></i>
                        <span x-show="!sidebarCollapsed || mobileMenuOpen" class="text-[11px] font-bold uppercase tracking-wider group-hover:text-white truncate">Configuración</span>
                    </div>
                    <i x-show="!sidebarCollapsed || mobileMenuOpen" class="fa-solid fa-chevron-down text-[10px] transition-transform shrink-0" :class="openMenu === 'config' ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="(openMenu === 'config') && (!sidebarCollapsed || mobileMenuOpen)" x-collapse class="ml-5 pl-4 border-l border-gray-700/50 space-y-1 mt-1 bg-[#0f2a4a]/50 rounded-r-md relative py-1">
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
            <button type="submit" class="w-full flex items-center py-2.5 px-3 text-gray-400 hover:text-red-400 transition-all duration-200 group rounded-md hover:bg-red-500/5" title="Cerrar Sesión">
                <div class="flex items-center space-x-3 min-w-0">
                    <i class="fa-solid fa-right-from-bracket text-sm group-hover:animate-pulse shrink-0"></i>
                    <span x-show="!sidebarCollapsed || mobileMenuOpen" class="text-[11px] font-bold uppercase tracking-wider truncate">Salir</span>
                </div>
            </button>
        </form>
    </div>
</aside>