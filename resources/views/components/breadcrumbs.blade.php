@props(['items' => []])

<nav {{ $attributes->merge(['class' => 'flex items-center text-sm text-slate-500 gap-1']) }}>
    
    <a href="{{ route('dashboard') }}" class="inline-flex items-center text-slate-600 hover:text-teal-600 transition-colors">
        <x-lucide-home class="w-4 h-4" />
    </a>

    @foreach($items as $label => $url)
        <x-lucide-chevron-right class="w-4 h-4 text-slate-300" />

        @if($loop->last || $url === '#')
            <span class="font-semibold text-teal-700">
                {{ $label }}
            </span>
        @else
            <a href="{{ $url }}" class="hover:text-teal-600 transition-colors">
                {{ $label }}
            </a>
        @endif
    @endforeach
</nav>