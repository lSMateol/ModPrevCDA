@extends('layouts.app')

@section('content')
<div class="px-4 py-6 sm:px-10 pb-20 max-w-7xl mx-auto" x-data="dashboardData()">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --bg-page: #f8fafc;
            --primary: #001834;
            --accent: #3b82f6;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --card-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
        }

        .dash-container { font-family: 'Inter', sans-serif; color: var(--text-main); }
        .dash-header { margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center; }
        .dash-title { font-size: 28px; font-weight: 800; tracking: -0.025em; margin: 0; color: var(--primary); }
        .dash-subtitle { font-size: 14px; color: var(--text-muted); margin-top: 4px; font-weight: 500; }

        .stats-grid { display: grid; grid-template-columns: repeat(12, 1fr); gap: 24px; }
        .dash-card { 
            background: white; 
            border-radius: 16px; 
            box-shadow: var(--card-shadow); 
            border: 1px solid rgba(0,0,0,0.04);
            padding: 24px;
            display: flex;
            flex-direction: column;
            min-width: 0; /* Evita desbordamiento en flex/grid */
        }
        .card-title { font-size: 16px; font-weight: 700; color: var(--primary); margin: 0 0 20px 0; display: flex; align-items: center; gap: 10px; }
        .card-title iconify-icon { font-size: 20px; color: var(--accent); }

        /* Rendimiento Diario */
        .rendimiento-card { grid-column: span 4; }
        .chart-container { position: relative; height: 200px; width: 100%; display: flex; align-items: center; justify-content: center; margin: 0 auto; }
        .rendimiento-stats { margin-top: 20px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }
        .stat-item { text-align: center; }
        .stat-val { font-size: 18px; font-weight: 700; display: block; }
        .stat-label { font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em; }

        /* Tendencia */
        .tendencia-card { grid-column: span 8; }
        .tendencia-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .tendencia-filters { display: flex; gap: 8px; background: #f1f5f9; padding: 4px; border-radius: 8px; }
        .filter-btn { padding: 6px 12px; font-size: 12px; font-weight: 600; border-radius: 6px; cursor: pointer; transition: 0.2s; border: none; background: transparent; color: var(--text-muted); }
        .filter-btn.active { background: white; color: var(--primary); box-shadow: 0 2px 4px rgba(0,0,0,0.05); }

        /* Accesos Directos */
        .alertas-card { grid-column: span 5; }
        .accesos-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; flex: 1; }
        .acceso-item { 
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; 
            padding: 20px; border-radius: 12px; border: 1px solid #f1f5f9; background: #f8fafc; 
            text-decoration: none; transition: 0.2s; text-align: center;
        }
        .acceso-item:hover { background: #fff; border-color: var(--accent); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1); transform: translateY(-2px); }
        .acceso-icon { font-size: 28px; color: var(--accent); }
        .acceso-label { font-size: 11px; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.025em; }
        .acceso-item.warning .acceso-icon { color: var(--warning); }
        .acceso-item.success .acceso-icon { color: var(--success); }
        .acceso-item.danger .acceso-icon { color: var(--danger); }

        /* Actividad */
        .actividad-card { grid-column: span 7; }
        .table-scroll { overflow-x: auto; margin-top: 4px; }
        .dash-table { width: 100%; border-collapse: collapse; }
        .dash-table th { text-align: left; padding: 12px 16px; font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; border-bottom: 1px solid #f1f5f9; }
        .dash-table td { padding: 16px; font-size: 13px; color: var(--text-main); border-bottom: 1px solid #f1f5f9; }
        .badge-status { padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 600; }
        .badge-status.aprobado { background: #dcfce7; color: #15803d; }
        .badge-status.rechazado { background: #fee2e2; color: #ef4444; }
        .badge-status.pendiente { background: #fef3c7; color: #92400e; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

        @media (max-width: 1024px) {
            .stats-grid { grid-template-columns: 1fr; gap: 20px; }
            .rendimiento-card, .tendencia-card, .alertas-card, .actividad-card { grid-column: span 1; }
        }

        @media (max-width: 768px) {
            .dash-header { flex-direction: column; align-items: flex-start; gap: 16px; }
            .dash-title { font-size: 24px; }
            .dash-subtitle { font-size: 13px; }
            .tendencia-header { flex-direction: column; align-items: flex-start; gap: 12px; }
        }

        @media (max-width: 640px) {
            .dash-card { padding: 20px 16px; }
            .accesos-grid { grid-template-columns: 1fr 1fr; gap: 12px; } /* Mantener 2 columnas si es posible */
            .chart-container { height: 180px; }
            .rendimiento-stats { gap: 8px; }
            .stat-val { font-size: 16px; }
            .stat-label { font-size: 10px; }
        }
    </style>

    <div class="dash-container">
        <header class="dash-header">
            <div>
                <h1 class="dash-title">Mi Perfil</h1>
                <p class="dash-subtitle">Panel de control personal y métricas de diagnóstico operativo</p>
            </div>
            <div class="flex gap-4">
                <div class="bg-white px-4 py-2 rounded-xl shadow-sm border border-slate-100 flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center text-blue-600">
                        <iconify-icon icon="lucide:calendar"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-500 font-bold uppercase">Hoy</p>
                        <p class="text-sm font-bold text-slate-800">{{ now()->format('d M, Y') }}</p>
                    </div>
                </div>
            </div>
        </header>

        <div class="stats-grid">
            <!-- 1. RENDIMIENTO DIARIO -->
            <div class="dash-card rendimiento-card">
                <h2 class="card-title">
                    <iconify-icon icon="lucide:pie-chart"></iconify-icon>
                    Rendimiento Diario
                </h2>
                <div class="chart-container">
                    <canvas id="rendimientoChart"></canvas>
                    <div style="position: absolute; text-align: center;">
                        <span style="font-size: 32px; font-weight: 800; color: var(--primary);">{{ $rendimiento['aprobados'] + $rendimiento['rechazados'] + $rendimiento['pendientes'] }}</span>
                        <p style="font-size: 10px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin: -4px 0 0 0;">Total Hoy</p>
                    </div>
                </div>
                <div class="rendimiento-stats">
                    <div class="stat-item">
                        <span class="stat-val text-blue-500">{{ $rendimiento['pendientes'] }}</span>
                        <span class="stat-label">Pendientes</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-val text-emerald-500">{{ $rendimiento['aprobados'] }}</span>
                        <span class="stat-label">Aprobados</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-val text-rose-500">{{ $rendimiento['rechazados'] }}</span>
                        <span class="stat-label">Rechazados</span>
                    </div>
                </div>
            </div>

            <!-- 2. TENDENCIA DE DIAGNÓSTICO -->
            <div class="dash-card tendencia-card">
                <div class="tendencia-header">
                    <h2 class="card-title" style="margin: 0;">
                        <iconify-icon icon="lucide:trending-up"></iconify-icon>
                        Tendencia de Diagnóstico
                    </h2>
                    <div class="tendencia-filters">
                        <button class="filter-btn active" @click="changeRange('semana', $event)">Semanal</button>
                        <button class="filter-btn" @click="changeRange('mes', $event)">Mensual</button>
                    </div>
                </div>
                <div style="flex: 1; min-height: 250px; position: relative;">
                    <div id="wrapper-semana" style="position: absolute; inset: 0;">
                        <canvas id="tendenciaChartSemana"></canvas>
                    </div>
                    <div id="wrapper-mes" style="position: absolute; inset: 0; display: none;">
                        <canvas id="tendenciaChartMes"></canvas>
                    </div>
                </div>
            </div>

            <!-- 3. ACCESOS DIRECTOS -->
            @php
                $prefix = auth()->user()->hasRole('Administrador') ? 'admin' : 'digitador';
            @endphp
            <div class="dash-card alertas-card">
                <h2 class="card-title">
                    <iconify-icon icon="lucide:zap"></iconify-icon>
                    Accesos Directos
                </h2>
                <div class="accesos-grid">
                    <a href="{{ route($prefix . '.diagnosticos.index') }}?action=agendar" class="acceso-item">
                        <iconify-icon icon="lucide:calendar-plus" class="acceso-icon"></iconify-icon>
                        <span class="acceso-label">Agendar<br>Diagnóstico</span>
                    </a>
                    <a href="{{ route($prefix . '.rechazados') }}" class="acceso-item danger">
                        <iconify-icon icon="lucide:refresh-cw" class="acceso-icon"></iconify-icon>
                        <span class="acceso-label">Reagendar<br>Rechazado</span>
                    </a>
                    <a href="{{ route($prefix . '.vehiculos.create') }}" class="acceso-item success">
                        <iconify-icon icon="lucide:car-front" class="acceso-icon"></iconify-icon>
                        <span class="acceso-label">Crear<br>Vehículo</span>
                    </a>
                    <a href="{{ route($prefix . '.alertas') }}" class="acceso-item warning">
                        <iconify-icon icon="lucide:bell-ring" class="acceso-icon"></iconify-icon>
                        <span class="acceso-label">Ver<br>Alertas</span>
                    </a>
                </div>
            </div>

            <!-- 4. ACTIVIDAD RECIENTE -->
            <div class="dash-card actividad-card">
                <h2 class="card-title">
                    <iconify-icon icon="lucide:activity"></iconify-icon>
                    Actividad Reciente
                </h2>
                <div class="table-scroll">
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>Vehículo / Empresa</th>
                                <th>Digitador</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($actividad as $diag)
                                <tr>
                                    <td>
                                        <div class="flex flex-col">
                                            <span style="font-weight: 700; font-family: monospace;">{{ $diag->vehiculo->placaveh ?? 'N/A' }}</span>
                                            <span style="font-size: 11px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">
                                                {{ $diag->vehiculo->empresa->razsoem ?? 'Sin Empresa' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td style="white-space: nowrap;">{{ $diag->persona->nomper ?? 'N/A' }}</td>
                                    <td>
                                        @if(is_null($diag->aprobado))
                                            <span class="badge-status pendiente">Pendiente</span>
                                        @elseif($diag->aprobado == 1)
                                            <span class="badge-status aprobado">Aprobado</span>
                                        @else
                                            <span class="badge-status rechazado">Rechazado</span>
                                        @endif
                                    </td>
                                    <td style="white-space: nowrap; font-size: 11px;">{{ \Carbon\Carbon::parse($diag->fecdia)->format('d/m/y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function dashboardData() {
        return {
            rendimientoChart: null,
            tendenciaChartSemana: null,
            tendenciaChartMes: null,
            tendenciaData: @json($tendencia),
            
            init() {
                this.initRendimientoChart();
                this.initTendenciaChart('semana');
            },

            initRendimientoChart() {
                const ctx = document.getElementById('rendimientoChart').getContext('2d');
                this.rendimientoChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Pendientes', 'Aprobados', 'Rechazados'],
                        datasets: [{
                            data: [
                                {{ $rendimiento['pendientes'] }}, 
                                {{ $rendimiento['aprobados'] }}, 
                                {{ $rendimiento['rechazados'] }}
                            ],
                            backgroundColor: ['#3b82f6', '#10b981', '#ef4444'],
                            borderWidth: 0,
                            hoverOffset: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%',
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            },

            initTendenciaChart(range) {
                if (range === 'semana' && this.tendenciaChartSemana) return;
                if (range === 'mes' && this.tendenciaChartMes) return;

                const canvasId = range === 'semana' ? 'tendenciaChartSemana' : 'tendenciaChartMes';
                const ctx = document.getElementById(canvasId).getContext('2d');
                
                let filteredData = [...this.tendenciaData];
                if (range === 'semana') {
                    filteredData = filteredData.slice(-7);
                } else {
                    filteredData = filteredData.slice(-30);
                }

                const labels = filteredData.map(d => {
                    const date = new Date(d.fecha + 'T00:00:00');
                    return date.toLocaleDateString('es-CO', { day: '2-digit', month: 'short' });
                });
                const values = filteredData.map(d => d.total);

                const chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Diagnósticos',
                            data: values,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.05)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointBackgroundColor: '#3b82f6',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1, color: '#94a3b8', font: { size: 10 } },
                                grid: { borderDash: [5, 5], color: '#f1f5f9' }
                            },
                            x: {
                                ticks: { color: '#94a3b8', font: { size: 10 } },
                                grid: { display: false }
                            }
                        }
                    }
                });

                if (range === 'semana') {
                    this.tendenciaChartSemana = chart;
                } else {
                    this.tendenciaChartMes = chart;
                }
            },

            changeRange(range, event) {
                const buttons = document.querySelectorAll('.filter-btn');
                buttons.forEach(btn => btn.classList.remove('active'));
                if (event && event.currentTarget) {
                    event.currentTarget.classList.add('active');
                }

                document.getElementById('wrapper-semana').style.display = range === 'semana' ? 'block' : 'none';
                document.getElementById('wrapper-mes').style.display = range === 'mes' ? 'block' : 'none';

                this.initTendenciaChart(range);
            }
        }
    }
</script>
@endsection
