@props([
    'align' => 'left', // left, center, right
    'sortable' => false,
    'name' => null,
])

@php
    $textAlign = match ($align) {
        'center' => 'text-center',
        'right' => 'text-right',
        default => 'text-left',
    };

    $currentSort = request('sort');
    $currentDirection = request('direction', 'desc');
    $isActive = $currentSort === $name;
    
    $nextDirection = ($isActive && $currentDirection === 'asc') ? 'desc' : 'asc';
    
    $icon = 'chevrons-up-down';
    if ($isActive) {
        $icon = $currentDirection === 'asc' ? 'chevron-up' : 'chevron-down';
    }

    $url = $sortable && $name 
        ? request()->fullUrlWithQuery(['sort' => $name, 'direction' => $nextDirection]) 
        : '#';
@endphp

<th {{ $attributes->merge(['class' => "px-3 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 $textAlign"]) }}>
    @if($sortable && $name)
        <a href="{{ $url }}" class="group inline-flex items-center gap-1 cursor-pointer hover:text-slate-700 transition-colors">
            <span>{{ $slot }}</span>
            <x-dynamic-component 
                :component="'lucide-'.$icon" 
                class="w-3 h-3 {{ $isActive ? 'text-teal-600' : 'text-slate-300 group-hover:text-slate-400' }}" 
            />
        </a>
    @else
        {{ $slot }}
    @endif
</th>