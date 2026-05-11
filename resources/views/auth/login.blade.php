<!DOCTYPE html>
<html lang="es" x-data="{ loading: false, loaded: false }" x-init="setTimeout(() => loaded = true, 100)">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>CDA Rastrillantas - Portal de Acceso</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Scripts & Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#001834',
                        accent: '#3b82f6',
                        'accent-dark': '#1d4ed8',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        headline: ['Manrope', 'sans-serif'],
                    },
                    animation: {
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'shimmer': 'shimmer 2.5s infinite linear',
                    },
                    keyframes: {
                        shimmer: {
                            '0%': { transform: 'translateX(-100%)' },
                            '100%': { transform: 'translateX(100%)' }
                        }
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        
        body { background-color: #001834; }

        /* Efecto de Grano Cinematográfico */
        .grain-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background-image: url("https://www.transparenttextures.com/patterns/carbon-fibre.png");
            opacity: 0.03;
            pointer-events: none;
            z-index: 50;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Floating Label Logic */
        .floating-label-input:focus-within label,
        .floating-label-input input:not(:placeholder-shown) + label {
            transform: translateY(-1.5rem) scale(0.85);
            color: #3b82f6;
            font-weight: 700;
        }

        /* Glow en Focus */
        .input-glow:focus-within {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.15);
            border-color: rgba(59, 130, 246, 0.5);
        }

        /* Animación de entrada por partes */
        .reveal-delay-1 { transition-delay: 200ms; }
        .reveal-delay-2 { transition-delay: 400ms; }
        .reveal-delay-3 { transition-delay: 600ms; }

        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob { animation: blob 7s infinite; }

        /* Estética de Scanner en Botón */
        .btn-scanner::after {
            content: '';
            position: absolute;
            top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.2), transparent);
            transform: skewX(-25deg);
        }
        .btn-scanner:hover::after {
            animation: shimmer 1.5s infinite;
        }
    </style>
