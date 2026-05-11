@php
    $active = $mupActive ?? '';
    $prefix = auth()->user()->hasRole('Administrador') ? 'admin' : 'digitador';
@endphp
<div class="mup-tabs-scroll" role="navigation" aria-label="Secciones del módulo MUP">
    <div class="mup-tabs">
        <a href="{{ route($prefix . '.mup.conductores.index') }}" class="mup-tab {{ $active === 'conductores' ? 'active' : '' }}">Conductor</a>
        <a href="{{ route($prefix . '.mup.propietarios.index') }}" class="mup-tab {{ $active === 'propietarios' ? 'active' : '' }}">Propietario</a>
        <a href="{{ route($prefix . '.mup.empresas.index') }}" class="mup-tab {{ $active === 'empresas' ? 'active' : '' }}">Empresas</a>
        <a href="{{ route($prefix . '.mup.usuarios.index') }}" class="mup-tab {{ $active === 'usuarios' ? 'active' : '' }}">Usuario</a>
    </div>
</div>
