<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Recuperación de Contraseña - RASTRILLANTAS</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "secondary-fixed-dim": "#a7c9f8", "on-primary-fixed": "#271900", "on-primary-container": "#c08a00", "outline": "#73777f", "secondary": "#3f608a", "on-secondary": "#ffffff", "on-surface-variant": "#43474e", "on-tertiary-fixed": "#001c3b", "background": "#f7fafc", "surface-container-highest": "#e0e3e5", "on-primary-fixed-variant": "#5e4200", "surface-bright": "#f7fafc", "surface-tint": "#7c5800", "primary-container": "#3c2900", "on-primary": "#ffffff", "inverse-on-surface": "#eef1f3", "surface-container-low": "#f1f4f6", "surface-container-high": "#e5e9eb", "secondary-container": "#adcefe", "surface-container-lowest": "#ffffff", "surface": "#f7fafc", "primary-fixed": "#ffdea8", "primary-fixed-dim": "#ffba20", "tertiary-container": "#002c59", "tertiary-fixed": "#d5e3ff", "tertiary": "#001834", "on-secondary-fixed": "#001c38", "inverse-primary": "#ffba20", "surface-variant": "#e0e3e5", "tertiary-fixed-dim": "#a7c8ff", "on-error-container": "#93000a", "on-surface": "#181c1e", "secondary-fixed": "#d3e4ff", "outline-variant": "#c3c6cf", "on-tertiary-container": "#6b95d4", "on-secondary-container": "#365881", "error-container": "#ffdad6", "on-tertiary": "#ffffff", "error": "#ba1a1a", "surface-container": "#ebeef0", "on-error": "#ffffff", "on-secondary-fixed-variant": "#254870", "inverse-surface": "#2d3133", "primary": "#221500", "on-background": "#181c1e", "surface-dim": "#d7dadc", "on-tertiary-fixed-variant": "#124782"
                    },
                    "borderRadius": { "DEFAULT": "0.125rem", "lg": "0.25rem", "xl": "0.5rem", "full": "0.75rem" },
                    "fontFamily": { "headline": ["Manrope"], "body": ["Inter"], "label": ["Inter"] }
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24 }
        .bg-workshop { background-image: linear-gradient(rgba(0, 24, 52, 0.85), rgba(0, 24, 52, 0.85)), url('{{ asset(config("assets.auth.backgrounds.recovery")) }}'); background-size: cover; background-position: center }
        .tonal-shift { background-color: #f1f4f6 }
        body { min-height: max(884px, 100dvh); }
    </style>
</head>
<body class="font-body bg-background text-on-surface flex flex-col min-h-screen">
    <!-- TopAppBar -->
    <header class="fixed top-0 w-full z-50 bg-slate-50/90 dark:bg-slate-950/90 backdrop-blur-xl flex items-center justify-between px-6 h-16">
        <div class="flex items-center">
            <span class="material-symbols-outlined text-yellow-500 mr-4">arrow_back</span>
            <span class="font-manrope font-bold tracking-tight text-xl font-black text-slate-900 dark:text-white tracking-widest uppercase">RASTRILLANTAS</span>
        </div>
        <div class="hidden md:block">
            <p class="font-headline text-xs font-extrabold tracking-[0.2em] text-on-surface-variant uppercase">Modulo Preventivo</p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center pt-16 bg-workshop relative overflow-hidden">
        <!-- Decorative Glass Elements -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-primary-fixed-dim/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-tertiary-container/30 rounded-full blur-3xl"></div>
        <div class="relative w-full max-w-lg px-6 py-12">
            <!-- Central Identity -->
            <div class="flex flex-col items-center mb-10">
                <img alt="Logo CDA Rastrillantas" class="h-24 w-auto mb-6 object-contain" decoding="async" src="{{ asset(config('assets.logos.main')) }}"/>
                <h1 class="font-headline text-2xl font-extrabold text-white text-center tracking-tight">CDA Rastrillantas LTDA</h1>
                <p class="font-label text-[10px] uppercase tracking-[0.3em] text-primary-fixed-dim mt-2 font-bold">Modulo Preventivo</p>
            </div>

            <!-- Recovery Card -->
            <div class="bg-surface-container-lowest/95 backdrop-blur-xl rounded-xl shadow-2xl p-8 md:p-10 border border-white/10">
                <div class="mb-8">
                    <h2 class="font-headline text-xl font-bold text-on-surface mb-2">Recuperar Contraseña</h2>
                    <p class="text-on-surface-variant text-sm">Ingresa los datos solicitados para validar tu identidad y restablecer tu acceso.</p>
                </div>
                
                @if (session('status'))
                    <div class="mb-4 font-body text-sm text-green-600 dark:text-green-400">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="space-y-8">
                    @csrf
                    <!-- Section 1: Email -->
                    <div class="space-y-2">
                        <label class="font-label text-[11px] font-bold uppercase tracking-wider text-on-surface-variant ml-1">Correo Electrónico</label>
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-sm">mail</span>
                            <input name="email" value="{{ old('email') }}" type="email" required autofocus class="w-full pl-10 pr-4 py-3 bg-surface-container-high border-b-2 border-outline-variant focus:border-primary-fixed-dim focus:ring-0 transition-all text-sm outline-none" placeholder="ejemplo@rastrillantas.com"/>
                        </div>
                        @error('email')
                        <div class="flex items-center gap-2 mt-2 px-1">
                            <span class="material-symbols-outlined text-error text-[18px]">error</span>
                            <p class="text-error text-xs font-medium">{{ $message }}</p>
                        </div>
                        @enderror
                    </div>

                    <!-- Section 2: Security Questions (Ignored by default breeze but kept for design) -->
                    <div class="space-y-6 pt-2">
                        <div class="flex items-center gap-2 border-l-4 border-primary-fixed-dim pl-4">
                            <h3 class="font-headline text-sm font-bold uppercase tracking-tight text-tertiary">Preguntas Clave</h3>
                        </div>
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label class="font-label text-[11px] font-bold uppercase tracking-wider text-on-surface-variant ml-1">Seleccionar Pregunta</label>
                                <div class="relative">
                                    <select name="security_question" class="w-full px-4 py-3 bg-surface-container-high border-b-2 border-outline-variant focus:border-primary-fixed-dim focus:ring-0 appearance-none text-sm outline-none cursor-pointer">
                                        <option disabled="" selected="" value="">Escoge una pregunta de seguridad...</option>
                                        <option>¿Cuál es el nombre de tu primera mascota?</option>
                                        <option>¿Cuál es la ciudad de nacimiento de tu madre?</option>
                                        <option>¿Cuál fue el modelo de tu primer vehículo?</option>
                                        <option>¿Nombre de tu escuela primaria?</option>
                                    </select>
                                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-outline pointer-events-none">expand_more</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="font-label text-[11px] font-bold uppercase tracking-wider text-on-surface-variant ml-1">Tu Respuesta</label>
                                <input name="security_answer" type="text" class="w-full px-4 py-3 bg-surface-container-high border-b-2 border-outline-variant focus:border-primary-fixed-dim focus:ring-0 transition-all text-sm outline-none" placeholder="Escribe tu respuesta aquí"/>
                            </div>
                        </div>
                    </div>

                    <!-- Main Action -->
                    <button type="submit" class="w-full py-4 bg-primary-fixed-dim text-on-primary-fixed font-headline font-bold text-sm rounded-lg hover:brightness-105 transition-all shadow-lg shadow-primary-fixed-dim/20 uppercase tracking-widest mt-4">
                        Validar Respuestas
                    </button>

                    <!-- Navigation Link -->
                    <div class="text-center pt-4">
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-secondary font-semibold text-sm hover:text-primary transition-colors">
                            <span class="material-symbols-outlined text-base">arrow_back</span> Volver al Inicio de Sesión
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full mt-auto py-8 bg-slate-900 dark:bg-black flex flex-col md:flex-row items-center justify-between px-10 gap-4">
        <div class="flex items-center gap-2">
            <span class="text-yellow-500 font-bold font-inter text-[10px] uppercase tracking-wider">© 2024 CDA RASTRILLANTAS LTDA</span>
        </div>
        <div class="flex gap-6">
            <a class="font-inter text-[10px] uppercase tracking-wider text-slate-400 hover:text-yellow-400 transition-all opacity-80 hover:opacity-100" href="#">POLÍTICAS DE PRIVACIDAD</a>
            <a class="font-inter text-[10px] uppercase tracking-wider text-slate-400 hover:text-yellow-400 transition-all opacity-80 hover:opacity-100" href="#">TÉRMINOS DE USO</a>
            <a class="font-inter text-[10px] uppercase tracking-wider text-slate-400 hover:text-yellow-400 transition-all opacity-80 hover:opacity-100" href="#">SOPORTE</a>
        </div>
    </footer>
</body>
</html>