</head>
<body class="font-sans text-slate-200 antialiased overflow-hidden min-h-screen">

    <!-- Capa de Grano -->
    <div class="grain-overlay"></div>

    <div class="relative flex min-h-screen lg:flex-row flex-col">
        
        <!-- LADO IZQUIERDO: VISUAL IMPACT -->
        <div class="hidden lg:flex lg:w-7/12 relative bg-primary items-center justify-center overflow-hidden" 
             x-data="{ mouseX: 0, mouseY: 0 }" 
             @mousemove="mouseX = ($event.clientX / window.innerWidth - 0.5) * 20; mouseY = ($event.clientY / window.innerHeight - 0.5) * 20">
            
            <!-- Background Image with Parallax -->
            <div class="absolute inset-0 z-0 transition-transform duration-700 ease-out scale-110"
                 :style="`transform: translate(${mouseX}px, ${mouseY}px) scale(1.1)`">
                <img src="{{ asset(config('assets.auth.backgrounds.shared')) }}" 
                     class="w-full h-full object-cover opacity-60" 
                     alt="Inspección Rastrillantas"
                     :class="loaded ? 'opacity-60' : 'opacity-0'" 
                     style="transition: opacity 2s ease-in-out">
                <div class="absolute inset-0 bg-gradient-to-tr from-[#001834] via-[#001834]/80 to-transparent"></div>
            </div>

            <!-- Animated Blobs -->
            <div class="absolute top-0 -left-4 w-72 h-72 bg-blue-600 rounded-full mix-blend-multiply filter blur-3xl opacity-10 animate-blob"></div>

            <!-- Content Over Image -->
            <div class="relative z-10 px-12 xl:px-24 transition-all duration-1000 transform"
                 :class="loaded ? 'translate-x-0 opacity-100' : '-translate-x-12 opacity-0'">
                
                <div class="flex flex-col items-start gap-10 mb-8 reveal-delay-1">
                    <!-- Logo Oficial: Estilo Badge Premium -->
                    <div class="relative group">
                        <!-- Aura de luz detrás del logo -->
                        <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-cyan-400 rounded-3xl blur opacity-20 group-hover:opacity-40 transition duration-1000"></div>
                        
                        <!-- Contenedor del Logo -->
                        <div class="relative flex items-center justify-center p-5 bg-white rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.3)] border border-white/20">
                            <img src="{{ asset(config('assets.logos.main')) }}" alt="Logo Rastrillantas" class="h-20 w-auto object-contain">
                        </div>
                    </div>
                    
                    <div class="h-px w-20 bg-blue-500/50"></div>
                </div>
                
                <h3 class="text-5xl xl:text-7xl font-headline font-black text-white leading-tight mb-6 reveal-delay-2">
                    Control Total en <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-300 uppercase">Tiempo Real.</span>
                </h3>
                
                <p class="text-slate-200 text-lg max-w-lg font-medium leading-relaxed drop-shadow-md reveal-delay-3">
                    Accede a la plataforma líder en diagnóstico preventivo. Precisión, seguridad y eficiencia en un solo lugar.
                </p>

                <div class="mt-12 flex gap-8 reveal-delay-3">
                    <div class="flex flex-col group cursor-default">
                        <span class="text-white font-bold text-2xl tracking-tighter uppercase drop-shadow-lg group-hover:text-blue-400 transition-colors">Rastrillantas</span>
                        <span class="text-blue-400 text-[10px] font-black uppercase tracking-[3px]">Diagnostic System</span>
                    </div>
                </div>
            </div>

            <!-- Footer Left -->
            <div class="absolute bottom-10 left-12 xl:left-24 opacity-50">
                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest">© 2024 CDA Rastrillantas LTDA. Todos los derechos reservados.</p>
            </div>
        </div>

        <!-- LADO DERECHO: LOGIN PORTAL -->
        <div class="flex-1 flex items-center justify-center relative p-6 sm:p-12 md:p-20 bg-primary/95 lg:bg-primary transition-all duration-1000 delay-300"
             :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
            
            <!-- Logo para móviles -->
            <div class="lg:hidden absolute top-10 left-10 flex items-center gap-3">
                <img src="{{ asset(config('assets.logos.main')) }}" alt="Logo Rastrillantas" class="h-10 w-auto object-contain">
            </div>

            <div class="w-full max-w-md space-y-12" x-data="{ showPass: false }">
                
                <!-- Header -->
                <div class="space-y-4">
                    <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-[10px] font-bold uppercase tracking-[2px] animate-pulse-slow">
                        Portal de Acceso Seguro
                    </div>
                    <h1 class="text-4xl font-headline font-black text-white tracking-tight">Bienvenido de nuevo</h1>
                    <p class="text-slate-400 text-sm font-medium">Ingresa tus credenciales para acceder al centro de diagnóstico.</p>
                </div>

                <!-- Form -->
                <form method="POST" action="{{ route('login') }}" class="space-y-7" @submit="loading = true">
                    @csrf
                    
                    <!-- Input Usuario -->
                    <div class="space-y-2">
                        <label for="email" class="block text-[10px] font-black text-slate-500 uppercase tracking-[2px] ml-1">
                            Correo Electrónico
                        </label>
                        <div class="relative input-glow transition-all duration-300 rounded-2xl group">
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                                class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white font-semibold outline-none focus:bg-white/[0.08] transition-all placeholder-slate-600"
                                placeholder="ejemplo@cda.com">
                            <div class="absolute right-6 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-blue-400 transition-colors">
                                <iconify-icon icon="lucide:user" class="text-xl"></iconify-icon>
                            </div>
                        </div>
                        @error('email')
                            <p class="text-red-400 text-[10px] font-bold uppercase tracking-wide px-2 mt-2 flex items-center gap-2">
                                <iconify-icon icon="lucide:alert-circle" class="text-lg"></iconify-icon>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Input Password -->
                    <div class="space-y-2">
                        <label for="password" class="block text-[10px] font-black text-slate-500 uppercase tracking-[2px] ml-1">
                            Contraseña
                        </label>
                        <div class="relative input-glow transition-all duration-300 rounded-2xl group">
                            <input :type="showPass ? 'text' : 'password'" name="password" id="password" required
                                class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white font-semibold outline-none focus:bg-white/[0.08] transition-all placeholder-slate-600"
                                placeholder="••••••••">
                            <button type="button" @click="showPass = !showPass" 
                                class="absolute right-6 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white transition-colors">
                                <iconify-icon :icon="showPass ? 'lucide:eye-off' : 'lucide:eye'" class="text-xl"></iconify-icon>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-red-400 text-[10px] font-bold uppercase tracking-wide px-2 mt-2 flex items-center gap-2">
                                <iconify-icon icon="lucide:alert-circle" class="text-lg"></iconify-icon>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Options -->
                    <div class="flex items-center justify-between px-2">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative flex items-center">
                                <input type="checkbox" name="remember" class="w-5 h-5 rounded-lg border-white/10 bg-white/5 text-blue-600 focus:ring-blue-500/50 transition-all cursor-pointer">
                            </div>
                            <span class="text-xs text-slate-400 font-bold group-hover:text-slate-300 transition-colors">Recordarme</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs text-blue-400 font-bold hover:text-blue-300 transition-colors underline-offset-4 hover:underline">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button type="submit" 
                            :disabled="loading"
                            class="relative overflow-hidden w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-white py-5 rounded-2xl font-black text-xs uppercase tracking-[2px] shadow-2xl shadow-blue-900/40 transform active:scale-[0.98] transition-all flex items-center justify-center gap-3 group btn-scanner">
                            
                            <span x-show="!loading" class="flex items-center gap-3">
                                Entrar al Sistema
                                <iconify-icon icon="lucide:shield-check" class="text-lg group-hover:translate-x-1 transition-transform"></iconify-icon>
                            </span>
                            
                            <span x-show="loading" class="flex items-center gap-3" x-cloak>
                                <iconify-icon icon="lucide:loader-2" class="text-xl animate-spin"></iconify-icon>
                                Validando Credenciales...
                            </span>
                        </button>
                    </div>
                </form>

                <!-- Footer Mobile -->
                <div class="lg:hidden pt-10 text-center">
                    <p class="text-slate-600 text-[9px] font-bold uppercase tracking-[2px]">CDA Rastrillantas LTDA</p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
