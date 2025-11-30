@props([
    'title' => null,
    'subtitle' => null,
    'padding' => 'p-6',
])

@php
    $classes = trim("bg-white rounded-xl shadow-sm border border-slate-200 {$padding}");
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @if($title || $subtitle)
        <div class="mb-4 space-y-1">
            @if($title)
                <p class="text-sm font-semibold text-slate-900">{{ $title }}</p>
            @endif
            @if($subtitle)
                <p class="text-sm text-slate-500">{{ $subtitle }}</p>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>