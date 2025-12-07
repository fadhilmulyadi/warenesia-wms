@props(['variant' => 'primary'])

@php
    $variantClasses = [
        'primary' => 'bg-sky-50 text-sky-700 border-sky-200',
        'blue' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'warning' => 'bg-amber-50 text-amber-700 border-amber-200',
        'danger' => 'bg-rose-50 text-rose-700 border-rose-200',
        'gray' => 'bg-slate-100 text-slate-600 border-slate-300',
        'neutral' => 'bg-slate-50 text-slate-600 border-slate-200',
    ];

    $badgeClasses = $variantClasses[$variant] ?? $variantClasses['primary'];
@endphp

<span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $badgeClasses }}">
    {{ $slot }}
</span>
