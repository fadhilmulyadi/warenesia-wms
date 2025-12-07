@props([
    'label',
    'value' => null,
    'icon' => null
])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-1 border-b border-slate-100 last:border-0 pb-3 last:pb-0']) }}>
    <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
        @if($icon)
            <x-dynamic-component :component="'lucide-' . $icon" class="w-3.5 h-3.5 opacity-70" />
        @endif
        {{ $label }}
    </dt>
    <dd class="text-sm font-medium text-slate-800 break-words">
        {{ $value ?? $slot }}
    </dd>
</div>
