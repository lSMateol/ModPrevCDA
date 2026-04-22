@php
    $mupBase = auth()->user()->hasRole('Administrador') ? 'admin' : 'digitador';
    $currentRoute = Route::currentRouteName();
@endphp

<div class="mb-8 overflow-x-auto no-scrollbar">
    <div class="inline-flex p-1.5 bg-gray-200/50 backdrop-blur-md rounded-2xl border border-gray-200/50 shadow-inner">
        <!-- Conductores -->
        <a href="{{ route($mupBase . '.mup.conductores.index') }}" 
           class="flex items-center gap-2.5 px-6 py-2.5 rounded-xl transition-all duration-300 {{ str_contains($currentRoute, 'conductores') ? 'bg-white shadow-sm text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-700 hover:bg-white/40' }}">
            <iconify-icon icon="lucide:user-round-cog" class="text-lg"></iconify-icon>
            <span class="text-xs uppercase tracking-widest">Conductores</span>
        </a>

        <!-- Propietarios -->
        <a href="{{ route($mupBase . '.mup.propietarios.index') }}" 
           class="flex items-center gap-2.5 px-6 py-2.5 rounded-xl transition-all duration-300 {{ str_contains($currentRoute, 'propietarios') ? 'bg-white shadow-sm text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-700 hover:bg-white/40' }}">
            <iconify-icon icon="lucide:briefcase" class="text-lg"></iconify-icon>
            <span class="text-xs uppercase tracking-widest">Propietarios</span>
        </a>

        <!-- Empresas -->
        <a href="{{ route($mupBase . '.mup.empresas.index') }}" 
           class="flex items-center gap-2.5 px-6 py-2.5 rounded-xl transition-all duration-300 {{ str_contains($currentRoute, 'empresas') ? 'bg-white shadow-sm text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-700 hover:bg-white/40' }}">
            <iconify-icon icon="lucide:building-2" class="text-lg"></iconify-icon>
            <span class="text-xs uppercase tracking-widest">Empresas</span>
        </a>

        <!-- Usuarios (Seguridad) -->
        <a href="{{ route($mupBase . '.mup.usuarios.index') }}" 
           class="flex items-center gap-2.5 px-6 py-2.5 rounded-xl transition-all duration-300 {{ str_contains($currentRoute, 'usuarios') ? 'bg-white shadow-sm text-blue-600 font-bold' : 'text-gray-500 hover:text-gray-700 hover:bg-white/40' }}">
            <iconify-icon icon="lucide:shield-check" class="text-lg"></iconify-icon>
            <span class="text-xs uppercase tracking-widest">Usuarios</span>
        </a>
    </div>
</div>

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
