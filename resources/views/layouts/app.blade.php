<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="url-prefix" content="{{ auth()->check() ? (auth()->user()->hasRole('Administrador') ? 'admin' : (auth()->user()->hasRole('Digitador') ? 'digitador' : 'empresa')) : '' }}">
    <title>ModPrevCDA - Rastrillantas</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-gray-100" x-data="{ sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true', mobileMenuOpen: false }" x-init="$watch('sidebarCollapsed', value => localStorage.setItem('sidebarCollapsed', value))">
    <div class="flex h-screen overflow-hidden">
        
        @include('components.sidebar')

        <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden transition-all duration-300">
            
            {{-- Mobile header if needed --}}
            {{-- Mobile header --}}
            <div class="lg:hidden flex items-center justify-between p-5 bg-[#001834] text-white shrink-0 border-b border-white/5 shadow-lg">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-800 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20">
                        <iconify-icon icon="lucide:car-front" class="text-white text-xl"></iconify-icon>
                    </div>
                    <div>
                        <h1 class="text-white font-black text-xs tracking-tighter uppercase leading-none">Rastrillantas</h1>
                        <p class="text-[9px] text-blue-400 font-bold tracking-[2px] uppercase mt-1">Diagnostic</p>
                    </div>
                </div>
                <button @click="mobileMenuOpen = true" class="w-10 h-10 flex items-center justify-center bg-white/5 hover:bg-white/10 rounded-xl transition-all active:scale-95">
                    <iconify-icon icon="lucide:menu" class="text-2xl text-blue-400"></iconify-icon>
                </button>
            </div>

            <main class="p-4 sm:p-6">
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>