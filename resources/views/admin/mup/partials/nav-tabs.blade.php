@php
    $active = $mupActive ?? '';
@endphp
<div class="mup-tabs-scroll" role="navigation" aria-label="Secciones del módulo MUP">
    <div class="mup-tabs">
        <a href="{{ route('admin.mup.conductores.index') }}" class="mup-tab {{ $active === 'conductores' ? 'active' : '' }}">Conductor</a>
        <a href="{{ route('admin.mup.propietarios.index') }}" class="mup-tab {{ $active === 'propietarios' ? 'active' : '' }}">Propietario</a>
        <a href="{{ route('admin.mup.empresas.index') }}" class="mup-tab {{ $active === 'empresas' ? 'active' : '' }}">Empresas</a>
        <a href="{{ route('admin.mup.usuarios.index') }}" class="mup-tab {{ $active === 'usuarios' ? 'active' : '' }}">Usuario</a>
    </div>
</div>
