@php
    $params = $diagnostico->parametros->groupBy(function($p) {
        return $p->parametro->tippar->nomtip;
    });
    $paramValues = $diagnostico->parametros->pluck('valor', 'parametro.nompar')->toArray();
@endphp

<div class="container">
    <header>
        <div class="header-left">
            <h1>INSPECCION PREVENTIVA</h1>
            <div class="order-info">
                <strong>SIDAUTO</strong><br>
                <strong>No. Registro: {{ $diagnostico->iddia }}</strong>
            </div>
        </div>
        <div class="header-right">
            <div class="business-info">
                NIT 832002212-2<br>
                RASTRILLANTAS LTDA<br>
                Dirección Cra. 4 N 4-31<br>
                Cd. Soacha<br>
                Tel. 5717813283
            </div>
            <img src="{{ asset('assets/logos/logo-cda.webp') }}" class="logo" alt="Logo">
        </div>
    </header>

    <!-- A. INFORMACION GENERAL -->
    <div class="section-title">A. INFORMACION GENERAL</div>
    
    <table>
        <tr>
            <td class="label" colspan="2">1. FECHA</td>
            <td class="label" colspan="4">2. DATOS DEL PROPIETARIO O TENEDOR DEL VEHICULO</td>
        </tr>
        <tr>
            <td class="label">Fecha Prueba</td>
            <td class="value" colspan="1">{{ \Carbon\Carbon::parse($diagnostico->fecdia)->format('Y-m-d H:i:s') }}</td>
            <td class="label">Nombre o Razón social</td>
            <td class="value" colspan="3">{{ $diagnostico->vehiculo->empresa->razsoem ?? 'PARTICULAR' }}</td>
        </tr>
        <tr>
            <td class="label">Dirección</td>
            <td class="value" colspan="1">{{ $diagnostico->vehiculo->empresa->direcem ?? 'N/A' }}</td>
            <td class="label">Documento de Identidad</td>
            <td class="value" colspan="3">{{ $diagnostico->vehiculo->empresa->nitemp ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Teléfono</td>
            <td class="value" colspan="1">{{ $diagnostico->vehiculo->empresa->telemp ?? 'N/A' }}</td>
            <td class="label">Ciudad:</td>
            <td class="value">Soacha</td>
            <td class="label">Departamento:</td>
            <td class="value">CUNDINAMARCA</td>
        </tr>
    </table>

    <!-- 3. DATOS DEL VEHICULO -->
    <div class="section-title">3. DATOS DEL VEHICULO</div>
    <table>
        <tr>
            <td class="label">Placa</td>
            <td class="value">{{ $diagnostico->vehiculo->placaveh }}</td>
            <td class="label">País</td>
            <td class="value">COLOMBIA</td>
            <td class="label">Servicio</td>
            <td class="value">Publico</td>
        </tr>
        <tr>
            <td class="label">Clase</td>
            <td class="value">BUSETA</td>
            <td class="label">Marca</td>
            <td class="value">{{ $diagnostico->vehiculo->marca->nommar ?? 'N/A' }}</td>
            <td class="label">Linea</td>
            <td class="value">NPR100P</td>
        </tr>
        <tr>
            <td class="label">Modelo</td>
            <td class="value">1998</td>
            <td class="label">No. licencia transito</td>
            <td class="value">10016651055</td>
            <td class="label">Fecha Matricula</td>
            <td class="value">1998-01-27</td>
        </tr>
        <tr>
            <td class="label">Color</td>
            <td class="value">BLANCO ROJO</td>
            <td class="label">Combustible</td>
            <td class="value">DIESEL</td>
            <td class="label">VIN o Chasis</td>
            <td class="value">9GCNPR65PWB443504</td>
        </tr>
        <tr>
            <td class="label">No. Motor</td>
            <td class="value">867586</td>
            <td class="label">Tipo motor</td>
            <td class="value">4 T</td>
            <td class="label">Cilindraje</td>
            <td class="value">4.500</td>
        </tr>
        <tr>
            <td class="label">Kilometraje</td>
            <td class="value">{{ $diagnostico->kilomt }}</td>
            <td class="label">Numero de sillas</td>
            <td class="value">28</td>
            <td class="label">Vidrios polarizados</td>
            <td class="value">SI( ) NO( X )</td>
        </tr>
        <tr>
            <td class="label">Blindaje:</td>
            <td class="value" colspan="5">SI( ) NO( X )</td>
        </tr>
    </table>

    <!-- B. RESULTADOS DE LA INSPECCIÓN MECANIZADA -->
    <div class="section-title">B. RESULTADOS DE LA INSPECCIÓN MECANIZADA</div>
    
    <table class="mechanized-section">
        <tr>
            <th colspan="3">4. EMISIONES AUDIBLES</th>
            <th colspan="2" style="background: #eee;">5. LUCES</th>
        </tr>
        <tr>
            <th style="width: 15%;"></th>
            <th style="width: 10%;">Valor</th>
            <th style="width: 8%;">Unid</th>
            <th style="width: 50%;">PARÁMETRO</th>
            <th style="width: 17%;">ESTADO</th>
        </tr>
        <tr>
            <td class="label">RUIDO ESCAPE</td>
            <td class="text-center">{{ $paramValues['RUIDO ESCAPE'] ?? '1' }}</td>
            <td class="text-center">dBA</td>
            <td class="label">LUZ IZQUIERDA (BAJA)</td>
            <td class="text-center" style="font-weight: bold;">
                {{ isset($paramValues['luz_izquierda']) ? (strtolower($paramValues['luz_izquierda']) == 'funciona' ? 'FUNCIONA' : 'NO FUNCIONA') : '-' }}
            </td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td class="label">LUZ DERECHA (BAJA)</td>
            <td class="text-center" style="font-weight: bold;">
                {{ isset($paramValues['luz_derecha']) ? (strtolower($paramValues['luz_derecha']) == 'funciona' ? 'FUNCIONA' : 'NO FUNCIONA') : '-' }}
            </td>
        </tr>
    </table>

    <div class="section-title">DEFECTOS</div>
    <table class="mechanized-section">
        <tr>
            <th style="width: 40%;">PARÁMETRO</th>
            <th style="width: 10%;">SI</th>
            <th style="width: 10%;">NO</th>
            <th style="width: 10%;">N/A</th>
            <th style="width: 30%;">RESULTADO</th>
        </tr>
        <tr>
            <td class="label">DILUSION GASOLINA</td>
            <td class="text-center">{{ ($paramValues['dilusion_gasolina'] ?? '') == 'si' ? 'X' : '' }}</td>
            <td class="text-center">{{ ($paramValues['dilusion_gasolina'] ?? '') == 'no' ? 'X' : '' }}</td>
            <td class="text-center">{{ ($paramValues['dilusion_gasolina'] ?? '') == 'na' ? 'X' : '' }}</td>
            <td class="text-center" style="font-weight: bold;">{{ strtoupper($paramValues['dilusion_gasolina'] ?? '-') }}</td>
        </tr>
        <tr>
            <td class="label">CRITERIOS DE VALIDACION (MOTOR DIESEL)</td>
            <td class="text-center">{{ ($paramValues['Criterios_de_validacion'] ?? '') == 'si' ? 'X' : '' }}</td>
            <td class="text-center">{{ ($paramValues['Criterios_de_validacion'] ?? '') == 'no' ? 'X' : '' }}</td>
            <td class="text-center">{{ ($paramValues['Criterios_de_validacion'] ?? '') == 'na' ? 'X' : '' }}</td>
            <td class="text-center" style="font-weight: bold;">{{ strtoupper($paramValues['Criterios_de_validacion'] ?? '-') }}</td>
        </tr>
    </table>

    <div class="section-title">7. EMISIONES DE GASES - VEHICULO DIESEL</div>
    <table class="mechanized-section">
        <tr>
            <th>Temp C</th>
            <th>Rpm</th>
            <th>Ciclo 1</th>
            <th>Unid</th>
            <th>Ciclo 2</th>
            <th>Unid</th>
            <th>Ciclo 3</th>
            <th>Unid</th>
            <th>Ciclo 4</th>
            <th>Unid</th>
            <th>Resultado</th>
            <th>Valor</th>
            <th>Norma</th>
            <th>Unidad</th>
        </tr>
        <tr>
            <td class="text-center">{{ $paramValues['temp_c'] ?? '75' }}</td>
            <td class="text-center">{{ $paramValues['rpm'] ?? '4061' }}</td>
            <td class="text-center">{{ $paramValues['ciclo1'] ?? '4.13' }}</td>
            <td class="text-center">%</td>
            <td class="text-center">{{ $paramValues['ciclo2'] ?? '2.50' }}</td>
            <td class="text-center">%</td>
            <td class="text-center">{{ $paramValues['ciclo3'] ?? '2.27' }}</td>
            <td class="text-center">%</td>
            <td class="text-center">{{ $paramValues['ciclo4'] ?? '2.41' }}</td>
            <td class="text-center">%</td>
            <td class="label">Resultado</td>
            <td class="text-center">{{ $paramValues['resultado_diesel'] ?? '2.33' }}</td>
            <td class="text-center">35</td>
            <td class="text-center">%</td>
        </tr>
    </table>

    <div class="section-title">D. DEFECTOS ENCONTRADOS EN LA INSPECCIÓN VISUAL Y SENSORIAL</div>
    <table>
        <tr>
            <th style="width: 50%;">Descripción (Observaciones)</th>
            <th style="width: 30%;">Grupo / Categoría</th>
            <th colspan="2" class="text-center">Tipo de defecto</th>
        </tr>
        <tr>
            <th></th>
            <th></th>
            <th class="text-center" style="width: 40px;">A</th>
            <th class="text-center" style="width: 40px;">B</th>
        </tr>
        @php
            $descRaw = $paramValues['desc_inspeccion'] ?? '';
            $data = @json_decode($descRaw, true);
            $listaDefectos = [];
            $obsGeneral = '';
            if (is_array($data)) {
                if (isset($data['list'])) {
                    $listaDefectos = $data['list'];
                    $obsGeneral = $data['obs'] ?? '';
                } else {
                    $listaDefectos = $data;
                }
            }
        @endphp

        @if(count($listaDefectos) > 0)
            @foreach($listaDefectos as $defecto)
            <tr>
                <td style="font-size: 8pt;">{{ $defecto['obs'] ?? ($defecto['desc'] ?? 'Sin observaciones') }}</td>
                <td class="text-center">{{ $defecto['grupo'] ?? '-' }}</td>
                <td class="text-center">{{ ($defecto['tipo'] ?? '') == 'Tipo A' ? 'X' : '' }}</td>
                <td class="text-center">{{ ($defecto['tipo'] ?? '') == 'Tipo B' ? 'X' : '' }}</td>
            </tr>
            @endforeach
        @elseif(!empty($descRaw) && !is_array($data))
            <tr>
                <td style="font-size: 8pt;">{{ $descRaw }}</td>
                <td class="text-center">{{ $paramValues['grupo_inspeccion'] ?? 'N/A' }}</td>
                <td class="text-center">{{ ($paramValues['tipo_defecto'] ?? '') == 'Tipo A' ? 'X' : '' }}</td>
                <td class="text-center">{{ ($paramValues['tipo_defecto'] ?? '') == 'Tipo B' ? 'X' : '' }}</td>
            </tr>
        @else
            <tr>
                <td colspan="4" class="text-center" style="color: #666; font-style: italic; height: 30px; vertical-align: middle;">No se encontraron defectos del listado base</td>
            </tr>
        @endif
    </table>

    @if(!empty($obsGeneral))
    <div style="margin-top: 5px; border: 1px solid #000; padding: 5px;">
        <strong style="font-size: 8pt; text-transform: uppercase;">Observaciones Generales / Otros Hallazgos:</strong>
        <p style="margin: 3px 0 0 0; font-size: 8pt; white-space: pre-wrap;">{{ $obsGeneral }}</p>
    </div>
    @endif

    <div style="display: flex; gap: 20px; align-items: flex-start; margin-top: 5px;">
        <div style="flex: 1;">
            <div class="status-box">
                ESTADO: {{ $diagnostico->aprobado ? 'APROBADO' : 'RECHAZADO' }}
            </div>
            <div style="font-size: 7.5pt; margin-top: 8px; line-height: 1.3;">
                <strong>CAUSAL DE RECHAZO:</strong><br>
                a. Se encuentra al menos un defecto Tipo A<br>
                b. La cantidad total de defectos Tipo B sea:<br>
                &nbsp;&nbsp;&nbsp;&nbsp;• Igual o superior a 10 para vehículos particulares<br>
                &nbsp;&nbsp;&nbsp;&nbsp;• Igual o superior a 5 para vehículos públicos<br>
                &nbsp;&nbsp;&nbsp;&nbsp;• Igual o superior a 5 para vehículos tipo motocicletas<br>
                &nbsp;&nbsp;&nbsp;&nbsp;• Igual o superior a 7 para vehículos tipo motocarros<br>
            </div>
        </div>
        <div style="border: 1px solid #000; padding: 10px; width: 40%; font-size: 7.5pt; text-align: center; font-weight: bold; margin-top: 5px;">
            NOTA: ESTE FORMULARIO NO CUENTA COMO<br>REVISIÓN TÉCNICO - MECÁNICA
        </div>
    </div>

    <div style="margin-top: 15px; font-size: 8pt;">
        <table style="border: none;">
            <tr style="border: none;">
                <td style="border: none; width: 80px;"><strong>INSPECTOR:</strong></td>
                <td style="border: none; border-bottom: 1px solid #000; width: 220px;">{{ $diagnostico->inspector->nomper ?? '' }} {{ $diagnostico->inspector->apeper ?? '' }}</td>
                <td style="border: none; padding-left: 20px; width: 80px;"><strong>DIGITADOR:</strong></td>
                <td style="border: none; border-bottom: 1px solid #000; width: 200px;">{{ $diagnostico->persona->nomper ?? '' }} {{ $diagnostico->persona->apeper ?? '' }}</td>
            </tr>
        </table>
    </div>

    <div class="footer-signatures" style="margin-top: 40px;">
        <div class="signature-box">
            E. NOMBRE Y FIRMA AUTORIZADAS<br>
            <span style="font-weight: bold; font-size: 9pt;">Ing. {{ $diagnostico->ingeniero->nomper ?? '' }} {{ $diagnostico->ingeniero->apeper ?? '' }}</span>
        </div>
    </div>

    @if($diagnostico->fotos->count() > 0)
    <div class="photos-container">
        @foreach($diagnostico->fotos as $foto)
        <div class="photo-item">
            <img src="{{ route('storage.fallback', ['path' => $foto->rutafoto]) }}" alt="Evidencia">
        </div>
        @endforeach
    </div>
    @endif

    <div style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px;">
        <p style="margin: 0; font-weight: 900; color: #cc0000; font-size: 9.5pt; text-transform: uppercase;">
            REVISIÓN PREVENTIVA DE {{ $diagnostico->dpiddia ? 'REINSPECCIÓN' : 'INSPECCIÓN' }}
        </p>
        <p style="margin: 4px 0 0 0; font-size: 8.5pt; line-height: 1.4; text-align: justify; font-weight: 500;">
            El presente reporte confirma que hoy {{ \Carbon\Carbon::parse($diagnostico->fecdia)->format('Y-m-d') }} 
            el automotor de placas <strong>{{ $diagnostico->vehiculo->placaveh }}</strong> realiza 
            {{ $diagnostico->dpiddia ? 'reinspección de la' : 'la' }} revisión preventiva. 
            De acuerdo a los resultados el CDA Rastrillantas certifica que el vehículo 
            <strong>{{ $diagnostico->aprobado ? 'aprobó' : 'no aprobó' }}</strong> a la revisión preventiva.
        </p>
    </div>
</div>
