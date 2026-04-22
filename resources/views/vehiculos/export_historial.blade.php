<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Mantenimiento Preventivo - CDA RASTRILLANTAS</title>
    <style>
        @page { size: letter; margin: 30px 45px; }
        body { font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 11px; color: #1f2937; line-height: 1.5; background: #fff; margin: 0; padding: 0; }
        
        /* Contenedor principal */
        .report-container { width: 100%; max-width: 800px; margin: 0 auto; }
        
        /* Header Table */
        .header-table { width: 100%; border-bottom: 3px solid #001834; margin-bottom: 25px; padding-bottom: 15px; }
        .logo-box { width: 50%; vertical-align: middle; }
        .header-title-box { width: 50%; text-align: right; vertical-align: middle; }
        .header-title { font-size: 20px; font-weight: 800; color: #001834; text-transform: uppercase; line-height: 1.1; letter-spacing: -0.5px; background: #f3f4f6; padding: 10px 15px; border-radius: 4px; display: inline-block; border-right: 4px solid #001834; }
        
        /* Info de contacto */
        .contact-info { width: 100%; margin-bottom: 20px; font-size: 9.5px; color: #4b5563; }
        
        /* Bloque destinatario */
        .details-section { margin-bottom: 25px; }
        .report-date { font-size: 12px; margin-bottom: 15px; font-weight: 500; }
        .recipient-box { margin-bottom: 15px; line-height: 1.3; }
        .recipient-name { font-size: 14px; font-weight: 700; color: #111827; text-transform: uppercase; }
        .recipient-manager { font-size: 12px; font-weight: 500; color: #374151; }
        .recipient-role { font-size: 11px; color: #6b7280; font-style: italic; }
        
        /* Texto introductorio */
        .intro-text { font-size: 11.5px; text-align: justify; margin-bottom: 25px; padding: 15px; background-color: #f9fafb; border-radius: 8px; border-left: 4px solid #001834; color: #374151; }
        .intro-text strong { color: #001834; }
        
        /* Tabla de Datos */
        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        .data-table th { background-color: #001834; color: #ffffff; font-weight: 600; text-align: left; padding: 10px 8px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; border: 1px solid #001834; }
        .data-table td { padding: 10px 8px; border: 1px solid #e5e7eb; vertical-align: top; font-size: 10.5px; word-wrap: break-word; }
        .data-table tr:nth-child(even) { background-color: #f9fafb; }
        
        /* Columnas específicas */
        .col-fecha { width: 85px; }
        .col-orden { width: 60px; text-align: center; }
        .col-placa { width: 80px; font-weight: 700; font-family: 'Courier New', Courier, monospace; }
        .col-obs { width: auto; }
        
        /* Estados */
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 9px; font-weight: 700; text-transform: uppercase; margin-top: 5px; }
        .badge-success { background-color: #d1fae5; color: #065f46; border: 1px solid #059669; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; border: 1px solid #dc2626; }
        .badge-warning { background-color: #fffbeb; color: #92400e; border: 1px solid #d97706; }
        .badge-muted { background-color: #f3f4f6; color: #4b5563; border: 1px solid #9ca3af; }
        
        /* Observaciones lista */
        .obs-list { margin: 0; padding: 0; list-style: none; font-size: 9.5px; color: #1f2937; }
        .obs-item { margin-bottom: 2px; }
        .obs-item::before { content: "• "; color: #dc2626; font-weight: bold; }
        
        /* Footer */
        .footer { position: fixed; bottom: 20px; width: 100%; text-align: center; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 10px; }
        
        /* Botones de acción (no impresión) */
        .actions-bar { background-color: #f3f4f6; padding: 15px; text-align: center; margin-bottom: 20px; border-radius: 8px; border: 1px solid #e5e7eb; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none; transition: 0.2s; cursor: pointer; border: none; }
        .btn-print { background-color: #001834; color: #ffffff; }
        .btn-print:hover { background-color: #002b5c; }
        
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; }
            .report-container { max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="no-print actions-bar">
        <button class="btn btn-print" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
            IMPRIMIR / DESCARGAR PDF
        </button>
    </div>

    <div class="report-container">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td class="logo-box">
                    <img src="{{ asset('assets/logos/logo-cda.webp') }}" alt="CDA RASTRILLANTAS LTDA" style="height: 75px; width: auto; display: block;">
                </td>
                <td class="header-title-box">
                    <div class="header-title">Informe de<br>Mantenimiento Preventivo</div>
                </td>
            </tr>
        </table>

        <!-- Contact Info -->
        <div class="contact-info">
            Carrera 4 Autopista sur #4 - 31 sur Soacha - Cundinamarca | 
            Nit: 832002212-2<br>
            Tel: +57-1-7813283 | Móvil: 301-4806231 - 300-210 7280 | 
            Email: contacto@cdarastrellantas.com
        </div>

        @php \Carbon\Carbon::setLocale('es'); @endphp

        <div class="details-section">
            <div class="report-date">
                Soacha, {{ \Carbon\Carbon::now()->translatedFormat('d \d\e F \d\e Y') }}
            </div>

            <div class="recipient-box">
                <div class="recipient-name">Señores: {{ $empresa ? $empresa->razsoem : 'REPORTE GENERAL DE FLOTA' }}</div>
                <div class="recipient-manager">{{ $empresa ? ($empresa->nomger ?? 'GERENCIA GENERAL') : '' }}</div>
                <div class="recipient-role">Gerente / Encargado de Flota</div>
            </div>
        </div>

        <div class="intro-text">
            Dando cumplimiento al protocolo de revisión establecido, me permito entregarles el informe final de <strong>{{ $totalVehiculos }}</strong> vehículos registrados bajo su supervisión. Durante el periodo evaluado, se realizaron un total de <strong>{{ $diagnosticos->count() }}</strong> diagnósticos técnicos de revisión preventiva y correctiva para asegurar la integridad operativa de su flota.
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-fecha">Fecha</th>
                    <th class="col-orden"># Orden</th>
                    <th class="col-placa">Placa</th>
                    <th class="col-obs">Hallazgos y Estado del Mantenimiento</th>
                </tr>
            </thead>
            <tbody>
                @forelse($diagnosticos as $diag)
                    @php
                        // Hallazgos dinámicos basados en fallos
                        $hallazgos = [];
                        if ($diag->aprobado == 0) {
                            foreach($diag->parametros as $p) {
                                $meta = $p->parametro;
                                if (!$meta) continue;
                                if ($meta->control === 'radio' && ($p->valor === 'no' || $p->valor === 'no_funciona')) {
                                    $hallazgos[] = strtoupper($meta->nompar);
                                }
                                if ($meta->control === 'number' && $meta->rini !== null && $meta->rfin !== null) {
                                    if (floatval($p->valor) < floatval($meta->rini) || floatval($p->valor) > floatval($meta->rfin)) {
                                        $hallazgos[] = strtoupper($meta->nompar) . " (FUERA DE RANGO)";
                                    }
                                }
                            }
                        }
                    @endphp
                    <tr>
                        <td class="col-fecha">
                            <div style="font-weight: 600;">{{ \Carbon\Carbon::parse($diag->fecdia)->format('d/m/Y') }}</div>
                            <div style="font-size: 8.5px; color: #6b7280;">Vence: {{ \Carbon\Carbon::parse($diag->fecdia)->addMonths(2)->format('d/m/Y') }}</div>
                        </td>
                        <td class="col-orden">{{ str_pad($diag->iddia, 4, '0', STR_PAD_LEFT) }}</td>
                        <td class="col-placa">{{ $diag->vehiculo->placaveh ?? 'N/A' }}</td>
                        <td class="col-obs">
                            @if(!empty($hallazgos))
                                <ul class="obs-list">
                                    @foreach($hallazgos as $hallazgo)
                                        <li class="obs-item">{{ $hallazgo }}</li>
                                    @endforeach
                                </ul>
                            @endif
                            
                            @if($diag->aprobado == 0 && $diag->rechazo && $diag->rechazo->motivo)
                                <div style="margin-top: 6px; margin-bottom: 6px; font-size: 9.5px; background-color: #fef2f2; border-left: 2px solid #ef4444; padding: 5px 8px; color: #7f1d1d; border-radius: 3px;">
                                    <strong>MOTIVO DE RECHAZO:</strong> {{ $diag->rechazo->motivo }}
                                </div>
                            @endif
                            
                            @if($diag->aprobado == 1)
                                <span class="badge badge-success">APROBADO</span>
                            @elseif($diag->aprobado == 0 && $diag->aprobado !== null)
                                <span class="badge badge-danger">NO APROBADO</span>
                            @endif
                            
                            @if($diag->vehiculo && !$empresa)
                                <div style="font-size: 8px; color: #9ca3af; margin-top: 5px; text-transform: uppercase;">
                                    {{ $diag->vehiculo->empresa->razsoem ?? 'Sin Empresa' }}
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px; color: #9ca3af;">
                            No se encontraron registros de mantenimiento bajo los criterios seleccionados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            Este informe es un documento digital verificado. Generado automáticamente por el Sistema de Gestión Vehicular CDA Rastrellantas Ltda.
        </div>
    </div>
</body>
</html>
