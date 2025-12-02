@props(['name', 'options' => [], 'selected' => []])

@php
    // Pastikan selected adalah array
    $selected = is_array($selected) ? $selected : [];
@endphp

<div class="flex flex-wrap gap-2">
    @foreach($options as $value => $label)
        @php
            $isActive = in_array($value, $selected);
        @endphp
        <label class="relative">
            <input 
                type="checkbox" 
                name="{{ $name }}[]" 
                value="{{ $value }}" 
                class="peer sr-only"
                @checked($isActive)
            >
            <div class="px-3 py-1.5 rounded-full text-xs font-medium border transition-all cursor-pointer select-none
                {{ $isActive 
                    ? 'bg-teal-600 border-teal-600 text-white shadow-md shadow-teal-200' 
                    : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50' 
                }}
                peer-checked:bg-teal-600 peer-checked:border-teal-600 peer-checked:text-white
            ">
                {{ $label }}
            </div>
        </label>
    @endforeach
</div>