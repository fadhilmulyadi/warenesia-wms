@props([
    'title' => 'Belum ada item',
    'description' => null,
    'icon' => 'package-open',
    'iconClass' => 'w-8 h-8 mb-2 opacity-50',
    'containerClass' => '',
])

<div
    {{ $attributes->merge([
        'class' =>
            'flex flex-col items-center justify-center py-10 text-slate-400 ' .
            'border-2 border-dashed border-slate-200 rounded-xl bg-slate-50/50 ' .
            $containerClass
    ]) }}
>
    {{-- Icon --}}
    @if (trim($slot) !== '')
        {{ $slot }}
    @else
        <x-dynamic-component :component="'lucide-' . $icon" class="{{ $iconClass }}" />
    @endif

    {{-- Title --}}
    <span class="text-xs font-medium">
        {{ $title }}
    </span>

    {{-- Description (optional) --}}
    @if ($description)
        <p class="mt-1 text-[11px] text-slate-400/90 text-center max-w-xs">
            {{ $description }}
        </p>
    @endif

    {{-- Actions (optional) --}}
    @isset($actions)
        <div class="mt-4">
            {{ $actions }}
        </div>
    @endisset
</div>