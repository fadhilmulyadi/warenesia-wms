@props([
    'href' => '#',
    'variant' => 'secondary', // primary, secondary, danger, ghost
    'icon' => null,
    'type' => 'a', // Gunakan 'a' untuk link, 'button' / 'submit' untuk tombol
])

@php
    $baseClasses = 'inline-flex items-center justify-center h-9 px-4 rounded-lg text-xs font-semibold transition-all duration-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed gap-2';

    $variants = [
        'primary' => 'bg-teal-600 text-white hover:bg-teal-700 focus:ring-teal-500 border border-transparent',
        'secondary' => 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 focus:ring-slate-200',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 border border-transparent',
        'ghost' => 'bg-transparent text-slate-600 hover:bg-slate-100 hover:text-slate-900 shadow-none',
        'outline-danger' => 'bg-white text-red-600 border border-red-200 hover:bg-red-50 focus:ring-red-200',
    ];

    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['secondary']);
    $isLink = $type === 'a';
    $buttonType = in_array($type, ['submit', 'reset', 'button'], true) ? $type : 'button';
@endphp

@if($isLink)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <x-dynamic-component :component="'lucide-'.$icon" class="w-3.5 h-3.5" />
        @endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button type="{{ $buttonType }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <x-dynamic-component :component="'lucide-'.$icon" class="w-3.5 h-3.5" />
        @endif
        <span>{{ $slot }}</span>
    </button>
@endif
