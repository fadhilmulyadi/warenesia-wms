@props([
    'name' => 'password',
    'label' => 'Password',
    'id' => null,
    'required' => false,
    'placeholder' => '',
])

@php
    $id = $id ?? $name;
@endphp

<div class="space-y-2" x-data="{ 
    generate() {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%';
        let pwd = '';
        for (let i = 0; i < 12; i++) {
            pwd += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        this.$refs.input.value = pwd;
        this.$dispatch('input', pwd); // Dispatch input event for x-model binding if needed
        
        // Auto-fill confirmation if it exists in the same form context
        const confirmInput = document.getElementById('{{ $name }}_confirmation') || document.getElementById('{{ $id }}_confirmation');
        if (confirmInput) {
            confirmInput.value = pwd;
        }
    },
    copy() {
        const val = this.$refs.input.value;
        if(val) {
            navigator.clipboard.writeText(val);
            // Optional: Show toast or feedback
        }
    }
}">
    <div class="flex items-center justify-between">
        <x-input-label :for="$id" :value="$label" />
        <button type="button" @click="generate()" class="text-[11px] font-semibold text-teal-700 hover:text-teal-800 flex items-center gap-1">
            <x-lucide-refresh-cw class="w-3 h-3" />
            Generate
        </button>
    </div>
    
    <div class="relative">
        <x-text-input
            x-ref="input"
            :id="$id"
            :name="$name"
            type="password"
            :required="$required"
            :placeholder="$placeholder"
            {{ $attributes->merge(['class' => 'block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500 pr-10']) }}
            autocomplete="new-password"
        />
        <button type="button" @click="$refs.input.type = $refs.input.type === 'password' ? 'text' : 'password'" 
            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600">
            <x-lucide-eye class="w-4 h-4" />
        </button>
    </div>
    <x-input-error :messages="$errors->get($name)" />
</div>
