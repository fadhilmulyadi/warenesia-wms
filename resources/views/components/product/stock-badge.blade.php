@props(['product'])

@php
    $isLow = $product->current_stock <= $product->min_stock && $product->current_stock > 0;
    $isOut = $product->current_stock == 0;
@endphp

<div class="flex flex-col items-end gap-0.5">
    <span @class([
        'text-xs font-bold leading-none',
        'text-slate-700' => !$isLow && !$isOut,
        'text-amber-600' => $isLow,
        'text-rose-600' => $isOut,
    ])>
        {{ $product->current_stock }}
        <span class="text-[10px] font-normal text-slate-500 ml-0.5 uppercase">
            {{ $product->unit->name ?? '' }}
        </span>
    </span>

    <span class="text-[10px] text-slate-400 font-medium mt-0.5">
        Min: {{ $product->min_stock }}
    </span>
</div>
