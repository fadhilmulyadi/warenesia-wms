@props([
    'src' => null,
    'alt' => '',
    'size' => 'h-9 w-9',
    'textSize' => 'text-[10px]'
])

<div {{ $attributes->merge(['class' => "$size shrink-0 rounded-lg bg-slate-100 flex items-center justify-center $textSize text-slate-500 border border-slate-200 overflow-hidden"]) }}>
    @if($src)
        <img src="{{ $src }}" alt="{{ $alt }}" class="h-full w-full object-cover">
    @else
        {{ strtoupper(substr($alt, 0, 2)) }}
    @endif
</div>