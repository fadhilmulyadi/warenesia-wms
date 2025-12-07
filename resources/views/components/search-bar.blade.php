@props(['value' => '', 'placeholder' => 'Search...', 'name' => 'q'])

<div class="relative">
    <input 
        type="text" 
        name="{{ $name }}" 
        value="{{ $value }}"
        placeholder="{{ $placeholder }}" 
        {{ $attributes->merge([
            'class' => 'w-full h-9 rounded-lg border-slate-200 pl-8 pr-3 py-2 text-xs focus:border-teal-500 focus:ring-teal-500'
        ]) }}
    >
    
    <div class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
        <x-lucide-search class="h-3.5 w-3.5" />
    </div>
</div>