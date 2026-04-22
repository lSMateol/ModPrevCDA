<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Consolidado de Flota - {{ $empresa->razsoem }}</title>
    @include('diagnosticos.partials.report_styles')
</head>
<body>
    <button class="no-print print-btn" onclick="window.print()" style="display: none;">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
        Imprimir Reporte
    </button>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.print-btn').style.display = 'flex';
        });
    </script>
    
    <!-- Marca de Agua (Fija en el fondo) -->
    <div class="watermark" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; pointer-events: none; overflow: hidden; opacity: 0.04; display: flex; flex-wrap: wrap; align-content: space-around; justify-content: space-around; transform: rotate(-30deg) scale(1.5);">
        @for($i=0; $i<20; $i++)
            <div style="font-size: 40pt; font-weight: 900; margin: 40px; white-space: nowrap;">REVISIÓN PREVENTIVA</div>
        @endfor
    </div>

    @foreach($diagnosticos as $diagnostico)
        @include('diagnosticos.partials.report_body', ['diagnostico' => $diagnostico])
        
        @if(!$loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach

</body>
</html>
