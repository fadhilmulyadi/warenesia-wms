@props([
    'icon' => null,
    'href' => null,
    'danger' => false,
    'onClick' => null
])

@php
    $baseClasses = "group flex w-full items-center px-4 py-2 text-[12px] transition-colors cursor-pointer";
    $textClass = $danger ? "text-red-600 hover:bg-red-50" : "text-slate-700 hover:bg-slate-50";
    $iconBase = "mr-3 h-3.5 w-3.5";
    $iconColor = $danger ? "text-red-400 group-hover:text-red-600" : "text-slate-400 group-hover:text-teal-600";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "$baseClasses $textClass"]) }}>
        @if($icon)
            <x-dynamic-component :component="'lucide-'.$icon" class="{{ $iconBase }} {{ $iconColor }}" />
        @endif
        {{ $slot }}
    </a>
@else
    <button type="button" @if($onClick) @click="{{ $onClick }}" @endif {{ $attributes->merge(['class' => "$baseClasses $textClass"]) }}>
        @if($icon)
            <x-dynamic-component :component="'lucide-'.$icon" class="{{ $iconBase }} {{ $iconColor }}" />
        @endif
        {{ $slot }}
    </button>
@endif