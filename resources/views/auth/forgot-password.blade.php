<!DOCTYPE html>
<html lang="es" x-data="{ loading: false, loaded: false, isFocus: false }" x-init="setTimeout(() => loaded = true, 100)">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Recuperar Acceso - CDA Rastrillantas</title>
    
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
                        success: '#10b981',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        headline: ['Manrope', 'sans-serif'],
                    },
                    animation: {
                        'breathing': 'breathing 8s ease-in-out infinite',
                        'shimmer': 'shimmer 2s infinite linear',
                    },
                    keyframes: {
                        breathing: {
                            '0%, 100%': { opacity: 0.15, transform: 'scale(1)' },
                            '50%': { opacity: 0.35, transform: 'scale(1.1)' }
                        },
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

        .grain-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background-image: url("https://www.transparenttextures.com/patterns/carbon-fibre.png");
            opacity: 0.03;
            pointer-events: none;
            z-index: 50;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(30px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transform-style: preserve-3d;
            perspective: 1000px;
        }

        .input-glow:focus-within {
            box-shadow: 0 0 30px rgba(59, 130, 246, 0.15);
            border-color: rgba(59, 130, 246, 0.5);
        }

        /* Scanner Effect */
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

        .card-tilt {
            transition: transform 0.2s ease-out;
        }
    </style>
</head>
<body class="font-sans text-slate-200 antialiased min-h-screen flex items-center justify-center p-6 overflow-hidden">

    <!-- Capa de Grano Cinemático -->
    <div class="grain-overlay"></div>

    <!-- Background Image with Focus Tunneling -->
    <div class="fixed inset-0 z-0 transition-all duration-1000"
         :class="isFocus ? 'scale-105 blur-md opacity-40' : 'scale-100 blur-0 opacity-60'">
        <img src="{{ asset(config('assets.auth.backgrounds.recovery')) }}" 
             class="w-full h-full object-cover" alt="Fondo Rastrillantas">
        <div class="absolute inset-0 bg-gradient-to-tr from-[#001834] via-[#001834]/80 to-transparent"></div>
    </div>

    <!-- Main Container -->
    <main class="relative z-10 w-full max-w-lg"
          x-data="{ tiltX: 0, tiltY: 0 }"
          @mousemove="tiltX = ($event.clientX / window.innerWidth - 0.5) * 15; tiltY = ($event.clientY / window.innerHeight - 0.5) * 15"
          :style="`transform: perspective(1000px) rotateX(${-tiltY}deg) rotateY(${tiltX}deg)`">
        
        <div class="glass-card rounded-[3rem] p-8 md:p-14 shadow-[0_50px_100px_-20px_rgba(0,0,0,0.5)] relative overflow-hidden card-tilt transition-all duration-700"
             :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'">
            
            <!-- Logo con Breathing Aura -->
            <div class="flex flex-col items-center text-center mb-12">
                <div class="relative group mb-8">
                    <!-- Aura Animada -->
                    <div class="absolute -inset-4 bg-blue-500 rounded-full blur-2xl opacity-20 animate-breathing"></div>
                    <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-cyan-400 rounded-[2rem] blur opacity-25 group-hover:opacity-50 transition duration-1000"></div>
                    
                    <div class="relative bg-white p-5 rounded-[2rem] shadow-2xl border border-white/20 transform group-hover:scale-105 transition-transform duration-500">
                        <img src="{{ asset(config('assets.logos.main')) }}" alt="Logo Rastrillantas" class="h-16 w-auto object-contain">
                    </div>
                </div>
                
                <h1 class="text-3xl font-headline font-black text-white tracking-tight leading-none">Recuperar Acceso</h1>
                <p class="text-slate-400 text-xs mt-4 font-bold uppercase tracking-[2px]">Seguridad de Diagnóstico</p>
            </div>

            <!-- Success Message State -->
            @if (session('status'))
                <div class="mb-10 p-6 bg-emerald-500/10 border border-emerald-500/20 rounded-3xl flex flex-col items-center text-center gap-4 text-emerald-400 animate-pulse-slow transition-all duration-700">
                    <div class="w-12 h-12 rounded-full bg-emerald-500/20 flex items-center justify-center mb-2">
                        <iconify-icon icon="lucide:mail-check" class="text-3xl"></iconify-icon>
                    </div>
                    <p class="text-xs font-black uppercase tracking-widest leading-relaxed">
                        {{ session('status') }}
                    </p>
                    <p class="text-[10px] text-slate-500 font-bold">Por favor revisa tu bandeja de entrada.</p>
                </div>
            @else
                <p class="text-slate-500 text-[10px] font-bold text-center uppercase tracking-widest mb-10 max-w-xs mx-auto">
                    Ingresa tu correo institucional para validar tu identidad.
                </p>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-8" @submit="loading = true">
                @csrf

                <!-- Input Email -->
                <div class="space-y-3" @focusin="isFocus = true" @focusout="isFocus = false">
                    <label for="email" class="block text-[10px] font-black text-slate-500 uppercase tracking-[2px] ml-2">
                        Correo Institucional
                    </label>
                    <div class="relative input-glow transition-all duration-500 rounded-3xl group overflow-hidden bg-white/5 border border-white/10">
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                            class="w-full bg-transparent px-8 py-5 text-white font-bold outline-none placeholder-slate-700 transition-all text-sm"
                            placeholder="usuario@rastrillantas.com">
                        <div class="absolute right-8 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-blue-400 transition-colors">
                            <iconify-icon icon="lucide:shield-check" class="text-2xl transition-transform group-focus-within:rotate-12"></iconify-icon>
                        </div>
                    </div>
                    @error('email')
                        <p class="text-red-400 text-[10px] font-black uppercase tracking-widest px-4 mt-3 flex items-center gap-2">
                            <iconify-icon icon="lucide:shield-alert" class="text-xl"></iconify-icon>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Action Button -->
                <div class="pt-4">
                    <button type="submit" 
                        :disabled="loading"
                        class="relative overflow-hidden w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-white py-6 rounded-[2rem] font-black text-xs uppercase tracking-[3px] shadow-[0_20px_40px_-10px_rgba(59,130,246,0.3)] transform active:scale-[0.98] transition-all flex items-center justify-center gap-4 group btn-scanner">
                        
                        <span x-show="!loading" class="flex items-center gap-3">
                            Validar Acceso
                            <iconify-icon icon="lucide:key-round" class="text-xl group-hover:rotate-45 transition-transform duration-500"></iconify-icon>
                        </span>
                        
                        <span x-show="loading" class="flex items-center gap-3" x-cloak>
                            <iconify-icon icon="lucide:loader-2" class="text-2xl animate-spin"></iconify-icon>
                            Iniciando Protocolo...
                        </span>
                    </button>
                </div>

                <!-- Footer Links -->
                <div class="flex flex-col items-center gap-8 pt-8">
                    <a href="{{ route('login') }}" class="group flex items-center gap-3 text-slate-500 hover:text-white transition-all text-[10px] font-black uppercase tracking-[3px]">
                        <iconify-icon icon="lucide:arrow-left" class="text-lg group-hover:-translate-x-2 transition-transform duration-500"></iconify-icon>
                        Regresar al Portal
                    </a>
                    
                    <div class="flex items-center gap-3 w-full">
                        <div class="h-px flex-1 bg-white/5"></div>
                        <span class="text-[8px] text-slate-700 font-black uppercase tracking-[5px]">Safe Access</span>
                        <div class="h-px flex-1 bg-white/5"></div>
                    </div>
                </div>
            </form>
        </div>
    </main>

</body>
</html>
