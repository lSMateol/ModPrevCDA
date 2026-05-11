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
        .data-table { width: 100%; border-collapse: collapse; margin-top: 8px; table-layout: fixed; }
        .data-table th { background-color: #001834; color: #ffffff; font-weight: 600; text-align: left; padding: 7px 7px; font-size: 9px; text-transform: uppercase; letter-spacing: 0.04em; border: 1px solid #001834; }
        .data-table td { padding: 7px 7px; border: 1px solid #e5e7eb; vertical-align: top; font-size: 9.5px; word-wrap: break-word; line-height: 1.3; }
        .data-table tr:nth-child(even) { background-color: #f9fafb; }
        
        /* Columnas específicas */
        .col-fecha { width: 78px; }
        .col-orden { width: 50px; text-align: center; }
        .col-placa { width: 72px; font-weight: 700; font-family: 'Courier New', Courier, monospace; }
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

            /* Evitar que una fila se parta a la mitad entre dos páginas */
            .data-table tbody tr {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            /* Repetir el encabezado de la tabla en cada página */
            .data-table thead {
                display: table-header-group;
            }

            /* Separación visual al inicio de cada nueva página */
            @page {
                margin: 28px 40px 28px 40px;
            }
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
            Dando cumplimiento al protocolo de revisión establecido, me permito entregarles el informe técnico correspondiente a <strong>{{ $diagnosticos->count() }}</strong> diagnóstico(s) de revisión preventiva realizados sobre <strong>{{ $totalVehiculos }}</strong> vehículo(s) durante el periodo:
            <strong>
                @if($periodoInicio && $periodoFin)
                    del {{ $periodoInicio }} al {{ $periodoFin }}
                @elseif($periodoInicio)
                    desde el {{ $periodoInicio }}
                @elseif($periodoFin)
                    hasta el {{ $periodoFin }}
                @else
                    sin filtro de fecha (histórico completo)
                @endif
            </strong>.
            Los resultados reflejan el estado técnico de la flota registrada bajo supervisión del CDA Rastrillantas Ltda.
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
                    <tr>
                        <td class="col-fecha">
                            <div style="font-weight: 600;">{{ \Carbon\Carbon::parse($diag->fecdia)->format('d/m/Y') }}</div>
                            <div style="font-size: 8.5px; color: #6b7280;">Vence: {{ \Carbon\Carbon::parse($diag->fecdia)->addMonths(2)->format('d/m/Y') }}</div>
                        </td>
                        <td class="col-orden">{{ str_pad($diag->iddia, 4, '0', STR_PAD_LEFT) }}</td>
                        <td class="col-placa">{{ $diag->vehiculo->placaveh ?? 'N/A' }}</td>
                        <td class="col-obs">
                            @if($diag->aprobado == 0)
                                @if($diag->rechazo && $diag->rechazo->motivo)
                                    {{-- Motivo oficial de rechazo --}}
                                    <div style="margin-bottom: 5px; font-size: 9.5px; background-color: #fef2f2; border-left: 2px solid #ef4444; padding: 5px 8px; color: #7f1d1d; border-radius: 3px;">
                                        <strong>MOTIVO:</strong> {{ $diag->rechazo->motivo }}
                                    </div>
                                @endif
                                
                                @if(isset($diag->fallas_calculadas) && count($diag->fallas_calculadas) > 0)
                                    {{-- Parámetros con fallo real de la base de datos --}}
                                    <div style="margin-bottom: 5px; font-size: 9px; background-color: #fff7ed; border-left: 2px solid #f97316; padding: 5px 8px; color: #7c2d12; border-radius: 3px;">
                                        <strong>FALLAS DETECTADAS:</strong>
                                        @php
                                            // Agrupar fallas por grupo y tipo
                                            $gruposTipoA = [];
                                            $fallasTecnicas = [];
                                            foreach ($diag->fallas_calculadas as $falla) {
                                                $isTipoA = is_array($falla) && ($falla['is_tipo_a'] ?? false);
                                                $desc = is_array($falla) ? $falla['desc'] : $falla;
                                                $parts = explode(' - ', $desc, 2);
                                                $grupo = count($parts) > 1 ? ucfirst(strtolower($parts[0])) : 'General';
                                                $obs = count($parts) > 1 ? ucfirst(strtolower($parts[1])) : ucfirst(strtolower($desc));
                                                if ($isTipoA) {
                                                    if (!isset($gruposTipoA[$grupo])) $gruposTipoA[$grupo] = [];
                                                    $gruposTipoA[$grupo][] = $obs;
                                                } else {
                                                    $fallasTecnicas[] = $obs;
                                                }
                                            }
                                        @endphp
                                        
                                        {{-- Defectos Tipo A agrupados --}}
                                        @if(count($gruposTipoA) > 0)
                                            <div style="margin-top: 5px; border-left: 2px solid #dc2626; padding-left: 5px;">
                                                <div style="font-size: 7.5px; font-weight: bold; color: #991b1b; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 3px;">▲ Defectos Tipo A</div>
                                                @foreach($gruposTipoA as $grupo => $items)
                                                    <div style="margin-bottom: 3px;">
                                                        <span style="font-weight: bold; color: #7f1d1d; font-size: 8px;">{{ $grupo }}:</span>
                                                        <span style="color: #9a3412; font-size: 8px;">{{ implode(', ', $items) }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        {{-- Fallas Técnicas --}}
                                        @if(count($fallasTecnicas) > 0)
                                            <div style="margin-top: 4px; border-left: 2px solid #64748b; padding-left: 5px;">
                                                <div style="font-size: 7.5px; font-weight: bold; color: #475569; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 2px;">Fallas Técnicas</div>
                                                <div style="color: #374151; font-size: 8px;">{{ implode(', ', $fallasTecnicas) }}</div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                
                                @if(!($diag->rechazo && $diag->rechazo->motivo) && (!isset($diag->fallas_calculadas) || count($diag->fallas_calculadas) == 0))
                                    <div style="font-size: 9px; color: #991b1b; font-style: italic;">Sin detalle de fallo registrado</div>
                                @endif
                                <span class="badge badge-danger">NO APROBADO</span>
                            @else
                                <span class="badge badge-success">APROBADO</span>
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

        {{-- Firma de la Ingeniera --}}
        @php
            $firmaPath = public_path('assets/firmas/firma_ingeniera.png');
            $firmaBase64 = '';
            if (file_exists($firmaPath)) {
                $data = @file_get_contents($firmaPath);
                if ($data) {
                    $firmaBase64 = 'data:image/png;base64,' . base64_encode($data);
                }
            }
        @endphp
        <div style="margin-top: 55px; padding-top: 20px; border-top: 2px solid #001834; text-align: center;">
            <div style="display: inline-block; text-align: center; min-width: 240px;">
                @if($firmaBase64)
                    <img src="{{ $firmaBase64 }}" alt="Firma Ingeniera Autorizada"
                         style="max-height: 70px; max-width: 200px; display: block; margin: 0 auto 6px auto;">
                @else
                    <div style="height: 70px;"></div>
                @endif
                <div style="border-top: 1.5px solid #001834; padding-top: 6px;">
                    <span style="font-size: 10px; font-weight: 700; text-transform: uppercase; color: #001834; letter-spacing: 0.05em;">Firma Ingeniera Autorizada</span><br>
                    <span style="font-size: 9px; color: #374151;">CDA Rastrillantas Ltda.</span>
                </div>
            </div>
            <div style="margin-top: 12px; font-size: 9px; color: #9ca3af;">
                Generado el {{ \Carbon\Carbon::now()->translatedFormat('d \d\e F \d\e Y') }} —
                Sistema de Gestión Vehicular
            </div>
        </div>
    </div>
</body>
</html>
