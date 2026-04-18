@extends('layouts.app')

@section('content')
    <div id="historial-wrapper">
        <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>

        <style>
            #historial-wrapper {
                --background: #f4f6f8;
                --foreground: #111827;
                --primary: #0b1f38;
                --border: #e5e7eb;
                --card-bg: #ffffff;
                --success-bg: #d1fae5;
                --success-text: #065f46;
                --warning-bg: #fef3c7;
                --warning-text: #92400e;
                --danger-bg: #fee2e2;
                --danger-text: #991b1b;
                --muted-bg: #f3f4f6;
                --muted-text: #4b5563;
                font-family: 'Inter', system-ui, sans-serif;
                background-color: var(--background);
                color: var(--foreground);
                min-height: calc(100vh - 65px);
            }

            #historial-wrapper .main-content { padding: 32px 40px; display: flex; flex-direction: column; gap: 24px; }
            #historial-wrapper .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 16px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; transition: 0.2s; border: 1px solid transparent; height: 40px; }
            #historial-wrapper .btn-primary { background-color: var(--primary); color: #ffffff; }
            #historial-wrapper .btn-outline { background-color: transparent; border-color: #d1d5db; color: #374151; }
            #historial-wrapper .card { background: var(--card-bg); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid var(--border); overflow: hidden; }
            #historial-wrapper .p-6 { padding: 24px; }
            #historial-wrapper .page-header { display: flex; justify-content: space-between; align-items: flex-start; }
            #historial-wrapper .page-title { font-size: 24px; font-weight: 700; margin: 0 0 6px 0; }
            #historial-wrapper .page-subtitle { font-size: 14px; color: #6b7280; margin: 0; }
            #historial-wrapper .header-actions { display: flex; gap: 12px; }
            #historial-wrapper .filters-grid { display: grid; grid-template-columns: 2fr 1.5fr 1.5fr auto; gap: 16px; align-items: flex-end; }
            #historial-wrapper .input-group { display: flex; flex-direction: column; gap: 8px; }
            #historial-wrapper .input-label { font-size: 13px; font-weight: 500; color: #374151; }
            #historial-wrapper .input-control { height: 40px; width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0 12px; font-size: 14px; background-color: #ffffff; outline: none; }
            #historial-wrapper .search-box { position: relative; width: 100%; }
            #historial-wrapper .search-box .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6b7280; font-size: 16px; }
            #historial-wrapper .search-box input { padding-left: 36px; }
            #historial-wrapper .table-container { overflow-x: auto; }
            #historial-wrapper .data-table { width: 100%; border-collapse: collapse; text-align: left; }
            #historial-wrapper .data-table th { padding: 14px 24px; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; border-bottom: 1px solid var(--border); background-color: #f9fafb; }
            #historial-wrapper .data-table td { padding: 16px 24px; font-size: 14px; color: #374151; border-bottom: 1px solid var(--border); vertical-align: middle; }
            #historial-wrapper .data-table tr:last-child td { border-bottom: none; }
            #historial-wrapper .two-line-cell { display: flex; flex-direction: column; gap: 4px; }
            #historial-wrapper .two-line-cell .main-text { font-weight: 600; color: #111827; font-size: 14px; }
            #historial-wrapper .two-line-cell .sub-text { font-size: 12px; color: #6b7280; }
            #historial-wrapper .status-badge { display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; border: 1px solid transparent; text-transform: uppercase; }
            #historial-wrapper .status-badge.success { background-color: #ecfdf5; color: #10b981; border-color: #d1fae5; }
            #historial-wrapper .status-badge.danger { background-color: #fef2f2; color: #ef4444; border-color: #fee2e2; }
            #historial-wrapper .status-badge.warning { background-color: #fffbeb; color: #f59e0b; border-color: #fef3c7; }
            #historial-wrapper .actions-cell { display: flex; justify-content: flex-end; gap: 4px; }
            #historial-wrapper .icon-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; border: none; background: transparent; color: #6b7280; cursor: pointer; }
            #historial-wrapper .pagination { padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); font-size: 13px; color: #6b7280; }
        </style>

        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Historial Mantenimiento</h1>
                    <p class="page-subtitle">Registro y seguimiento de mantenimientos preventivos realizados dentro de las instalaciones</p>
                </div>
                <div class="header-actions">
                    <a href="#" class="btn btn-primary">
                        <iconify-icon icon="lucide:plus"></iconify-icon> Nuevo Agendamiento
                    </a>
                </div>
            </div>

            <!-- FILTERS -->
            <form method="GET" class="card p-6">
                <div class="filters-grid">
                    <div class="input-group">
                        <label class="input-label">Buscar Vehículo</label>
                        <div class="search-box">
                            <iconify-icon icon="lucide:search" class="search-icon"></iconify-icon>
                            <input type="text" name="search" class="input-control" placeholder="Placa o modelo..." value="{{ request('search') }}" />
                        </div>
                    </div>
                    <div class="input-group">
                        <label class="input-label">Estado</label>
                        <select name="estado" class="input-control" style="appearance: none; background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2216%22 height=%2216%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%236B7280%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22%3E%3Cpath d=%22m6 9 6 6 6-6%22/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 12px center;">
                            <option value="">Todos los estados</option>
                            <option value="aprobado" {{ request('estado') === 'aprobado' ? 'selected' : '' }}>Aprobado</option>
                            <option value="no_aprobado" {{ request('estado') === 'no_aprobado' ? 'selected' : '' }}>No Aprobado</option>
                            <option value="reasignado" {{ request('estado') === 'reasignado' ? 'selected' : '' }}>Reasignado</option>
                            <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label class="input-label">Fecha</label>
                        <input type="date" name="fecha" class="input-control" value="{{ request('fecha') }}" />
                    </div>
                    <button type="submit" class="btn btn-outline" style="height: 40px">
                        <iconify-icon icon="lucide:filter"></iconify-icon> Filtrar
                    </button>
                    @php
                        $rolePrefix = auth()->user()->hasRole('Administrador') ? 'admin.' : (auth()->user()->hasRole('Digitador') ? 'digitador.' : 'empresa.');
                    @endphp
                    @if(request()->anyFilled(['search', 'estado', 'fecha']))
                        <a href="{{ route($rolePrefix . 'historial.index') }}" class="btn btn-outline" style="height: 40px">
                            Limpiar
                        </a>
                    @endif
                </div>
            </form>

            <!-- DATA TABLE -->
            <div class="card">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--border)">
                    <div style="display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 15px; color: var(--primary);">
                        <iconify-icon icon="lucide:clipboard-list" style="font-size: 18px"></iconify-icon>
                        Registros de Mantenimiento ({{ $diagnosticos->total() }})
                    </div>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Vehículo</th>
                                <th>Estado / Diagnóstico</th>
                                @if(!auth()->user()->hasRole('Empresa'))
                                    <th class="text-right">Acciones</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($diagnosticos as $diag)
                                <tr>
                                    <td>
                                        <div class="two-line-cell">
                                            <span class="main-text">{{ \Carbon\Carbon::parse($diag->fecdia)->format('d M Y') }}</span>
                                            <span class="sub-text">{{ \Carbon\Carbon::parse($diag->fecdia)->format('h:i A') }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="two-line-cell">
                                            <span class="main-text" style="font-family: monospace">{{ $diag->vehiculo->placaveh ?? 'N/A' }}</span>
                                            <span class="sub-text">
                                                {{ $diag->vehiculo->marca->nommarlin ?? 'N/A' }} 
                                                {{ $diag->vehiculo->modveh ? ' - ' . $diag->vehiculo->modveh : '' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($diag->aprobado === 1)
                                            <span class="status-badge success">APROBADO</span>
                                        @elseif($diag->aprobado === 0)
                                            @if($diag->rechazo && $diag->rechazo->estadorec === 'Reasignado')
                                                <span class="status-badge warning">REASIGNADO</span>
                                            @else
                                                <span class="status-badge danger">NO APROBADO</span>
                                            @endif
                                        @else
                                            <span class="status-badge" style="background-color: #f3f4f6; color: #6b7280;">PENDIENTE</span>
                                        @endif
                                    </td>
                                    @if(!auth()->user()->hasRole('Empresa'))
                                        <td>
                                            <div class="actions-cell">
                                                @hasanyrole('Administrador|Digitador')
                                                    <a href="{{ route($rolePrefix . 'diagnosticos.edit', $diag->iddia) }}" class="icon-btn" title="Editar">
                                                        <iconify-icon icon="lucide:edit-2"></iconify-icon>
                                                    </a>
                                                    <a href="{{ route($rolePrefix . 'diagnosticos.show', $diag->iddia) }}" class="icon-btn" title="Ver Detalles">
                                                        <iconify-icon icon="lucide:eye"></iconify-icon>
                                                    </a>
                                                @endhasanyrole
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 32px; color: #6b7280;">No hay historiales de mantenimiento registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div class="pagination" style="flex-direction: column; align-items: stretch;">
                    {{ $diagnosticos->links() }}
                </div>
            </div>
        </main>
    </div>
@endsection
