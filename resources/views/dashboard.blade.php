<x-app-layout>
    <div class="flex h-screen bg-gray-100">
        <aside class="w-64 bg-slate-900 shadow-xl hidden md:block">
             @include('components.sidebar') 
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <x-slot name="header">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Dashboard - Panel de Control CDA') }}
                </h2>
            </x-slot>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200 p-6">
                <div class="max-w-7xl mx-auto">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h3 class="text-lg font-bold mb-4">¡Bienvenido al sistema de Prevención CDA Rastrillantas!</h3>
                            <p>{{ __("Has iniciado sesión correctamente.") }}</p>
                            
                            <hr class="my-4">
                            
                            <div class="mt-4 p-4 border-2 border-dashed border-gray-300 rounded-lg text-center text-gray-500">
                                Selecciona una opción del menú lateral para comenzar a trabajar.
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</x-app-layout>