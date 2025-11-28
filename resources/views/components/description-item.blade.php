@props(['label', 'value' => null, 'icon' => null])

<div class="group">
    <div class="text-[10px] text-slate-400 uppercase tracking-wider font-bold mb-1 flex items-center gap-1">
        @if($icon)
            <x-dynamic-component :component="'lucide-'.$icon" class="w-3 h-3 opacity-70" />
        @endif
        {{ $label }}
    </div>
    <div class="text-sm font-medium text-slate-900 group-hover:text-teal-700 transition-colors">
        {{ $value ?? $slot }}
    </div>
</div>