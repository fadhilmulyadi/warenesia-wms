@props([
    'items' => [],
    'emptyTitle' => 'Tidak ada data',
    'emptyDescription' => null,
    'emptyIcon' => 'package-search',
])

<ul class="divide-y divide-slate-100">
    @forelse($items as $item)

        @if(!empty($item['href']))
            <a href="{{ $item['href'] }}" class="block hover:bg-slate-50 transition">
        @endif

        <li class="flex items-center gap-3 py-3 cursor-pointer">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-50 text-slate-500">
                @if(!empty($item['icon']))
                    <x-dynamic-component :component="'lucide-' . $item['icon']" class="h-5 w-5" />
                @else
                    <span class="h-2 w-2 rounded-full bg-slate-300"></span>
                @endif
            </div>

            <div class="min-w-0 flex-1">
                <p class="truncate text-base md:text-sm font-semibold text-slate-900">{{ $item['title'] ?? '' }}</p>

                @if(!empty($item['description']))
                    <p class="truncate text-sm md:text-xs text-slate-500">{{ $item['description'] }}</p>
                @endif
            </div>

            @if(!empty($item['meta']))
                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold leading-none {{ $item['meta_color'] ?? 'border-slate-200 bg-slate-50 text-slate-600' }}">
                    {{ $item['meta'] }}
                </span>
            @endif
        </li>

        @if(!empty($item['href']))
            </a>
        @endif

    @empty
        <li class="py-6">
            @isset($empty)
                {{ $empty }}
            @else
                <x-empty-state
                    :title="$emptyTitle"
                    :description="$emptyDescription"
                    :icon="$emptyIcon"
                    containerClass="border-2 border-dashed border-slate-200 bg-slate-50 rounded-xl py-6"
                />
            @endisset
        </li>
    @endforelse
</ul>
