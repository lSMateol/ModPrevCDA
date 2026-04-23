<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Exportación de Reporte</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .error-card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08), 0 1px 3px rgba(0,0,0,0.05);
            max-width: 480px;
            width: 100%;
            text-align: center;
            overflow: hidden;
            animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .error-header {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            padding: 40px 30px 30px;
        }
        .error-icon {
            width: 72px;
            height: 72px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            backdrop-filter: blur(10px);
        }
        .error-icon svg {
            width: 36px;
            height: 36px;
            color: #fff;
        }
        .error-header h1 {
            color: #fff;
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .error-body {
            padding: 32px 30px;
        }
        .error-body p {
            font-size: 0.9rem;
            color: #4a5568;
            line-height: 1.6;
            margin-bottom: 12px;
        }
        .error-body p strong {
            color: #1a202c;
        }
        .info-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fff5f5;
            border: 1px solid #fed7d7;
            color: #c53030;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 12px;
            margin: 12px 0 20px;
        }
        .vehicle-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #edf2f7;
            color: #2d3748;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 8px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 20px;
        }
        .btn-close {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #001834;
            color: #fff;
            border: none;
            padding: 14px 32px;
            border-radius: 14px;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            width: 100%;
        }
        .btn-close:hover {
            background: #003060;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(0,24,52,0.25);
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-header">
            <div class="error-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <h1>{{ $titulo }}</h1>
        </div>
        <div class="error-body">
            <div class="vehicle-tag">
                🚗 Placa {{ $placa }} &bull; Expediente #{{ $iddia }}
            </div>
            <p>{!! $mensaje !!}</p>
            <div class="info-badge">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                {!! $detalle !!}
            </div>
            <button class="btn-close" onclick="window.close(); if(!window.closed) history.back();">
                Cerrar ventana
            </button>
        </div>
    </div>
</body>
</html>
