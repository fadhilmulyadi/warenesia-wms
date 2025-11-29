@props([
    'name',
    'options' => [],
    'selected' => [],
    'columns' => 1,
])

@php
    $selectedValues = collect((array) $selected)
        ->filter(static fn ($value) => $value !== null && $value !== '')
        ->map(static fn ($value) => (string) $value)
        ->toArray();

    $normalized = collect($options)->map(static function ($option, $key) {
        if (is_array($option)) {
            $value = (string) ($option['value'] ?? $option['id'] ?? $key);
            $label = (string) ($option['label'] ?? $option['name'] ?? $value);
        } else {
            $value = is_int($key) ? (string) $option : (string) $key;
            $label = (string) $option;
        }

        return [
            'value' => $value,
            'label' => $label,
        ];
    })->filter(static fn ($item) => $item['value'] !== '');

    $columnClass = match ((int) $columns) {
        2 => 'grid-cols-2',
        3 => 'grid-cols-3',
        4 => 'grid-cols-4',
        default => 'grid-cols-1',
    };
@endphp

<div class="grid gap-2 {{ $columnClass }}">
    @foreach($normalized as $option)
        <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-50 cursor-pointer">
            <input
                type="checkbox"
                name="{{ $name }}[]"
                value="{{ $option['value'] }}"
                @checked(in_array($option['value'], $selectedValues, true))
                class="rounded border-slate-300 text-teal-600 shadow-sm focus:ring-teal-500 w-4 h-4"
            >
            <span class="text-sm text-slate-700">{{ $option['label'] }}</span>
        </label>
    @endforeach
</div>
