@props([
    'items' => [],
])

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
    @foreach($items as $item)
        @php
            $href = $item['href'] ?? '#';
        @endphp
        <a href="{{ $href }}"
           class="group flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm transition hover:border-slate-300 hover:shadow-md">
            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-50 text-slate-600">
                @if(!empty($item['icon']))
                    <x-dynamic-component :component="'lucide-' . $item['icon']" class="h-5 w-5" />
                @else
                    <x-lucide-arrow-right class="h-5 w-5" />
                @endif
            </span>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-slate-900">
                    {{ $item['title'] ?? '' }}
                </p>
                @if(!empty($item['description']))
                    <p class="truncate text-xs text-slate-500">
                        {{ $item['description'] }}
                    </p>
                @endif
            </div>
            <x-lucide-arrow-up-right
                class="h-4 w-4 text-slate-300 transition group-hover:text-slate-500" />
        </a>
    @endforeach
</div>
