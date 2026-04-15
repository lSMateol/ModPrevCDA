<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Restablecer Contraseña - CDA Rastrillantas</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-secondary-fixed": "#001c38", "surface-container-low": "#f1f4f6", "on-surface": "#181c1e", "surface-dim": "#d7dadc", "on-surface-variant": "#43474e", "error-container": "#ffdad6", "surface-tint": "#7c5800", "on-secondary-fixed-variant": "#254870", "on-tertiary-fixed": "#001c3b", "on-primary": "#ffffff", "inverse-primary": "#ffba20", "surface-container-lowest": "#ffffff", "inverse-surface": "#2d3133", "tertiary-container": "#002c59", "on-secondary": "#ffffff", "primary-fixed-dim": "#ffba20", "inverse-on-surface": "#eef1f3", "on-tertiary": "#ffffff", "on-secondary-container": "#365881", "background": "#f7fafc", "tertiary-fixed-dim": "#a7c8ff", "surface-variant": "#e0e3e5", "surface-bright": "#f7fafc", "on-background": "#181c1e", "on-tertiary-container": "#6b95d4", "surface-container-highest": "#e0e3e5", "outline-variant": "#c3c6cf", "primary-container": "#3c2900", "on-tertiary-fixed-variant": "#124782", "error": "#ba1a1a", "on-primary-fixed-variant": "#5e4200", "on-primary-container": "#c08a00", "on-error": "#ffffff", "outline": "#73777f", "on-primary-fixed": "#271900", "surface-container": "#ebeef0", "primary-fixed": "#ffdea8", "secondary-fixed-dim": "#a7c9f8", "secondary-container": "#adcefe", "tertiary": "#001834", "primary": "#221500", "surface-container-high": "#e5e9eb", "secondary-fixed": "#d3e4ff", "surface": "#f7fafc", "tertiary-fixed": "#d5e3ff", "secondary": "#3f608a", "on-error-container": "#93000a"
                    },
                    "borderRadius": { "DEFAULT": "0.125rem", "lg": "0.25rem", "xl": "0.5rem", "full": "0.75rem" },
                    "fontFamily": { "headline": ["Manrope"], "body": ["Inter"], "label": ["Inter"] }
                },
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; margin: 0; padding: 0; }
        .font-manrope { font-family: 'Manrope', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    </style>
</head>
<body class="bg-surface text-on-surface min-h-screen flex flex-col relative">
    <!-- Full-bleed Background Image Overlay -->
    <div class="fixed inset-0 z-0 h-full w-full">
        <img alt="Modern automotive diagnostic center" class="w-full h-full object-cover" decoding="async" src="{{ asset(config('assets.auth.backgrounds.reset')) }}"/>
        <div class="absolute inset-0 bg-tertiary/60 backdrop-blur-sm"></div>
    </div>

    <!-- Main Content -->
    <main class="relative z-10 flex-grow flex items-center justify-center p-6 pb-32">
        <div class="w-full max-w-md">
            <!-- White Content Card -->
            <div class="bg-surface-container-lowest rounded-xl shadow-2xl overflow-hidden border-t-4 border-primary-fixed-dim">
                <div class="p-8 md:p-10 flex flex-col items-center">
                    <!-- Logo Section -->
                    <div class="mb-8">
                        <img alt="CDA Rastrillantas logo" class="h-20 w-auto object-contain" decoding="async" src="{{ asset(config('assets.logos.main')) }}"/>
                    </div>

                    <!-- Header Text -->
                    <div class="text-center mb-8">
                        <h1 class="font-manrope font-extrabold text-2xl text-tertiary mb-2">Restablecer Contraseña</h1>
                        <p class="text-on-surface-variant text-sm leading-relaxed">Crea una nueva contraseña para acceder a tu cuenta.</p>
                    </div>

                    <!-- Password Reset Form -->
                    <form method="POST" action="{{ route('password.store') }}" class="w-full space-y-6">
                        @csrf
                        
                        <!-- Password Reset Token -->
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">
                        <input type="hidden" name="email" value="{{ old('email', $request->email) }}">
                        
                        @error('email')
                        <div class="flex items-center gap-2 mb-4 px-1">
                            <span class="material-symbols-outlined text-error text-[18px]">error</span>
                            <p class="text-error text-xs font-medium">{{ $message }}</p>
                        </div>
                        @enderror

                        <!-- New Password Field -->
                        <div class="space-y-1.5">
                            <label class="block font-inter text-[10px] uppercase tracking-[0.1em] font-bold text-tertiary ml-1">
                                NUEVA CONTRASEÑA
                            </label>
                            <div class="relative group">
                                <input name="password" type="password" required autocomplete="new-password" class="w-full bg-surface-container-high border-none border-b-2 border-outline-variant/20 focus:ring-0 focus:border-primary-fixed-dim transition-all px-4 py-3 text-on-surface rounded-md" placeholder="••••••••"/>
                                <button class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-tertiary transition-colors" type="button">
                                    <span class="material-symbols-outlined" data-icon="visibility">visibility</span>
                                </button>
                            </div>
                            @error('password')
                            <div class="flex items-center gap-2 mt-2 px-1">
                                <span class="material-symbols-outlined text-error text-[18px]">error</span>
                                <p class="text-error text-xs font-medium">{{ $message }}</p>
                            </div>
                            @enderror
                        </div>

                        <!-- Confirm Password Field -->
                        <div class="space-y-1.5">
                            <label class="block font-inter text-[10px] uppercase tracking-[0.1em] font-bold text-tertiary ml-1">
                                CONFIRMAR CONTRASEÑA
                            </label>
                            <div class="relative group">
                                <input name="password_confirmation" type="password" required autocomplete="new-password" class="w-full bg-surface-container-high border-none border-b-2 border-outline-variant/20 focus:ring-0 focus:border-primary-fixed-dim transition-all px-4 py-3 text-on-surface rounded-md" placeholder="••••••••"/>
                                <button class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-tertiary transition-colors" type="button">
                                    <span class="material-symbols-outlined" data-icon="visibility">visibility</span>
                                </button>
                            </div>
                            @error('password_confirmation')
                            <div class="flex items-center gap-2 mt-2 px-1">
                                <span class="material-symbols-outlined text-error text-[18px]">error</span>
                                <p class="text-error text-xs font-medium">{{ $message }}</p>
                            </div>
                            @enderror
                        </div>

                        <!-- Main Action Button -->
                        <button type="submit" class="w-full bg-primary-fixed-dim text-on-primary-fixed font-manrope font-bold py-4 rounded-md tracking-wider hover:brightness-105 active:scale-[0.98] transition-all flex justify-center items-center gap-2 mt-4 shadow-lg shadow-primary-fixed-dim/20">
                            ACTUALIZAR CONTRASEÑA
                        </button>
                    </form>

                    <!-- Back Link -->
                    <div class="mt-8">
                        <a href="{{ route('login') }}" class="flex items-center gap-2 text-tertiary text-sm font-semibold hover:opacity-70 transition-opacity group">
                            <span class="material-symbols-outlined text-lg transition-transform group-hover:-translate-x-1" data-icon="arrow_back">arrow_back</span>
                            Volver al Inicio de Sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer Shared Component -->
    <footer class="relative z-10 flex flex-col items-center gap-2 w-full pb-8">
        <div class="flex gap-6 items-center">
            <a class="font-inter text-[10px] uppercase tracking-[0.05em] text-[#f7fafc] opacity-80 hover:opacity-100 transition-opacity font-semibold" href="#">Políticas de Privacidad</a>
            <span class="w-1 h-1 bg-[#ffba20] rounded-full"></span>
            <a class="font-inter text-[10px] uppercase tracking-[0.05em] text-[#f7fafc] opacity-80 hover:opacity-100 transition-opacity font-semibold" href="#">Términos de Uso</a>
        </div>
        <p class="font-inter text-[10px] uppercase tracking-[0.05em] text-[#f7fafc] opacity-60">© 2024 CDA RASTRILLANTAS LTDA</p>
    </footer>
</body>
</html>
