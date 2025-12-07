@props([
    'title',
    'value',
    'subtitle' => null,
    'icon' => null,
])

<div class="flex items-start justify-between gap-3">
    <div class="space-y-1">
        <p class="text-sm font-medium text-slate-500">{{ $title }}</p>
        <p class="text-2xl md:text-3xl font-semibold text-slate-900 leading-tight">{{ $value }}</p>
        @if($subtitle)
            <p class="text-xs text-slate-500">{{ $subtitle }}</p>
        @endif
    </div>
    @if($icon)
        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-slate-50 text-slate-500">
            <x-dynamic-component :component="'lucide-' . $icon" class="h-5 w-5" />
        </span>
    @endif
</div>
