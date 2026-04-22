@php
    $prefix = Auth::check() && Auth::user()->hasRole('Administrador') ? 'admin' : (Auth::check() && Auth::user()->hasRole('Digitador') ? 'digitador' : 'empresa');
    $ultimoDiag = \App\Models\Diag::latest('iddia')->first();
    $currentId = Request::route('diagnostico') ?? ($ultimoDiag ? $ultimoDiag->iddia : null);
    
    // Determinar qué menú debe estar abierto según la ruta actual
    $initialOpenMenu = null;
    if (Request::is('*/diagnosticos*') || Request::routeIs('*.alertas') || Request::routeIs('*.rechazados*')) $initialOpenMenu = 'operacion';
    elseif (Request::is('*/vehiculos*') || Request::is('*/historial*') || Request::is('*/marcas*')) $initialOpenMenu = 'gestion';
    elseif (Request::routeIs('*.mup.*')) $initialOpenMenu = 'entidades';
    elseif (Request::is('*/config*') || Request::is('*/parametros*')) $initialOpenMenu = 'config';
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
     class="fixed inset-0 z-40 bg-black/60 backdrop-blur-md lg:hidden">
</div>

<aside :class="{
        'w-72': !sidebarCollapsed,
        'w-20': sidebarCollapsed,
        'translate-x-0': mobileMenuOpen,
        '-translate-x-full lg:translate-x-0': !mobileMenuOpen
    }"
    class="sidebar-transition fixed inset-y-0 left-0 z-50 bg-[#001834] h-full flex flex-col text-slate-400 shadow-[20px_0_40px_-15px_rgba(0,0,0,0.5)] lg:static lg:inset-0"
    x-data="{ openMenu: '{{ $initialOpenMenu }}' }">
    
    {{-- LOGO SECTION --}}
    <div class="px-6 h-[100px] flex items-center justify-between relative shrink-0 border-b border-white/5">
        <div class="flex items-center gap-3 overflow-hidden" x-show="!sidebarCollapsed || mobileMenuOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-x-4">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-800 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20 shrink-0">
                <iconify-icon icon="lucide:car-front" class="text-white text-xl"></iconify-icon>
            </div>
            <div class="min-w-0">
                <h1 class="text-white font-black text-xs tracking-tighter uppercase leading-none">Rastrillantas</h1>
                <p class="text-[9px] text-blue-400 font-bold tracking-[2px] uppercase mt-1">Diagnostic</p>
            </div>
        </div>

        {{-- Isotipo para modo colapsado --}}
        <div x-show="sidebarCollapsed && !mobileMenuOpen" class="w-full flex justify-center" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-50">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-800 rounded-2xl flex items-center justify-center shadow-xl shadow-blue-500/20">
                <iconify-icon icon="lucide:car-front" class="text-white text-2xl"></iconify-icon>
            </div>
        </div>

        {{-- Toggle Button Desktop --}}
        <button @click="sidebarCollapsed = !sidebarCollapsed" 
                class="hidden lg:flex absolute -right-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-white text-[#001834] rounded-full items-center justify-center shadow-xl hover:scale-110 transition-all z-[60] border-4 border-[#001834]">
            <iconify-icon :icon="sidebarCollapsed ? 'lucide:chevron-right' : 'lucide:chevron-left'" class="text-sm font-bold"></iconify-icon>
        </button>

        {{-- Mobile Close Button --}}
        <button @click="mobileMenuOpen = false" class="lg:hidden p-2 text-white/50 hover:text-white transition">
            <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
        </button>
    </div>

    {{-- NAVIGATION SECTION --}}
    <nav class="flex-1 overflow-y-auto overflow-x-hidden py-8 sidebar-scroll">
        <div class="px-4 space-y-2">

            {{-- 1. OPERACIÓN --}}
            @hasanyrole('Administrador|Digitador')
            <div class="space-y-1">
                <button @click="sidebarCollapsed ? (sidebarCollapsed = false, openMenu = 'operacion') : (openMenu = (openMenu === 'operacion' ? null : 'operacion'))" 
                        class="sidebar-item w-full flex items-center justify-between py-3 px-3 rounded-xl group transition-all duration-200"
                        :class="openMenu === 'operacion' ? 'bg-white/5 text-white' : 'hover:bg-white/[0.03] hover:text-white'">
                    <div class="flex items-center gap-4 min-w-0">
                        <iconify-icon icon="lucide:layout-dashboard" class="text-xl shrink-0 transition-transform group-hover:scale-110" :class="openMenu === 'operacion' ? 'text-blue-400' : 'text-slate-500'"></iconify-icon>
                        <span x-show="!sidebarCollapsed || mobileMenuOpen" class="text-[11px] font-black uppercase tracking-widest truncate">Operación</span>
                        <span x-show="sidebarCollapsed && !mobileMenuOpen" class="collapsed-tooltip">Operación</span>
                    </div>
                    <iconify-icon x-show="!sidebarCollapsed || mobileMenuOpen" icon="lucide:chevron-down" class="text-xs transition-transform" :class="openMenu === 'operacion' ? 'rotate-180' : ''"></iconify-icon>
                </button>
                
                <div x-show="(openMenu === 'operacion') && (!sidebarCollapsed || mobileMenuOpen)" x-collapse class="space-y-1 mt-1">
                    <a href="{{ url($prefix . '/diagnosticos') }}" class="flex items-center gap-3 py-2.5 px-10 text-[10px] font-bold uppercase tracking-widest transition group {{ Request::is('*/diagnosticos') ? 'text-white' : 'text-slate-500 hover:text-white' }}">
                        <div class="w-1.5 h-1.5 rounded-full bg-slate-700 group-hover:bg-blue-400 transition-colors {{ Request::is('*/diagnosticos') ? 'bg-blue-400' : '' }}"></div>
                        Diagnóstico
                    </a>
                    <a href="{{ $currentId ? url($prefix . '/diagnosticos/' . $currentId) : '#' }}" class="flex items-center gap-3 py-2.5 px-10 text-[10px] font-bold uppercase tracking-widest transition group {{ Request::is('*/diagnosticos/*') && !Request::is('*/edit') ? 'text-white' : 'text-slate-500 hover:text-white' }}">
                        <div class="w-1.5 h-1.5 rounded-full bg-slate-700 group-hover:bg-blue-400 transition-colors {{ Request::is('*/diagnosticos/*') && !Request::is('*/edit') ? 'bg-blue-400' : '' }}"></div>
                        Detalle
                    </a>
                    <a href="{{ route($prefix . '.alertas') }}" class="flex items-center gap-3 py-2.5 px-10 text-[10px] font-bold uppercase tracking-widest transition group {{ Request::routeIs('*.alertas') ? 'text-white' : 'text-slate-500 hover:text-white' }}">
                        <div class="w-1.5 h-1.5 rounded-full bg-slate-700 group-hover:bg-blue-400 transition-colors {{ Request::routeIs('*.alertas') ? 'bg-blue-400' : '' }}"></div>
                        Alertas
                    </a>
                    <a href="{{ route($prefix . '.rechazados') }}" class="flex items-center gap-3 py-2.5 px-10 text-[10px] font-bold uppercase tracking-widest transition group {{ Request::routeIs('*.rechazados*') ? 'text-white' : 'text-slate-500 hover:text-white' }}">
                        <div class="w-1.5 h-1.5 rounded-full bg-slate-700 group-hover:bg-blue-400 transition-colors {{ Request::routeIs('*.rechazados*') ? 'bg-blue-400' : '' }}"></div>
                        Rechazados
                    </a>
                </div>
            </div>
            @endhasanyrole

            {{-- 2. GESTIÓN VEHICULAR --}}
            @hasanyrole('Administrador|Digitador|Empresa')
            <div class="space-y-1">
                <button @click="sidebarCollapsed ? (sidebarCollapsed = false, openMenu = 'gestion') : (openMenu = (openMenu === 'gestion' ? null : 'gestion'))" 
                        class="sidebar-item w-full flex items-center justify-between py-3 px-3 rounded-xl group transition-all duration-200"
                        :class="openMenu === 'gestion' ? 'bg-white/5 text-white' : 'hover:bg-white/[0.03] hover:text-white'">
                    <div class="flex items-center gap-4 min-w-0">
                        <iconify-icon icon="lucide:car-front" class="text-xl shrink-0 transition-transform group-hover:scale-110" :class="openMenu === 'gestion' ? 'text-blue-400' : 'text-slate-500'"></iconify-icon>
                        <span x-show="!sidebarCollapsed || mobileMenuOpen" class="text-[11px] font-black uppercase tracking-widest truncate">Vehículos</span>
                        <span x-show="sidebarCollapsed && !mobileMenuOpen" class="collapsed-tooltip">Vehículos</span>
                    </div>
                    <iconify-icon x-show="!sidebarCollapsed || mobileMenuOpen" icon="lucide:chevron-down" class="text-xs transition-transform" :class="openMenu === 'gestion' ? 'rotate-180' : ''"></iconify-icon>
                </button>
                
                <div x-show="(openMenu === 'gestion') && (!sidebarCollapsed || mobileMenuOpen)" x-collapse class="space-y-1 mt-1">
                    <a href="{{ url($prefix . '/vehiculos') }}" class="flex items-center gap-3 py-2.5 px-10 text-[10px] font-bold uppercase tracking-widest transition group {{ Request::is('*/vehiculos') && Request::query('view') != 'perfil' ? 'text-white' : 'text-slate-500 hover:text-white' }}">
                        <div class="w-1.5 h-1.5 rounded-full bg-slate-700 group-hover:bg-blue-400 transition-colors {{ Request::is('*/vehiculos') && Request::query('view') != 'perfil' ? 'bg-blue-400' : '' }}"></div>
                        Listado General
                    </a>
                    <a href="{{ url($prefix . '/vehiculos-empresa') }}" class="flex items-center gap-3 py-2.5 px-10 text-[10px] font-bold uppercase tracking-widest transition group {{ Request::is('*/vehiculos-empresa*') ? 'text-white' : 'text-slate-500 hover:text-white' }}">
                        <div class="w-1.5 h-1.5 rounded-full bg-slate-700 group-hover:bg-blue-400 transition-colors {{ Request::is('*/vehiculos-empresa*') ? 'bg-blue-400' : '' }}"></div>
                        Flotas Empresa
                    </a>
                    <a href="{{ url($prefix . '/historial') }}" class="flex items-center gap-3 py-2.5 px-10 text-[10px] font-bold uppercase tracking-widest transition group {{ Request::is('*/historial*') ? 'text-white' : 'text-slate-500 hover:text-white' }}">
                        <div class="w-1.5 h-1.5 rounded-full bg-slate-700 group-hover:bg-blue-400 transition-colors {{ Request::is('*/historial*') ? 'bg-blue-400' : '' }}"></div>
                        Historial
                    </a>
                </div>
            </div>
            @endhasanyrole

            {{-- 3. ENTIDADES (MUP) --}}
            @hasanyrole('Administrador|Digitador')
            <div class="space-y-1">
                <button @click="sidebarCollapsed ? (sidebarCollapsed = false, openMenu = 'entidades') : (openMenu = (openMenu === 'entidades' ? null : 'entidades'))" 
                        class="sidebar-item w-full flex items-center justify-between py-3 px-3 rounded-xl group transition-all duration-200"
                        :class="openMenu === 'entidades' ? 'bg-white/5 text-white' : 'hover:bg-white/[0.03] hover:text-white'">
                    <div class="flex items-center gap-4 min-w-0">
                        <iconify-icon icon="lucide:users-2" class="text-xl shrink-0 transition-transform group-hover:scale-110" :class="openMenu === 'entidades' ? 'text-blue-400' : 'text-slate-500'"></iconify-icon>
                        <span x-show="!sidebarCollapsed || mobileMenuOpen" class="text-[11px] font-black uppercase tracking-widest truncate">Administración</span>
                        <span x-show="sidebarCollapsed && !mobileMenuOpen" class="collapsed-tooltip">Administración MUP</span>
                    </div>
                    <iconify-icon x-show="!sidebarCollapsed || mobileMenuOpen" icon="lucide:chevron-down" class="text-xs transition-transform" :class="openMenu === 'entidades' ? 'rotate-180' : ''"></iconify-icon>
                </button>
                
                <div x-show="(openMenu === 'entidades') && (!sidebarCollapsed || mobileMenuOpen)" x-collapse class="space-y-1 mt-1">
                    @php
                        $mupRoute = auth()->user()->hasRole('Administrador') ? route('admin.mup.usuarios.index') : route('digitador.mup.usuarios.index');
                    @endphp
                    <a href="{{ $mupRoute }}" class="flex items-center gap-3 py-2.5 px-10 text-[10px] font-bold uppercase tracking-widest transition group {{ Request::routeIs('*.mup.*') ? 'text-white' : 'text-slate-500 hover:text-white' }}">
                        <div class="w-1.5 h-1.5 rounded-full bg-slate-700 group-hover:bg-blue-400 transition-colors {{ Request::routeIs('*.mup.*') ? 'bg-blue-400' : '' }}"></div>
                        Módulo MUP
                    </a>
                </div>
            </div>
            @endhasanyrole

            {{-- 4. CONFIGURACIÓN --}}
            @role('Administrador')
            <div class="space-y-1 pt-6 mt-6 border-t border-white/5">
                <button @click="sidebarCollapsed ? (sidebarCollapsed = false, openMenu = 'config') : (openMenu = (openMenu === 'config' ? null : 'config'))"
                        class="sidebar-item w-full flex items-center justify-between py-3 px-3 rounded-xl group transition-all duration-200"
                        :class="openMenu === 'config' ? 'bg-white/5 text-white' : 'hover:bg-white/[0.03] hover:text-white'">
                    <div class="flex items-center gap-4 min-w-0">
                        <iconify-icon icon="lucide:settings-2" class="text-xl shrink-0 transition-transform group-hover:scale-110" :class="openMenu === 'config' ? 'text-blue-400' : 'text-slate-500'"></iconify-icon>
                        <span x-show="!sidebarCollapsed || mobileMenuOpen" class="text-[11px] font-black uppercase tracking-widest truncate">Ajustes</span>
                        <span x-show="sidebarCollapsed && !mobileMenuOpen" class="collapsed-tooltip">Configuración Sistema</span>
                    </div>
                    <iconify-icon x-show="!sidebarCollapsed || mobileMenuOpen" icon="lucide:chevron-down" class="text-xs transition-transform" :class="openMenu === 'config' ? 'rotate-180' : ''"></iconify-icon>
                </button>
                
                <div x-show="(openMenu === 'config') && (!sidebarCollapsed || mobileMenuOpen)" x-collapse class="space-y-1 mt-1">
                    <a href="#" class="flex items-center gap-3 py-2.5 px-10 text-[10px] font-bold uppercase tracking-widest text-slate-500 hover:text-white transition group">
                        <div class="w-1.5 h-1.5 rounded-full bg-slate-700 group-hover:bg-blue-400 transition-colors"></div>
                        Tipo Parámetro
                    </a>
                    <a href="#" class="flex items-center gap-3 py-2.5 px-10 text-[10px] font-bold uppercase tracking-widest text-slate-500 hover:text-white transition group">
                        <div class="w-1.5 h-1.5 rounded-full bg-slate-700 group-hover:bg-blue-400 transition-colors"></div>
                        Parámetro
                    </a>
                    <a href="#" class="flex items-center gap-3 py-2.5 px-10 text-[10px] font-bold uppercase tracking-widest text-slate-500 hover:text-white transition group">
                        <div class="w-1.5 h-1.5 rounded-full bg-slate-700 group-hover:bg-blue-400 transition-colors"></div>
                        Dominio
                    </a>
                    <a href="#" class="flex items-center gap-3 py-2.5 px-10 text-[10px] font-bold uppercase tracking-widest text-slate-500 hover:text-white transition group">
                        <div class="w-1.5 h-1.5 rounded-full bg-slate-700 group-hover:bg-blue-400 transition-colors"></div>
                        Valor
                    </a>
                </div>
            </div>
            @endrole

        </div>
    </nav>

    {{-- USER SECTION / LOGOUT --}}
    <div class="p-4 border-t border-white/5 bg-black/10">
        <div class="flex items-center gap-3 px-2 mb-4 overflow-hidden" x-show="!sidebarCollapsed || mobileMenuOpen">
            <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-white font-black text-[10px]">
                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-black text-white truncate uppercase">{{ Auth::user()->name }}</p>
                <p class="text-[8px] text-slate-500 font-bold uppercase tracking-tighter">{{ Auth::user()->roles->first()->name ?? 'Usuario' }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-item w-full flex items-center py-3 px-3 text-slate-500 hover:text-red-400 transition-all duration-200 group rounded-xl hover:bg-red-500/10">
                <div class="flex items-center gap-4 min-w-0">
                    <iconify-icon icon="lucide:log-out" class="text-xl shrink-0 group-hover:translate-x-1 transition-transform"></iconify-icon>
                    <span x-show="!sidebarCollapsed || mobileMenuOpen" class="text-[11px] font-black uppercase tracking-widest truncate">Cerrar Sesión</span>
                    <span x-show="sidebarCollapsed && !mobileMenuOpen" class="collapsed-tooltip text-red-400">Salir del Sistema</span>
                </div>
            </button>
        </form>
    </div>
</aside>