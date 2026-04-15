<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>CDA Rastrillantas - Login</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "inverse-surface": "#2d3133", "error-container": "#ffdad6", "inverse-primary": "#ffba20", "surface-bright": "#f7fafc", "on-surface-variant": "#43474e", "secondary": "#3f608a", "on-secondary": "#ffffff", "on-tertiary-fixed-variant": "#124782", "on-primary-container": "#c08a00", "surface-container-lowest": "#ffffff", "on-primary": "#ffffff", "surface-container": "#ebeef0", "on-tertiary-fixed": "#001c3b", "surface-tint": "#7c5800", "surface-dim": "#d7dadc", "on-primary-fixed-variant": "#5e4200", "error": "#ba1a1a", "surface-container-high": "#e5e9eb", "tertiary-fixed": "#d5e3ff", "tertiary-fixed-dim": "#a7c8ff", "on-primary-fixed": "#271900", "outline": "#73777f", "tertiary-container": "#002c59", "secondary-fixed": "#d3e4ff", "primary-container": "#3c2900", "secondary-container": "#adcefe", "on-error": "#ffffff", "on-background": "#181c1e", "primary": "#221500", "inverse-on-surface": "#eef1f3", "primary-fixed": "#ffdea8", "on-secondary-fixed-variant": "#254870", "on-tertiary": "#ffffff", "secondary-fixed-dim": "#a7c9f8", "on-secondary-fixed": "#001c38", "on-surface": "#181c1e", "primary-fixed-dim": "#ffba20", "on-secondary-container": "#365881", "surface-variant": "#e0e3e5", "on-tertiary-container": "#6b95d4", "surface": "#f7fafc", "background": "#f7fafc", "outline-variant": "#c3c6cf", "surface-container-low": "#f1f4f6", "tertiary": "#001834", "on-error-container": "#93000a", "surface-container-highest": "#e0e3e5"
                    },
                    "borderRadius": { "DEFAULT": "0.125rem", "lg": "0.25rem", "xl": "0.5rem", "full": "0.75rem" },
                    "fontFamily": { "headline": ["Manrope"], "body": ["Inter"], "label": ["Inter"] }
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .glass-panel { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(16px); }
        body { min-height: max(884px, 100dvh); }
    </style>
