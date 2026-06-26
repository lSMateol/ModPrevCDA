<!DOCTYPE html>
<html lang="es" x-data="{ 
    loading: false, 
    loaded: false, 
    isFocus: false,
    step: 1,
    email: '{{ old('email') }}',
    secretQuestion: '',
    errorMessage: '',
    findQuestion() {
        if (!this.email) {
            this.errorMessage = 'Por favor ingresa tu usuario o correo.';
            return;
        }
        this.loading = true;
        this.errorMessage = '';
        fetch('{{ route('password.find-question') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email: this.email })
        })
        .then(response => {
            return response.json().then(data => {
                if (!response.ok) {
                    throw new Error(data.message || 'Error al buscar la pregunta.');
                }
                return data;
            });
        })
        .then(data => {
            this.loading = false;
            if (data.success) {
                this.secretQuestion = data.question;
                this.step = 2;
            } else {
                this.errorMessage = data.message || 'No se pudo recuperar la pregunta.';
            }
        })
        .catch(err => {
            this.loading = false;
            this.errorMessage = err.message || 'Error de conexión. Intente de nuevo.';
        });
    }
}" x-init="
    setTimeout(() => loaded = true, 100);
    @if(old('email') && $errors->any())
        findQuestion();
    @endif
">
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
            <div class="flex flex-col items-center text-center mb-10">
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

            <!-- Estado: Identidad verificada (paso 1 completado) -->
            @if (session('status'))
                <div class="mb-8 p-5 bg-emerald-500/10 border border-emerald-500/20 rounded-3xl flex flex-col items-center text-center gap-3 text-emerald-400 transition-all duration-700">
                    <div class="w-12 h-12 rounded-full bg-emerald-500/20 flex items-center justify-center">
                        <iconify-icon icon="lucide:shield-check" class="text-3xl"></iconify-icon>
                    </div>
                    <p class="text-xs font-black uppercase tracking-widest leading-relaxed">
                        {{ session('status') }}
                    </p>
                </div>
            @else
                <p class="text-slate-500 text-[10px] font-bold text-center uppercase tracking-widest mb-8 max-w-xs mx-auto">
                    Ingresa tu usuario o correo y responde la pregunta de seguridad.
                </p>
            @endif

            <form method="POST" action="{{ route('password.email') }}" id="recovery-form" class="space-y-6" @submit="if(step === 1) { $event.preventDefault(); findQuestion(); } else { loading = true; }">
                @csrf

                <!-- Input: Usuario o Correo -->
                <div class="space-y-3" @focusin="isFocus = true" @focusout="isFocus = false">
                    <label for="email" class="block text-[10px] font-black text-slate-500 uppercase tracking-[2px] ml-2">
                        Usuario o Correo Institucional
                    </label>
                    <div class="relative input-glow transition-all duration-500 rounded-3xl group overflow-hidden bg-white/5 border border-white/10" :class="step === 2 ? 'opacity-60' : ''">
                        <input type="text" name="email" id="email" x-model="email" :readonly="step === 2" required autofocus
                            class="w-full bg-transparent px-8 py-5 text-white font-bold outline-none placeholder-slate-700 transition-all text-sm"
                            placeholder="usuario o correo@rastrillantas.com">
                        <div class="absolute right-8 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-blue-400 transition-colors">
                            <iconify-icon icon="lucide:user" class="text-2xl transition-transform group-focus-within:scale-110"></iconify-icon>
                        </div>
                    </div>
                    @error('email')
                        <p class="text-red-400 text-[10px] font-black uppercase tracking-widest px-4 mt-3 flex items-center gap-2">
                            <iconify-icon icon="lucide:shield-alert" class="text-xl shrink-0"></iconify-icon>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Pregunta Secreta + Respuesta (Paso 2) -->
                <div x-show="step === 2" x-cloak class="space-y-6" x-transition>
                    <div class="space-y-3" @focusin="isFocus = true" @focusout="isFocus = false">
                        <!-- Pregunta (mostrada como etiqueta destacada) -->
                        <div class="flex items-start gap-2 px-2">
                            <iconify-icon icon="lucide:help-circle" class="text-blue-400/60 text-lg shrink-0 mt-0.5"></iconify-icon>
                            <div>
                                <span class="block text-[10px] font-black text-slate-500 uppercase tracking-[2px] mb-0.5">Pregunta de Seguridad</span>
                                <span class="text-[11px] font-bold text-slate-300" x-text="secretQuestion"></span>
                            </div>
                        </div>
                        <!-- Campo de respuesta -->
                        <div class="relative input-glow transition-all duration-500 rounded-3xl group overflow-hidden bg-white/5 border border-white/10">
                            <input type="password" name="secret_answer" id="secret_answer" :required="step === 2"
                                class="w-full bg-transparent px-8 py-5 text-white font-bold outline-none placeholder-slate-700 transition-all text-sm"
                                placeholder="Tu respuesta secreta">
                            <div class="absolute right-8 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-blue-400 transition-colors">
                                <iconify-icon icon="lucide:lock-keyhole" class="text-2xl transition-transform group-focus-within:rotate-12"></iconify-icon>
                            </div>
                        </div>
                        @error('secret_answer')
                            <p class="text-red-400 text-[10px] font-black uppercase tracking-widest px-4 mt-3 flex items-center gap-2">
                                <iconify-icon icon="lucide:shield-alert" class="text-xl shrink-0"></iconify-icon>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <!-- Mensaje de Error Ajax Dinámico -->
                <div x-show="errorMessage" x-cloak class="p-4 bg-red-500/10 border border-red-500/20 rounded-2xl flex items-center gap-3 text-red-400">
                    <iconify-icon icon="lucide:shield-alert" class="text-2xl shrink-0"></iconify-icon>
                    <p class="text-[10px] font-black uppercase tracking-widest leading-relaxed" x-text="errorMessage"></p>
                </div>

                <!-- Action Button -->
                <div class="pt-2">
                    <button type="submit" 
                        :disabled="loading"
                        class="relative overflow-hidden w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-white py-6 rounded-[2rem] font-black text-xs uppercase tracking-[3px] shadow-[0_20px_40px_-10px_rgba(59,130,246,0.3)] transform active:scale-[0.98] transition-all flex items-center justify-center gap-4 group btn-scanner">
                        
                        <span x-show="!loading" class="flex items-center gap-3">
                            <span x-text="step === 1 ? 'Continuar' : 'Validar Identidad'"></span>
                            <iconify-icon icon="lucide:key-round" class="text-xl group-hover:rotate-45 transition-transform duration-500"></iconify-icon>
                        </span>
                        
                        <span x-show="loading" class="flex items-center gap-3" x-cloak>
                            <iconify-icon icon="lucide:loader-2" class="text-2xl animate-spin"></iconify-icon>
                            Verificando...
                        </span>
                    </button>

                    <!-- Botón para modificar usuario -->
                    <button type="button" x-show="step === 2 && !loading" @click="step = 1; secretQuestion = ''; errorMessage = '';"
                        class="w-full text-center text-slate-500 hover:text-white transition-colors text-[9px] font-black uppercase tracking-[2px] mt-4">
                        Modificar Usuario
                    </button>
                </div>

                <!-- Footer Links -->
                <div class="flex flex-col items-center gap-8 pt-4">
                    <a href="{{ route('login') }}" class="group flex items-center gap-3 text-slate-500 hover:text-white transition-all text-[10px] font-black uppercase tracking-[3px]">
                        <iconify-icon icon="lucide:arrow-left" class="text-lg group-hover:-translate-x-2 transition-transform duration-500"></iconify-icon>
                        Regresar al Portal
                    </a>
                    
                    <div class="flex items-center gap-3 w-full">
                        <div class="h-px flex-1 bg-white/5"></div>
                        <span class="text-[8px] text-slate-700 font-black uppercase tracking-[5px]">Acceso Seguro</span>
                        <div class="h-px flex-1 bg-white/5"></div>
                    </div>
                </div>
            </form>
        </div>
    </main>

</body>
</html>
