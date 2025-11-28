@props([
    'align' => 'left' // left, center, right
])

@php
    $textAlign = match ($align) {
        'center' => 'text-center',
        'right' => 'text-right',
        default => 'text-left',
    };
@endphp

<td {{ $attributes->merge(['class' => "px-3 py-3 align-top text-slate-600 $textAlign"]) }}>
    {{ $slot }}
</td>