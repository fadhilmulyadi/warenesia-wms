@props(['value'])

<span {{ $attributes->merge(['class' => 'text-xs font-medium text-slate-700 tabular-nums whitespace-nowrap']) }}>
    Rp {{ number_format($value, 0, ',', '.') }}
</span>