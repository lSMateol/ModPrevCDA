<div class="mt-3 space-y-2 p-4 bg-gray-50 rounded-2xl border border-gray-100 animate-in fade-in duration-300" x-show="password.length > 0">
    <div class="flex items-center justify-between mb-2">
        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Fortaleza de Contraseña</span>
        <span class="text-[10px] font-black px-2 py-0.5 rounded-full uppercase tracking-tighter"
            :class="passwordStrength.color" x-text="passwordStrength.label"></span>
    </div>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
        <template x-for="rule in passwordRules" :key="rule.id">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-full flex items-center justify-center transition-colors"
                    :class="rule.met ? 'bg-emerald-500 text-white' : 'bg-gray-200 text-gray-400'">
                    <iconify-icon :icon="rule.met ? 'lucide:check' : 'lucide:circle'" class="text-[10px]"></iconify-icon>
                </div>
                <span class="text-[10px] font-bold transition-colors"
                    :class="rule.met ? 'text-emerald-600' : 'text-gray-400'" x-text="rule.label"></span>
            </div>
        </template>
    </div>

    <!-- Indicador de Coincidencia -->
    <div class="mt-4 pt-4 border-t border-gray-200 flex items-center justify-between" x-show="password_confirmation.length > 0">
        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Confirmación</span>
        <div class="flex items-center gap-2">
            <iconify-icon :icon="passwordsMatch ? 'lucide:check-circle' : 'lucide:alert-circle'" 
                :class="passwordsMatch ? 'text-emerald-500' : 'text-red-500'" class="text-sm"></iconify-icon>
            <span class="text-[10px] font-black uppercase tracking-tighter"
                :class="passwordsMatch ? 'text-emerald-600' : 'text-red-600'"
                x-text="passwordsMatch ? 'Coinciden' : 'No coinciden'"></span>
        </div>
    </div>
</div>
