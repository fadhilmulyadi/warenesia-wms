@props(['label', 'value'])

<div class="flex justify-between items-start gap-3 text-xs">
    <span class="text-slate-500 shrink-0">{{ $label }}</span>
    <span class="font-medium text-slate-900 text-right truncate max-w-[60%]">
        {{ $value ?? $slot }}
    </span>
</div>