</head>
<body class="bg-surface font-body text-on-surface min-h-screen flex flex-col">
    <!-- TopAppBar -->
    <header class="bg-[#f7fafc] dark:bg-[#001834] flex items-center justify-between px-8 h-16 w-full sticky top-0 z-50 shadow-sm">
        <div class="flex items-center gap-3">
            <img alt="CDA Rastrillantas Logo" class="h-10 w-auto object-contain" decoding="async" src="{{ asset(config('assets.logos.main')) }}"/>
            <span class="font-manrope font-bold tracking-tight text-lg uppercase text-[#001834] dark:text-[#ffba20]">CDA Rastrillantas LTDA - Modulo Preventivo</span>
        </div>
    </header>

    <!-- Main Content: Login Screen -->
    <main class="flex-grow flex flex-col relative overflow-hidden">
        <!-- Aesthetic Background Elements (CDA context) -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <img alt="Automotive workshop background" class="absolute inset-0 w-full h-full object-cover opacity-10 mix-blend-multiply" decoding="async" src="{{ asset(config('assets.auth.backgrounds.shared')) }}"/>
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-secondary-container opacity-10 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 -left-12 w-64 h-64 bg-tertiary opacity-5 rounded-full blur-3xl"></div>
        </div>
        <div class="relative z-10 w-full max-w-md mx-auto px-6 pt-12 pb-8 flex flex-col items-center justify-center min-h-[calc(100vh-128px)]">
            <!-- Hero Section -->
            <div class="w-full mb-12 text-left">
                <h1 class="font-headline text-4xl font-extrabold text-tertiary mb-2 tracking-tight">Acceso</h1>
                <p class="font-body text-on-surface-variant text-sm tracking-wide">Inicia sesión en tu centro de mando preventivo.</p>
            </div>

            <!-- Login Form Container -->
            <form method="POST" action="{{ route('login') }}" class="w-full space-y-8">
                @csrf
                <!-- Input Group: Usuario -->
                <div class="space-y-2">
                    <label class="font-label text-[0.75rem] uppercase tracking-[0.05em] font-semibold text-on-surface-variant px-1">Usuario</label>
                    <div class="relative group">
                        <input name="email" value="{{ old('email') }}" type="email" required autofocus autocomplete="username" class="w-full bg-surface-container-high border-b border-outline-variant border-opacity-20 py-4 px-4 font-body text-on-surface focus:outline-none focus:border-primary-fixed-dim transition-colors duration-300 rounded-lg" placeholder="nombre.usuario@cda.com"/>
                    </div>
                    @error('email')
                    <div class="flex items-center gap-2 mt-3 px-1">
                        <span class="material-symbols-outlined text-error text-[18px]">error</span>
                        <p class="text-error text-xs font-medium">{{ $message }}</p>
                    </div>
                    @enderror
                </div>

                <!-- Input Group: Contraseña -->
                <div class="space-y-2">
                    <label class="font-label text-[0.75rem] uppercase tracking-[0.05em] font-semibold text-on-surface-variant px-1">Contraseña</label>
                    <div class="relative group">
                        <input name="password" type="password" required autocomplete="current-password" class="w-full bg-surface-container-high border-b border-outline-variant border-opacity-20 py-4 px-4 font-body text-on-surface focus:outline-none focus:border-primary-fixed-dim transition-colors duration-300 rounded-lg" placeholder="••••••••"/>
                    </div>
                    @error('password')
                    <div class="flex items-center gap-2 mt-3 px-1">
                        <span class="material-symbols-outlined text-error text-[18px]">error</span>
                        <p class="text-error text-xs font-medium">{{ $message }}</p>
                    </div>
                    @enderror
                </div>

                <!-- Action Button -->
                <div class="pt-4">
                    <button type="submit" class="w-full py-4 bg-primary-fixed-dim hover:opacity-90 transition-all duration-150 transform active:scale-[0.98] rounded-lg shadow-[0_24px_40px_rgba(24,28,30,0.06)] flex items-center justify-center gap-2">
                        <span class="font-label text-sm uppercase tracking-[0.1em] font-bold text-on-primary-fixed">Iniciar Sesión</span>
                        <span class="material-symbols-outlined text-on-primary-fixed text-lg">arrow_forward</span>
                    </button>
                </div>

                <!-- Secondary Link -->
                @if (Route::has('password.request'))
                <div class="text-center">
                    <a href="{{ route('password.request') }}" class="font-label text-xs uppercase tracking-wider text-secondary font-semibold hover:text-primary-fixed-dim transition-colors duration-300">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>
                @endif
            </form>

            <!-- Decorative Image Anchor -->
            <div class="mt-20 w-full rounded-xl overflow-hidden shadow-sm">
                <img class="w-full h-32 object-cover grayscale opacity-40 hover:grayscale-0 hover:opacity-100 transition-all duration-700" data-alt="Technical inspection line for heavy vehicles" loading="lazy" decoding="async" src="{{ asset(config('assets.auth.illustrations.login')) }}"/>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-[#f7fafc] dark:bg-[#001834] flex flex-col md:flex-row justify-between items-center px-12 py-6 w-full mt-auto border-t border-surface-variant/30">
        <p class="font-inter text-xs tracking-wide uppercase text-[#365881] dark:text-[#adcefe] opacity-70">© 2024 CDA Rastrillantas LTDA</p>
        <div class="flex gap-6 mt-4 md:mt-0">
            <a class="font-inter text-xs tracking-wide uppercase text-[#365881] opacity-70 hover:text-[#ffba20] transition-colors" href="#">Políticas de Privacidad</a>
            <a class="font-inter text-xs tracking-wide uppercase text-[#365881] opacity-70 hover:text-[#ffba20] transition-colors" href="#">Términos de Uso</a>
        </div>
    </footer>
</body>
</html>
