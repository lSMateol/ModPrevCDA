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

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-gray-100" x-data="{ sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true', mobileMenuOpen: false }" x-init="$watch('sidebarCollapsed', value => localStorage.setItem('sidebarCollapsed', value))">
    <div class="flex h-screen overflow-hidden">
        
        @include('components.sidebar')

        <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden transition-all duration-300">
            
            {{-- Mobile header if needed --}}
            <div class="lg:hidden flex items-center justify-between p-4 bg-[#0a1d37] text-white shrink-0">
                <div class="flex items-center space-x-3">
                    <div class="bg-orange-500 w-8 h-8 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-car-side text-white text-xs"></i>
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider">RASTRILLANTAS</span>
                </div>
                <button @click="mobileMenuOpen = true" class="p-2 hover:bg-white/10 rounded-md">
                    <i class="fa-solid fa-bars"></i>
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