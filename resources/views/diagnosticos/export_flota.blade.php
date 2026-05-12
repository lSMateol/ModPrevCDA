<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Consolidado de Flota - {{ $empresa->razsoem }}</title>
    <style>
        @page {
            size: letter;
            margin: 0.5cm;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 8.5pt;
            line-height: 1.15;
            color: #000;
            margin: 0;
            padding: 0;
            background-color: #f4f7f9;
        }
        .container {
            width: 100%;
            max-width: 850px;
            margin: 10px auto;
            background-color: #fff;
            padding: 0.5cm;
            box-shadow: 0 0 20px rgba(0,0,0,0.15);
            border-radius: 4px;
            box-sizing: border-box;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 5px;
        }
        .header-left h1 {
            font-size: 20pt;
            margin: 0;
            font-weight: bold;
            color: #000;
        }
        .header-right {
            text-align: right;
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }
        .business-info {
            font-size: 7.5pt;
            font-weight: bold;
            line-height: 1.2;
        }
        .logo {
            width: 90px;
            height: auto;
        }
        .order-info {
            text-align: left;
            margin-top: 2px;
        }
        .order-info strong {
            font-size: 10pt;
        }
        .section-title {
            background-color: #f0f0f0;
            padding: 2px 5px;
            font-weight: bold;
            border: 1px solid #000;
            margin-top: 8px;
            font-size: 8.5pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 2px 4px;
            text-align: left;
        }
        th {
            background-color: #f9f9f9;
            font-size: 8pt;
        }
        .label {
            font-weight: bold;
            background-color: #f2f2f2;
            width: 18%;
        }
        .value {
            width: 15.33%;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .mechanized-section {
            font-size: 7.5pt;
        }
        .mechanized-section th {
            text-align: center;
            background: #eee;
        }

        .footer-signatures {
            margin-top: 25px;
            display: flex;
            justify-content: space-around;
        }
        .signature-box {
            width: 40%;
            border-top: 1px solid #000;
            text-align: center;
            padding-top: 5px;
            font-size: 8pt;
        }

        .photos-container {
            margin-top: 15px;
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .photo-item {
            width: 170px;
            height: 120px;
            border: 1px solid #000;
            overflow: hidden;
            background: #f0f0f0;
        }
        .photo-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .status-box {
            border: 2px solid #000;
            padding: 5px 15px;
            display: inline-block;
            font-weight: bold;
            margin-top: 5px;
            font-size: 10pt;
        }

        @media print {
            .no-print { display: none; }
            body {
                margin: 0;
                padding: 0;
                background-color: #fff;
            }
            .container {
                max-width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
                border-radius: 0;
            }
        }

        .print-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #002D54;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 50px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            font-weight: bold;
            z-index: 1000;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<body>
    <div class="watermark-container">
        @for($i=0; $i<40; $i++)
            <div class="watermark-item">INSPECCIÓN PREVENTIVA</div>
        @endfor
    </div>
    <button class="no-print print-btn" onclick="window.print()">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
        Imprimir Reporte
    </button>

    {{--
        ============================================================
        CONTENIDO DEL REPORTE → diagnostico_report_card.blade.php
        Cualquier cambio ahí se refleja automáticamente en:
          • diagnosticos/export.blade.php       (reporte individual)
          • diagnosticos/export_flota.blade.php (reporte de flota)
        ============================================================
    --}}
    @foreach($diagnosticos as $diagnostico)
        @include('diagnosticos.partials.diagnostico_report_card', ['diagnostico' => $diagnostico])
        @if(!$loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach

</body>
</html>
