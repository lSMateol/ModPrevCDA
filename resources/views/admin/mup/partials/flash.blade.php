{{-- Alertas de sesión y validación (MUP) — mismo patrón en todo el módulo --}}
@if(session('success') || session('error') || $errors->any())
<div class="mup-flash-stack px-2 pt-2 pb-1" style="position: relative; z-index: 9999;" role="status" aria-live="polite">
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-3 rounded-r-md shadow-sm flex items-center gap-3">
            <iconify-icon icon="lucide:check-circle" class="text-green-500 text-xl shrink-0"></iconify-icon>
            <p class="text-sm text-green-700 font-medium min-w-0 break-words">{{ session('success') }}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-3 rounded-r-md shadow-sm flex items-center gap-3">
            <iconify-icon icon="lucide:alert-circle" class="text-red-500 text-xl shrink-0"></iconify-icon>
            <p class="text-sm text-red-700 font-medium min-w-0 break-words">{{ session('error') }}</p>
        </div>
    @endif
    @if($errors->any())
        <div class="bg-orange-50 border-l-4 border-orange-400 p-4 mb-3 rounded-r-md shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <iconify-icon icon="lucide:alert-triangle" class="text-orange-500 text-xl shrink-0"></iconify-icon>
                <p class="text-sm text-orange-700 font-bold">Corrija los siguientes errores:</p>
            </div>
            <ul class="list-disc list-inside text-xs text-orange-600 space-y-1 ml-4 sm:ml-8 min-w-0 break-words">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endif
