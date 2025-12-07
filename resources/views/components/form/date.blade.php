@props([
    'name',
    'value' => null,
    'placeholder' => null,
])

<input
    type="date"
    name="{{ $name }}"
    value="{{ $value }}"
    placeholder="{{ $placeholder }}"
    {{ $attributes->merge([
        'class' => 'w-full h-9 rounded-lg border-slate-200 px-3 text-xs text-slate-700 focus:border-teal-500 focus:ring-teal-500',
    ]) }}
>
